<?php
	// load up global things
	include_once '../autoloader.php';

	// destroy the session (clears all session data)
	session_destroy();

	echo json_encode( 
		array(
			'status' => 'ok',
		)
	);