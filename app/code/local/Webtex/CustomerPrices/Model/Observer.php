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

class Webtex_CustomerPrices_Model_Observer
{
	public function customerSaveAfter($observer)
	{
		$params = Mage::app()->getRequest()->getParams();
                $customerPrices = Mage::getModel('customerprices/prices'); 
		if(isset($params['customerprices'])){
		    $product = Mage::getModel('catalog/product');
		    foreach($params['customerprices'] as $_item){
		        $productId = $product->getIdBySku(trim($_item['sku']));
		        $customerPrices->loadByCustomer($productId, $params['customer_id'],$_item['qty'])
			               ->setCustomerId($params['customer_id'])
			               ->setCustomerEmail(trim($params['account']['email']))
			               ->setProductId($productId)
			               ->setStoreId($_item['website_id'])
			               ->setPrice($_item['price'])
			               ->setQty($_item['qty'])
			               ->setSpecialPrice($_item['specialprice']);

			if($_item['delete']=='1') {
			     $customerPrices->delete();
			} else {
			     $customerPrices->save();
			}
			$customerPrices->setId(null);
		    }
		}
		if(isset($params['user_discount'])) {
		        $customerPrices->loadByCustomer(0, $params['customer_id'],0)
			               ->setCustomerId($params['customer_id'])
			               ->setCustomerEmail(trim($params['account']['email']))
			               ->setProductId(0)
			               ->setStoreId($_item['website_id'])
			               ->setPrice(0)
			               ->setQty(0)
			               ->setSpecialPrice(0)
			               ->setDiscount($params['user_discount']);
			$customerPrices->save();
        
		}
	}

	
	public function productSaveAfter($observer)
	{
		$params = Mage::app()->getRequest()->getParams();
                $productId = $params['id'];
		foreach($params as $key => $value) {
			if(strpos($key, 'customerprices') === false || $value == 0) {
				continue;
			}
			foreach($value as $_item) {
			    $customerPrices = Mage::getModel('customerprices/prices');
			     $customerPrices->loadByCustomer($productId, $_item['customerId'],$_item['qty'])
					        ->setCustomerId($_item['customerId'])
					        ->setCustomerEmail(trim($_item['email']))
					        ->setProductId($productId)
					        ->setStoreId($_item['website_id'])
				                ->setPrice($_item['price'])
				                ->setQty($_item['qty'])
				                ->setSpecialPrice($_item['specialprice']);
			    if($_item['delete']=='1') {
			         $customerPrices->delete();
			    } else {
			         $customerPrices->save();
			    }
			}
		}
	}

    public function layeredPrice($observer)
    {
        $collection = $observer->getCollection();

        $query = Mage::app()->getRequest()->getQuery();
        if(empty($query ) || !isset($query['price'])) {
            return $this;
        }

        list($index, $range) = explode('-', $query['price']);
        $begin = $index;
        $to = $range;

        if(empty($begin)){
           $begin = 0;
        }
        
        if(empty($to)){
            $to = PHP_INT_MAX;
        }

        if(($customer = Mage::getModel('customer/session')->getCustomer()) && $customer->getId()) {
            $col = "IF(cp_prices.special_price > 0, cp_prices.special_price,IF(cp_prices.price > 0,cp_prices.price,IF(price_index.final_price IS NULL, price_index.min_price,price_index.final_price)))";
            //$col = "IF(cp_prices.special_price > 0, cp_prices.special_price,IF(cp_prices.price > 0,cp_prices.price,IF(price_index.final_price > 0, price_index.final_price,price_index.min_price)))";
            //$col = "IF(cp_prices.special_price > 0, cp_prices.special_price,IF(cp_prices.price > 0,cp_prices.price,price_index.final_price))";

            $tablePrefix = (string) Mage::getConfig()->getTablePrefix();
            $from = $collection->getSelect()->getPart('from');
            if(!in_array('cp_prices', array_keys($from))) {
                $_where = $collection->getSelect()->getPart('where');
                $collection->getSelect()
                       ->joinLeft(array('cp_prices' => $tablePrefix.'customerprices_prices'),
                            'cp_prices.product_id=e.entity_id AND cp_prices.customer_id='.$customer->getId().' and cp_prices.qty = 1',
                            array())
                        ->reset(Zend_Db_Select::WHERE)
                        ->where($col.' >= '.$begin)
                        ->where($col.' < '.$to);

                foreach($_where as $expression){
                    if(!strpos($expression, 'min_price')){
                       if(substr($expression,0,3) == "AND") {
                           $collection->getSelect()->where(substr($expression,4));
                       } else {
                           $collection->getSelect()->where($expression);
                       }
                    }
                }

            }
        }
    }
    
    public function sortByPrice($observer)
    {
        $collection = $observer->getCollection();
        $orderPart = $collection->getSelect()->getPart(Zend_Db_Select::ORDER);
        $isPriceOrder = false;
        foreach($orderPart as $v) {
            if(is_array($v) && $v[0] == 'price_index.min_price') {
                $isPriceOrder = true;
                $orderDir = $v[1];
            }
        }

        $customer = Mage::getModel('customer/session')->getCustomer();

        if(!$isPriceOrder || !$customer->getId()){
            return $this;
        }

        $from = $collection->getSelect()->getPart('from');
        if(!in_array('cp_prices', array_keys($from))) {
            $col = "IF(cp_prices.special_price > 0, cp_prices.special_price,IF(cp_prices.price > 0,cp_prices.price,IF(price_index.final_price IS NULL, price_index.min_price,price_index.final_price)))";
            //$col = "IF(cp_prices.special_price > 0, cp_prices.special_price,IF(cp_prices.price > 0,cp_prices.price,IF(price_index.final_price > 0, price_index.final_price,price_index.min_price)))";
            //$col = "IF(cp_prices.special_price > 0, cp_prices.special_price,IF(cp_prices.price > 0,cp_prices.price,price_index.final_price))";

            $tablePrefix = (string) Mage::getConfig()->getTablePrefix();
            $collection->getSelect()
                   ->joinLeft(array('cp_prices' => $tablePrefix.'customerprices_prices'),
                        'cp_prices.product_id=e.entity_id AND cp_prices.customer_id='.$customer->getId(),
                        array());

            $collection->getSelect()
                ->reset(Zend_Db_Select::ORDER)
                ->order('CAST(min_price as signed)'.$orderDir);
        }
    }

	public function collectionLoadAfter($observer)
	{
	  $_session = Mage::getSingleton('adminhtml/session_quote');
	  $prices = Mage::getModel('customerprices/prices');
	  if($_session->getCustomerId()) {
	      foreach($observer->getCollection() as $_item) {
	          $oldPrice = $_item->getData('price');
	          $price = $prices->getProductCustomerPrice($_item,$_session->getCustomerId());
	          $specialPrice = $prices->getProductCustomerSpecialPrice($_item,$_session->getCustomerId());
                  $rulePrice  = Mage::getModel('catalogrule/rule')->calcProductPriceRule($_item, $price);
                  $specialPriceFrom = $_item->getSpecialFromDate();
                  $specialPriceTo   = $_item->getSpecialToDate();
                  $_item->setPrice($price);
                  if($specialPrice){
                      $_item->setSpecialPrice($specialPrice);
                  } else {
                      $_item->setSpecialPrice(null);
                  }
                  $rulePrice  = Mage::getModel('catalogrule/rule')->calcProductPriceRule($_item, $price);
	          if($_item->getTypeId() != 'bundle') {
	              $finalPrice = $_item->getPriceModel()->calculatePrice($price,$specialPrice,$specialPriceFrom,$specialPriceTo,$rulePrice,null,null,$_item->getId());
	              $_item->setCalculatedFinalPrice($finalPrice);
	              $_item->setFinalPrice($finalPrice);
	              if($_item->getTypeId() == 'grouped'){
	                 $_item->setGroupedPrice($_item);
	              }
	              if($_item->getTypeId() == 'configurable') {
	                 $_item->setConfPrice($_item,$oldPrice);
	              }
	          } else {
	             $minP = $_item->getMinimalPrice($_item);
	             $maxP = $_item->getMaximalPrice($_item);
	             $_item->setMinPrice($minP);
	             $_item->setMinimalPrice($minP);
	             $_item->setMaxPrice($maxP);
	             $_item->setMaximalPrice($maxP);
	          }
	      }
	   } else {
	      foreach($observer->getCollection() as $_item) {
	          $oldPrice = $_item->getData('price');
	          $price = $prices->getProductPrice($_item);
	          $specialPrice = $prices->getProductSpecialPrice($_item);
                  $rulePrice  = Mage::getModel('catalogrule/rule')->calcProductPriceRule($_item, $price);
                  $specialPriceFrom = $_item->getSpecialFromDate();
                  $specialPriceTo   = $_item->getSpecialToDate();
                  $_item->setPrice($price);
                  if($specialPrice){
                      $_item->setSpecialPrice($specialPrice);
                  } else {
                      $_item->setSpecialPrice(null);
                  }
                  $rulePrice  = Mage::getModel('catalogrule/rule')->calcProductPriceRule($_item, $price);
	          if($_item->getTypeId() != 'bundle') {
	              $finalPrice = $_item->getPriceModel()->calculatePrice($price,$specialPrice,$specialPriceFrom,$specialPriceTo,$rulePrice,null,null,$_item->getId());
	              $_item->setCalculatedFinalPrice($finalPrice);
	              $_item->setFinalPrice($finalPrice);
	              $_item->setData('price',$price);
	              $_item->setData('minimal_price',$price);
	              if($_item->getTypeId() == 'grouped'){
	                 $_item->setGroupedPrice($_item);
	              }
	              if($_item->getTypeId() == 'configurable') {
	                 $_item->setConfPrice($_item,$oldPrice);
	              }
	          } else {
	             $minP = $_item->getMinimalPrice($_item);
	             $maxP = $_item->getMaximalPrice($_item);
	             $_item->setMinPrice($minP);
	             $_item->setMinimalPrice($minP);
	             $_item->setMaxPrice($maxP);
	             $_item->setMaximalPrice($maxP);
	          }
	      }
	   }
          return $this;
	}
	
	public function productLoadAfter($observer)
	{
	    $prices = Mage::getModel('customerprices/prices');
            $product = $observer->getEvent()->getProduct();
            $_session = Mage::getSingleton('adminhtml/session_quote');
            if($_session->getCustomerId()) {
               $product->setPrice($prices->getProductCustomerPrice($product,$_session->getCustomerId()));
	       $product->setSpecialPrice($prices->getProductCustomerSpecialPrice($product,$_session->getCustomerId()));
	    } else {
              $price = $prices->getProductPrice($product);
	      $specialPrice = $prices->getProductSpecialPrice($product);
              $rulePrice  = Mage::getModel('catalogrule/rule')->calcProductPriceRule($product, $price);
              $specialPriceFrom = $product->getSpecialFromDate();
              $specialPriceTo   = $product->getSpecialToDate();
	      if($product->getTypeId() != 'bundle') {
	          $finalPrice = $product->getPriceModel()->calculatePrice($price,$specialPrice,$specialPriceFrom,$specialPriceTo,$rulePrice,null,null,$product->getId());
	          $product->setCalculatedFinalPrice($finalPrice);
	          $product->setFinalPrice($finalPrice);
	          $product->setData('price',$price);
	      }
	    }
	    return $this;
	}

	public function processFrontFinalPrice($observer)
	{
	    $product = $observer->getEvent()->getProduct();
	    $prices = Mage::getModel('customerprices/prices');
	    if($product->getTypeId() != 'bundle') {
	          $basePrice        = $prices->getProductPrice($product);
	          $specialPrice     = $prices->getProductSpecialPrice($product);
                  $specialPriceFrom = $product->getSpecialFromDate();
                  $specialPriceTo   = $product->getSpecialToDate();
                  $specialPriceFrom = $product->getSpecialFromDate();
                  $specialPriceTo = $product->getSpecialToDate();
                  $rulePrice  = Mage::getModel('catalogrule/rule')->calcProductPriceRule($product, $basePrice);
                  $finalPrice = $product->getPriceModel()->calculatePrice(
                                $basePrice,
                                $specialPrice,
                                $specialPriceFrom,
                                $specialPriceTo,
                                $rulePrice,
                                null,
                                null,
                                $product->getId());
                 $product->setCalculatedFinalPrice($finalPrice);
                 $product->setData('final_price', $finalPrice);
            }
          return $this;
        }

	public function processBackFinalPrice($observer)
	{
           $_session = Mage::getSingleton('adminhtml/session_quote');
	   $customer = $_session->getCustomerId();
	   $prod    = $observer->getEvent()->getProduct();
	   $qty     = $observer->getEvent()->getQty();
	   if($customer && $prod->getTypeId() != 'bundle') {
	    $product = Mage::getModel('catalog/product')->load($prod->getId());
	    $basePrice = $product->getData('price');
	    $prices = Mage::getModel('customerprices/prices');
	    $price     = $prices->getProductCustomerPrice($product,$customer,$qty);
	    $sPrice    = $prices->getProductCustomerSpecialPrice($product,$customer,$qty);
            $specialPriceFrom = $product->getSpecialFromDate();
            $specialPriceTo   = $product->getSpecialToDate();
            $rulePrice  = Mage::getModel('catalogrule/rule')->calcProductPriceRule($product, $basePrice);
            $finalPrice = $product->getPriceModel()->calculatePrice(
                            $basePrice,
                            $sPrice,
                            $specialPriceFrom,
                            $specialPriceTo,
                            $rulePrice,
                            null,
                            null,
                            $product->getId());
             $product->setCalculatedFinalPrice($finalPrice);
             $product->setData('final_price', $finalPrice);
             $product->setData('price',$price);
            }
          return $this;
        }

}