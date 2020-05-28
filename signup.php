<?php
	// load up global things
	include_once 'autoloader.php';

	// get twitter login url
	$eciTwitterApi = new eciTwitterApi( TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET );
	$twitterPreLoginData = $eciTwitterApi->getDataForLogin( TWITTER_CALLBACK_URL );

	// get twitch login url
	$eciTwitchApi = new eciTwitchApi( TWITCH_CLIENT_ID, TWITCH_CLIENT_SECRET );
	$twitchLoginUrl = $eciTwitchApi->getLoginUrl( TWITCH_REDIRECT_URI );

	// only if you are logged out can you view the signup page
	loggedInRedirect();
?>
<!DOCTYPE html>
<html>
	<head>
		<!-- title of our page -->
		<title>Easy, Code Is | Sign Up</title>

		<!-- include fonts -->
		<link href="https://fonts.googleapis.com/css?family=Coda" rel="stylesheet">

		<!-- need this so everything looks good on mobile devices -->
		<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />

		<!-- css styles for our signup page-->
		<link href="css/global.css" rel="stylesheet" type="text/css">
		<link href="css/signup.css" rel="stylesheet" type="text/css">

		<!-- jquery -->
		<script type="text/javascript" src="js/jquery.js"></script>

		<!-- include our loader overlay script -->
		<script type="text/javascript" src="js/loader.js"></script>

		<script>
			$( function() { // once the document is ready, do things
				// initialize our loader overlay
				loader.initialize();

				$( '#signup_button' ).on( 'click', function() { // onclick for our signup button
					processSignup();
				} );

				$( '.form-input' ).keyup( function( e ) {
					if ( e.keyCode == 13 ) { // our enter key
						processSignup();
					}
				} );
			} );

			function processSignup() {
				// clear error message and red borders on signup click
				$( '#error_message' ).html( '' );
				$( 'input' ).removeClass( 'invalid-input' );

				// assume no fields are blank
				var allFieldsFilledIn = true;

				$( 'input' ).each( function() { // simple front end check, loop over inputs
					if ( '' == $( this ).val() ) { // input is blank, add red border and set flag to false
						$( this ).addClass( 'invalid-input ');
						allFieldsFilledIn = false;
					}
				} );

				if ( allFieldsFilledIn ) { // all fields are filled in!
					loader.showLoader();

					$.ajax( {
						url: 'php/process_signup.php',
						data: $( '#signup_form' ).serialize(),
						type: 'post',
						dataType: 'json',
						success: function( data ) {
							if ( 'ok' == data.status ) {
								loader.hideLoader();
								window.location.href = "login.php";
							} else if ( 'fail' == data.status ) {
								$( '#error_message' ).html( data.message );
								loader.hideLoader();
							}
						}
					} );
				} else { // some fields are not filled in, show error message and scroll to top of page
					$( '#error_message' ).html( 'All fields must be filled in.' );
					$( window ).scrollTop( 0 );
				}
			}
		</script>
	</head>
	<body>
		<div class="site-header">
			<div class="site-header-pad">
				<a class="header-home-link" href="index.php">
					Easy, Code Is
				</a>
			</div>
		</div>
		<div class="site-content-container">
			<div class="site-content-centered">
				<div class="site-content-section">
					<div class="site-content-section-inner">
						<div class="section-heading">Sign Up</div>
						<form id="signup_form" name="signup_form">
							<div id="error_message" class="error-message">
							</div>
							<div>
								<div class="section-label">Email</div>
								<div><input class="form-input" type="text" name="email" /></div>
							</div>
							<div class="section-mid-container">
								<div class="section-label">First Name</div>
								<div><input class="form-input" type="text" name="first_name" /></div>
							</div>
							<div class="section-mid-container">
								<div class="section-label">Last Name</div>
								<div><input class="form-input" type="text" name="last_name" /></div>
							</div>
							<div class="section-mid-container">
								<div class="section-label">Password</div>
								<div><input class="form-input" type="password" name="password" /></div>
							</div>
							<div class="section-mid-container">
								<div class="section-label">Confirm Password</div>
								<div><input class="form-input" type="password" name="confirm_password" /></div>
							</div>
						</form>
						<div class="section-action-container">
							<div class="section-button-container" id="signup_button">
								<div>Sign Up</div>
							</div>
						</div>
						<div class="section-action-container">
							- OR -
						</div>
						<div class="section-action-container">
							<a href="<?php echo getFacebookLoginUrl(); ?>" class="a-fb">
								<div class="fb-button-container">
									Login with Facebook (PHP)
								</div>
							</a>
						</div>
						<div class="section-action-container">
							<div id="error_message_twitter_php" class="error-message">
								<?php if ( 'fail' == $twitterPreLoginData['status'] ) : ?>
									<div>
										<?php echo $twitterPreLoginData['message']; ?>
									</div>
								<?php endif; ?>
							</div>
						</div>
						<div class="section-action-container">
							<a href="<?php echo $twitterPreLoginData['twitter_login_url'] ;?>" class="a-tw">
								<div class="tw-button-container">
									Login with Twitter (PHP)
								</div>
							</a>
						</div>
						<div class="section-action-container">
							<a href="<?php echo $twitchLoginUrl; ?>" class="a-twitch">
								<div class="twitch-button-container">
									Login with Twitch (PHP)
								</div>
							</a>
						</div>
						<div class="section-footer-container">
							Already a member? <a class="a-default" href="login.php">Login</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>