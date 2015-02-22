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
class Webtex_CustomerPrices_Block_Adminhtml_Customer_Edit_Tab_Customerprices extends Mage_Adminhtml_Block_Widget_Form 
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('webtex/customerprices/customer/edit/customerprices.phtml');
    }

    protected function _prepareLayout()
    {
        $this->setChild('customerprices_search_block',
            $this->getLayout()->createBlock('customerprices/Adminhtml_Customer_Edit_Tab_Customerprices_Productgrid')
        );

        $this->setChild('customerprices_box',
            $this->getLayout()->createBlock('customerprices/Adminhtml_Customer_Edit_Tab_Customerprices_Customerprices')
        );
        return parent::_prepareLayout();
    }

    public function getCustomerpricesGridHtml()
    {
        return $this->getChildHtml('customerprices_search_block');
    }
    public function getCustomerpricesBoxHtml()
    {
        return $this->getChildHtml('customerprices_box');
    }
    
    public function getCustomerDiscount()
    {
        $customer = Mage::registry('current_customer');
        $customerId = $customer->getId();
        if($customerId) {
            $products = Mage::getModel('customerprices/prices')->getCollection()->addCustomerFilter($customerId)->load();
            return $products->getData();
        }
        return '';
    }
}
