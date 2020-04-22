<?php
	// load up global things
	include_once 'autoloader.php';
?>
<!DOCTYPE html>
<html>
	<head>
		<!-- title of our page -->
		<title>Easy, Code Is</title>

		<!-- include fonts -->
		<link href="https://fonts.googleapis.com/css?family=Coda" rel="stylesheet">

		<!-- need this so everything looks good on mobile devices -->
		<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />

		<!-- css styles for our home page-->
		<link href="css/global.css" rel="stylesheet" type="text/css">
		<link href="css/home.css" rel="stylesheet" type="text/css">

		<!-- jquery -->
		<script type="text/javascript" src="js/jquery.js"></script>

		<!-- include our loader overlay script -->
		<script type="text/javascript" src="js/loader.js"></script>

		<script>
			$( function() { // do things when the document is ready
				// initialize our loader overlay
				loader.initialize();

				$( '#load_test' ).on( 'click', function() { // on click for our load test link
					// show our loading overlay
					loader.showLoader();

					setInterval( function() { // after 3 seconds, hide our loading overlay
						loader.hideLoader();
					}, 3000 );
				} );

				$( '#logout_link' ).on( 'click', function() { // on click for our logout link
					// show our loading overlay
					loader.showLoader();

					// server side logout
					$.ajax( {
						url: 'php/process_logout.php',
						type: 'post',
						dataType: 'json',
						success: function( data ) {
							loader.hideLoader();
							window.location.href = "index.php";
						}
					} );
				} );
			} );
		</script>
	</head>
	<body>
		<div class="background-video-container">
			<video class="background-video-element" autoplay muted loop >
				<source src="assets/background_video.mp4" />
			</video>
			<img class="background-video-image" src="assets/background_video_image.png" />
			<div class="background-video-overlay"></div>
			<div class="background-video-text-overlay">
				<div>Easy, Code Is</div>
				<div class="action-container pc-only">
					<?php if ( isLoggedIn() ) : ?>
						<div class="logged-in-text">Logged in as <b><?php echo $_SESSION['user_info']['first_name']; ?></b></div>
					<?php else : ?>
						<a class="a-action" href="signup.php">
							<div class="button-container">
								<div class="button-container-pad">
									SIGN UP
								</div>
							</div>
						</a>
						<a class="a-action" href="login.php">
							<div class="button-container">
								<div class="button-container-pad">
									LOGIN
								</div>
							</div>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<div class="content">
			<div class="content-inner">
				<div class="content-inner-padding">
					<div class="action-container mobile-only">
						<?php if ( isLoggedIn() ) : ?>
							<div class="logged-in-text">Logged in as <b><?php echo $_SESSION['user_info']['first_name']; ?></b></div>
						<?php else : ?>
							<a class="a-action" href="signup.php">
								<div class="button-container">
									<div class="button-container-pad">
										SIGN UP
									</div>
								</div>
							</a>
							<a class="a-action" href="login.php">
								<div class="button-container default-margin-top">
									<div class="button-container-pad">
										LOGIN
									</div>
								</div>
							</a>
						<?php endif; ?>
					</div>
					<h1>
						Welcome to Easy, Code Is!
					</h1>
					<div>
						This is the website where you, the users, decide what features get added. Comment on any of my videos and let me know what you want to learn and see implemented! I will constantly be building on this website, adding features and creating videos based on what you, the users, want to see and learn.
					</div>
				</div>
			</div>
		</div>
		<div class="footer-container">
			<div><a class="a-default" href="https://github.com/jstolpe/easycodeis">View Easy, Code Is on GitHub</a></div>
			<div><span id="load_test">Loading Overlay Test (lasts 3 sec)</span></div>
			<?php if ( isLoggedIn() ) : ?>
				<?php if ( isAdmin() ) : ?>
					<div>
						<a class="a-default" href="adminpanel.php">Admin Panel</a>
					</div>
				<?php endif; ?>
				<div>
					<a class="a-default" href="myaccount.php">My Account</a>
				</div>
				<div id="logout_link" class="a-default">Logout</div>
			<?php endif; ?>
		</div>
	</body>
</html>