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
 * Representative admin block
 *
 * @category	Escoin
 * @package		Escoin_Representative
 * @author Ultimate Module Creator
 */
class Escoin_Representative_Block_Adminhtml_Representative extends Mage_Adminhtml_Block_Widget_Grid_Container{
	/**
	 * constructor
	 * @access public
	 * @return void
	 * @author Ultimate Module Creator
	 */
	public function __construct(){
		$this->_controller 		= 'adminhtml_representative';
		$this->_blockGroup 		= 'representative';
		$this->_headerText 		= Mage::helper('representative')->__('Representative');
		$this->_addButtonLabel 	= Mage::helper('representative')->__('Add Representative');
		parent::__construct();
	}
}