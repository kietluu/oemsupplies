<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
	<meta name="author" content="Brian DiChiara" />

	<title>Carousel Demo</title>
	<style type="text/css">
		/* reset */
		* { margin:0; padding:0; border:0; }
	</style>
	
	<link rel="stylesheet" type="text/css" href="bcarousel.css" />
	<script type="text/javascript" src="jquery.min.js"></script>
	<script type="text/javascript" src="jquery.easing.1.3.js"></script>
	<script type="text/javascript" src="bcarousel.js"></script>
	<script type="text/javascript" src="bshowcase.js"></script>
	
	<script type="text/javascript">
	
	$(function(r){
		// delay init() because webkit is just TOO f'n fast
		setTimeout(function(e){
			bCarousel.init();
		}, 300);
		
		//bShowcase.init();
	});
	// alert("mia dcm");
		// $(".bCarousel-wrapper").hover(function() {
			// $(".back").addClass("abc");
			// $(".forward").addClass("abc");
		// });
	
	</script>
	
	<style type="text/css">
		body { padding:25px; }
		
		.the-image {
			height:300px;
			overflow:hidden;
		}
		.the-image img {
			width:500px;
		}
		
		#info {
			padding:15px;
			margin:15px;
			background:#EEE;
		}
	</style>
</head>
<body>

<h1>bCarousel + bShowcase Demo</h1>

<div id="showcase">
	<div class="the-image">
		<img src="sample-images/sample1.jpg" />
	</div>
	<div class="the-text">
		<div class="line1">Sample Image 1-1</div>
		<div class="line2">Sample Caption 1-1</div>
	</div>
</div>

<div id="the-carousel" class="bcarousel">
	<ul>
		
			<li><a href="http://dantri.com.vn/" class="showcase-thumb">
				<img src="sample-images/thumbs/sample1.jpg" alt="" title="" /></a>
			</li>
			<li><a href="http://dantri.com.vn/" class="showcase-thumb">
				<img src="sample-images/thumbs/sample2.jpg" alt="" title="" /></a>
			</li>
			<li><a href="http://dantri.com.vn/" class="showcase-thumb">
				<img src="sample-images/thumbs/sample3.jpg" alt="" title="" /></a>
			</li>
			<li><a href="http://dantri.com.vn/" class="showcase-thumb">
				<img src="sample-images/thumbs/sample4.jpg" alt="" title="" /></a>
			</li>
			<li><a href="http://dantri.com.vn/" class="showcase-thumb">
				<img src="sample-images/thumbs/sample5.jpg" alt="" title="" /></a>
			</li>
		
	</ul>
</div>



</body>
</html>