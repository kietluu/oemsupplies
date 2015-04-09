function WR360AdhocEmbedInitialize(placeholder, viewerWidth, viewerHeight, baseWidth, graphicsPath, configFileURL, rootPath, licensePath)
{
	var imageBlock = jQuery(placeholder);
	if (imageBlock.length <= 0)
		return;
	
	if (viewerWidth != "")
        imageBlock.css("width", viewerWidth);

	if (viewerHeight != "")
        imageBlock.css("height", viewerHeight)

    imageBlock.css("padding", 0);
	var newHtml = "<div id='wr360PlayerId'> \
                      <script language='javascript' type='text/javascript'> \
                         _imageRotator.settings.graphicsPath   = '" + graphicsPath + "'; \
                         _imageRotator.settings.configFileURL  = '" + configFileURL + "'; \
                         _imageRotator.settings.rootPath  = '" + rootPath + "'; \
                         _imageRotator.settings.responsiveBaseWidth = " + baseWidth + "; \
                         _imageRotator.licenseFileURL = '" + licensePath + "'; \
                      </script> \
                   </div>";

    imageBlock.html(newHtml);
    imageBlock.css("visibility", "visible");
	_imageRotator.runImageRotator("wr360PlayerId");
}


function WR360AdhocPopupInitialize(placeholder, framePath, thumbPath, prettyTheme)
{
    var imageBlock = jQuery(placeholder);
    if (imageBlock.length <= 0)
        return;

    var newHtml = "<a href='" + framePath + "'" + "rel='prettyPhoto'><img src='" + thumbPath + "'/></a>";
    imageBlock.html(newHtml);
    imageBlock.css("visibility", "visible");

    if (prettyTheme == "default")
        jq360("a[rel^='prettyPhoto']").prettyPhoto({deeplinking:false, animation_speed:0});
    else
        jq360("a[rel^='prettyPhoto']").prettyPhoto({theme:prettyTheme, deeplinking:false, animation_speed:0});
}

function WR360StartFrame360Viewer()
{
    var _360IFrameParams  = new parent.WR360IFrameParams();

    var curWidth  = jQuery("#wr360frame_id", window.parent.document).width();
    var curHeight = jQuery("#wr360frame_id", window.parent.document).height();

    jQuery("#frame_content").css("width",  curWidth + "px");
    jQuery("#frame_content").css("height", curHeight + "px");

    _imageRotator.settings.configFileURL   = _360IFrameParams.configFileURL;;
    _imageRotator.licenseFileURL           = _360IFrameParams.licensePath;
    _imageRotator.settings.rootPath        = _360IFrameParams.rootPath;
    _imageRotator.settings.graphicsPath    = _360IFrameParams.graphicsPath;
    _imageRotator.settings.viewWidthJQFix  = parseInt(curWidth);
    _imageRotator.settings.viewHeightJQFix = parseInt(curHeight);

    _imageRotator.runImageRotator("wr360PlayerId");
}

jq360 = jQuery.noConflict();


