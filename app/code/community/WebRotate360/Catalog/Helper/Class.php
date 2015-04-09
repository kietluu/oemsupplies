<?php

class WebRotate360_Catalog_Helper_Class extends Mage_Core_Helper_Abstract
{
    public function getSkinBasedOnConfig()
    {
        $_viewerSkin = Mage::getStoreConfig('wr360_product_view/settings/viewer_skin');

        return ("webrotate360/imagerotator/html/css/" . $_viewerSkin . ".css");
    }
}