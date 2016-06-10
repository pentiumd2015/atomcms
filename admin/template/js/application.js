$.datepicker.setDefaults($.datepicker.regional["ru"]);

$(function() {
/* # Bootstrap Plugins
================================================== */
	//===== Add fadeIn animation to dropdown =====//

	$('.dropdown, .btn-group').on('show.bs.dropdown', function(e){
		$(this).find('.dropdown-menu').first().stop(true, true).fadeIn(100);
	});


	//===== Add fadeOut animation to dropdown =====//

	$('.dropdown, .btn-group').on('hide.bs.dropdown', function(e){
		$(this).find('.dropdown-menu').first().stop(true, true).fadeOut(100);
	});

/* # Default Layout Options
================================================== */



	//===== Applying offcanvas class =====//

	$(document).on('click', '.offcanvas', function () {
		$('body').toggleClass('offcanvas-active');
	});




	//===== Panel Options (collapsing, closing) =====//

	/* Collapsing */
	$('[data-panel=collapse]').click(function(e){
	e.preventDefault();
	var $target = $(this).parent().parent().next('div');
	if($target.is(':visible')) 
	{
	$(this).children('i').removeClass('icon-arrow-up9');
	$(this).children('i').addClass('icon-arrow-down9');
	}
	else 
	{
	$(this).children('i').removeClass('icon-arrow-down9');
	$(this).children('i').addClass('icon-arrow-up9');
	}            
	$target.slideToggle(200);
	});

	/* Closing */
	$('[data-panel=close]').click(function(e){
		e.preventDefault();
		var $panelContent = $(this).parent().parent().parent();
		$panelContent.slideUp(200).remove(200);
	});



	//===== Showing spinner animation demo =====//

	$('.run-first').click(function(){
	    $('body').append('<div class="overlay"><div class="opacity"></div><i class="icon-spinner2 spin"></i></div>');
	    $('.overlay').fadeIn(150);
		window.setTimeout(function(){
	        $('.overlay').fadeOut(150, function() {
	        	$(this).remove();
	        });
	    },5000); 
	});

	$('.run-second').click(function(){
	    $('body').append('<div class="overlay"><div class="opacity"></div><i class="icon-spinner3 spin"></i></div>');
	    $('.overlay').fadeIn(150);
		window.setTimeout(function(){
	        $('.overlay').fadeOut(150, function() {
	        	$(this).remove();
	        });
	    },5000); 
	});

	$('.run-third').click(function(){
	    $('body').append('<div class="overlay"><div class="opacity"></div><i class="icon-spinner7 spin"></i></div>');
	    $('.overlay').fadeIn(150);
		window.setTimeout(function(){
	        $('.overlay').fadeOut(150, function() {
	        	$(this).remove();
	        });
	    },5000); 
	});


	//===== Hiding sidebar =====//

	/*$('.sidebar-toggle').click(function () {
		$('.page-container').toggleClass('sidebar-hidden');
	});*/


	//===== Disabling main navigation links =====//

	$('.navigation .disabled a, .navbar-nav > .disabled > a').click(function (e){
		e.preventDefault();
	});



	//===== Toggling active class in accordion groups =====//

	
    
  // refreshSidebarHeight();
   // 
  //  $(window).on("resize load", refreshSidebarHeight);
    
    $('[data-toggle="tooltip"]').tooltip();
});

function refreshSidebarHeight(){
    var $sidebar = $(".page-container .sidebar");
    
    if($(window).outerWidth(true) >= 992){
        var $content = $(".page-container .page-content-wrapper");
        
        var height = $content.height();
    
        if (height >= $sidebar.height()){
            $sidebar.css("min-height", height + "px");
        }else{
            $sidebar.css("min-height", "");
        }
    }else{
        $sidebar.css("min-height", "");
    }
    
}

var timeout;

function delay(callback, duration){
    if(typeof callback == "function"){
        if(!duration){
            duration = 500;
        }
        
        clearTimeout(timeout);
        
        timeout = setTimeout(callback, duration);
    }
    
}