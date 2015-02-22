jQuery(document).ready(function(){	
	// applying the settings
	jQuery('#theMenu_acc').Accordion({
		active: 'span.selected',
		header: 'span.head',
		alwaysOpen: false,
		animated: true,
		showSpeed: 400,
		hideSpeed: 800
	});
	
	jQuery('#shopby').Accordion({
		active: 'span.selected',
		header: 'span.title',
		alwaysOpen: false,
		animated: true,
		showSpeed: 400,
		hideSpeed: 800
	});
});	
function initMenu() {
  jQuery('#menu ol').show();
  jQuery('#menu li a').click(
    function() {
        jQuery(this).next().slideToggle('normal');
		}
	);
  jQuery('#menu li a.title_auto').click(
    function() {
       	jQuery(this).toggleClass("select_auto");
	    }
	);
	
  jQuery('#menu_collap ol').hide();
  jQuery('#menu_collap li a').click(
    function() {
        jQuery(this).next().slideToggle('normal');
		}
	);
  jQuery('#menu_collap li a.select_collap').click(
    function() {
      	jQuery(this).toggleClass("title_collap");
	    }
	);
	
  jQuery('#cate_collap ul').hide();
  jQuery('#cate_collap li a.title_collap').click(
    function() {
        jQuery(this).next().slideToggle('normal');
		}
	);
   jQuery('#cate_collap li a.title_collap').click(
    function() {
      	jQuery(this).toggleClass("select_auto");
	    }
	);
}
jQuery(document).ready(function() {initMenu();});



  
