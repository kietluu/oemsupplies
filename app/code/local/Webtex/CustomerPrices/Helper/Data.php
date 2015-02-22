<?php

/**
 * Webtex
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Webtex EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.webtex.com/LICENSE-1.0.html
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@webtex.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.webtex.com/ for more information
 * or send an email to sales@webtex.com
 *
 * @category   Webtex
 * @package    Webtex_CustomerPrices
 * @copyright  Copyright (c) 2010 Webtex (http://www.webtex.com/)
 * @license    http://www.webtex.com/LICENSE-1.0.html
 */

/**
 * Customer Prices extension
 *
 * @category   Webtex
 * @package    Webtex_CustomerPrices
 * @author     Webtex Dev Team <dev@webtex.com>
 */
class Webtex_CustomerPrices_Helper_Data extends Mage_Core_Helper_Abstract {
    const XML_CUSTOMERPRICES_ENABLED = 'customerprices/settings/isenable';
    const XML_CUSTOMERPRICES_HIDE_PRICE = 'customerprices/settings/hide_price';
    const XML_CUSTOMERPRICES_GROUPS = 'webtex_catalog/customerprices/groups';
    public $_categoryHide = null;


    public function isEnabled() {
        return Mage::getStoreConfigFlag(self::XML_CUSTOMERPRICES_ENABLED);
    }

    public function isHidePrice() {
        return Mage::getStoreConfigFlag(self::XML_CUSTOMERPRICES_HIDE_PRICE);
    }

    public function getCustomPrice($product) {
        $prices = Mage::getModel('customerprices/prices');
        $price = $prices->getProductPrice($product);

        return $price;
    }

    public function getCustomSpecialPrice($product) {
        $prices = Mage::getModel('customerprices/prices');
        $price = $prices->getProductSpecialPrice($product);

        return $price;
    }
    
    public function isShowListBlock($prod){
        if(!$this->isEnabled() || Mage::helper('customer')->isLoggedIn() || !$prod){
            return true;
        }
        $product = Mage::getModel('catalog/product')->load($prod->getId());
        $showPrice = $product->getAttributeText('guest_hide_price');
        if(!isset($this->_categoryHide)){
            $this->_categoryHide = $this->isHideCategoryPrice($product->getCategory());
        }
        
        return !($this->isHidePrice() || $this->_categoryHide || ($showPrice == 'Yes'));
    }
    
    public function isHideCategoryPrice($category) {
        if(!$category){
            return false;
        }

        if($category->getData('guest_hide_price') == 1){
            return true;
        } else {
            if($category->getParentId()!=0){
                return $this->isHideCategoryPrice($category->getParentCategory());
            }
            return false;
        }
    
    }
}