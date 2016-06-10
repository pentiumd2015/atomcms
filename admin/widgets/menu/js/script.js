$(function(){
    $nav = $('.navigation');
    
	$nav.find('li.active').parents('li').addClass('active');
	$nav.find('li').not('.active').has('ul').children('ul').addClass('hidden-ul');
	$nav.find('li').has('ul').children('a').parent('li').addClass('has-ul');

    $nav.addClass("nav_show");

	$(document).on('click', '.sidebar-toggle', function (e){
	    e.preventDefault();

	    $('body').toggleClass('sidebar-narrow');

	    if($('body').hasClass('sidebar-narrow')){
	        $nav.children('li').children('ul').css('display', '');

		    $('.sidebar-content').hide().delay().queue(function(){
		        $(this).show().addClass('animated fadeIn').clearQueue();
		    });
	    }else {
	        $nav.children('li').children('ul').css('display', 'none');
	        $nav.children('li.active').children('ul').css('display', 'block');

		    $('.sidebar-content').hide().delay().queue(function(){
		        $(this).show().addClass('animated fadeIn').clearQueue();
		    });
	    }
	});

	$nav.find('li').has('ul').children('a').on('click', function(e){
	    e.preventDefault();

	    if($('body').hasClass('sidebar-narrow')){
	        $li = $(this).parent('li > ul li').not('.disabled');
			$li.toggleClass('active').children('ul').slideToggle(250);
			$li.siblings().removeClass('active').children('ul').slideUp(250);
	    }else{
            $li = $(this).parent('li').not('.disabled');
           
            if($li.hasClass("active")){
                $(this).removeClass("rotate_expand").addClass("rotate_hide");
                $li.removeClass('active').children('ul').slideUp(250);
            }else{
                $(this).removeClass("rotate_hide").addClass("rotate_expand");
                $li.addClass('active').children('ul').slideDown(250);
            }
	    }
	}); 
});