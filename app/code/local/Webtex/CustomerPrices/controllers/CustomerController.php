<?php

class Webtex_CustomerPrices_CustomerController extends Mage_Adminhtml_Controller_Action
{
    protected function _initCustomer($idFieldName = 'id')
    {
        $this->_title($this->__('Customers'))->_title($this->__('Manage Customers'));

        $customerId = (int) $this->getRequest()->getParam($idFieldName);
        $customer = Mage::getModel('customer/customer');

        if ($customerId) {
            $customer->load($customerId);
        }

        Mage::register('current_customer', $customer);
        return $this;
    }

    public function customerpricesAction()
    {
        $this->_initCustomer();
        $this->loadLayout();
        $this->getResponse()->setBody($this->getLayout()->createBlock('customerprices/adminhtml_customer_edit_tab_customerprices')->toHtml());
    }

    public function productsgridAction()
    {
        $this->_initCustomer();
        $this->loadLayout();
        $this->getResponse()->setBody($this->getLayout()->createBlock('customerprices/adminhtml_customer_edit_tab_customerprices_productgrid_grid')->toHtml());
    }

}
