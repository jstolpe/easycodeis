<?php
	// load up global things
	include_once '../autoloader.php';

	// check for user with email address
	$userInfo = getUserWithEmailAddress( trim( $_POST['email'] ) );

	if ( '' == $_POST['email'] || empty( $userInfo ) ) {
		$status = 'fail';
		$message = 'Invalid Email or Password';
	} elseif ( '' == $_POST['password'] || !password_verify( $_POST['password'], $userInfo['password'] ) ) { // password check
		$status = 'fail';
		$message = 'Invalid Email or Password';
	} else {
		$status = 'ok';
		$message = '';

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