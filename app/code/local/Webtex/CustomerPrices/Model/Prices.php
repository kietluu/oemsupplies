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
 * Customer Prics extension
 *
 * @category   Webtex
 * @package    Webtex_CustomerPrices
 * @author     Webtex Dev Team <dev@webtex.com>
 */
class Webtex_CustomerPrices_Model_Prices extends Mage_Core_Model_Abstract {

    protected static $_url = null;
    protected $_product = null;
    protected $_pricesCollection = null;

    public function _construct() {
        parent::_construct();
        $this->_init('customerprices/prices');
    }

    public function getPricesCollection($productId) {
        if (is_null($this->_pricesCollection)) {
            $this->_pricesCollection = Mage::getResourceModel('customerprices/prices_collection')
                            ->addProductFilter($productId);
        }

        return $this->_pricesCollection;
    }

    public function deleteByCustomer($productId, $customerId) {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tablePrefix = (string) Mage::getConfig()->getTablePrefix();

        $connection->delete($tablePrefix . 'customerprices_prices', 'product_id = ' . $productId . ' and customer_id = ' . $customerId);
    }

    public function deleteByProduct($productId) {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tablePrefix = (string) Mage::getConfig()->getTablePrefix();

        $connection->delete($tablePrefix . 'customerprices_prices', 'product_id = ' . $productId);
    }

    public function getProductPrice($product, $qty = 1)
    {
        if (!Mage::helper('customer')->isLoggedIn() || !($product->getId()) || $product->getApplyCustomerPrice()) {
            return min($product->getPrice(), $product->getGroupPrice());
        }

        $productPrice = min($product->getData('price'), $product->getGroupPrice());

        if(is_null($qty)) {
            $qty = 1;
        }
    
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tablePrefix = (string) Mage::getConfig()->getTablePrefix();

        $customerId = Mage::helper('customer')->getCustomer()->getId();

        $query = $connection->select()->from($tablePrefix . 'customerprices_prices')->where('product_id = 0 AND customer_id = ' . $customerId . ' AND qty = 0');
        $row = $connection->fetchRow($query);

	if(isset($row['discount'])){
	    if($pos = strpos($row['discount'],'%')){
	        if(in_array(substr($row['discount'], 0, 1), array('+', '-'))) {
	            $productPrice = $productPrice + $productPrice/100 * (int)$row['discount'] ;
	        } else {
	            $productPrice = $productPrice/100 * (int)$row['discount'] ;
	        }
	    } elseif (in_array(substr($row['discount'], 0, 1), array('+', '-'))){
	        $productPrice = $productPrice + (int)$row['discount'] ;
	    } else {
	        $productPrice =  $row['discount'];
	    }
	}

        $query = $connection->select()->from($tablePrefix . 'customerprices_prices')->where('product_id = ' . $product->getId() . ' AND customer_id = ' . $customerId . ' AND qty <= '. $qty);
        $row = $connection->fetchRow($query);

        if(isset($row['price']) && (int)$row['price'] != 0){
	    if($pos = strpos($row['price'],'%')){
	        if(in_array(substr($row['price'], 0, 1), array('+', '-'))) {
	            $productPrice = $productPrice + $productPrice/100 * (int)$row['price'] ;
	        } else {
	            $productPrice = $productPrice/100 * (int)$row['price'] ;
	        }
	    } elseif (in_array(substr($row['price'], 0, 1), array('+', '-'))){
	        $productPrice = $productPrice + (int)$row['price'] ;
	    } else {
	        $productPrice =  $row['price'];
	    }
	}

	
        $product->setApplyCustomerPrice(true);
        return $productPrice;
    }

    public function getProductSpecialPrice($product, $qty = 1)
    {
        if (!Mage::helper('customer')->isLoggedIn() || !($product->getId()) || $product->getApplyCustomerSpecialPrice()) {
            return $product->getData('special_price');
        }

        $store = Mage::app()->getStore();
        $specialPriceFrom = $product->getSpecialFromDate();
        $specialPriceTo   = $product->getSpecialToDate();
        if (!Mage::app()->getLocale()->isStoreDateInInterval($store, $specialPriceFrom, $specialPriceTo)) {
            return ;
        }

        if(is_null($qty)) {
            $qty = 1;
        }
        
        $productPrice = $product->getData('special_price');
        if(!$productPrice){
            $productPrice = 0; 
        }

        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tablePrefix = (string) Mage::getConfig()->getTablePrefix();

        $customerId = Mage::helper('customer')->getCustomer()->getId();

        $query = $connection->select()->from($tablePrefix . 'customerprices_prices')->where('product_id = 0 AND customer_id = ' . $customerId . ' AND qty = 0');
        $row = $connection->fetchRow($query);

	if(isset($row['discount'])){
	    if($pos = strpos($row['discount'],'%')){
	        if(in_array(substr($row['discount'], 0, 1), array('+', '-'))) {
	            $productPrice = $productPrice + $productPrice/100 * (int)$row['discount'] ;
	        } else {
	            $productPrice = $productPrice/100 * (int)$row['discount'] ;
	        }
	    } elseif (in_array(substr($row['discount'], 0, 1), array('+', '-'))){
	        $productPrice = $productPrice + (int)$row['discount'] ;
	    } else {
	        $productPrice =  $row['discount'];
	    }
	}


        $query = $connection->select()->from($tablePrefix . 'customerprices_prices')->where('product_id = ' . $product->getId() . ' AND customer_id = ' . $customerId . ' and qty <= '.$qty);
        $row = $connection->fetchRow($query);
	if(isset($row['special_price']) && (int)$row['special_price'] != 0){
	    if($pos = strpos($row['special_price'],'%')){
	        if(in_array(substr($row['special_price'], 0, 1), array('+', '-'))) {
	            $productPrice = $productPrice + $productPrice/100 * (int)$row['special_price'] ;
	        } else {
	            $productPrice = $productPrice/100 * (int)$row['special_price'] ;
	        }
	    } elseif (in_array(substr($row['special_price'], 0, 1), array('+', '-'))){
	        $productPrice = $productPrice + (int)$row['special_price'] ;
	    } else {
	        $productPrice =  $row['special_price'];
	    }
	}

        if($productPrice > 0){
            $product->setApplyCustomerSpecialPrice(true);
            $product->setSpecialPrice($productPrice);
        }
        return $productPrice;
    }

    public function getProductCustomerPrice($product,$customerId,$qty = 1)
    {
        if(is_null($qty)) {
            $qty = 1;
        }
        
        $productPrice = min($product->getPrice(), $product->getGroupPrice());
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tablePrefix = (string) Mage::getConfig()->getTablePrefix();

        $query = $connection->select()->from($tablePrefix . 'customerprices_prices')->where('product_id = 0 AND customer_id = ' . $customerId . ' AND qty = 0');
        $row = $connection->fetchRow($query);

	if(isset($row['discount'])){
	    if($pos = strpos($row['discount'],'%')){
	        if(in_array(substr($row['discount'], 0, 1), array('+', '-'))) {
	            $productPrice = $productPrice + $productPrice/100 * (int)$row['discount'] ;
	        } else {
	            $productPrice = $productPrice/100 * (int)$row['discount'] ;
	        }
	    } elseif (in_array(substr($row['discount'], 0, 1), array('+', '-'))){
	        $productPrice = $productPrice + (int)$row['discount'] ;
	    } else {
	        $productPrice =  $row['discount'];
	    }
	}

        $query = $connection->select()->from($tablePrefix . 'customerprices_prices')->where('product_id = ' . $product->getId() . ' AND customer_id = ' . $customerId .' and qty <= '.$qty);
        $row = $connection->fetchRow($query);

        if(isset($row['price']) && (int)$row['price'] != 0){
	    if($pos = strpos($row['price'],'%')){
	        if(in_array(substr($row['price'], 0, 1), array('+', '-'))) {
	            $productPrice = $productPrice + $productPrice/100 * (int)$row['price'] ;
	        } else {
	            $productPrice = $productPrice/100 * (int)$row['price'] ;
	        }
	    } elseif (in_array(substr($row['price'], 0, 1), array('+', '-'))){
	        $productPrice = $productPrice + (int)$row['price'] ;
	    } else {
	        $productPrice =  $row['price'];
	    }
	}
        return $productPrice;
    }

    public function getProductCustomerSpecialPrice($product,$customerId, $qty = 1)
    {

        $store = Mage::app()->getStore();
        $specialPriceFrom = $product->getSpecialFromDate();
        $specialPriceTo   = $product->getSpecialToDate();
        if (!Mage::app()->getLocale()->isStoreDateInInterval($store, $specialPriceFrom, $specialPriceTo)) {
            return ;
        }

        if(is_null($qty)) {
            $qty = 1;
        }

        $productPrice = $product->getData('special_price');
        if(!$productPrice){
            $productPrice = 0;
        }

        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tablePrefix = (string) Mage::getConfig()->getTablePrefix();

        $query = $connection->select()->from($tablePrefix . 'customerprices_prices')->where('product_id = 0 AND customer_id = ' . $customerId . ' AND qty = 0');
        $row = $connection->fetchRow($query);

	if(isset($row['discount'])){
	    if($pos = strpos($row['discount'],'%')){
	        if(in_array(substr($row['discount'], 0, 1), array('+', '-'))) {
	            $productPrice = $productPrice + $productPrice/100 * (int)$row['discount'] ;
	        } else {
	            $productPrice = $productPrice/100 * (int)$row['discount'] ;
	        }
	    } elseif (in_array(substr($row['discount'], 0, 1), array('+', '-'))){
	        $productPrice = $productPrice + (int)$row['discount'] ;
	    } else {
	        $productPrice =  $row['discount'];
	    }
	}


        $query = $connection->select()->from($tablePrefix . 'customerprices_prices')->where('product_id = ' . $product->getId() . ' AND customer_id = ' . $customerId . ' and qty <= '.$qty);
        $row = $connection->fetchRow($query);
	if(isset($row['special_price']) && (int)$row['special_price'] != 0){
	    if($pos = strpos($row['special_price'],'%')){
	        if(in_array(substr($row['special_price'], 0, 1), array('+', '-'))) {
	            $productPrice = $productPrice + $productPrice/100 * (int)$row['special_price'] ;
	        } else {
	            $productPrice = $productPrice/100 * (int)$row['special_price'] ;
	        }
	    } elseif (in_array(substr($row['special_price'], 0, 1), array('+', '-'))){
	        $productPrice = $productPrice + (int)$row['special_price'] ;
	    } else {
	        $productPrice =  $row['special_price'];
	    }
	}

        if($productPrice > 0){
            $product->setApplyCustomerSpecialPrice(true);
            $product->setSpecialPrice($productPrice);
        }
        return $productPrice;
    }

    public function loadByCustomer($productId, $customerId, $qty = 1)
    {
        if(is_null($qty)) {
            $qty = 1;
        }
        $this->setData($this->getResource()->loadByCustomer($productId, $customerId, $qty));
        return $this;
    }

}