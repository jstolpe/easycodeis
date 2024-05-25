<?php
	/**
	 * Make call to facebook endpoint
	 *
	 * @param string $endpoint make call to this enpoint
	 * @param array $params array keys are the variable names required by the endpoint
	 *
	 * @return array $response
	 */
	function makeFacebookApiCall( $endpoint, $params ) {
		// open curl call, set endpoint and other curl params
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $endpoint . '?' . http_build_query( $params ) );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );

		// get curl response, json decode it, and close curl
		$fbResponse = curl_exec( $ch );
		$fbResponse = json_decode( $fbResponse, TRUE );
		curl_close( $ch );

		return array( // return response data
			'endpoint' => $endpoint,
			'params' => $params,
			'has_errors' => isset( $fbResponse['error'] ) ? TRUE : FALSE, // boolean for if an error occured
			'error_message' => isset( $fbResponse['error'] ) ? $fbResponse['error']['message'] : '', // error message
			'fb_response' => $fbResponse // actual response from the call
		);
	}

	/**
	 * Get facebook api login url that will take the user to facebook and present them with login dialog
	 *
	 * Endpoint: https://www.facebook.com/{fb-graph-api-version}/dialog/oauth?client_id={app-id}&redirect_uri={redirect-uri}&state={state}&scope={scope}&auth_type={auth-type}
	 *
	 * @param void
	 *
	 * @return string
	 */
	function getFacebookLoginUrl() {
		// endpoint for facebook login dialog
		$endpoint = 'https://www.facebook.com/' . FB_GRAPH_VERSION . '/dialog/oauth';

		$params = array( // login url params required to direct user to facebook and promt them with a login dialog
			'client_id' => FB_APP_ID,
			'redirect_uri' => FB_REDIRECT_URI,
			'state' => FB_APP_STATE,
			'scope' => 'email',
			'auth_type' => 'rerequest'
		);

		// return login url
		return $endpoint . '?' . http_build_query( $params );
	}

	/**
	 * Get an access token with the code from facebook
	 *
	 * Endpoint https://graph.facebook.com/{fb-graph-version}/oauth/access_token?client_id{app-id}&client_secret={app-secret}&redirect_uri={redirect_uri}&code={code}
	 *
	 * @param string $code
	 *
	 * @return array $response
	 */
	function getAccessTokenWithCode( $code ) {
		// endpoint for getting an access token with code
		$endpoint = FB_GRAPH_DOMAIN . FB_GRAPH_VERSION . '/oauth/access_token';

		$params = array( // params for the endpoint
			'client_id' => FB_APP_ID,
			'client_secret' => FB_APP_SECRET,
			'redirect_uri' => FB_REDIRECT_URI,
			'code' => $code
		);

		// make the api call
		return makeFacebookApiCall( $endpoint, $params );
	}

	/**
	 * Get a users facebook info
	 *
	 * Endpoint https://graph.facebook.com/me?fields={fields}&access_token={access-token}
	 *
	 * @param string $accessToken
	 *
	 * @return array $response
	 */
	function getFacebookUserInfo( $accessToken ) {
		// endpoint for getting a users facebook info
		$endpoint = FB_GRAPH_DOMAIN . 'me';

		$params = array( // params for the endpoint
			'fields' => 'first_name,last_name,email,picture',
			'access_token' => $accessToken
		);

		// make the api call
		return makeFacebookApiCall( $endpoint, $params );
	}

	/**
	 * Try and log a user in with facebook
	 *
	 * @param array $get contains the url $_GET variables from the redirect uri after user authenticates with facebook
	 *
	 * @return array $response
	 */
	function tryAndLoginWithFacebook( $get ) {
		// assume fail
		$status = 'fail';
		$message = '';

		// reset session vars
		$_SESSION['fb_access_token'] = array();
		$_SESSION['fb_user_info'] = array();
		$_SESSION['eci_login_required_to_connect_facebook'] = false;

		if ( isset( $get['error'] ) ) { // error comming from facebook GET vars
			$message = $get['error_description'];
		} else { // no error in facebook GET vars
			// get an access token with the code facebook sent us
			$accessTokenInfo = getAccessTokenWithCode( $get['code'] );

			if ( $accessTokenInfo['has_errors'] ) { // there was an error getting an access token with the code
				$message = $accessTokenInfo['error_message'];
			} else { // we have access token! :D
				// set access token in the session
				$_SESSION['fb_access_token'] = $accessTokenInfo['fb_response']['access_token'];

				// get facebook user info with the access token
				$fbUserInfo = getFacebookUserInfo( $_SESSION['fb_access_token'] );

				if ( !$fbUserInfo['has_errors'] && !empty( $fbUserInfo['fb_response']['id'] ) && !empty( $fbUserInfo['fb_response']['email'] ) ) { // facebook gave us the users id/email
					// 	all good!
					$status = 'ok';

					// save user info to session
					$_SESSION['fb_user_info'] = $fbUserInfo['fb_response'];

					// check for user with facebook id
					$userInfoWithId = getRowWithValue( 'easycodeis_users', 'fb_user_id', $fbUserInfo['fb_response']['id'] );

					// check for user with email
					$userInfoWithEmail = getRowWithValue( 'easycodeis_users', 'email', $fbUserInfo['fb_response']['email'] );

					if ( $userInfoWithId || ( $userInfoWithEmail && !$userInfoWithEmail['password'] ) ) { // user has logged in with facebook before so we found them
						// update user
						updateRow( 'easycodeis_users', 'fb_access_token', $_SESSION['fb_access_token'], $userInfoWithId['id'] );
						$userInfo = getRowWithValue( 'easycodeis_users', 'id', $userInfoWithId['id'] );

						// save info to php session so they are logged in
						$_SESSION['is_logged_in'] = true;
						$_SESSION['user_info'] = $userInfo;
					} elseif ( $userInfoWithEmail && !$userInfoWithEmail['fb_user_id'] ) { // existing account exists for the email and has not logged in with facebook before
						$_SESSION['eci_login_required_to_connect_facebook'] = true;
					} else { // user not found with id/email sign them up and log them in
						// sign user up
						$fbUserInfo['fb_response']['fb_access_token'] = $_SESSION['fb_access_token'];
						$userId = signUserUp( $fbUserInfo['fb_response'] );
						$userInfo = getRowWithValue( 'easycodeis_users', 'id', $userId );

						// save info to php session so they are logged in
						$_SESSION['is_logged_in'] = true;
						$_SESSION['user_info'] = $userInfo;
					}
				} else {
					$message = 'Invalid creds';
				}
			}
		}

		return array( // return status and message of login
			'status' => $status,
			'message' => $message,
		);
	}

	/**
	 * Get debug info on an access token
	 *
	 * Endpoint https://graph.facebook.com/debug_token?input_token={access-token}&access_token={access-token}
	 *
	 * @param string $accessToken
	 *
	 * @return array $response
	 */
	function getDebugAccessTokenInfo( $accessToken ) {
		// endpoint for getting debug info
		$endpoint = FB_GRAPH_DOMAIN . 'debug_token';

		$params = array( // params for the endpoint
			'input_token' => $accessToken,
			'access_token' => $accessToken
		);

		// make the api call
		return makeFacebookApiCall( $endpoint, $params );
	}