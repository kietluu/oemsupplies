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
 * Representative admin edit tabs
 *
 * @category	Escoin
 * @package		Escoin_Representative
 * @author Ultimate Module Creator
 */
class Escoin_Representative_Block_Adminhtml_Representative_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs{
	/**
	 * constructor
	 * @access public
	 * @return void
	 * @author Ultimate Module Creator
	 */
	public function __construct(){
		parent::__construct();
		$this->setId('representative_tabs');
		$this->setDestElementId('edit_form');
		$this->setTitle(Mage::helper('representative')->__('Representative'));
	}
	/**
	 * before render html
	 * @access protected
	 * @return Escoin_Representative_Block_Adminhtml_Representative_Edit_Tabs
	 * @author Ultimate Module Creator
	 */
	protected function _beforeToHtml(){
		$this->addTab('form_representative', array(
			'label'		=> Mage::helper('representative')->__('Representative'),
			'title'		=> Mage::helper('representative')->__('Representative'),
			'content' 	=> $this->getLayout()->createBlock('representative/adminhtml_representative_edit_tab_form')->toHtml(),
		));
		if (!Mage::app()->isSingleStoreMode()){
			$this->addTab('form_store_representative', array(
				'label'		=> Mage::helper('representative')->__('Store views'),
				'title'		=> Mage::helper('representative')->__('Store views'),
				'content' 	=> $this->getLayout()->createBlock('representative/adminhtml_representative_edit_tab_stores')->toHtml(),
			));
		}
		return parent::_beforeToHtml();
	}
}