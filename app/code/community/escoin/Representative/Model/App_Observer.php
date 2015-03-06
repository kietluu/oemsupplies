<?php 


class Escoin_Representative_Model_Observer extends Varien_Event_Observer
{

	const XML_PATH_REGISTER_EMAIL_TEMPLATE = 'customer/create_account/email_template';
    const XML_PATH_REGISTER_EMAIL_IDENTITY = 'customer/create_account/email_identity';
    const XML_PATH_REMIND_EMAIL_TEMPLATE = 'customer/password/remind_email_template';
    const XML_PATH_FORGOT_EMAIL_TEMPLATE = 'customer/password/forgot_email_template';
    const XML_PATH_FORGOT_EMAIL_IDENTITY = 'customer/password/forgot_email_identity';
    const XML_PATH_DEFAULT_EMAIL_DOMAIN         = 'customer/create_account/email_domain';
    const XML_PATH_IS_CONFIRM                   = 'customer/create_account/confirm';
    const XML_PATH_CONFIRM_EMAIL_TEMPLATE       = 'customer/create_account/email_confirmation_template';
    const XML_PATH_CONFIRMED_EMAIL_TEMPLATE     = 'customer/create_account/email_confirmed_template';
    const XML_PATH_GENERATE_HUMAN_FRIENDLY_ID   = 'customer/create_account/generate_human_friendly_id';


    const XML_PATH_EMAIL_TEMPLATE               = 'sales_email/order/template';
    const XML_PATH_EMAIL_GUEST_TEMPLATE         = 'sales_email/order/guest_template';
    const XML_PATH_EMAIL_IDENTITY               = 'sales_email/order/identity';
    const XML_PATH_EMAIL_COPY_TO                = 'sales_email/order/copy_to';
    const XML_PATH_EMAIL_COPY_METHOD            = 'sales_email/order/copy_method';
    const XML_PATH_EMAIL_ENABLED                = 'sales_email/order/enabled';

      const XML_PATH_UPDATE_EMAIL_TEMPLATE        = 'sales_email/order_comment/template';
    const XML_PATH_UPDATE_EMAIL_GUEST_TEMPLATE  = 'sales_email/order_comment/guest_template';
    const XML_PATH_UPDATE_EMAIL_IDENTITY        = 'sales_email/order_comment/identity';
    const XML_PATH_UPDATE_EMAIL_COPY_TO         = 'sales_email/order_comment/copy_to';
    const XML_PATH_UPDATE_EMAIL_COPY_METHOD     = 'sales_email/order_comment/copy_method';
    const XML_PATH_UPDATE_EMAIL_ENABLED         = 'sales_email/order_comment/enabled';

    const XML_PATH_EMAIL_TEMPLATE_INVOICE               = 'sales_email/invoice/template';
    const XML_PATH_EMAIL_GUEST_TEMPLATE_INVOICE         = 'sales_email/invoice/guest_template';
    const XML_PATH_EMAIL_IDENTITY_INVOICE               = 'sales_email/invoice/identity';
    const XML_PATH_EMAIL_COPY_TO_INVOICE                = 'sales_email/invoice/copy_to';
    const XML_PATH_EMAIL_COPY_METHOD_INVOICE            = 'sales_email/invoice/copy_method';
    const XML_PATH_EMAIL_ENABLED_INVOICE                = 'sales_email/invoice/enabled';

    const XML_PATH_UPDATE_EMAIL_TEMPLATE_INVOICE        = 'sales_email/invoice_comment/template';
    const XML_PATH_UPDATE_EMAIL_GUEST_TEMPLATE_INVOICE  = 'sales_email/invoice_comment/guest_template';
    const XML_PATH_UPDATE_EMAIL_IDENTITY_INVOICE        = 'sales_email/invoice_comment/identity';
    const XML_PATH_UPDATE_EMAIL_COPY_TO_INVOICE         = 'sales_email/invoice_comment/copy_to';
    const XML_PATH_UPDATE_EMAIL_COPY_METHOD_INVOICE     = 'sales_email/invoice_comment/copy_method';
    const XML_PATH_UPDATE_EMAIL_ENABLED_INVOICE         = 'sales_email/invoice_comment/enabled';

  


    const XML_PATH_EMAIL_TEMPLATE_SHIPMENT               = 'sales_email/shipment/template';
    const XML_PATH_EMAIL_GUEST_TEMPLATE_SHIPMENT        = 'sales_email/shipment/guest_template';
    const XML_PATH_EMAIL_IDENTITY_SHIPMENT               = 'sales_email/shipment/identity';
    const XML_PATH_EMAIL_COPY_TO_SHIPMENT                = 'sales_email/shipment/copy_to';
    const XML_PATH_EMAIL_COPY_METHOD_SHIPMENT            = 'sales_email/shipment/copy_method';
    const XML_PATH_EMAIL_ENABLED_SHIPMENT                = 'sales_email/shipment/enabled';

    const XML_PATH_UPDATE_EMAIL_TEMPLATE_SHIPMENT        = 'sales_email/shipment_comment/template';
    const XML_PATH_UPDATE_EMAIL_GUEST_TEMPLATE_SHIPMENT  = 'sales_email/shipment_comment/guest_template';
    const XML_PATH_UPDATE_EMAIL_IDENTITY_SHIPMENT        = 'sales_email/shipment_comment/identity';
    const XML_PATH_UPDATE_EMAIL_COPY_TO_SHIPMENT         = 'sales_email/shipment_comment/copy_to';
    const XML_PATH_UPDATE_EMAIL_COPY_METHOD_SHIPMENT     = 'sales_email/shipment_comment/copy_method';
    const XML_PATH_UPDATE_EMAIL_ENABLED_SHIPMENT         = 'sales_email/shipment_comment/enabled';




    /**
     * Order states
     */
    const STATE_NEW             = 'new';
    const STATE_PENDING_PAYMENT = 'pending_payment';
    const STATE_PROCESSING      = 'processing';
    const STATE_COMPLETE        = 'complete';
    const STATE_CLOSED          = 'closed';
    const STATE_CANCELED        = 'canceled';
    const STATE_HOLDED          = 'holded';
    const STATE_PAYMENT_REVIEW  = 'payment_review';



    protected function getCustomerRepresentative($customerId=0)
    {

        // $customer = Mage::getModel('customer/customer')->load($customerId);
        
        //  $representative = Mage::getModel('representative/representative')->load($customer->getSchool());
        //  return $representative;
        
        $rep = Mage::getModel('representative/representative')->getCollection();
        $reps = array();

        foreach ($rep as  $r) {
            if(stripos($r->getCustomer(),','.$customerId.',')!== FALSE){
                $reps = Mage::getModel('representative/representative')->load($r->getEntityId());
                break;
            }
        }
        return $reps;

    }

	/**
	 * [customerRegisterSuccess description]
	 * @param  [type] $evt
	 * @return [type]
	 */
	public function customerRegisterSuccess($evt)
	{
		$customer = $evt->getEvent()->getCustomer();
		//$customer = Mage::getModel('customer/customer')->load(1);
		//$rep_id = $customer->getSchool();
		//$representative =  $this->getCustomerRepresentative($customer->getEntityId());
         $representative = Mage::getModel('representative/representative')->load($customer->getSchool());
		
		
		if($representative->getCustomer() !== ''):
				$customers = $representative->getCustomer();
				$customers .= ',' . $customer->getEntityId();
		else:
				$customers = $customer->getEntityId();
		endif;
		
        $rep_name = $representative->getRepresentative();
		$representative->setCustomer($customers);
		$representative->save();

		$data['customer']=$customer;

		//--- Send Email Start----//
			
		$storeId = Mage::app()->getStore()->getId();
		$templateId = Mage::getStoreConfig(self::XML_PATH_REGISTER_EMAIL_TEMPLATE, $storeId);
		$mailer = Mage::getModel('core/email_template_mailer');
        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo($representative->getEmail(), $representative->getRepresentative());
        
        $mailer->addEmailInfo($emailInfo);

       
        // Set all required params and send emails
        $mailer->setSender(Mage::getStoreConfig(self::XML_PATH_REGISTER_EMAIL_IDENTITY, $storeId));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($templateId);
        $mailer->setTemplateParams(array('customer'=>$customer,'rep_name'=>$rep_name));
        $mailer->send();

		//$this->_sendMail("{$representative->getEmail()}",'Customer Support','new_customer_register',$data);


    	//--- Send Email End----//
				
	}

	protected function _sendMail($to,$from='',$template_type='',$templateVariables=array())
	{

		switch ($template_type) {
			case 'new_customer_register':
				$template_code = self::XML_PATH_REGISTER_EMAIL_TEMPLATE;
				$identity_code = self::XML_PATH_REGISTER_EMAIL_IDENTITY;
				break;
			
			default:
				# code...
				break;
		}
		$translate = Mage::getSingleton('core/translate');
		 /* @var $translate Mage_Core_Model_Translate */ 
		$translate->setTranslateInline(false);
		$storeId = Mage::app()->getStore()->getId();
		Mage::getModel('core/email_template')
		   ->setDesignConfig(array('area' => 'frontend', 'store' => $storeId))
		   ->sendTransactional(Mage::getStoreConfig($template_code, $storeId),
		  Mage::getStoreConfig($identity_code, $storeId),
		    $to,
		    $name,
		    $templateVariables
		    ); 
		   $translate->setTranslateInline(true);

		   return TRUE;

	}

	

	public function orderPlaceSuccess($evt)
	{
		$order = $evt->getEvent()->getOrder();

		$suc = $this->_sendNewOrderEmail($order);
				

	}

	 protected function _sendNewOrderEmail($order)
    {
        $storeId = Mage::app()->getStore()->getId();

        $representative = $this->getCustomerRepresentative($order->getCustomerId());

        // Start store emulation process
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

        try {
            // Retrieve specified view block from appropriate design package (depends on emulated store)
            $paymentBlock = Mage::helper('payment')->getInfoBlock($order->getPayment())
                ->setIsSecureMode(true);
            $paymentBlock->getMethod()->setStore($storeId);
            $paymentBlockHtml = $paymentBlock->toHtml();
        } catch (Exception $exception) {
            // Stop store emulation process
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
            throw $exception;
        }

        // Stop store emulation process
        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

        // Retrieve corresponding email template id and customer name
        if ($order->getCustomerIsGuest()) {
            $templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_GUEST_TEMPLATE, $storeId);
            $customerName = $order->getBillingAddress()->getName();
        } else {
            $templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE, $storeId);
            $customerName = $order->getCustomerName();
        }

        $mailer = Mage::getModel('core/email_template_mailer');
        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo($representative->getEmail(), $customerName);
        
        $mailer->addEmailInfo($emailInfo);

       
        // Set all required params and send emails
        $mailer->setSender(Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $storeId));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($templateId);
        $mailer->setTemplateParams(array(
                'order'        => $order,
                'billing'      => $order->getBillingAddress(),
                'payment_html' => $paymentBlockHtml
            )
        );

        		
         $mailer->send();
         return true;
       
    }

    public function orderInvoicePlace($evt)
    {
    	$invoice = $evt->getEvent()->getInvoice();
    	//$order = Mage::getModel('sales/order')->load($ord->getOrderId());
    			
        $this->sendInvoiceEmail($invoice);
    }

    public function sendInvoiceEmail($invoice,$notifyCustomer = true, $comment = '')
    {
        $order = $invoice->getOrder();
        $storeId = $order->getStore()->getId();
        $representative = $this->getCustomerRepresentative($order->getCustomerId());

        // Retrieve corresponding email template id and customer name
        if ($order->getCustomerIsGuest()) {
            $templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_GUEST_TEMPLATE_INVOICE, $storeId);
            $customerName = $order->getBillingAddress()->getName();
        } else {
            $templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE_INVOICE, $storeId);
            $customerName = $order->getCustomerName();
        }

        $mailer = Mage::getModel('core/email_template_mailer');
        if ($notifyCustomer) {
            $emailInfo = Mage::getModel('core/email_info');
            $emailInfo->addTo($representative->getEmail(), $customerName);
            $mailer->addEmailInfo($emailInfo);
        }

         try {
            // Retrieve specified view block from appropriate design package (depends on emulated store)
            $paymentBlock = Mage::helper('payment')->getInfoBlock($order->getPayment())
                ->setIsSecureMode(true);
            $paymentBlock->getMethod()->setStore($storeId);
            $paymentBlockHtml = $paymentBlock->toHtml();
        } catch (Exception $exception) {
            // Stop store emulation process
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
            throw $exception;
        }


        // Set all required params and send emails
        $mailer->setSender(Mage::getStoreConfig(self::XML_PATH_UPDATE_EMAIL_IDENTITY, $storeId));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($templateId);
        $mailer->setTemplateParams(array(
                'order'        => $order,
                'invoice'      => $invoice,
                'comment'      => $comment,
                'billing'      => $order->getBillingAddress(),
                'payment_html' => $paymentBlockHtml
            )
        );
        $mailer->send();

        return $this;
    }

    public function orderShipPlace($evt)
    {
        $shipment = $evt->getEvent()->getShipment();
        $this->sendShipmentEmail($shipment);
    }

    public function sendShipmentEmail($shipment,$notifyCustomer = true, $comment = '')
    {
        $order = $shipment->getOrder();
        $storeId = $order->getStore()->getId();
        $representative = $this->getCustomerRepresentative($order->getCustomerId());

        // Retrieve corresponding email template id and customer name
        if ($order->getCustomerIsGuest()) {
            $templateId = Mage::getStoreConfig(self::XML_PATH_UPDATE_EMAIL_GUEST_TEMPLATE_SHIPMENT, $storeId);
            $customerName = $order->getBillingAddress()->getName();
        } else {
            $templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE_SHIPMENT, $storeId);
            $customerName = $order->getCustomerName();
        }

        $mailer = Mage::getModel('core/email_template_mailer');
        if ($notifyCustomer) {
            $emailInfo = Mage::getModel('core/email_info');
            $emailInfo->addTo($representative->getEmail(), $customerName);
            $mailer->addEmailInfo($emailInfo);
        }

        try {
            // Retrieve specified view block from appropriate design package (depends on emulated store)
            $paymentBlock = Mage::helper('payment')->getInfoBlock($order->getPayment())
                ->setIsSecureMode(true);
            $paymentBlock->getMethod()->setStore($storeId);
            $paymentBlockHtml = $paymentBlock->toHtml();
        } catch (Exception $exception) {
            // Stop store emulation process
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
            throw $exception;
        }


        // Set all required params and send emails
        $mailer->setSender(Mage::getStoreConfig(self::XML_PATH_UPDATE_EMAIL_IDENTITY_SHIPMENT, $storeId));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($templateId);
        $mailer->setTemplateParams(array(
                'order'    => $order,
                'shipment' => $shipment,
                'comment'  => $order->getComment(),
                'billing'  => $order->getBillingAddress(),
                'payment_html' => $paymentBlockHtml
            )
        );
        $mailer->send();

        return $this;
    }

     public function sendOrderUpdateEmail($order)
    {

    	$notifyCustomer=TRUE;
       $storeId = Mage::app()->getStore()->getId();
       
       

        // Retrieve corresponding email template id and customer name
        if ($order->getCustomerIsGuest()) {
            $templateId = Mage::getStoreConfig(self::XML_PATH_UPDATE_EMAIL_GUEST_TEMPLATE, $storeId);
            $customerName = $order->getBillingAddress()->getName();
        } else {

            if($order->getState() == Mage_Sales_Model_Order::STATE_COMPLETE){

                $templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE_SHIPMENT, $storeId);
                
               }
            // $templateId = Mage::getStoreConfig(self::XML_PATH_UPDATE_EMAIL_TEMPLATE, $storeId);
             $customerName = $order->getCustomerName();
        }

        $mailer = Mage::getModel('core/email_template_mailer');
        if ($notifyCustomer) {
            $emailInfo = Mage::getModel('core/email_info');
            $emailInfo->addTo('tmukherjee13@gmail.com', $customerName);
           
            $mailer->addEmailInfo($emailInfo);
        }


        // Set all required params and send emails
        $mailer->setSender(Mage::getStoreConfig(self::XML_PATH_UPDATE_EMAIL_IDENTITY, $storeId));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($templateId);
        $mailer->setTemplateParams(array(
                'order'   => $order,
                'comment' => $order->getComment(),
                'billing' => $order->getBillingAddress()
            )
        );
        $mailer->send();

        return TRUE;
    }

    

 
	

 



	
}