<?php
	// include config
	include 'C:\wamp\easycodeis_includes\config.php';

	/**
	 * Get DB connection
	 *
	 * @param void
	 *
	 * @return db connection
	 */
	function getDatabaseConnection() {
		try { // connect to database and return connections
			$conn = new PDO( 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS );
			return $conn;
		} catch ( PDOException $e ) { // connection to database failed, report error message
			return $e->getMessage();
		}
	}

	/**
	 * Get user with email address
	 *
	 * @param array $email
	 *
	 * @return array $userInfo
	 */
	function getUserWithEmailAddress( $email ) {
		// get database connection
		$databaseConnection = getDatabaseConnection();

		// create our sql statment
		$statement = $databaseConnection->prepare( '
			SELECT
				*
			FROM
				users
			WHERE
				email = :email
		' );

		// execute sql with actual values
		$statement->setFetchMode( PDO::FETCH_ASSOC );
		$statement->execute( array(
			'email' => trim( $email )
		) );

		// get and return user
		$user = $statement->fetch();
		return $user;
	}

	/**
	 * Sign a user up
	 *
	 * @param array $info
	 *
	 * @return array $userInfo
	 */
	function signUserUp( $info ) {
		// get database connection
		$databaseConnection = getDatabaseConnection();

		// create our sql statment
		$statement = $databaseConnection->prepare( '
			INSERT INTO
				users (
					email,
					first_name,
					last_name,
					password,
					key_value
				)
			VALUES (
				:email,
				:first_name,
				:last_name,
				:password,
				:key_value
			)
		' );

		// execute sql with actual values
		$statement->execute( array(
			'email' => trim( $info['email'] ),
			'first_name' => trim( $info['first_name'] ),
			'last_name' => trim( $info['last_name'] ),
			'password' => hashedPassword( $info['password'] ),
			'key_value' => newKey(),
		) );

		// return id of inserted row
		return $databaseConnection->lastInsertId();
	}

	/**
	 * Generate a key for a user
	 *
	 * @param array $info
	 *
	 * @return array $userInfo
	 */
	function newKey( $length = 32 ) {
		$time = md5( uniqid() ) . microtime();
		return substr( md5( $time ), 0, $length );
	}

	/**
	 * Hash password
	 *
	 * @param String $password plain text password
	 * @param String $salt to hash passoword with set to false auto gen one
	 *
	 * @return Sting of password now hashed
	 */
	function hashedPassword( $password ) {
		$random = openssl_random_pseudo_bytes( 18 );
		$salt = sprintf( '$2y$%02d$%s',
			12, // 2^n cost factor, hackers got nothin on this!
			substr( strtr( base64_encode( $random ), '+', '.' ), 0, 22 )
		);

		// hash password with salt
		$hash = crypt( $password, $salt );

		// return hash
		return $hash;
	}