<?php
//set your email here:
$yourEmail = 'yourEmail@something.fr';
/*
 * CONTACT FORM
 */
//If the form is submitted
if(isset($_POST['submitted'])) { 
    //Check to make sure that the name field is not empty
    if($_POST['contact_name'] === '') { 
            $hasError = true;
    } else {
            $name = $_POST['contact_name'];
    }

    //Check to make sure sure that a valid email address is submitted
    if($_POST['contact_email'] === '')  { 
            $hasError = true;
    } else if (!preg_match("/^[a-z0-9]+([_\\.-][a-z0-9]+)*@([a-z0-9]+([\.-][a-z0-9]+)*)+\\.[a-z]{2,}$/i", $_POST['contact_email'])) {
            $hasError = true;
    } else {
            $email = $_POST['contact_email'];
    }

    //Check to make sure comments were entered	
    if($_POST['contact_textarea'] === '') {
            $hasError = true;
    } else {
            if(function_exists('stripslashes')) {
                    $comments = stripslashes($_POST['contact_textarea']);
            } else {
                    $comments = $_POST['contact_textarea'];
            }
    }

    //If there is no error, send the email
    if(!isset($hasError)) {

            $emailTo = $yourEmail;
            $subject = "Message From Your Website";
            $body = "Name: $name \n\nEmail: $email \n\nComments: $comments";
            $headers = 'From : my site <'.$emailTo.'>' . "\r\n" . 'answer to : ' . $email;

            mail($emailTo, $subject, $body, $headers);

            $emailSent = true; 
    }
    
}
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <!-- PAGE TITLE -->
    <title>Contact - Tee Up</title>
    <!-- MAKE IT RESPONSIVE -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- SEO -->
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- BOOTSTRAP -->
    <link href="css/bootstrap.min.css" rel="stylesheet" media="screen">
    <!-- STYLES -->
    <link href="css/style.css" rel="stylesheet" media="screen">
    <!-- FONTS -->
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400italic,400,600,700' rel='stylesheet' type='text/css'>
    <link href='http://fonts.googleapis.com/css?family=Playfair+Display:400,700,400italic' rel='stylesheet' type='text/css'>
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="js/html5shiv.js"></script>
      <script src="js/respond.min.js"></script>
    <![endif]-->
  </head>
  <!-- START BODY -->
  <body>
  	<!-- You can add class="boxed" just below to #page -->
  	<div id="page">
	  	<!-- START HEADER -->
	  	<header id="header">
	  		<div id="topbar" class="animate_top_bottom">
		  		<div class="container">
			  		<ul>
				  		<li><span class="icon-phone"></span>+000 000 1234</li>
				  		<li><span class="icon-mail"></span><a href="mailto:yourEmail@example.com">yourEmail@example.com</a></li>
			  		</ul>
			  		<ul class="topbar-right">
			  			<li>
				  			<a href="#" id="open-languages">English <span class="icon-arrow-down4"></span></a>
					  		<ul id="languages">
						  		<li><a href="#">French</a></li>
						  		<li><a href="#">Deutsch</a></li>
						  		<li><a href="#">Spanish</a></li>
					  		</ul>
				  		</li>
				  		<li><span class="icon-user"></span><a href="#" id="open-signin">Sign In</a></li>
			  		</ul>
		  		</div>
	  		</div>
		  	<div class="container">
			  	<a href="index.html" title="Logo Image" id="logo">
				  	<img src="images/logo.png" alt="Logo Image">
			  	</a>
			  	<nav id="navigation">
				  	<ul>
					  	<li><a href="index.html">Home</a></li>
					  	<li class="with-submenu"><a href="about.html">About</a>
					  		<ul class="submenu">
						  		<li><a href="testimonials.html">Testimonials</a></li>
						  		<li><a href="sitemap.html">Sitemap</a></li>
						  		<li><a href="faq.html">FAQ page</a></li>
						  		<li><a href="404.html">404 page</a></li>
						  		<li><a href="new-page.html">New Page</a></li>
					  		</ul>
					  	</li>
					  	<li><a href="gallery.html">Gallery</a></li>
					  	<li><a href="rates.html">Rates</a></li>
					  	<li><a href="events.html">Events</a></li>
					  	<li><a href="blog.html">Blog</a></li>
					  	<li><a href="members/index.html">Member's Area</a></li>
					  	<li><a href="contact.html">Contact</a></li>
					  	<li class="menu-search">
					  		<a href="#" id="search-toggle"><span class="icon-search"></span></a>
					  		<div class="search-container">
						  		<form role="search" method="get" action="#" >
									<input type="text" value="" name="s" id="s" placeholder="Search..."/>
									<input type="hidden" name="searchsubmit" id="searchsubmit" value="true" />
									<button type="submit" name="searchsubmit"><i class="icon-search"></i></button>
						        </form>
					  		</div>
					  	</li>
				  	</ul>
			  	</nav>
			  	<a href="#navigation" id="mobile-menu"><span class="icon-list2"></span></a>
		  	</div>
		  	<!-- BACKGROUND HEADER-->
		  	<div id="background" class="small" style="background-image: url('images/slider/3.jpg');"></div>
	  	</header>
	  	<!-- END HEADER -->
	  	
	  	<!-- START MAIN CONTAINER -->
	  	<div id="main-container">
	  		<!-- START CONTAINER -->
		  	<section id="container" class="container">
		  		<!-- TITLE -->
	  			<div class="heading page-title animate_right_left">
			  		<h1>Contact</h1>
			  		<hr>
	  			</div>
	  		  <!-- PHP ALERTS FROM THE FORMS -->
			  <?php if(isset($emailSent) && $emailSent == true) { ?>
			        <div class="alert-success alert" >
			            <a class="close icon" data-dismiss="alert" href="#"><span class="icon icon-close"></span></a>
			            <strong><?php echo'Thanks, '. $name  .'.';?></strong>
			                <p><?php echo'Your message was sent successfully. You will receive a response shortly.'; ?></p>
			        </div><!-- .alert -->
			    <?php } ?>
			    <?php if(isset($hasError) && $hasError == true) { ?>
			        <div class="alert-danger alert">
			            <a class="close icon" data-dismiss="alert" href="#"><span class="icon icon-close"></span></a>
			            <strong><?php echo'Sorry,'; ?></strong>
			                <p><?php echo'Your message can\'t be send...check if your email is correct otherwise a field is missing...'; ?></p>
			        </div><!-- .alert -->
			    <?php } ?>
			    <!-- END ALERT -->
	  			<div class="row">	
			  		<div class="col-md-6 animate_right_left">
			  			<h3 class="center">Map</h3>
				  		<div id="map"></div>
			  		</div>
					<div id="contact-container" class="col-md-6 animate_right_left">
						<!-- CONTACT FORM -->
						<div class="center">
							<h3>Get in touch Now</h3>
							<a href="javascript:void(0)" id="open-address">Informations <i class="icon-arrow-right3"></i></a>
						</div>
						<form class="form-horizontal" method="post" action="contact.php" id="form">
							<div class="form-group">
								<label for="contact_name" class="col-lg-2 control-label">Name</label>
								<div class="col-lg-10">
									<input type="text" class="form-control" id="contact_name" name="contact_name">
								</div>
							</div>
							<div class="form-group">
								<label for="contact_email" class="col-lg-2 control-label">Email</label>
								<div class="col-lg-10">
								 	<input type="email" class="form-control" id="contact_email" name="contact_email">
								</div>
							</div>
							<div class="form-group">
								<label for="contact_textarea" class="col-lg-2 control-label">Message</label>
								<div class="col-lg-10">
									<textarea class="form-control" rows="3" id="contact_textarea" name="contact_textarea"></textarea>
								</div>
							</div>
							<div class="form-group">
								<div class="col-lg-offset-2 col-lg-10">
								 	<input type="hidden" name="submitted" id="submitted" value="true" />
								 	<button type="submit" class="btn btn-default" name="submitted"><i class="icon-paperplane"></i>Send</button>
								</div>
							</div>
						</form>
						<!-- CONTACT TEXT -->
						<div id="address">
							<a href="javascript:void(0)" id="close-address"><i class="icon-cross"></i></a>
							<div class="center">
								<h3>Informations</h3>
							</div>
							<p class="center">
								2301 Alton Road, Miami Beach, FL 33140 <br/>
								<strong>Phone: 305-532-3350</strong><br/>
								<strong>Fax: 305-532-3840</strong><br/>
								<strong>www.miamibeachgolfclub.com</strong><br/>
							</p>
						</div>
					</div>
				</div>
		  	</section>
	  		<!-- END CONTAINER -->
	  	</div>
	  	<!-- END MAIN CONTAINER -->
	  	
	  	<!-- START FOOTER -->
	  	<footer id="footer">
		  	<div id="widgets" class="container">
			  	<div class="row">
				  	<div class="col-md-3 widget">
					  	<h3>Our Golf</h3>
					  	<ul>
						  	<li><a href="gallery.html">Gallery</a></li>
						  	<li><a href="about.html">About</a></li>
						  	<li><a href="blog.html">Blog</a></li>
						  	<li><a href="contact.html">Contact</a></li>
					  	</ul>
				  	</div>
				  	<div class="col-md-3 widget">
					  	<h3>Blog Archives</h3>
					  	<ul class="archives">
						  	<li><a href="blog.html">November 2013</a></li>
						  	<li><a href="blog.html">December 2013</a></li>
						  	<li><a href="blog.html">January 2014</a></li>
						  	<li><a href="blog.html">February 2014</a></li>
					  	</ul>
				  	</div>
				  	<div class="col-md-3 widget">
					  	<h3>Upcoming Events</h3>
					  	<ul class="events">
						  	<li><a href="events.html">Your Event Name (01/02/15)</a></li>
						  	<li><a href="events.html">Your Event Name (02/03/15)</a></li>
						  	<li><a href="events.html">Your Event Name (03/04/15)</a></li>
						  	<li><a href="events.html">Your Event Name (02/04/15)</a></li>
					  	</ul>
				  	</div>
				  	<div class="col-md-3 widget">
					  	<h3>Get In Touch</h3>
					  	<ul>
						  	<li class="list-home">2301 Alton Road, Miami Beach, FL 33140</li>
						  	<li class="list-phone">+ 305-532-3350</li>
					  	</ul>
					  	<ul class="social-list list-unstyled">
                            <li><a href="#" title="Join us on Facebook" class="facebook-link"><span class="icon-facebook"></span></a></li>
                            <li><a href="#" title="Join us on Twitter" class="twitter-link"><span class="icon-twitter"></span></a></li>
                            <li><a href="#" title="Join us on Vimeo" class="vimeo-link"><span class="icon-vimeo"></span></a></li>
                            <li><a href="#" title="Join us on Google Plus" class="googleplus-link"><span class="icon-googleplus"></span></a></li>
                            <li><a href="#" title="Join us on Flickr" class="flickr-link"><span class="icon-flickr"></span></a></li>
                            <!-- DELETE THE COMMENTS TO ENABLE 
                            <li><a href="#" title="Join us on Pinterest" class="pinterest-link"><span class="icon-pinterest"></span></a></li>
                            <li><a href="#" title="Join us on Tumblr" class="tumblr-link"><span class="icon-tumblr"></span></a></li>
                            <li><a href="#" title="Join us on Instagram" class="instagram-link"><span class="icon-instagram"></span></a></li>
                            <li><a href="#" title="Join us on Linkedin" class="linkedin-link"><span class="icon-linkedin"></span></a></li>
                            <li><a href="#" class="rss-link" title="Subscribe to our RSS feed" class="rss-link"><span class="icon-rss"></span></a></li>
                            -->
                        </ul>
					</div>
			  	</div>
		  	</div>
		  	<div id="copyright">
			  	<p>&#169; Copyright - <a href="http://themeforest.net/user/doubleF/portfolio">2F design</a></p>
		  	</div>
	  	</footer>
	  	<!-- END FOOTER -->
  	</div>
	
	<!-- SIGN IN CONTENT-->
	<div id="signin-container">
		<div id="signin">
			<a href="javascript:void(0)" class="clearfix" id="close-signin"><i class="icon-cross"></i></a>
		  	<div class="heading">
			  	<h1>Sign In</h1>
			  	<hr>
  			</div>
			<form class="form-horizontal" method="post" id="signinform" role="form">
			  <div class="form-group">
			    <label for="inputName" class="col-sm-3 control-label">Name</label>
			    <div class="col-sm-9">
			      <input type="text" class="form-control" id="inputName" placeholder="Name">
			    </div>
			  </div>
			  <div class="form-group">
			    <label for="inputPassword" class="col-sm-3 control-label">Password</label>
			    <div class="col-sm-9">
			      <input type="password" class="form-control" id="inputPassword" placeholder="Password">
			    </div>
			  </div>
			  <div class="form-group">
			    <div class="col-sm-offset-3 col-sm-9">
			      <div class="checkbox">
			        <label>
			          <input type="checkbox"> Remember me
			        </label>
			      </div>
			    </div>
			  </div>
			  <div class="form-group">
			    <div class="center">
			      <button type="submit" class="btn btn-default"><i class="icon-checkmark"></i> Sign in</button>
			    </div>
			  </div>
			</form>
		</div>
	</div>

    <!-- SCRIPTS -->
    <script src="http://code.jquery.com/jquery.js"></script>
    <!-- ONLY FOR THE CONTACT PAGE : MAP SCRIPT -->
    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&amp;sensor=false"></script>
    <script>
		function initialize() {
		  var myLatlng = new google.maps.LatLng(25.7993,-80.138023); //http://itouchmap.com/latlong.html
		  var mapOptions = {
		    zoom: 15,
		    center: myLatlng
		  }
		  var map = new google.maps.Map(document.getElementById('map'), mapOptions);
		
		  var marker = new google.maps.Marker({
		      position: myLatlng,
		      map: map,
		      title: 'Hello World!'
		  });
		}
		
		google.maps.event.addDomListener(window, 'load', initialize);
		
	</script>
    <!-- END -->
    <script src="js/alert.js"></script>
    <script src="js/dropdown.js"></script>
    <script src="js/waypoints.min.js"></script>
    <script src="js/isotope.pkgd.min.js"></script>
    <script src="js/jquery.fancybox.pack.js"></script>
    <script src="js/pace.min.js"></script>
    <script src="js/jquery.flexslider-min.js"></script>
    <script src="js/jquery.themepunch.plugins.min.js"></script>
    <script src="js/jquery.themepunch.revolution.min.js"></script>
    <script src="js/custom.js"></script>
  </body>
  <!-- END BODY -->
</html>