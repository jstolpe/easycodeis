<?php
	// include our functions file
	include 'functions.php';

	// destroy the session (clears all session data)
	session_destroy();

	echo json_encode( 
		array(
			'status' => 'ok',
		)
	);