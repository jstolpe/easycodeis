<?php
	class eciTwitterApi {
		const TWITTER_API_VERSION = '1.1';
		const TWITTER_API_DOMAIN = 'https://api.twitter.com/';

		private $_consumerKey;
		private $_consumerSecret;
		private $_oauthToken;
		private $_oauthTokenSecret;

		public function __construct( $consumerKey, $consumerSecret, $oauthToken = '', $oauthTokenSecret = '' ) {
			$this->_consumerKey = $consumerKey;
			$this->_consumerSecret = $consumerSecret;
			$this->_oauthToken = $oauthToken;
			$this->_oauthTokenSecret = $oauthTokenSecret;
		}

		public function getDataForLogin( $callbackUrl ) {
			$requestToken = $this->getRequestToken( $callbackUrl );
			$requestToken['twitter_login_url'] = $this->getLoginUrl( $requestToken );

			if ( 'ok' == $requestToken['status'] ) {
				$_SESSION['request_oauth_token'] = $requestToken['api_data']['oauth_token'];
				$_SESSION['request_oauth_token_secret'] = $requestToken['api_data']['oauth_token_secret'];
			}

			return $requestToken;
		}

		public function getLoginUrl( $requestToken ) {
			$loginUrl = '';

			if ( 'ok' == $requestToken['status'] ) {
				$endpoint = self::TWITTER_API_DOMAIN . 'oauth/authorize';
				$params = array(
					'oauth_token' => $requestToken['api_data']['oauth_token']
				);
				$loginUrl = $endpoint . '?' . http_build_query( $params );
			}

			return $loginUrl;
		}

		public function getUserInfo() {
			$method = 'GET';
			$endpoint = self::TWITTER_API_DOMAIN . self::TWITTER_API_VERSION . '/account/verify_credentials.json';
			$urlParams = array(
				'include_email' => 'true'
			);
			$authorizationParams = array(
				'oauth_version' => '1.0',
				'oauth_nonce' => md5( microtime() . mt_rand() ),
				'oauth_timestamp' => time(),
				'oauth_consumer_key' => $this->_consumerKey,
				'oauth_token' => $this->_oauthToken,
				'oauth_signature_method' => 'HMAC-SHA1'
			);
			$authorizationParams['oauth_signature'] = $this->getSignature( $method, $endpoint, $authorizationParams, $urlParams );

			$apiParams = array(
				'method' => $method,
				'endpoint' => $endpoint,
				'authorization' => $this->getAuthorizationString( $authorizationParams ),
				'url_params' => $urlParams
			);

			return $this->makeApiCall( $apiParams );
		}

		public function getAccessToken( $oauthVerifier ) {
			$method = 'POST';
			$endpoint = self::TWITTER_API_DOMAIN . 'oauth/access_token';

			$authorizationParams = array(
				'oauth_version' => '1.0',
				'oauth_nonce' => md5( microtime() . mt_rand() ),
				'oauth_timestamp' => time(),
				'oauth_consumer_key' => $this->_consumerKey,
				'oauth_token' => $this->_oauthToken,
				'oauth_verifier' => $oauthVerifier,
				'oauth_signature_method' => 'HMAC-SHA1'
			);
			$authorizationParams['oauth_signature'] = $this->getSignature( $method, $endpoint, $authorizationParams );

			$apiParams = array(
				'method' => $method,
				'endpoint' => $endpoint,
				'authorization' => $this->getAuthorizationString( $authorizationParams ),
				'url_params' => array()
			);

			return $this->makeApiCall( $apiParams );
		}

		public function getRequestToken( $callbackUrl ) {
			$method = 'POST';
			$endpoint = self::TWITTER_API_DOMAIN . 'oauth/request_token';

			$authorizationParams = array(
				'oauth_callback' => $callbackUrl,
				'oauth_consumer_key' => $this->_consumerKey,
				'oauth_nonce' => md5( microtime() . mt_rand() ),
				'oauth_signature_method' => 'HMAC-SHA1',
				'oauth_timestamp' => time(),
				'oauth_version' => '1.0'
			);
			$authorizationParams['oauth_signature'] = $this->getSignature( $method, $endpoint, $authorizationParams );

			$apiParams = array(
				'method' => $method,
				'endpoint' => $endpoint,
				'authorization' => $this->getAuthorizationString( $authorizationParams ),
				'url_params' => array()
			);

			return $this->makeApiCall( $apiParams );
		}

		public function makeApiCall( $apiParams ) {
			$curlOptions = array(
				CURLOPT_URL => $apiParams['endpoint'],
				CURLOPT_CAINFO => PATH_TO_CERT,
				CURLOPT_RETURNTRANSFER => TRUE,
				CURLOPT_SSL_VERIFYPEER => TRUE,
				CURLOPT_SSL_VERIFYHOST => 2,
				CURLOPT_HEADER => TRUE,
				CURLOPT_HTTPHEADER => array(
					'Accept: application/json',
					$apiParams['authorization'],
					'Expect:'
				)
			);

			if ( 'POST' == $apiParams['method'] ) {
				$curlOptions[CURLOPT_POST] = TRUE;
				$curlOptions[CURLOPT_POSTFIELDS] = http_build_query( $apiParams['url_params'] );
			} elseif ( 'GET' == $apiParams['method'] ) {
				$curlOptions[CURLOPT_URL] .= '?' . http_build_query( $apiParams['url_params'] );
			}

			$ch = curl_init();
			curl_setopt_array( $ch, $curlOptions );
			$apiResponse = curl_exec( $ch );
			$responseParts = explode( "\r\n\r\n", $apiResponse );
			$responseBody = array_pop( $responseParts );
			$responseBodyJson = json_decode( $responseBody );

			if ( json_last_error() == JSON_ERROR_NONE ) {
				$response = json_decode( $responseBody, true );
			} else {
				parse_str( $responseBody, $response );
			}

			if ( 200 == curl_getinfo( $ch, CURLINFO_HTTP_CODE ) ) {
				$status = 'ok';
				$message = '';
			} else {
				$status = 'fail';
				$message = isset( $response['errors'][0]['message'] ) ? $response['errors'][0]['message'] : 'Unauthorized';
			}

			curl_close( $ch );

			return array(
				'status' => $status,
				'message' => $message,
				'api_data' => $response,
				'endpoint' => $curlOptions[CURLOPT_URL],
				'authorization' => $apiParams['authorization']
			);
		}

		public function getSignature( $method, $endpoint, $authorizationParams, $urlParams = array() ) {
			$authorizationParams = array_merge( $authorizationParams, $urlParams );

			uksort( $authorizationParams, 'strcmp' );

			foreach ( $authorizationParams as $key => $value ) {
				$authorizationParams[$key] = rawurlencode( $key ) . '=' . rawurlencode( $value );
			}

			$signatureBase = array(
				rawurlencode( $method ),
				rawurlencode( $endpoint ),
				rawurlencode( implode( '&', $authorizationParams ) ),
			);
			$signatureBaseString = implode( '&', $signatureBase );

			$signatureKey = array(
				rawurlencode( $this->_consumerSecret ),
				$this->_oauthTokenSecret ? rawurlencode( $this->_oauthTokenSecret ) : ''
			);
			$signatureKeyString = implode( '&', $signatureKey );

			return base64_encode( hash_hmac( 'sha1', $signatureBaseString, $signatureKeyString, true ) );
		}

		public function getAuthorizationString( $authorizationParams ) {
			$authorizationString = 'Authorization: OAuth';
			$count = 0;

			foreach ( $authorizationParams as $key => $value ) {
				$authorizationString .= !$count ? ' ' : ', ';
				$authorizationString .= rawurlencode( $key ) . '="' . rawurlencode( $value ) . '"';
				$count++;
			}

			return $authorizationString;
		}

		public function tryAndLoginWithTwitter() {
			$twitterUserInfo = $this->getUserInfo();
			$_SESSION['eci_login_required_to_connect_twitter'] = false;

			if ( 'fail' == $twitterUserInfo['status'] ) {
				$status = 'fail';
				$message = $twitterUserInfo['message'];
				unset( $_SESSION['oauth_token'] );
				unset( $_SESSION['oauth_token_secret'] );
			} elseif ( 'ok' == $twitterUserInfo['status'] ) {
				$status = 'ok';
				$message = '';

				if ( !empty( $twitterUserInfo['api_data']['id'] ) && !empty( $twitterUserInfo['api_data']['email'] ) ) {
					$_SESSION['tw_user_info'] = $twitterUserInfo['api_data'];

					$userInfoWithId = getRowWithValue( 'users', 'tw_user_id', $twitterUserInfo['api_data']['id'] );

					$userInfoWithEmail = getRowWithValue( 'users', 'email', $twitterUserInfo['api_data']['email'] );

					if ( $userInfoWithId || ( $userInfoWithEmail && !$userInfoWithEmail['password'] ) ) {
						$userId = $userInfoWithId ? $userInfoWithId['id'] : $userInfoWithEmail['id'];
						updateRow( 'users', 'oauth_token', $_SESSION['oauth_token'], $userId );
						updateRow( 'users', 'oauth_token_secret', $_SESSION['oauth_token_secret'], $userId );
						updateRow( 'users', 'tw_user_id', $_SESSION['tw_user_info']['id'], $userId );
						$userInfo = getRowWithValue( 'users', 'id', $userId );

						$_SESSION['is_logged_in'] = true;
						$_SESSION['user_info'] = $userInfo;
					} elseif ( $userInfoWithEmail && !$userInfoWithEmail['tw_user_id'] ) {
						$userId = $userInfoWithEmail['id'];
						$_SESSION['eci_login_required_to_connect_twitter'] = true;
					} else {
						$name = explode( ' ', $twitterUserInfo['api_data']['name'] );
						$signupUserInfo = array(
							'email' => $twitterUserInfo['api_data']['email'],
							'first_name' => isset( $name[0] ) ? $name[0] : '',
							'last_name' => isset( $name[1] ) ? $name[1] : '',
							'tw_user_id' => $twitterUserInfo['api_data']['id'],
							'oauth_token' => $_SESSION['oauth_token'],
							'oauth_token_secret' => $_SESSION['oauth_token_secret'],
						);
						$userId = signUserUp( $signupUserInfo );
						$userInfo = getRowWithValue( 'users', 'id', $userId );

						$_SESSION['is_logged_in'] = true;
						$_SESSION['user_info'] = $userInfo;
					}
				}
			}

			return array(
				'status' => $status,
				'message' => $message
			);
		}
	}