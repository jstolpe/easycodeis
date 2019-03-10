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
			$( function() {
				// initialize our loader overlay
				loader.initialize();

				$( '#signup_button' ).on( 'click', function() {
					$( '#error_message' ).html( '' );
					$( 'input' ).removeClass( 'invalid-input' );

					var allFieldsFilledIn = true;

					$( 'input' ).each( function() {
						if ( '' == $( this ).val() ) { // invalid
							$( this ).addClass( 'invalid-input ');
							allFieldsFilledIn = false;
						}
					} );

					if ( allFieldsFilledIn ) {
						loader.showLoader();

						// backend sign user up

						window.location.href = "login.php";
					} else {
						$( '#error_message' ).html( 'All fields must be filled in.' );
						$( window ).scrollTop( 0 );
					}
				} );
			} );
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
							<div id="error_message" class="error-message"></div>
							<div>
								<div class="section-label">Email</div>
								<div><input type="text" name="email" /></div>
							</div>
							<div class="section-mid-container">
								<div class="section-label">First Name</div>
								<div><input type="text" name="first_name" /></div>
							</div>
							<div class="section-mid-container">
								<div class="section-label">Last Name</div>
								<div><input type="text" name="last_name" /></div>
							</div>
							<div class="section-mid-container">
								<div class="section-label">Password</div>
								<div><input type="password" name="password" /></div>
							</div>
							<div class="section-mid-container">
								<div class="section-label">Confirm Password</div>
								<div><input type="password" name="confirm_password" /></div>
							</div>
						</form>
						<div class="section-action-container">
							<div class="section-button-container" id="signup_button">
								<div>Sign Up</div>
							</div>
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