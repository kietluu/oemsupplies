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
 * Representative admin edit block
 *
 * @category	Escoin
 * @package		Escoin_Representative
 * @author Ultimate Module Creator
 */
class Escoin_Representative_Block_Adminhtml_Representative_Edit extends Mage_Adminhtml_Block_Widget_Form_Container{
	/**
	 * constuctor
	 * @access public
	 * @return void
	 * @author Ultimate Module Creator
	 */
	public function __construct(){
		parent::__construct();
		$this->_blockGroup = 'representative';
		$this->_controller = 'adminhtml_representative';
		$this->_updateButton('save', 'label', Mage::helper('representative')->__('Save Representative'));
		$this->_updateButton('delete', 'label', Mage::helper('representative')->__('Delete Representative'));
		$this->_addButton('saveandcontinue', array(
			'label'		=> Mage::helper('representative')->__('Save And Continue Edit'),
			'onclick'	=> 'saveAndContinueEdit()',
			'class'		=> 'save',
		), -100);
		$this->_formScripts[] = "
			function saveAndContinueEdit(){
				editForm.submit($('edit_form').action+'back/edit/');
			}
		";
	}
	/**
	 * get the edit form header
	 * @access public
	 * @return string
	 * @author Ultimate Module Creator
	 */
	public function getHeaderText(){
		if( Mage::registry('representative_data') && Mage::registry('representative_data')->getId() ) {
			return Mage::helper('representative')->__("Edit Representative '%s'", $this->htmlEscape(Mage::registry('representative_data')->getRepresentative()));
		} 
		else {
			return Mage::helper('representative')->__('Add Representative');
		}
	}
}