<?php
	/**
	 * Config file used to store creds we want to keep out of our repository and www root.
	 *
	 * Easy, Code Is site structure
	 *     config.php: wamp64/easycodeis_includes/config.php
	 *	   repository: wamp64/www/easycodeis/ 
	 *
	 * 1. Copy this file to your easycodeis_includes folder above.
	 * 2. Rename it config.php.
	 * 3. Uncomment and update the below defines with your own creds.
	 * 4. This file is included in autoloader.php which is included on every page.
	 */

	// define db creds
	// define( 'DB_HOST', 'localhost' ); // database host
	// define( 'DB_NAME', 'easycodeis' ); // database name
	// define( 'DB_USER', 'root' ); // database username
	// define( 'DB_PASS', '' ); // database password

	// fb creds
	// define( 'FB_APP_ID', 'YOUR-FB-APP-ID' );
	// define( 'FB_APP_SECRET', 'YOUR-FB-APP-SECRET' );
	// define( 'FB_REDIRECT_URI', 'YOUR-REDIRECT-URI' );

	// twitter creds
	// define( 'TWITTER_CONSUMER_KEY', 'YOUR-TWITTER-CONSUMER-KEY' );
	// define( 'TWITTER_CONSUMER_SECRET', 'YOUR-TWITTER-CONSUMER-SECRET' );
	// define( 'TWITTER_CALLBACK_URL', 'YOUR-CALLBACK-URL' );

	// path to cert
	// https://curl.haxx.se/docs/caextract.html
	// define( 'PATH_TO_CERT', 'C:\wamp64\easycodeis_includes\cacert.pem' );

	// twitch creds
	// define( 'TWITCH_CLIENT_ID', 'YOUR-TWITCH-CLIENT-ID' );
	// define( 'TWITCH_CLIENT_SECRET', 'YOUR-TWITCH-CLIENT-SECRET' );
	// define( 'TWITCH_REDIRECT_URI', 'YOUR-REDIRECT-URI' );