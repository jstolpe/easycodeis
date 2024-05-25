<?php
	/**
	 * Class for handling all calls to the Twitch API
	 *
	 * @author Justin Stolpe
	 */
	class eciTwitchApi {
		/**
		 * @var api authorization domain
		 */
		const TWITTER_ID_DOMAIN = 'https://id.twitch.tv/';

		/**
		 * @var api endpoint calls domain
		 */
		const TWITTER_API_DOMAIN = 'https://api.twitch.tv/helix/';

		/**
		 * @var client id
		 */
		private $_clientId;

		/**
		 * @var client secret
		 */
		private $_clientSecret;

		/**
		 * @var access token
		 */
		private $_accessToken;

		/**
		 * @var refresh token
		 */
		private $_refreshToken;

		/**
		 * Constructor for this class
		 *
		 * @param string $clientId twitch client id
		 * @param string $clientSecret twitch client secret
		 * @param string $accessToken twitch access token
		 *
		 * @return void
		 */
		public function __construct( $clientId, $clientSecret, $accessToken = '' ) {
			// set client id
			$this->_clientId = $clientId;

			// set client secret
			$this->_clientSecret = $clientSecret;

			// set access token
			$this->_accessToken = $accessToken;
		}

		/**
		 * Get the login url
		 *
		 * @param array $redirectUri
		 *
		 * @return string
		 */
		public function getLoginUrl( $redirectUri ) {
			// request endpoint
			$endpoint = self::TWITTER_ID_DOMAIN . 'oauth2/authorize';

			// store state so we can check it once the user comes back to our redirect uri
			$_SESSION['twitch_state'] = md5( microtime() . mt_rand() );

			$params = array( // params for endpoint
				'client_id' => $this->_clientId,
				'redirect_uri' => $redirectUri,
				'response_type' => 'code',
				'scope' => 'user:read:email',
				'state' => $_SESSION['twitch_state']
			);

			// add params to endpoint and return the login url
			return $endpoint . '?' . http_build_query( $params );
		}

		/**
		 * Try and log a user in with Twitch
		 *
		 * @param string $code code from Twitch
		 * @param string $redirectUri redirect uri
		 *
		 * @return array
		 */
		public function tryAndLoginWithTwitch( $code, $redirectUri ) {
			// get access token
			$accessToken = $this->getTwitchAccessToken( $code, $redirectUri );

			// save status and message from access token call
			$status = $accessToken['status'];
			$message = $accessToken['message'];

			if ( 'ok' == $status ) { // we got an access token1
				// set access token and refresh token class vars 
				$this->_accessToken = $accessToken['api_data']['access_token'];
				$this->_refreshToken = $accessToken['api_data']['refresh_token'];

				// get user info
				$userInfo = $this->getUserInfo();

				// save status and message from get user info call
				$status = $userInfo['status'];
				$message = $userInfo['message'];
				
				if ( 'ok' == $userInfo['status'] && isset( $userInfo['api_data']['data'][0] ) ) { // we have user info!
					// log user in with info from get user info api call
					$this->_logUserInWithTwitch( $userInfo['api_data']['data'][0] );
				}
			}

			return array( // return status and message of login
				'status' => $status,
				'message' => $message
			);
		}

		/**
		 * Log a user in
		 *
		 * @param array $apiUserInfo user info from Twitch
		 *
		 * @return void
		 */
		private function _logUserInWithTwitch( $apiUserInfo ) {
			// save user info and tokens in the session
			$_SESSION['twitch_user_info'] = $apiUserInfo;
			$_SESSION['twitch_user_info']['access_token'] = $this->_accessToken;
			$_SESSION['twitch_user_info']['refresh_token'] = $this->_refreshToken;

			// boolean if user has signed up on our site before using social to login
			$_SESSION['eci_login_required_to_connect_twitch'] = false;
		
			// check for user with twitch id
			$userInfoWithId = getRowWithValue( 'easycodeis_users', 'twitch_user_id', $apiUserInfo['id'] );

			// check for user with email
			$userInfoWithEmail = getRowWithValue( 'easycodeis_users', 'email', $apiUserInfo['email'] );
		
			if ( $userInfoWithId || ( $userInfoWithEmail && !$userInfoWithEmail['password'] ) ) { // user was found by email/id log them in
				// get user id
				$userId = $userInfoWithId ? $userInfoWithId['id'] : $userInfoWithEmail['id'];

				// save twitch id and tokens to the user
				updateRow( 'easycodeis_users', 'twitch_user_id', $apiUserInfo['id'], $userId );
				updateRow( 'easycodeis_users', 'twitch_access_token', $this->_accessToken, $userId );
				updateRow( 'easycodeis_users', 'twitch_refresh_token', $this->_refreshToken, $userId );

				// get user info
				$userInfo = getRowWithValue( 'easycodeis_users', 'id', $userId );

				// update session so the user is logged in
				$_SESSION['is_logged_in'] = true;
				$_SESSION['user_info'] = $userInfo;
			} elseif ( $userInfoWithEmail && !$userInfoWithEmail['twitch_user_id'] ) { // user needs to enter their password to connect the account
				$_SESSION['eci_login_required_to_connect_twitch'] = true;
			} else { // user was not found in our database, sign them up and log them in
				$signupUserInfo = array( // data we need to insert the user in our database
					'email' => $apiUserInfo['email'], // email from Twitch response
					'first_name' => $apiUserInfo['display_name'], // using display_name as first name cause not first name in Twitch response
					'last_name' => '', // no last name in Twitch response
					'twitch_user_id' => $apiUserInfo['id'], // Twitch id from Twitch response
					'twitch_access_token' => $this->_accessToken, // access token from Twitch response
					'twitch_refresh_token' => $this->_refreshToken // refresh token from Twitch response
				);

				// sign user up
				$userId = signUserUp( $signupUserInfo );

				// get user info
				$userInfo = getRowWithValue( 'easycodeis_users', 'id', $userId );

				// update session so the user is logged in
				$_SESSION['is_logged_in'] = true;
				$_SESSION['user_info'] = $userInfo;
			}
		}

		/**
		 * Get a users info from Twitch
		 *
		 * @param void
		 *
		 * @return array
		 */
		public function getUserInfo() {
			// requet endpoint
			$endpoint = self::TWITTER_API_DOMAIN . 'users';

			$apiParams = array( // params for our api call
				'endpoint' => $endpoint,
				'type' => 'GET',
				'authorization' => $this->getAuthorizationHeaders(),
				'url_params' => array()
			);

			// make api call and return response
			return $this->makeApiCall( $apiParams );
		}

		/**
		 * Get authorization header for api call
		 *
		 * @param void
		 *
		 * @return array
		 */
		public function getAuthorizationHeaders() {
			return array( // this array will be used as the header for the api call
				'Client-ID: ' . $this->_clientId,
				'Authorization: Bearer ' . $this->_accessToken
			);
		}

		/**
		 * Get access token
		 *
		 * @param string $code code from Twitch
		 * @param string $redirectUri redirect uri
		 *
		 * @return array
		 */
		public function getTwitchAccessToken( $code, $redirectUri ) {
			// requet endpoint
			$endpoint = self::TWITTER_ID_DOMAIN . 'oauth2/token';

			$apiParams = array( // params for our api call
				'endpoint' => $endpoint,
				'type' => 'POST',
				'url_params' => array(
					'client_id' => $this->_clientId,
					'client_secret' => $this->_clientSecret,
					'code' => $code,
					'grant_type' => 'authorization_code',
					'redirect_uri' => $redirectUri
				)
			);

			// make api call and return response
			return $this->makeApiCall( $apiParams );
		}

		/**
		 * Make calls to the Twitch API
		 *
		 * @param array $params
		 *
		 * @return array
		 */
		public function makeApiCall( $params ) {
			$curlOptions = array( // curl options
				CURLOPT_URL => $params['endpoint'], // endpoint
				CURLOPT_CAINFO => PATH_TO_CERT, // ssl certificate
				CURLOPT_RETURNTRANSFER => TRUE, // return stuff!
				CURLOPT_SSL_VERIFYPEER => TRUE, // verify peer
				CURLOPT_SSL_VERIFYHOST => 2, // verify host
			);

			if ( isset( $params['authorization'] ) ) { // we need to pass along headers with the request
				$curlOptions[CURLOPT_HEADER] = TRUE;
				$curlOptions[CURLOPT_HTTPHEADER] = $params['authorization'];
			}

			if ( 'POST' == $params['type'] ) { // post request things
				$curlOptions[CURLOPT_POST] = TRUE;
                $curlOptions[CURLOPT_POSTFIELDS] = http_build_query( $params['url_params'] );
			} elseif ( 'GET' == $params['type'] ) { // get request things
				$curlOptions[CURLOPT_URL] .= '?' . http_build_query( $params['url_params'] );
			}

			// initialize curl
			$ch = curl_init();

			// set curl options
			curl_setopt_array( $ch, $curlOptions );

			// make call
			$apiResponse = curl_exec( $ch );

			if ( isset( $params['authorization'] ) ) { // we have headers to deal with
				// get size of header
				$headerSize = curl_getinfo( $ch, CURLINFO_HEADER_SIZE );

				// remove header from response so we are left with json body
				$apiResponseBody = substr( $apiResponse, $headerSize );

				// json decode response body
				$apiResponse = json_decode( $apiResponseBody, true );	
			} else { // no headers response is json string
				// json decode response body
				$apiResponse = json_decode( $apiResponse, true );
			}

			// close curl
			curl_close( $ch );

			return array(
				'status' => isset( $apiResponse['status'] ) ? 'fail' : 'ok', // if status then there was an error
				'message' => isset( $apiResponse['message'] ) ? $apiResponse['message'] : '', // if message return it
				'api_data' => $apiResponse, // api response data
				'endpoint' => $curlOptions[CURLOPT_URL], // endpoint hit
				'url_params' => $params['url_params'] // url params sent with the request
			);
		}
	}