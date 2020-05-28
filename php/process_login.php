<?php
	// load up global things
	include_once '../autoloader.php';

	// check for user with email address
	$userInfo = getUserWithEmailAddress( trim( $_POST['email'] ) );

	if ( '' == $_POST['email'] || empty( $userInfo ) ) { // no email or password is invalid
		$status = 'fail';
		$message = 'Invalid Email or Password';
	} elseif ( '' == $_POST['password'] || !password_verify( $_POST['password'], $userInfo['password'] ) ) { // password check
		$status = 'fail';
		$message = 'Invalid Email or Password';
	} else { // all good
		$status = 'ok';
		$message = '';

		if ( isset( $_SESSION['fb_user_info']['id'] ) ) { // if we have facebook id save it
			updateRow( 'users', 'fb_user_id', $_SESSION['fb_user_info']['id'], $userInfo['id'] );
		}

		if ( isset( $_SESSION['fb_access_token'] ) ) { // if we have an access token save it
			updateRow( 'users', 'fb_access_token', $_SESSION['fb_access_token'], $userInfo['id'] );
		}

		if ( isset( $_SESSION['oauth_token'] ) ) { // if we have an access token save it
			updateRow( 'users', 'oauth_token', $_SESSION['oauth_token'], $userInfo['id'] );
		}

		if ( isset( $_SESSION['oauth_token_secret'] ) ) { // if we have an access token secret save it
			updateRow( 'users', 'oauth_token_secret', $_SESSION['oauth_token_secret'], $userInfo['id'] );
		}

		if ( isset( $_SESSION['tw_user_info']['id'] ) ) { // if we have an twitter user id save it
			updateRow( 'users', 'tw_user_id', $_SESSION['tw_user_info']['id'], $userInfo['id'] );
		}

		if ( isset( $_SESSION['twitch_user_info'] ) ) { // if we have an twitch info save it
			updateRow( 'users', 'twitch_user_id', $_SESSION['twitch_user_info']['id'], $userInfo['id'] );
			updateRow( 'users', 'twitch_access_token', $_SESSION['twitch_user_info']['access_token'], $userInfo['id'] );
			updateRow( 'users', 'twitch_refresh_token', $_SESSION['twitch_user_info']['refresh_token'], $userInfo['id'] );
		}

 		// save info to php session
		$_SESSION['is_logged_in'] = true;
		$_SESSION['user_info'] = $userInfo;
	}

	echo json_encode(
		array(
			'status' => $status,
			'message' => $message
		)
	);