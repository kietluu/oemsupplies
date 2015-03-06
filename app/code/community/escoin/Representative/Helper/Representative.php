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
 * Representative helper
 *
 * @category	Escoin
 * @package		Escoin_Representative
 * @author Ultimate Module Creator
 */
class Escoin_Representative_Helper_Representative extends Mage_Core_Helper_Abstract{
	/**
	 * check if breadcrumbs can be used
	 * @access public
	 * @return bool
	 * @author Ultimate Module Creator
	 */
	public function getUseBreadcrumbs(){
		return Mage::getStoreConfigFlag('representative/representative/breadcrumbs');
	}
}