<!DOCTYPE html>
<html>
	<head>
		<!-- title of our page -->
		<title>Easy, Code Is | Login</title>

		<!-- include fonts -->
		<link href="https://fonts.googleapis.com/css?family=Coda" rel="stylesheet">

		<!-- need this so everything looks good on mobile devices -->
		<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />

		<!-- css styles for our login page-->
		<link href="css/global.css" rel="stylesheet" type="text/css">
		<link href="css/login.css" rel="stylesheet" type="text/css">

		<!-- jquery -->
		<script type="text/javascript" src="js/jquery.js"></script>

		<!-- include our loader overlay script -->
		<script type="text/javascript" src="js/loader.js"></script>

		<script>
			$( function() { // once the document is ready, do things
				// initialize our loader overlay
				loader.initialize();

				$( '#login_button' ).on( 'click', function() { // onclick for our login button
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

						// server side login
						$.ajax( {
							url: 'php/process_login.php',
							data: $( '#login_form' ).serialize(),
							type: 'post',
							dataType: 'json',
							success: function( data ) {
								if ( 'ok' == data.status ) {
									loader.hideLoader();
									window.location.href = "index.php";
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
						<div class="section-heading">Login</div>
						<form id="login_form" name="login_form">
							<div id="error_message" class="error-message">
							</div>
							<div>
								<div class="section-label">Email</div>
								<div><input type="text" name="email" /></div>
							</div>
							<div class="section-mid-container">
								<div class="section-label">Password</div>
								<div><input type="password" name="password" /></div>
							</div>
						</form>
						<div class="section-action-container">
							<div class="section-button-container" id="login_button">
								<div>Login</div>
							</div>
						</div>
						<div class="section-footer-container">
							Not a member? <a class="a-default" href="signup.php">Sign Up</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>