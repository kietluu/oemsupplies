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
 * Representative admin controller
 *
 * @category	Escoin
 * @package		Escoin_Representative
 * @author Ultimate Module Creator
 */
class Escoin_Representative_Adminhtml_Representative_RepresentativeController extends Escoin_Representative_Controller_Adminhtml_Representative{
	/**
	 * init the representative
	 * @access protected
	 * @return Escoin_Representative_Model_Representative
	 */
	protected function _initRepresentative(){
		$representativeId  = (int) $this->getRequest()->getParam('id');
		$representative	= Mage::getModel('representative/representative');
		if ($representativeId) {
			$representative->load($representativeId);
		}
		Mage::register('current_representative', $representative);
		return $representative;
	}
 	/**
	 * default action
	 * @access public
	 * @return void
	 * @author Ultimate Module Creator
	 */
	public function indexAction() {
		$this->loadLayout();
		$this->_title(Mage::helper('representative')->__('Representative'))
			 ->_title(Mage::helper('representative')->__('Representatives'));
		$this->renderLayout();
	}
	/**
	 * grid action
	 * @access public
	 * @return void
	 * @author Ultimate Module Creator
	 */
	public function gridAction() {
		$this->loadLayout()->renderLayout();
	}
	/**
	 * edit representative - action
	 * @access public
	 * @return void
	 * @author Ultimate Module Creator
	 */
	public function editAction() {
		$representativeId	= $this->getRequest()->getParam('id');
		$representative  	= $this->_initRepresentative();
		if ($representativeId && !$representative->getId()) {
			$this->_getSession()->addError(Mage::helper('representative')->__('This representative no longer exists.'));
			$this->_redirect('*/*/');
			return;
		}
		$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
		if (!empty($data)) {
			$representative->setData($data);
		}
		Mage::register('representative_data', $representative);
		$this->loadLayout();
		$this->_title(Mage::helper('representative')->__('Representative'))
			 ->_title(Mage::helper('representative')->__('Representatives'));
		if ($representative->getId()){
			$this->_title($representative->getRepresentative());
		}
		else{
			$this->_title(Mage::helper('representative')->__('Add representative'));
		}
		if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) { 
			$this->getLayout()->getBlock('head')->setCanLoadTinyMce(true); 
		}
		$this->renderLayout();
	}
	/**
	 * new representative action
	 * @access public
	 * @return void
	 * @author Ultimate Module Creator
	 */
	public function newAction() {
		$this->_forward('edit');
	}
	/**
	 * save representative - action
	 * @access public
	 * @return void
	 * @author Ultimate Module Creator
	 */
	public function saveAction() {
		if ($data = $this->getRequest()->getPost('representative')) {
			try {
                            
                            
                                $customers='';
                                foreach($data['customer'] as $c):
                                    $customers.=$c.',';
                                endforeach;
                                $customers = rtrim($customers, ',');
                                $data['customer']=$customers;
                            
                            
				$representative = $this->_initRepresentative();
				$representative->addData($data);
				$representative->save();
                                
                                
                                
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('representative')->__('Representative was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);
				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $representative->getId()));
					return;
				}
				$this->_redirect('*/*/');
				return;
			} 
			catch (Mage_Core_Exception $e){
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				Mage::getSingleton('adminhtml/session')->setFormData($data);
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
				return;
			}
			catch (Exception $e) {
				Mage::logException($e);
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('representative')->__('There was a problem saving the representative.'));
				Mage::getSingleton('adminhtml/session')->setFormData($data);
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
				return;
			}
		}
		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('representative')->__('Unable to find representative to save.'));
		$this->_redirect('*/*/');
	}
	/**
	 * delete representative - action
	 * @access public
	 * @return void
	 * @author Ultimate Module Creator
	 */
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0) {
			try {
				$representative = Mage::getModel('representative/representative');
				$representative->setId($this->getRequest()->getParam('id'))->delete();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('representative')->__('Representative was successfully deleted.'));
				$this->_redirect('*/*/');
				return; 
			}
			catch (Mage_Core_Exception $e){
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
			catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('representative')->__('There was an error deleteing representative.'));
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
				Mage::logException($e);
				return;
			}
		}
		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('representative')->__('Could not find representative to delete.'));
		$this->_redirect('*/*/');
	}
	/**
	 * mass delete representative - action
	 * @access public
	 * @return void
	 * @author Ultimate Module Creator
	 */
	public function massDeleteAction() {
		$representativeIds = $this->getRequest()->getParam('representative');
		if(!is_array($representativeIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('representative')->__('Please select representatives to delete.'));
		}
		else {
			try {
				foreach ($representativeIds as $representativeId) {
					$representative = Mage::getModel('representative/representative');
					$representative->setId($representativeId)->delete();
				}
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('representative')->__('Total of %d representatives were successfully deleted.', count($representativeIds)));
			}
			catch (Mage_Core_Exception $e){
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
			catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('representative')->__('There was an error deleteing representatives.'));
				Mage::logException($e);
			}
		}
		$this->_redirect('*/*/index');
	}
	/**
	 * mass status change - action
	 * @access public
	 * @return void
	 * @author Ultimate Module Creator
	 */
	public function massStatusAction(){
		$representativeIds = $this->getRequest()->getParam('representative');
		if(!is_array($representativeIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('representative')->__('Please select representatives.'));
		} 
		else {
			try {
				foreach ($representativeIds as $representativeId) {
				$representative = Mage::getSingleton('representative/representative')->load($representativeId)
							->setStatus($this->getRequest()->getParam('status'))
							->setIsMassupdate(true)
							->save();
				}
				$this->_getSession()->addSuccess($this->__('Total of %d representatives were successfully updated.', count($representativeIds)));
			}
			catch (Mage_Core_Exception $e){
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
			catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('representative')->__('There was an error updating representatives.'));
				Mage::logException($e);
			}
		}
		$this->_redirect('*/*/index');
	}
	/**
	 * export as csv - action
	 * @access public
	 * @return void
	 * @author Ultimate Module Creator
	 */
	public function exportCsvAction(){
		$fileName   = 'representative.csv';
		$content	= $this->getLayout()->createBlock('representative/adminhtml_representative_grid')->getCsv();
		$this->_prepareDownloadResponse($fileName, $content);
	}
	/**
	 * export as MsExcel - action
	 * @access public
	 * @return void
	 * @author Ultimate Module Creator
	 */
	public function exportExcelAction(){
		$fileName   = 'representative.xls';
		$content	= $this->getLayout()->createBlock('representative/adminhtml_representative_grid')->getExcelFile();
		$this->_prepareDownloadResponse($fileName, $content);
	}
	/**
	 * export as xml - action
	 * @access public
	 * @return void
	 * @author Ultimate Module Creator
	 */
	public function exportXmlAction(){
		$fileName   = 'representative.xml';
		$content	= $this->getLayout()->createBlock('representative/adminhtml_representative_grid')->getXml();
		$this->_prepareDownloadResponse($fileName, $content);
	}
}