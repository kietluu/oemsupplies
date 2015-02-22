// JavaScript Document
jQuery.noConflict();
jQuery(document).ready(function(){
	Boxgrid();	
	Mycarousel();
	
});

function Boxgrid(){
	jQuery('.boxgrid.caption').hover(function(){
		jQuery(".cover", this).stop().animate({top:'65px'},{queue:false,duration:160});
	}, function() {
		jQuery(".cover", this).stop().animate({top:'100px'},{queue:false,duration:160});
	});
}


function Mycarousel(){
	jQuery('#mycarousel').jcarousel({		
        auto: 2,		
		scroll: 1,	
        wrap: 'last',
        initCallback: mycarousel_initCallback
	});
}




function mycarousel_initCallback(carousel)
{
    // Disable autoscrolling if the user clicks the prev or next button.
    carousel.buttonNext.bind('click', function() {
        carousel.startAuto(1);
    });

    carousel.buttonPrev.bind('click', function() {
        carousel.startAuto(1);
    });

    // Pause autoscrolling if the user moves with the cursor over the clip.
    carousel.clip.hover(function() {
        carousel.stopAuto();
    }, function() {
        carousel.startAuto();
    });
};
