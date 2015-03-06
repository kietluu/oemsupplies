<?php
/**
 * Escoin_Representative extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category   	Escoin
 * @package		Escoin_Representative
 * @copyright  	Copyright (c) 2013
 * @license		http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Representative model
 *
 * @category	Escoin
 * @package		Escoin_Representative
 * @author Ultimate Module Creator
 */
class Escoin_Representative_Model_Representative extends Mage_Core_Model_Abstract{
	/**
	 * Entity code.
	 * Can be used as part of method name for entity processing
	 */
	const ENTITY= 'representative_representative';
	const CACHE_TAG = 'representative_representative';
	/**
	 * Prefix of model events names
	 * @var string
	 */
	protected $_eventPrefix = 'representative_representative';
	
	/**
	 * Parameter name in event
	 * @var string
	 */
	protected $_eventObject = 'representative';
	/**
	 * constructor
	 * @access public
	 * @return void
	 * @author Ultimate Module Creator
	 */
	public function _construct(){
		parent::_construct();
		$this->_init('representative/representative');
	}
	/**
	 * before save representative
	 * @access protected
	 * @return Escoin_Representative_Model_Representative
	 * @author Ultimate Module Creator
	 */
	protected function _beforeSave(){
		parent::_beforeSave();
		$now = Mage::getSingleton('core/date')->gmtDate();
		if ($this->isObjectNew()){
			$this->setCreatedAt($now);
		}
		$this->setUpdatedAt($now);
		return $this;
	}
	/**
	 * save representative relation
	 * @access public
	 * @return Escoin_Representative_Model_Representative
	 * @author Ultimate Module Creator
	 */
	protected function _afterSave() {
		return parent::_afterSave();
	}
        
        public function getCustomers(){
            $customers =  Mage::getModel('customer/customer')->getCollection()->addAttributeToSelect('*');
            $cust=array();
            $i = 1;
            foreach($customers as $cus):
               $cust[$i++] = array('value'=>$cus->getEntityId(),'label'=>$cus->getName());
            endforeach;
            return $cust;
        }
        
}