<?php
	// include fucntions to use!
	include 'functions.php';

	// get user info with key and get user info with email address
	$userInfo = getRowWithValue( 'users', 'key_value', $_SESSION['user_info']['key_value'] );
	$userInfoWithEmail = getUserWithEmailAddress( trim( $_POST['email'] ) );

	if ( !filter_var( trim( $_POST['email'] ), FILTER_VALIDATE_EMAIL ) ) { // check email address
		$status = 'fail';
		$message = 'Invalid Email';
	} elseif ( !empty( $userInfoWithEmail ) && $_POST['email'] != $userInfo['email'] ) { // make sure if they are trying to change their email it is not already taken
		$status = 'fail';
		$message = 'Invalid Email';
	} elseif ( !$_POST['first_name'] || !$_POST['last_name'] ) { // check name
		$status = 'fail';
		$message = 'Invalid first or last name';
	} elseif ( isset( $_POST['change_password'] ) && ( !$_POST['password'] || $_POST['password'] != $_POST['confirm_password'] || strlen( $_POST['password'] ) < 8 ) ) { // check password/confirm password
		$status = 'fail';
		$message = 'Invalid password or passwords do not match and must be at least 8 characters';
	} else { // all good!
		$status = 'ok';
		$message = 'valid';

		// add to post so we can pass along the key value of the user
		$_POST['key_value'] = $userInfo['key_value'];

		// update the users info
		updateUserInfo( $_POST );

		// get the user info so we have most recent info
		$userInfo = getRowWithValue( 'users', 'key_value', $_SESSION['user_info']['key_value'] );

		// update session with most recently updated user info
		$_SESSION['user_info'] = $userInfo;
	}

	echo json_encode( // return json for ajaz on front end
		array(
			'status' => $status,
			'message' => $message
		)
	);