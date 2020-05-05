<?php
	/**
	 * Class for handling all calls to the Twitter API
	 *
	 * @author Justin Stolpe
	 */
	class eciTwitterApi {
		/**
		 * @var api version
		 */
		const TWITTER_API_VERSION = '1.1';

		/**
		 * @var api version
		 */
		const TWITTER_API_DOMAIN = 'https://api.twitter.com/';

		/**
		 * @var consumer key
		 */
		private $_consumerKey;

		/**
		 * @var consumer secret
		 */
		private $_consumerSecret;

		/**
		 * @var oauth token
		 */
		private $_oauthToken;

		/**
		 * @var oauth token secret
		 */
		private $_oauthTokenSecret;

		/**
		 * Constructor for this class
		 *
		 * @param string $consumerKey twitter consumer key
		 * @param string $consumerSecret twitter consumer secret
		 * @param string $oauthToken twitter token
		 * @param string $oauthTokenSecret twitter token secret
		 *
		 * @return void
		 */
		public function __construct( $consumerKey, $consumerSecret, $oauthToken = '', $oauthTokenSecret = '' ) {
			// set consumer key
			$this->_consumerKey = $consumerKey;

			// set consumer secret
			$this->_consumerSecret = $consumerSecret;

			// set oauth token
			$this->_oauthToken = $oauthToken;

			// set oauth secret
			$this->_oauthTokenSecret = $oauthTokenSecret;
		}

		/**
		 * Get data for our login page
		 *
		 * @param string $callbackUrl
		 *
		 * @return array
		 */
		public function getDataForLogin( $callbackUrl ) {
			// get a request token from twitter
			$requestToken = $this->getRequestToken( $callbackUrl );

			// get login url
			$requestToken['twitter_login_url'] = $this->getLoginUrl( $requestToken );

			if ( 'ok' == $requestToken['status'] ) { // save twitter token info to the session
				$_SESSION['request_oauth_token'] = $requestToken['api_data']['oauth_token'];
				$_SESSION['request_oauth_token_secret'] = $requestToken['api_data']['oauth_token_secret'];
			}

			// return request token and login url
			return $requestToken;
		}

		/**
		 * Get the login url
		 *
		 * @param array $requestToken
		 *
		 * @return string
		 */
		public function getLoginUrl( $requestToken ) {
			// intialize login url
			$loginUrl = '';

			if ( 'ok' == $requestToken['status'] ) {
				// request endpoint
				$endpoint = self::TWITTER_API_DOMAIN . 'oauth/authorize';

				$params = array( // url params
					'oauth_token' => $requestToken['api_data']['oauth_token']
				);

				// add url params to endpoint
				$loginUrl = $endpoint . '?' . http_build_query( $params );
			}

			// return login url
			return $loginUrl;
		}

		/**
		 * Get a users info from twitter
		 *
		 * @param void
		 *
		 * @return array
		 */
		public function getUserInfo() {
			// request method
			$method = 'GET';

			// requet endpoint
			$endpoint = self::TWITTER_API_DOMAIN . self::TWITTER_API_VERSION . '/account/verify_credentials.json';

			$urlParams = array( // url params for endpoint
				'include_email' => 'true'
			);
			$authorizationParams = array( // authorization parameters required by twitter
				'oauth_version' => '1.0', // oauth version
				'oauth_nonce' => md5( microtime() . mt_rand() ), // nonce
				'oauth_timestamp' => time(), // timestamp
				'oauth_consumer_key' => $this->_consumerKey,  // consumer key
				'oauth_token' => $this->_oauthToken, // oauth token
				'oauth_signature_method' => 'HMAC-SHA1' // signature method
			);

			// get signature
			$authorizationParams['oauth_signature'] = $this->getSignature( $method, $endpoint, $authorizationParams, $urlParams );

			$apiParams = array( // params for our api call
				'method' => $method, // request method
				'endpoint' => $endpoint, // endpoint
				'authorization' => $this->getAuthorizationString( $authorizationParams ),  // generate authorization string
				'url_params' => $urlParams // url params for endpoint
			);

			// make api call and return response
			return $this->makeApiCall( $apiParams );
		}

		/**
		 * Get an access token from twitter for a user
		 *
		 * @param string $oauthVerifier
		 *
		 * @return array
		 */
		public function getAccessToken( $oauthVerifier ) {
			// request method
			$method = 'POST';

			// requet endpoint
			$endpoint = self::TWITTER_API_DOMAIN . 'oauth/access_token';

			$authorizationParams = array( // authorization parameters required by twitter
				'oauth_version' => '1.0', // oauth version
				'oauth_nonce' => md5( microtime() . mt_rand() ),
				'oauth_timestamp' => time(), // nonce
				'oauth_consumer_key' => $this->_consumerKey, // consumer key
				'oauth_token' => $this->_oauthToken, // oauth token
				'oauth_verifier' => $oauthVerifier, // oauth verifier
				'oauth_signature_method' => 'HMAC-SHA1' // signature method
			);

			// get signature
			$authorizationParams['oauth_signature'] = $this->getSignature( $method, $endpoint, $authorizationParams );

			$apiParams = array( // params for our api call
				'method' => $method, // request method
				'endpoint' => $endpoint, // endpoint
				'authorization' => $this->getAuthorizationString( $authorizationParams ), // generate authorization string
				'url_params' => array() // url params for endpoint
			);

			// make api call and return response
			return $this->makeApiCall( $apiParams );
		}

		/**
		 * Get a request token for logging a user in with twitter
		 *
		 * @param string $callbackUrl
		 *
		 * @return array
		 */
		public function getRequestToken( $callbackUrl ) {
			// request method
			$method = 'POST';

			// requet endpoint
			$endpoint = self::TWITTER_API_DOMAIN . 'oauth/request_token';

			$authorizationParams = array( // authorization parameters required by twitter
				'oauth_callback' => $callbackUrl,  // callback url
				'oauth_consumer_key' => $this->_consumerKey, // consumer key
				'oauth_nonce' => md5( microtime() . mt_rand() ), // nonce
				'oauth_signature_method' => 'HMAC-SHA1', // signature method
				'oauth_timestamp' => time(), // timestamp
				'oauth_version' => '1.0' // oauth version
			);

			// get signature
			$authorizationParams['oauth_signature'] = $this->getSignature( $method, $endpoint, $authorizationParams );

			$apiParams = array( // params for our api call
				'method' => $method, // request method
				'endpoint' => $endpoint, // requet endpoint
				'authorization' => $this->getAuthorizationString( $authorizationParams ), // generate authorization string
				'url_params' => array() // url params for endpoint
			);

			// make api call and return response
			return $this->makeApiCall( $apiParams );
		}

		/**
		 * Make calls to the twitter API
		 *
		 * @param array $apiParams
		 *
		 * @return array
		 */
		public function makeApiCall( $apiParams ) {
			$curlOptions = array( // curl options
				CURLOPT_URL => $apiParams['endpoint'], // endpoint
				CURLOPT_CAINFO => PATH_TO_CERT, // ssl certificate
				CURLOPT_RETURNTRANSFER => TRUE, // return stuff!
				CURLOPT_SSL_VERIFYPEER => TRUE, // verify peer
				CURLOPT_SSL_VERIFYHOST => 2, // verify host
				CURLOPT_HEADER => TRUE, // sending headers
				CURLOPT_HTTPHEADER => array( // headers required by twitter
					'Accept: application/json', // json
					$apiParams['authorization'], // authorization
					'Expect:'
				)
			);

			if ( 'POST' == $apiParams['method'] ) { // post request things
				$curlOptions[CURLOPT_POST] = TRUE;
				$curlOptions[CURLOPT_POSTFIELDS] = http_build_query( $apiParams['url_params'] );
			} elseif ( 'GET' == $apiParams['method'] ) { // get request things
				$curlOptions[CURLOPT_URL] .= '?' . http_build_query( $apiParams['url_params'] );
			}

			// initialize curl
			$ch = curl_init();

			// set curl options
			curl_setopt_array( $ch, $curlOptions );

			// make call
			$apiResponse = curl_exec( $ch );

			// get response parts
			$responseParts = explode( "\r\n\r\n", $apiResponse );

			// body contains the good stuff
			$responseBody = array_pop( $responseParts );

			// json decode body
			$responseBodyJson = json_decode( $responseBody );

			if ( json_last_error() == JSON_ERROR_NONE ) { // decode json string
				$response = json_decode( $responseBody, true );
			} else { // parse str to response
				parse_str( $responseBody, $response );
			}

			if ( 200 == curl_getinfo( $ch, CURLINFO_HTTP_CODE ) ) { // twitter tells us to check for code 200
				// all good
				$status = 'ok';

				// no message
				$message = '';
			} else {
				// not all good
				$status = 'fail';

				// error message
				$message = isset( $response['errors'][0]['message'] ) ? $response['errors'][0]['message'] : 'Unauthorized';
			}

			// close curl
			curl_close( $ch );

			return array( // return array
				'status' => $status, // status
				'message' => $message,  // message
				'api_data' => $response, // api response
				'endpoint' => $curlOptions[CURLOPT_URL], // endpoint hit
				'authorization' => $apiParams['authorization'] // authorrization headers
			);
		}

		/**
		 * Twitter requires a signature for each API request. The signature must be base64 endcoded HMAC-SHA1
		 *
		 * @param string $method request method (GET,POST)
		 * @param string $endpoint Twitter API endpoint for the request
		 * @param array $authorizationParams authorization params required for the endpoint
		 * @param array $urlParams url params for the request
		 *
		 * @return string
		 */
		public function getSignature( $method, $endpoint, $authorizationParams, $urlParams = array() ) {
			// url params need to be included in the signature
			$authorizationParams = array_merge( $authorizationParams, $urlParams );

			// make sure to sort
			uksort( $authorizationParams, 'strcmp' );

			foreach ( $authorizationParams as $key => $value ) { // encode keys and params
				$authorizationParams[$key] = rawurlencode( $key ) . '=' . rawurlencode( $value );
			}

			$signatureBase = array( // signature base array
				rawurlencode( $method ), // encoded method
				rawurlencode( $endpoint ), // encoded endpoint
				rawurlencode( implode( '&', $authorizationParams ) ), // authorization params delimited by '&'
			);

			// create the signature base string delimited by '&'
			$signatureBaseString = implode( '&', $signatureBase );

			$signatureKey = array( // signature key
				rawurlencode( $this->_consumerSecret ), // encoded consumer secret
				$this->_oauthTokenSecret ? rawurlencode( $this->_oauthTokenSecret ) : '' // endocded access token if we have one
			);

			// create the signature key string delimited by '&'
			$signatureKeyString = implode( '&', $signatureKey );

			// return base64 encoded hmac as required by twitter
			return base64_encode( hash_hmac( 'sha1', $signatureBaseString, $signatureKeyString, true ) );
		}

		/**
		 * Create an authroirzation string from the array of authorization key/value pairs
		 *
		 * @param array $authorizationParams authorization params required for the endpoint
		 *
		 * @return string
		 */
		public function getAuthorizationString( $authorizationParams ) {
			// initialize string
			$authorizationString = 'Authorization: OAuth';

			// store count
			$count = 0;

			foreach ( $authorizationParams as $key => $value ) { // loop over authorization params array
				// if count is not zero append a comma each time
				$authorizationString .= !$count ? ' ' : ', ';

				// encode key/value as required by twitter
				$authorizationString .= rawurlencode( $key ) . '="' . rawurlencode( $value ) . '"';

				// increment count
				$count++;
			}

			return $authorizationString;
		}

		/**
		 * Try and log a user in with Twitter
		 *
		 * @param void
		 *
		 * @return string
		 */
		public function tryAndLoginWithTwitter() {
			// get user info
			$twitterUserInfo = $this->getUserInfo();

			// boolean if user has signed up on our site before using social to login
			$_SESSION['eci_login_required_to_connect_twitter'] = false;

			if ( 'fail' == $twitterUserInfo['status'] ) { // something failed
				// save status ane message
				$status = 'fail';
				$message = $twitterUserInfo['message'];

				// unset session tokens
				unset( $_SESSION['oauth_token'] );
				unset( $_SESSION['oauth_token_secret'] );
			} elseif ( 'ok' == $twitterUserInfo['status'] ) { // we got user info from twitter api
				// status/message
				$status = 'ok';
				$message = '';

				if ( !empty( $twitterUserInfo['api_data']['id'] ) && !empty( $twitterUserInfo['api_data']['email'] ) ) { // twitter gave us an id and email address
					// save twitter user info in the session
					$_SESSION['tw_user_info'] = $twitterUserInfo['api_data'];

					// check for user with twitter id
					$userInfoWithId = getRowWithValue( 'users', 'tw_user_id', $twitterUserInfo['api_data']['id'] );

					// check for user with twitter eemail
					$userInfoWithEmail = getRowWithValue( 'users', 'email', $twitterUserInfo['api_data']['email'] );

					if ( $userInfoWithId || ( $userInfoWithEmail && !$userInfoWithEmail['password'] ) ) { // user was found by email/id log them in
						// get user id
						$userId = $userInfoWithId ? $userInfoWithId['id'] : $userInfoWithEmail['id'];

						// save twitter id and token to our database
						updateRow( 'users', 'oauth_token', $_SESSION['oauth_token'], $userId );
						updateRow( 'users', 'oauth_token_secret', $_SESSION['oauth_token_secret'], $userId );
						updateRow( 'users', 'tw_user_id', $_SESSION['tw_user_info']['id'], $userId );

						// get user info
						$userInfo = getRowWithValue( 'users', 'id', $userId );

						// update session so the user is logged in
						$_SESSION['is_logged_in'] = true;
						$_SESSION['user_info'] = $userInfo;
					} elseif ( $userInfoWithEmail && !$userInfoWithEmail['tw_user_id'] ) { // user needs to enter their password to connect the account
						// get user id
						$userId = $userInfoWithEmail['id'];

						// update boolean so we can prompt user to enter password
						$_SESSION['eci_login_required_to_connect_twitter'] = true;
					} else { // user was not found in our database, sign them up and log them in
						// explode name on space for first/last name
						$name = explode( ' ', $twitterUserInfo['api_data']['name'] );
						$signupUserInfo = array( // data we need to insert the user in our database
							'email' => $twitterUserInfo['api_data']['email'], // email address from twitter response
							'first_name' => isset( $name[0] ) ? $name[0] : '', // first name from twitter response
							'last_name' => isset( $name[1] ) ? $name[1] : '', // last name from twitter response
							'tw_user_id' => $twitterUserInfo['api_data']['id'], // twitter user id from twitter response
							'oauth_token' => $_SESSION['oauth_token'], // twitter oauth token
							'oauth_token_secret' => $_SESSION['oauth_token_secret'], // twitter oauth token secret
						);

						// sign user up
						$userId = signUserUp( $signupUserInfo );

						// get user info
						$userInfo = getRowWithValue( 'users', 'id', $userId );

						// update session so the user is logged in
						$_SESSION['is_logged_in'] = true;
						$_SESSION['user_info'] = $userInfo;
					}
				}
			}

			return array( // return status and message of login
				'status' => $status,
				'message' => $message
			);
		}
	}