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

class Webtex_CustomerPrices_Block_Adminhtml_Customer_Edit_Tab_Customerprices_Productgrid_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('customerPricesCustomerSearchGrid');
        $this->setUseAjax(true);
        $this->setDefaultSort('entity_id');
    }


    protected function _prepareCollection()
    {
        $customers = array();
        $customers[] = 0;
        
        if($selected = $this->_getSelectedProducts()) {
            foreach($selected as $item) {
                $products[] = $item['product_id'];
            }
        }

        $collection = Mage::getResourceModel('catalog/product_collection')->addAttributeToSelect('name');
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('customer')->__('ID'),
            'width'     => '50px',
            'index'     => 'entity_id',
            'type'  => 'number',
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('customer')->__('Name'),
            'index'     => 'name'
        ));
        $this->addColumn('sku', array(
            'header'    => Mage::helper('customer')->__('SKU'),
            'width'     => '250',
            'index'     => 'sku'
        ));

        $this->addColumn('in_prices', array(
            'header'    => Mage::helper('sales')->__('Select'),
            'header_css_class' => 'a-center',
            'type'      => 'checkbox',
            'values'    => $this->_getSelectedProducts(),
            'name'      => 'in_prices',
            'align'     => 'center',
            'index'     => 'entity_id',
            'sortable'  => false,
        ));


        return parent::_prepareColumns();
    }

    public function _getSelectedProducts()
    {
        if($customer = Mage::registry('current_customer')) {
            $customerId = $customer->getId();
        } else {
            $customerId = Mage::app()->getRequest()->getParam('id');
        }
        if($customerId) {
            $products = Mage::getModel('customerprices/prices')
                ->getCollection()
                ->addCustomerFilter($customerId)
                ->addFieldToSelect('product_id')
                ->load();
            return $products;
        } else {
            return false;
        }
    }

    public function getGridUrl()
    {
        return $this->getUrl('customerprices/customer/productsgrid', array('_current'=> true));
    }
}
