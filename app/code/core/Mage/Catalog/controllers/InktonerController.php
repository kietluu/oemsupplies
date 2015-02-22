<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2014 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Catalog_InktonerController extends Mage_Core_Controller_Front_Action
{
	public $dbRead;

	/**
	 * Index action
	 */
	public function indexAction()
	{
		$this->loadLayout();
		$this->dbRead = Mage::getSingleton("core/resource")->getConnection("core_read");

		$brand = $this->getRequest()->getParam('brand');
		if($brand){
			$brandDetail = $this->dbRead->fetchRow("select search_id from ussco_matchbook_suppliers where id = '$brand'");
			$brand_id = $brandDetail['search_id'];
		}

		$model = $this->getRequest()->getParam('model');
		if($brand_id and $model){
			$url = "catalogsearch/smart?q=printer&fst1=SuppliesFinderBrand&fid11=$brand_id&fst2=SuppliesFinderModel&fid21=$model";
			$this->_redirect($url);
		}

		$search_type = $this->getRequest()->getParam('search_type');
		if($search_type == 'cartridge'){
			$keyword   = $this->getRequest()->getParam('keyword');
			if(!$keyword){
				$keyword = $this->getRequest()->getParam('inkkeyword');
			}
				
			$url = "catalogsearch/smart?q=$keyword&sf=2";
			$this->_redirect($url);
		}
			
		$this->_initLayoutMessages('catalog/session');
		$this->_initLayoutMessages('checkout/session');
		$this->renderLayout();
	}

	public function getCategoriesAction(){
		$layout = Mage::getSingleton('core/layout');

		$layout->getUpdate();
		$renderer = $layout->createBlock('catalog/inktoner');

		$renderer->setTemplate("catalog/inktoner/categories.phtml");
		$rendererhtml = $renderer->toHtml();

		$this->getResponse()->setBody($rendererhtml);
	}

	public function getCategoryModelsAction(){
		$layout = Mage::getSingleton('core/layout');

		$layout->getUpdate();
		$renderer = $layout->createBlock('catalog/inktoner');

		$renderer->setTemplate("catalog/inktoner/models.phtml");
		$rendererhtml = $renderer->toHtml();

		$this->getResponse()->setBody($rendererhtml);
	}

	public function getBrandsAction(){
		$this->dbRead = Mage::getSingleton("core/resource")->getConnection("core_read");

		$term  = $this->getRequest()->getParam('term');
		$query = "select supplier_name from ussco_matchbook_suppliers where supplier_name like '%$term%'";

		$result = $this->dbRead->fetchAll($query);
		$data = array();
		foreach($result as $brand){
			$data[] = $brand['supplier_name'];
		}

		$this->getResponse()->setBody(Zend_Json::encode($data));
	}

	public function getModelsAction(){
		$this->dbRead = Mage::getSingleton("core/resource")->getConnection("core_read");

		$term  = $this->getRequest()->getParam('term');
		$brand = $this->getRequest()->getParam('brand');

		$brand = strtolower($brand);
		$query = "select supplier_model from ussco_matchbook_suppliers_models msm inner join ussco_matchbook_suppliers_categories msc on
				 (msm.matchbook_suppliers_category_id = msc.id)
				 where matchbook_supplier_id = (select id from ussco_matchbook_suppliers where Lower(supplier_name) = '$brand') 
						and supplier_model like '%$term%' order by supplier_model asc";

		$result = $this->dbRead->fetchAll($query);
		$data = array();
		foreach($result as $brand){
			$data[] = $brand['supplier_model'];
		}

		$this->getResponse()->setBody(Zend_Json::encode($data));
	}

	public function getKeywordsAction(){
		$this->dbRead = Mage::getSingleton("core/resource")->getConnection("core_read");

		$type  = $this->getRequest()->getParam('type');
		$term  = $this->getRequest()->getParam('term');

		if($type == 'printer'){
			$query = "select concat_ws(' ',supplier_name,category_name,supplier_model) as supplier_model from ussco_matchbook_suppliers_models msm
						inner join ussco_matchbook_suppliers_categories msc on msm.matchbook_suppliers_category_id = msc.id 
						inner join ussco_matchbook_suppliers ms on msc.matchbook_supplier_id = ms.id 
						where supplier_model like '%$term%' order by supplier_model asc";
		}
		else{
			$query = "select concat_ws(' ',supplier_name,supplier_model) as supplier_model from ussco_matchbook_suppliers_models msm
						inner join ussco_matchbook_suppliers_categories msc on msm.matchbook_suppliers_category_id = msc.id 
						inner join ussco_matchbook_suppliers ms on msc.matchbook_supplier_id = ms.id 
						where supplier_model like '%$term%' order by supplier_model asc";
		}

		$result = $this->dbRead->fetchAll($query);
		$data = array();
		foreach($result as $brand){
			$data[] = $brand['supplier_model'];
		}

		$this->getResponse()->setBody(Zend_Json::encode($data));
	}
}