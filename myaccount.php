<?php
	// load up global things
	include_once 'autoloader.php';

	if ( !isLoggedIn() ) { // if user is not logged in they cannot see this page
		header( 'location: index.php' );
	}

	if ( !empty( $_SESSION['user_info']['fb_access_token'] ) ) { // get users facebook info is we have an access token
		$fbUserInfo = getFacebookUserInfo( $_SESSION['user_info']['fb_access_token'] );
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<!-- title of our page -->
		<title>Easy, Code Is | My Account</title>

		<!-- include fonts -->
		<link href="https://fonts.googleapis.com/css?family=Coda" rel="stylesheet">

		<!-- need this so everything looks good on mobile devices -->
		<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />

		<!-- css styles for our my account page-->
		<link href="css/global.css" rel="stylesheet" type="text/css">
		<link href="css/myaccount.css" rel="stylesheet" type="text/css">

		<!-- jquery -->
		<script type="text/javascript" src="js/jquery.js"></script>

		<!-- include our loader overlay script -->
		<script type="text/javascript" src="js/loader.js"></script>

		<script>
			$( function() { // once the document is ready, do things
				// initialize our loader overlay
				loader.initialize();

				$( '#change_password' ).on( 'click', function() { // onclick for our change password check box
					if ( $( '#change_password_section' ).is( ':visible' ) ) { // if visible, hide it
						$( '#change_password_section' ).hide();
					} else { // if hidden, show it
						$( '#change_password_section' ).show();
					}
				} );

				$( '#update_button' ).on( 'click', function() { // onclick for our update button
					processMyAccount();
				} );

				$( '.form-input' ).keyup( function( e ) {
					if ( e.keyCode == 13 ) { // our enter key
						processMyAccount();
					}
				} );

				$( '.a-fb' ).on( 'click', function() { 
					loader.showLoader();

					$.ajax( { 
						url: 'php/process_logout.php',
						type: 'post',
						dataType: 'json',
						success: function( data ) {
							loader.hideLoader();
							window.location.href = 'index.php';
						}
					} );
				} );

				$( '.show-hide' ).on( 'click', function() { 
					var showHideSection = $( this ).data( 'section' );

					if ( $( '#' + showHideSection ).is( ':visible' ) ) {
						$( this ).html( 'show' );
						$( '#' + showHideSection ).hide();
					} else {
						$( this ).html( 'hide' );
						$( '#' + showHideSection ).show();
					}
				} );
			} );

			function processMyAccount() {
				// clear error message
				$( '#error_message' ).html( '' );

				loader.showLoader();

				$.ajax( {
					url: 'php/process_myaccount.php',
					data: $( '#myaccount_form' ).serialize(),
					type: 'post',
					dataType: 'json',
					success: function( data ) {
						if ( 'ok' == data.status ) {
							window.location.reload();
						} else if ( 'fail' == data.status ) {
							$( '#error_message' ).html( data.message );
							loader.hideLoader();
						}
					}
				} );
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
						<div class="section-heading">My Account</div>
						<form id="myaccount_form" name="myaccount_form">
							<div id="error_message" class="error-message">
							</div>
							<div>
								<div class="section-label">Email</div>
								<div><input class="form-input" type="text" name="email" value="<?php echo $_SESSION['user_info']['email']; ?>" /></div>
							</div>
							<div class="section-mid-container">
								<div class="section-label">First Name</div>
								<div><input class="form-input" type="text" name="first_name" value="<?php echo $_SESSION['user_info']['first_name']; ?>" /></div>
							</div>
							<div class="section-mid-container">
								<div class="section-label">Last Name</div>
								<div><input class="form-input" type="text" name="last_name" value="<?php echo $_SESSION['user_info']['last_name']; ?>"/></div>
							</div>
							<div>
								<div class="section-label">
									<input type="checkbox" name="change_password" id="change_password" style="width:10px"/>
									<label for="change_password">Change Passowrd</label>
								</div>
							</div>
							<div id="change_password_section" style="display:none">
								<div class="section-mid-container">
									<div class="section-label">Password</div>
									<div><input class="form-input" type="password" name="password" /></div>
								</div>
								<div class="section-mid-container">
									<div class="section-label">Confirm Password</div>
									<div><input class="form-input" type="password" name="confirm_password" /></div>
								</div>
							</div>
						</form>
						<div class="section-action-container">
							<div class="section-button-container" id="update_button">
								<div>Update</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="site-content-container">
			<div class="site-content-centered">
				<div class="site-content-section">
					<div class="site-content-section-inner">
						<div class="section-heading">Connected Facebook Account</div>
						<?php if ( empty( $fbUserInfo ) || $fbUserInfo['has_errors'] ) : // could not get facebook user info ?>
							<div class="a-fb">
								<div class="fb-button-container">
									<div>Login With Facebook to Connect Facebook Account</div>
								</div>
							</div>
						<?php else : // display facebook user info ?> 
							<div>
								<div class="pro-img-cont">
									<img class="pro-img" src="<?php echo $fbUserInfo['fb_response']['picture']['data']['url']; ?>" />
								</div>
							</div>
							<div class="section-mid-container">
								<div class="section-label">
									Email
								</div>
								<div>
									<?php echo $fbUserInfo['fb_response']['email']; ?>
								</div>
							</div>
							<div class="section-mid-container">
								<div class="section-label">
									First Name
								</div>
								<div>
									<?php echo $fbUserInfo['fb_response']['first_name']; ?>
								</div>
							</div>
							<div class="section-mid-container">
								<div class="section-label">
									Last Name
								</div>
								<div>
									<?php echo $fbUserInfo['fb_response']['last_name']; ?>
								</div>
							</div>
							<div class="section-mid-container">
								<div class="section-label">
									User Info Raw FB Response
								</div>
								<div>
									<div class="a-default show-hide" data-section="fb_user_info">
										show
									</div>
									<div id="fb_user_info" class="show-hide-section">
										<textarea class="show-hide-textarea"><?php print_r( $fbUserInfo['fb_response'] ); ?></textarea>
									</div>
								</div>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>