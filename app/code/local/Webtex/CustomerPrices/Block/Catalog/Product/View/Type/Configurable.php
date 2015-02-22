<?php
/**
 * Webtex
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Webtex EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.webtexsoftware.com/LICENSE-1.0.html
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@webtex.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.webtexsoftware.com/ for more information
 * or send an email to sales@webtexsoftware.com
 *
 * @category   Webtex
 * @package    Webtex_PricesPerCustomer
 * @copyright  Copyright (c) 2010 Webtex (http://www.webtexsoftware.com/)
 * @license    http://www.webtex.com/LICENSE-1.0.html
 */

/**
 * Prices Per Customer extension
 *
 * @category   Webtex
 * @package    Webtex_PricesPerCustomer
 * @author     Webtex Dev Team <dev@webtex.com>
 */

class Webtex_CustomerPrices_Block_Catalog_Product_View_Type_Configurable extends Mage_Catalog_Block_Product_View_Type_Configurable
{
	public function getJsonConfig()
    {
		if(!Mage::helper('customer')->isLoggedIn()) {
		    return parent::getJsonConfig();
		}
		
		$customer = Mage::getSingleton('customer/session')->getCustomer();
		$config = Mage::helper('core')->jsonDecode(parent::getJsonConfig());

		foreach($config['attributes'] as $attrId => $attr){
			$basePrice   = $this->getProduct()->getFinalPrice();
			foreach($attr['options'] as $k => $value){
                                 $prod = Mage::getModel('catalog/product')->load($value['products'][0]);
                                 $prices = Mage::getModel('customerprices/prices');

	                         $pr  = $prices->getProductCustomerPrice($prod, $customer->getId());
	                         $spr = $prices->getProductCustomerSpecialPrice($prod, $customer->getId());

                                 $rpr  = Mage::getModel('catalogrule/rule')->calcProductPriceRule($prod, $pr);
                                 $rspr = Mage::getModel('catalogrule/rule')->calcProductPriceRule($prod, $spr);

                                 // if exists: $price = PriceRule
                                 if($rpr > 0) {
                                    $pr = $rpr;
                                 }
                                 if($rspr > 0) {
                                    $spr = $rspr;
                                 }
                                 // end PriceRule
                                 if($spr > 0) {
                                    $pr = $spr;
                                 }
                                $config['attributes'][$attrId]['options'][$k]['price'] = $pr - $basePrice;
			     }
		}

	        return Mage::helper('core')->jsonEncode($config);
	}
}