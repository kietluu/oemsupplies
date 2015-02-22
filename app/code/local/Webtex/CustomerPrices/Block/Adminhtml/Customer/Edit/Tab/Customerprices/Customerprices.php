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
class Webtex_CustomerPrices_Block_Adminhtml_Customer_Edit_Tab_Customerprices_Customerprices
    extends Mage_Adminhtml_Block_Widget
{
    protected $_customer;
    protected $_products;
    protected $_websites;

    /**
     * Define tier price template file
     *
     */
    public function __construct()
    {
        $this->_setProducts();
        $this->_customer = Mage::registry('current_customer');
        $this->setTemplate('webtex/customerprices/customer/edit/customerpricesgrid.phtml');
    }
    
    public function _prepareLayout()
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'     => Mage::helper('catalog')->__('Add Price'),
                'onclick'   => 'return customerPriceControl.showProductGrid();',
                'class'     => 'add'
            ));

        $button->setName('add_customer_price_item_button');

        $this->setChild('add_button', $button);
        return parent::_prepareLayout();
    
    }

    public function getProduct()
    {
        return $this->_product;
    }

    public function getValues()
    {
        if($this->_customer->getId()) {
            $product = Mage::getModel('catalog/product');
            $collection = Mage::getModel('customerprices/prices')->getCollection()->addCustomerFilter($this->_customer->getId())->addFieldToFilter('qty',array('gt' => 0));
            foreach($collection as $item){
                $product->load($item->getProductId());
                $item->setName($product->getName());
                $item->setSku($product->getSku());
            }
            return $collection;
        } else {
            return false;
        }
    }

    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }
    
    public function _setProducts()
    {
        if(!$this->_products) {
            $this->_products = array();
        }
        
        $collection = Mage::getResourceModel('catalog/product_collection');
        
        foreach($collection as $product)
        {
            $this->_products[] = $product->getData();
        }
    }

    public function getWebsites()
    {
        if (!is_null($this->_websites)) {
            return $this->_websites;
        }

        $this->_websites = array(
            0   => array(
                'name'      => Mage::helper('catalog')->__('All Websites'),
                'currency'  => Mage::app()->getBaseCurrencyCode()
            )
        );

        $websites           = Mage::app()->getWebsites(false);
        foreach ($websites as $website) {
            $this->_websites[$website->getId()] = array(
                'name'      => $website->getName(),
                'currency'  => $website->getBaseCurrencyCode()
            );
        }
        
        return $this->_websites;
    }

    public function getDefaultWebsite()
    {
        return Mage::app()->getStore($this->_customer->getStoreId())->getWebsiteId();
    }

    public function allowChangeWebsite()
    {
        return false;
    }
}
