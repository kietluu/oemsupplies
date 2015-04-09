<?php

class Recycle_Emptytoner_IndexController extends Mage_Core_Controller_Front_Action {

	public function indexAction() {
		//Get current layout state
		$this->loadLayout();
		$block = $this->getLayout()->createBlock(
            'Mage_Core_Block_Template',
            'recycle.empty_toner',
		array('template' => 'recycle/Emptytoner.phtml'));

		$this->getLayout()->getBlock('content')->append($block);
                $left_block = $this->getLayout()->createBlock(
            'Mage_Core_Block_Template',
            'featured',
		array('template' => 'catalog/navigation/featured_random.phtml'));

                $this->getLayout()->getBlock('left')->insert($left_block , 'featured', true);
		$this->getLayout()->getBlock('right')->insert($block, 'catalog.compare.sidebar', true);
		$this->_initLayoutMessages('core/session');
		$customer_data = Mage::getSingleton('customer/session')->getCustomer();
                $name = $customer_data->getName();
		$email = $customer_data->getEmail();
		$block->assign(array('name'=>$name ,'email'=> $email));
		$this->renderLayout();
	}

	public function requestservicetechAction() {
		//Get current layout state
		$this->loadLayout();
		$block = $this->getLayout()->createBlock(
            'Mage_Core_Block_Template',
            'recycle.service_tech',
		array('template' => 'recycle/Servicetechnician.phtml'));
            
            $left_block = $this->getLayout()->createBlock(
            'Mage_Core_Block_Template',
            'featured',
		array('template' => 'catalog/navigation/featured_random.phtml'));

                $this->getLayout()->getBlock('left')->insert($left_block , 'featured', true);

		$params = $this->getRequest()->getParams();
		if(isset($params) && !empty($params)) {
			$company_name = $params['companyname'] ;
			$name = $params['firstname'] ." ". $params['lastname'] ;
			$phone_number   = $params['phone'] ;
			$email_address  = $params['email'] ;
			$printername    = $params['printername'] ;
			$printermodelno = $params['printermodelno'] ;
			$under_warranty = $params['under_warranty'] ;
			if ($email_address && !empty($name) && $phone_number && $company_name) {
				$htmtmsg = '<br /><br /><table style="border-collapse:collapse;" cellpadding="5" cellspacing="0" align="left" border="1">
						 <tr><td width="180px;">Company : </td><td>'.$company_name.'</td></tr>
						 <tr><td>Name : </td><td>'.$name.'</td></tr>
						 <tr><td>Phone Number : </td><td>' . $phone_number .'</td></tr>
						 <tr><td>Email : </td><td>' . $email_address .'</td></tr>
                                                 <tr><td>Printer Name : </td><td>' . $printername .'</td></tr>
                                                 <tr><td>Printer Model No. : </td><td>' . $printermodelno .'</td></tr>
                                                 <tr><td>Under Warrenty : </td><td>' . $under_warranty .'</td></tr>
						 <tr><td>Comments : </td><td style="width:300px;word-wrap:break-word;">' . $_POST['comment'] .'</td></tr>
					 </table> <br /><br />';

				$text_message = "Company : $company_name \n\n";
				$text_message .= "Name : $name \n\n";
				$text_message .= "Phone Number : $phone_number \n\n";
				$text_message .= "Email : $email_address \n\n";
				$text_message .= "Comments : {$_POST['comment']} \n\n";
				$text_message .= "\n\n";
				$htmtmsg .= "<div><br clear='all' /></div><br /><br />";
				$subject = "Service Technician Request";

				$headers = "MIME-Version: 1.0" . "\r\n";
				$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
				$headers .= "From: daron_walton@oemsupplies.com" . "\r\n" ."";

				try{
					$to = "daron_walton@oemsupplies.com";
					mail($to,$subject,$htmtmsg ,$headers);
					
					mail("vipin.garg12@gmail.com",$subject,$htmtmsg ,$headers);
					Mage::getSingleton('core/session')->addSuccess('Your request has been sent. We will contact you shortly.');
				}
				catch(Exception $ex) {
					Mage::getSingleton('core/session')->addError('An error occured, Please try again.');
				}
			}
			
			$this->_redirect('request-service-technician');
		}

		$this->getLayout()->getBlock('content')->append($block);
		$this->getLayout()->getBlock('right')->insert($block, 'catalog.compare.sidebar', true);
		$this->_initLayoutMessages('core/session');
		$customer_data = Mage::getSingleton('customer/session')->getCustomer();
		$name = $customer_data->getName();
		$email = $customer_data->getEmail();
		$name = explode(" " , $name) ;
		$block->assign(array('name'=>$name ,'email'=> $email));
		$this->renderLayout();
	}

	public function sendemailAction() {
		//Fetch submited params
		$params = $this->getRequest()->getParams();
		if(isset($params) && !empty($params)) {
			$company_name = $params['companyname'] ;
			$name = $params['contactname'] ;
			$phone_number = $params['phone'] ;
			$email_address = $params['email'] ;
				
			if ($email_address && !empty($name) && $phone_number && $company_name) {
				$htmtmsg = '<br /><br /><table style="border-collapse:collapse;" cellpadding="5" cellspacing="0" align="left" border="1">
						 <tr><td width="180px;">Company : </td><td>'.$company_name.'</td></tr>
						 <tr><td>Name : </td><td>'.$name.'</td></tr>
						 <tr><td>Phone Number : </td><td>' . $phone_number .'</td></tr>
						 <tr><td>Email : </td><td>' . $email_address .'</td></tr>
						 <tr><td>Comments : </td><td style="width:300px;word-wrap:break-word;">' . $_POST['comment'] .'</td></tr>
					 </table> <br /><br />';

				$text_message = "Company : $company_name \n\n";
				$text_message .= "Name : $name \n\n";
				$text_message .= "Phone Number : $phone_number \n\n";
				$text_message .= "Email : $email_address \n\n";
				$text_message .= "Comments : {$_POST['comment']} \n\n";
				$text_message .= "\n\n";
				$htmtmsg .= "<div><br clear='all' /></div><br /><br />";
				$subject = "Recycle Empty Toners Request";

				$headers = "MIME-Version: 1.0" . "\r\n";
				$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
				$headers .= "From: donotreply@oemsupplies.com" . "\r\n" ."";

				try{
					$to = "daron_walton@oemsupplies.com";
					mail($to,$subject,$htmtmsg ,$headers);
					
					mail("vipin.garg12@gmail.com",$subject,$htmtmsg ,$headers);
					Mage::getSingleton('core/session')->addSuccess('Your request has been sent. We will contact you shortly.');
				}
				catch(Exception $ex) {
					Mage::getSingleton('core/session')->addError('An error occured, Please try again.');
				}
			}
		}

		$this->_redirect('recycle-empty-toner');
	}
}