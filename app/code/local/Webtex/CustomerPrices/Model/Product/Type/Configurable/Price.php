<?php

class Webtex_CustomerPrices_Model_Product_Type_Configurable_Price extends Mage_Catalog_Model_Product_Type_Configurable_Price
{

    public function getFinalPrice($qty=null, $product)
    {
        if(!Mage::helper('customer')->isLoggedIn()) {
            return parent::getFinalPrice($qty,$product);
        }
        
	$customer = Mage::getSingleton('customer/session')->getCustomer();
        

        if (is_null($qty) && !is_null($product->getCalculatedFinalPrice())) {
            return $product->getCalculatedFinalPrice();
        }
        if(is_null($qty)){
            $qty = 1;
        }
    
        $product->getTypeInstance(true)
            ->setStoreFilter($product->getStore(), $product);

        $attributes = $product->getTypeInstance(true)
            ->getConfigurableAttributes($product);
		
        $attrInfo = $product->getTypeInstance(true)->getSelectedAttributesInfo($product);
        $realProduct = null;
        
        $productPrice = $product->getData('final_price');
        
        foreach ($product->getTypeInstance(true)->getUsedProductIds($product) as $productId) {
         $productObject = Mage::getModel('catalog/product')->load($productId);
             foreach($attributes as $attr) {
                 $productAttribute   = $attr->getProductAttribute();
                 $attributeValue     = $productObject->getAttributeText($productAttribute->getAttributeCode());
                 if($attributeValue == $attrInfo[count($attrInfo)-1]['value']){
                     $realProduct = $productObject;
                 }
             }
        }
        

        $model = Mage::getModel('customerprices/prices')->getCollection()
	         ->addFieldToFilter('product_id', $realProduct->getId())
	         ->addFieldToFilter('customer_id',$customer->getId());
	if($model->count()){
	   $rows = $model->getData();
           if(isset($rows[0]['price']) && (int)$rows[0]['price'] != 0){
	    if($pos = strpos($rows[0]['price'],'%')){
	        if(in_array(substr($rows[0]['price'], 0, 1), array('+', '-'))) {
	            $productPrice = $productPrice + $productPrice/100 * (int)$rows[0]['price'] ;
	        } else {
	            $productPrice = $productPrice/100 * (int)$rows[0]['price'] ;
	        }
	    } elseif (in_array(substr($rows[0]['price'], 0, 1), array('+', '-'))){
	        $productPrice = $productPrice + (int)$rows[0]['price'] ;
	    } else {
	        $productPrice =  $rows[0]['price'];
	    }
	   }
           return $productPrice;
	}
        return parent::getFinalPrice($qty,$product);

    }

    public function getValueByIndex($prices,$attributeId)
    {
       return $this->_getValueByIndex($prices,$attributeId);
    }
}