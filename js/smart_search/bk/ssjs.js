(function( $ ) {
	$.fn.ssTypeAhead = function(options) {
		defaults = {
			serviceURL : "https://ta.ussco.com/smartchoice/smart-suggestions.jsonp",
			remoteId : "",
			keywordInterface : "Standard",
			selectEventFunction : null,
			minlength : 2
		};
		
		options = $.extend( defaults, options);
		
		function keywordDisplay(element) {
			if ($(element).val().length > options.minlength) {
				//alert(options.minlength);
				$.ajax({url:options.serviceURL,
					dataType: 'jsonp',
					jsonpCallback:'ssTACallback',
					data: {remoteId : options.remoteId, keywordInterface : options.keywordInterface, term : $(element).val()},
					success:function (data) {
						// Empty suggestions
						$(this.keywordEl).next().empty();
						var bucketSugs = data.sugs;
						if (bucketSugs.length == 0) return;
						for (loop=0; loop<bucketSugs.buckets.length; loop++) {
							var div1 = document.createElement('div');
							$(div1).addClass("ssSuggestBucket");
							if (bucketSugs.buckets[loop].header != null && bucketSugs.buckets[loop].header != "") {
								var h4 = document.createElement('h4');
								$(h4).html(bucketSugs.buckets[loop].header);
								$(div1).append(h4);
							}
							var ul1 = document.createElement('ul');
							$(div1).append(ul1);
							for (loopB=0; loopB<bucketSugs.buckets[loop].sugs.length; loopB++) {
								var li1 = document.createElement('li');
								$(ul1).append(li1);
								var a1 = document.createElement('a');
								$(li1).append(a1);
								$(a1).html(bucketSugs.buckets[loop].sugs[loopB].val);
								$(a1).attr('href','javascript:void(0);');

								$(li1).click(function() {
									var keyword = $(this).find(':first-child').html();
									$(this).parent().parent().parent().prev().val(keyword);
									if (options.selectEventFunction != null) {
										options.selectEventFunction(keyword);
									}
									$(this).parent().parent().parent().hide();
								});
							}
							$(this.keywordEl).next().append(div1);								
						}
						$(this.keywordEl).next().show();
					},
					keywordEl: $(element)
				});
			} else {
				$(element).next().hide();
			}
		}
		
		$(this).each(function() {
			// Create our suggestions box
			var div1 = document.createElement('div');
			$(div1).addClass("ssTypeAheadWrapper ajaxsearch");
			$(div1).css({'position':'absolute','width':'320px','top':'30px'});
			//$(div1).offset({left:0,top:($(this).offset().top + $(this).outerHeight(true))});
			$(div1).hide();			
			$(this).after(div1);
			
			// Check for events
			// Hide the dropdown on escape or enter
			$(this).keyup(function(event) {
				if ( event.keyCode == 27 || event.keyCode == 13 ) {
					$(this).next().hide();
				} else {
					keywordDisplay(this);
				}
			});
			//$(this).blur(function(event) {
			//	$(this).next().hide();
			//});
			// If more than 3 chars are entered then do type ahead
			$(this).keypress(function(event) {
				
			});
			$(this).focus(function(event) {
				keywordDisplay(this);
			});
		});
	};
})(jQuery);