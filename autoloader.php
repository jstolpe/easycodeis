<?php
	// access php session
	session_start();

	// site global defines
	define( 'USER_LEVEL_ADMIN', '1' );

	// fb defines
	define( 'FB_GRAPH_VERSION', 'v6.0' ); // facebook graph version
	define( 'FB_GRAPH_DOMAIN', 'https://graph.facebook.com/' ); // base domain for api
	define( 'FB_APP_STATE', 'eciphp' ); // verify state

	// include config (creds and things we keep out of www and repo)
	include_once __DIR__ . ( PHP_OS == 'Linux' ? '' : '/' ) . '/../../easycodeis_includes/config.php';

	// include global functions
	include_once __DIR__  . '/php/functions.php';

	// include facebook api functions
	include_once __DIR__  . '/php/facebook_api.php';

	// include twitter api functions
	include_once __DIR__  . '/php/twitter_api.php';

	// include twitch api functions
	include_once __DIR__  . '/php/twitch_api.php';