/*
 * Tee Up - Golf Template
 *
 * Copyright (c) 2014 F²
 * 
 * Main Javascript
 */
(function($) {
    "use strict";
    //LOADER
    $(window).load(function () {
		$('#loader').fadeOut();
	});
	//ANIMATIONS
    $(".animate_bottom_top, .animate_top_bottom, .animate_left_right, .animate_right_left,.animate_fade").waypoint(function () {
        if (!$(this).hasClass("animate_go")) {
            var e = $(this);
            setTimeout(function () {
                e.addClass("animate_go")
            }, 30)
        }
    }, {
        offset: "80%"
    });
	//SUBMENU
	$('.with-submenu a').mouseover(function () {
        var submenu = $(this).next();
        submenu.fadeIn();
        submenu.mouseleave(function () {
		    setTimeout(function () {
		        submenu.fadeOut();
		    }, 100);
		});
    });
    //LANGUAGES
    $('#open-languages').click(function (){
	    $("#languages").fadeToggle();
    })
    //RESPONSIVE MENU
    $("#mobile-menu").click(function(){
	    $("#navigation").fadeToggle();
    });
	//FANCYOX
	$('.fancybox').fancybox({
        openEffect  : 'elastic'
    });
    //SLIDERS
    $("#background.big").revolution({
            delay:12000,
            startheight:550,
            startwidth:890,
            thumbWidth:100,
            thumbHeight:50,
            thumbAmount:5,
            onHoverStop:"on",
            hideThumbs:200,
            navigationType:"bullet",
            navigationStyle:"round",
            touchenabled:"on",
            navOffsetHorizontal:0,
            navOffsetVertical:80,
            shadow:undefined,
            fullWidth:"on"
    });
    $('.sample-flexslider').flexslider({
	    animation: "slide",
    });
    //CAROUSELS
	$('.content-carousel').flexslider({
		animation: "slide",
		animationLoop: false,
		itemWidth: 380,
		slideshow: false
	});
	$(window).load(function() {
	  $('#gallery-slider').flexslider({
	    animation: "slide",
	    animationLoop: false,
	    itemWidth: 300,
	    itemMargin: 0,
	    slideshow: false
	  });
	});
    //BOOTSTRAP ALERTS FOR FORM
	$(".alert").alert();
	//GALLERY
	var $container = $('#gallery-container');
	$container.isotope({
	  itemSelector: '.item',
	  layoutMode: 'fitRows'
	});
	$('#filters').on( 'click', 'button', function( event ) {
	  var filtr = $(this).attr('data-filter');
	  $('#gallery-container').isotope({ filter: filtr });
	});
	//EVENTS
	var $container = $('#events-container');
	$container.isotope({
	  itemSelector: '.slide',
	  layoutMode: 'fitRows'
	});
	//MEMBERS
	var $container = $('#members-container');
	$container.isotope({
	  itemSelector: '.slide',
	  layoutMode: 'fitRows'
	});
	//DROPDOWN TOGGLE
	$('.dropdown-toggle').dropdown();
	//CONTACT
	$("#open-address, #close-address").click(function(){
		$("#address").fadeToggle();
	});
	//SIGN IN
	$("#open-signin, #close-signin").click(function(){
		$("#signin-container").fadeToggle();
	});
	//SEARCH FORM
	$("#search-toggle").click(function(){
		$("#navigation .search-container").fadeToggle();
		$("#navigation #s").focus();
	});

})(jQuery);