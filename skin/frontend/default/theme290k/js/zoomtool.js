/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    design
 * @package     enterprise_default
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */
(function( $ ) {
    $.fn.horizontalSlider = function(options){
         options = $.extend({
            speed: 3500
         }, options);

            return this.each(function () {
                var slider = $(this),
                    sliderWrapperWidth = slider.parent().innerWidth(),
                    next = $(options.next).attr('id'),
                    controls = $(options.next + ',' + options.prev),
                    slidingPosition,
                    sliderWidth = 0,
                    slides = $.map(slider.children(),function(val){
                        return $(val).width();
                    });

                $.each(slides,function() {
                    sliderWidth += this;
                });

                slider.css('width', sliderWidth);

                controls.mouseover(function(elem) {
                   elem.target.id == next ? slidingPosition = - sliderWidth + sliderWrapperWidth : slidingPosition = 0;
                   slider.animate({ marginLeft: slidingPosition }, options.speed);
                }).mouseout(function() {
                   slider.stop();
                });
            });
        }
})( jQuery );

/*
Slimbox v2.04 - The ultimate lightweight Lightbox clone for jQuery
(c) 2007-2010 Christophe Beyls <http://www.digitalia.be>
MIT-style license.
*/
(function(w) {
var E = w(window),u,f,F = -1,n,x,D,v,y,L,r,m = !window.XMLHttpRequest,s = [],l = document.documentElement,k = {},t = new Image(),J = new Image(),H,a,g,p,I,d,G,c,A,K;
w(function() {
    w("body").append(w([H = w('<div id="lbOverlay" />')[0],a = w('<div id="lbCenter" class="lbLoad" />')[0],G = w('<div id="lbBottomContainer" />')[0]]).css("display", "none"));
    g = w('<div id="lbImage" />').appendTo(a).append(p = w('<div style="position: relative;" />').append([I = w('<a id="lbPrevLink" href="#" />').click(B)[0],d = w('<a id="lbNextLink" href="#" />').click(e)[0]])[0])[0];
    c = w('<div id="lbBottom" />').appendTo(G).append([w('<a id="lbCloseLink" href="#" />').add(H).click(C)[0],A = w('<div id="lbCaption" />')[0],K = w('<div id="lbNumber" />')[0],w('<div style="clear: both;" />')[0]])[0]
});
w.slimbox = function(O, N, M) {
    u = w.extend({loop:false,overlayOpacity:0.8,overlayFadeDuration:400,resizeDuration:400,resizeEasing:"swing",initialWidth:250,initialHeight:250,imageWidth:725,imageHeight:680,imageFadeDuration:400,captionAnimationDuration:400,counterText:"Image {x} of {y}",closeKeys:[27,88,67],previousKeys:[37,80],nextKeys:[39,78]}, M);
    if (typeof O == "string") {
        O = [
            [O,N]
        ];
        N = 0
    }
    y = E.scrollTop() + (E.height() / 2);
    L = u.initialWidth;
    r = u.initialHeight;
    w(a).css({top:Math.max(0, y - (r / 2)),width:L,height:r,marginLeft:-L / 2 - 20}).show();
    v = m || (H.currentStyle && (H.currentStyle.position != "fixed"));
    if (v) {
        H.style.position = "absolute"
    }
    w(H).css("opacity", u.overlayOpacity).fadeIn(u.overlayFadeDuration);
    z();
    j(1);
    f = O;
    u.loop = u.loop && (f.length > 1);
    return b(N)
};
w.fn.slimbox = function(M, P, O) {
    P = P || function(Q) {
        return[Q.href,Q.title]
    };
    O = O || function() {
        return true
    };
    var N = this;
    return N.unbind("click").click(function() {
        var S = this,U = 0,T,Q = 0,R;
        T = w.grep(N, function(W, V) {
            return O.call(S, W, V)
        });
        for (R = T.length; Q < R; ++Q) {
            if (T[Q] == S) {
                U = Q
            }
            T[Q] = P(T[Q], Q)
        }
        return w.slimbox(T, U, M)
    })
};
function z() {
    var N = E.scrollLeft(),M = E.width();
    w([a,G]).css("left", N + (M / 2));
    if (v) {
        w(H).css({left:N,top:E.scrollTop(),width:M,height:E.height()})
    }
}

function j(M) {
    if (M) {
        w("object").add(m ? "select" : "embed").each(function(O, P) {
            s[O] = [P,P.style.visibility];
            P.style.visibility = "hidden"
        })
    } else {
        w.each(s, function(O, P) {
            P[0].style.visibility = P[1]
        });
        s = []
    }
    var N = M ? "bind" : "unbind";
    E[N]("scroll resize", z);
    w(document)[N]("keydown", o)
}

function o(O) {
    var N = O.keyCode,M = w.inArray;
    return(M(N, u.closeKeys) >= 0) ? C() : (M(N, u.nextKeys) >= 0) ? e() : (M(N, u.previousKeys) >= 0) ? B() : false
}

function B() {
    return b(x)
}

function e() {
    return b(D)
}

function b(M) {
    if (M >= 0) {
        F = M;
        n = f[F][0];
        x = (F || (u.loop ? f.length : 0)) - 1;
        D = ((F + 1) % f.length) || (u.loop ? 0 : -1);
        q();
        a.className = "lbLoading";
        k = new Image();
        k.onload = i;
        k.src = n
    }
    return false
}

function i() {
    a.className = "";
    w(g).css({backgroundImage:"url(" + n + ")",visibility:"hidden",display:""});
    w(p).width(!!u.imageWidth ? u.imageWidth : k.width);
    w([p,I,d]).height(!!u.imageHeight ? u.imageHeight : k.height);
    w(A).html(f[F][1] || "");
    w(K).html((((f.length > 1) && u.counterText) || "").replace(/{x}/, F + 1).replace(/{y}/, f.length));
    if (x >= 0) {
        t.src = f[x][0]
    }
    if (D >= 0) {
        J.src = f[D][0]
    }
    L = g.offsetWidth;
    r = g.offsetHeight;
    var M = Math.max(0, y - (r / 2));
    if (a.offsetHeight != r) {
        w(a).animate({height:r,top:M}, u.resizeDuration, u.resizeEasing)
    }
    if (a.offsetWidth != L) {
        w(a).animate({width:L,marginLeft:-L / 2 - 20}, u.resizeDuration, u.resizeEasing, function() {
//            w(this).removeClass('lbLoad');
        });
    }
    w(a).queue(function() {
        w(G).css({width:L,top:M + r,marginLeft:-L / 2 - 20,visibility:"hidden",display:""});
        w(g).css({display:"none",visibility:"",opacity:""}).fadeIn(u.imageFadeDuration, h)
    })
}

function h() {
    if (x >= 0) {
        w(I).show()
    }
    if (D >= 0) {
        w(d).show()
    }
    w(c).css("marginTop", -c.offsetHeight).animate({marginTop:0}, u.captionAnimationDuration);
    G.style.visibility = ""
    Z();
}

function Z() {
    var wrapper = w('#lbCenter'),
        links = w('a', wrapper),
        className = 'shown';

    if (w(links[0]).css('display') != 'none' && w(links[1]).css('display') != 'none') {
        wrapper.removeClass('shown_next shown_prev shown_none');
    } else if (w(links[0]).css('display') == 'none' && w(links[1]).css('display') == 'none') {
        wrapper.removeClass('shown_next shown_prev').addClass('shown_none');
    } else {
        wrapper.addClass(className + (w(links[0]).css('display') != 'none' ? '_prev' : '_next'));
    }
}

function q() {
    k.onload = null;
    k.src = t.src = J.src = n;
    w([a,g,c]).stop(true);
    w([I,d,G]).hide();
    w('#lbCenter').addClass('lbLoad');
}

function C() {
    if (F >= 0) {
        q();
        F = x = D = -1;
        w(a).hide();
        w(H).stop().fadeOut(u.overlayFadeDuration, j)
    }

    jQuery("body").css("overflow","auto");

    return false
}
})(jQuery);


jQuery(document).ready(function() {
	/* Zoom in and slide images of a carousel */
	var urlMap = new Array(),
	    urls = new Array(),
	    thumbnails = jQuery('#product-view-media-slider-list a.product-view-media-gallery'),
	    zoomButton = jQuery('#product-view-media-zoomtool');

	thumbnails.map(function() { urlMap.push(this.href) });
	jQuery.unique(urlMap.toArray());
	jQuery.map(urlMap, function(e, i){ urls.push(new Array(e)) });

	!urls.length && urls.push([jQuery('#product-view-media-main-image img').attr('src')]);

	zoomButton.bind('click.zoomtool', function() {
	    jQuery.slimbox(urls, parseInt(jQuery(this).attr('rel').substring(1)), {
	        overlayFadeDuration: 0,
	        imageFadeDuration: 0,
	        captionAnimationDuration: 0,
	        resizeDuration:0,
	        closeKeys: [27, 70],
	        nextKeys: [39, 83],
	        initialWidth: 725,
	        initialHeight: 540,
	        imageWidth: 720,
	        imageHeight: 540
	    });

	    jQuery("body").css("overflow","hidden");

	    return false;
	});

	thumbnails.bind('click.zoomtoolImage', function() {
	    jQuery('#product-view-media-main-image').children().first().replaceWith(jQuery('<img/>', {
	        src: jQuery(this).attr('rel'),
	        alt: jQuery(this).attr('title'),
	        width: 308,
	        height: 308
	    }));

	    zoomButton.attr('rel', "#"+ jQuery(this).attr('id').substring(4)).show();
	    return false;
	});

	// Click on video thumbnail
	jQuery('#product-view-media-slider-list a.product-view-media-slider-video').bind('click.zoomtoolVideo', function(){
	    jQuery('#product-view-media-main-image').children().first().replaceWith('<iframe width="450" height="300" src="' + jQuery(this).attr('href') + '" frameborder="0" allowfullscreen></iframe>');
	    zoomButton.hide();
	    return false;
	});

	// Release Slider for thumbnails
	if (jQuery('#product-view-media-slider-list ul li').length >= 5){
	    jQuery('#product-view-media-slider-list ul').horizontalSlider({
	        next: '#product-view-media-slider-next',
	        prev: '#product-view-media-slider-prev',
	        speed: 5000
	    });
	}
});/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


