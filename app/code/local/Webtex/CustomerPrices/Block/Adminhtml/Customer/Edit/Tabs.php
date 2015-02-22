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
class Webtex_CustomerPrices_Block_Adminhtml_Customer_Edit_Tabs extends Mage_Adminhtml_Block_Customer_Edit_Tabs
{
    protected function _beforeToHtml()
    {
        
//        $this->addTabAfter('customerprices', array(
//            'label'     => Mage::helper('customer')->__('Customer Prices'),
//            'class'     => 'ajax',
//            'url'       => $this->getUrl('customerprices/customer/customerprices', array('_current' => true)),
//        ),'addresses');

            $this->addTab('customerprices', array(
                'label'     => Mage::helper('catalog')->__('Customer Prices'),
                'content'   => $this->getLayout()
                    ->createBlock('customerprices/adminhtml_customer_edit_tab_customerprices')->toHtml(),
            ));
        return parent::_beforeToHtml();
    }
}
