<?php

class Mage_CatalogSearch_SmartController extends Mage_Core_Controller_Front_Action
{
	/**
	 * Retrieve catalog session
	 *
	 * @return Mage_Catalog_Model_Session
	 */
	protected function _getSession()
	{
		return Mage::getSingleton('catalog/session');
	}

	/**
	 * Display search result
	 */
	public function indexAction()
	{
		$query = Mage::helper('catalogsearch')->getQuery();
		/* @var $query Mage_CatalogSearch_Model_Query */

		$query->setStoreId(Mage::app()->getStore()->getId());

		Mage::helper('catalogsearch')->checkNotes();

		$this->loadLayout();
		$this->_initLayoutMessages('catalog/session');
		$this->_initLayoutMessages('checkout/session');
		$this->renderLayout();
	}
}