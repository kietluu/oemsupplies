/*
Project: bShowcase
Author: Brian DiChiara
Version: 1.3
Usage: Add this js file to your site assign a div as the #showcase,
	then give class .showcase-thumb to whatever you want to click
	set Caption to title attribute of image, split the lines by |
	set large src path to alt attribute of image
	Call bShowcase.init()
*/

var bShowcase = {
	
	$showcase : '',
	$image : '',
	$text : '',
	
	options : {
		speed : 300
	},
	
	init : function(selector, opts){
		if(!selector){
			selector = '#showcase';
		}
		
		bShowcase._setopts(opts);
		
		bShowcase.$showcase = $(selector);
		bShowcase.$image = $('.the-image', bShowcase.$showcase);
		bShowcase.$text = $('.the-text', bShowcase.$showcase);
		
		bShowcase.bind_thumbs();
	},
	
	bind_thumbs : function(e){
		var $thumbs = $('.showcase-thumb');
		$thumbs.click(function(e){
			var $new_img = $(this).find('img');
			var $sc_img = bShowcase.$image.find('img');
			
			if(!$sc_img.is(':animated') && $sc_img.attr('src') != $new_img.attr('alt')){
				// preload the image while the other is fading out
				var $preload = $('<img />').attr('src', $new_img.attr('alt'));

				$sc_img.fadeOut(bShowcase.options.speed, function(e){
					var text = $new_img.attr('title');
					var lines = text.split('|');
					$('.line1', bShowcase.$showcase).html(lines[0]);
					$('.line2', bShowcase.$showcase).html(lines[1]);
					
					$sc_img.attr('src', $new_img.attr('alt'));
					$sc_img.load(function(e){
						$sc_img.fadeIn(bShowcase.options.speed);
					});
				});
			}
			return false;
		});
	},
	
	_setopts : function(opts){
		if(typeof(opts) == 'object'){
			for(var opt in opts){
				bShowcase.options[opt] = opts[opt];
			}
		}
	}
}