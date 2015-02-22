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


/**
 * Product list
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Block_Smart_List extends Mage_Catalog_Block_Product_Abstract
{
	/**
	 * Default toolbar block name
	 *
	 * @var string
	 */
	protected $_defaultToolbarBlock = 'catalog/smart_toolbar';

	/**
	 * Product Collection
	 *
	 * @var Mage_Eav_Model_Entity_Collection_Abstract
	 */
	protected $_productCollection;

	public $requestUrl;

	public $searchUrl;

	public $username;

	public $password;

	public $numberResults = 0;

	public $_filters;

	public function init(){
		include_once Mage::getBaseDir()."/crons/config.php";

		$this->requestUrl = $requestUrl;
		$this->searchUrl  = $searchUrl;
		$this->username   = $username;
		$this->password   = $password;
	}

	public function setSize($size){
		$this->numberResults = (int)$size;
	}

	public function getSize(){
		return $this->numberResults;
	}

	public function setFilters($filters){
		$this->_filters = $filters;
	}

	public function getFilters(){
		return $this->_filters;
	}

	/**
	 * Retrieve loaded category collection
	 *
	 * @return Mage_Eav_Model_Entity_Collection_Abstract
	 */
	protected function _getProductCollection()
	{
		$this->init();

		if (is_null($this->_productCollection)) {
			$fstCategory = false;
			$page = (int)$_GET['p'];
			if(!$page){
				$page = 1;
			}

			$keyword = trim($_GET['q']);
			if($keyword and strlen($keyword) > 0){
				$response = $this->_doFilter($keyword , $page);
			}
			else{
				$keyword = $this->helper('catalogsearch')->getQueryText();
				$response = $this->_sendRequest($keyword , false , $page);
			}

			if($response['items']){
				$sku_list = array_keys($response['items']);

				$products = Mage::getResourceModel('catalog/product_collection')
				->addAttributeToSelect('*')
				//->addAttributeToFilter('SKU', array('in'=> array('ACM38000')));
				->addAttributeToFilter('SKU', array('in'=> $sku_list));
				$products->load();
			}

			$this->setFilters(array($response['AvailableFilters'] , $response['AppliedFilters']));
			$this->setSize($response['totalItems']);

			$this->_productCollection = $products;
		}

		return $this->_productCollection;
	}

	protected function _doFilter($keyword , $page = 1){
		$extraFilter = false;
		$sequence = 1;

		$refine_keyword = trim($_GET['rkey']);
		if($refine_keyword){
			$refine_keyword = str_ireplace("-"," ",$refine_keyword);
			$keyword = str_ireplace($refine_keyword,"",$keyword);
			$keyword = trim($keyword);
			$rkey = $refine_keyword;
		}

		if(isset($_GET['kf']) && !$_GET['kf']){
			$fkeyword = false;
			$sequence = 1;
		}
		else{
			$fkeyword = $keyword;
			$sequence = 2;
		}

		$fst = (string)$_GET['fst0'];
		if(!in_array($fst,array('Attribute','Brand','Category','Catalog','Contract','Item','ItemIndicator','Keyword','PriceRange','SuppliesFinderBrand','SuppliesFinderDeviceType','SuppliesFinderModel','Manufacturer','CountryOfOrigin','ProductClass','Price'))){
			$fst = 'Keyword';
		}

		$attAdded = array();
		$attAddedValue = array();
		$availFilters = array();
		$fstCategoryCount = 0;

		for($i=1;$i<=10;$i++){
			$filterValues = array();
			if(isset($_GET['fst'.$i]) and !empty($_GET['fst'.$i])){
				$fst = $_GET['fst'.$i];
				if(!in_array($fst,array('Attribute','Brand','Category','Catalog','Contract','Item','ItemIndicator','Keyword','PriceRange','SuppliesFinderBrand','SuppliesFinderDeviceType','SuppliesFinderModel','Manufacturer','CountryOfOrigin','ProductClass','Price'))){
					$fst = 'Keyword';
				}

				if( !in_array($fst,$attAdded) || (in_array($fst,$attAdded) && $attAddedValue[$fst] != $_GET['fid'.$i.'1']) ){
					for($k=1;$k<=10;$k++){
						if(isset($_GET['fid'.$i.$k]) and is_numeric($_GET['fid'.$i.$k])){
							if($fst == 'Category'){
								$fstCategory = true;
								$fstCategoryCount++;
								$availFilters[$fst][] = $_GET['fid'.$i.$k];
							}
							else{
								$filterValues[] = $_GET['fid'.$i.$k];
							}
						}
					}

					if($filterValues and sizeof($filterValues) > 0){
						$extraFilter .= $this->extraFilter($sequence,$fst,$filterValues);
						$sequence++;

						$attAdded[] = $fst;
						$attAddedValue[$fst]  = $_GET['fid'.$i.'1'];
					}
				}
			}
		}

		$fid = $_GET['fid0'];
		$fst = (string)$_GET['fst0'];
		if(is_numeric($fid)){
			$availFilters[$fst][] = $fid;
			$attAdded[] = $fst;
			$attAddedValue[$fst] = $fid;
		}

		if(count($availFilters) > 0){
			foreach($availFilters as $fst => $filterValues){
				if(sizeof($filterValues) > 0){
					$extraFilter .= $this->extraFilter($sequence,$fst,$filterValues);
					$sequence++;
				}
			}
		}

		if(!$fkeyword && !$fstCategory){
			$fkeyword = $keyword;
		}

		if($rkey && !$fkeyword){
			$extraFilter .= $this->extraFilter($sequence,'Keyword',array(0=>$rkey));
		}

		$dbRead  = Mage::getSingleton("core/resource")->getConnection("core_read");
		$dbWrite = Mage::getSingleton("core/resource")->getConnection("core_write");

		if(isset($_GET['order']) && $_GET['order']){
			$filter_category = $_GET['order'];
		}

		if(!$filter_category){
			$filter_category = 'BM';
		}
			
		if($filter_category == 'PA' || $filter_category == 'PD'){
			if($filter_category == 'PA'){
				$order = 'ASC';
			}
			else{
				$order = 'DESC';
			}

			$start = ($page-1)*10;

			$items = $dbRead->fetchAll("select * from ussco_smart_search where session_id = '".session_id()."' order by abs(product_price) $order limit $start , 10");
			if(sizeof($items) == 0){
				$bufferSize = 500;
				$SortResponse = $this->_sendRequest($fkeyword , $rkey , 1 , $bufferSize , $filter_category , $extraFilter);
				if($SortResponse['items'] and count($SortResponse['items']) > 0){
					$models = array();
					$sql_query = "Insert into ussco_smart_search(session_id , product_model , list_type) values";
					foreach($SortResponse['items'] as $item){
						$sql_query .= "('".session_id()."' , '".$item['products_model']."' , '".$item['list_type']."'),";
						$models[] = "'" . $item['products_model'] . "'";
					}

					$sql_query = substr($sql_query , 0, -1);
					if($sql_query){
						$dbWrite->query($sql_query);

						$models_str = implode(",",$models);
						$prices = $dbRead->fetchAll("select price , sku from catalog_product_index_price pc
													inner join catalog_product_entity pe on (pc.entity_id = pe.entity_id)
													where sku IN($models_str)");
						foreach($prices as $price){
							$update_query = "Update ussco_smart_search set product_price = '".$price['price']."' where product_model = '".$price['sku']."' and session_id = '".session_id()."'";
							$dbWrite->query($update_query);
						}
					}
				}

				$items = $dbRead->fetchAll("select * from ussco_smart_search where session_id = '".session_id()."' order by abs(product_price) $order limit $start , 10");
			}

			$referenceFilter = '';
			if(sizeof($items) > 0) {
				$referenceFilter = '<ns:ItemReferenceFilters>';
				$seq = 1;
				foreach($items as $item){
					$referenceFilter .= '<ns:ItemReference ReferenceType="'.$item['list_type'].'" Sequence="'.$seq.'">';
					$referenceFilter .= '<ns:ItemNumber>'.$item['product_model'].'</ns:ItemNumber>';
					$referenceFilter .= '</ns:ItemReference>';
					$seq++;
				}
			}
			$referenceFilter .= '</ns:ItemReferenceFilters>';

			$response = $this->_sendRequest($fkeyword , $rkey , 1 , 10 , 'BM' , $extraFilter , $referenceFilter);
			//print_r($response);
			//print "daasda"; exit;
		}
		else{
			$dbWrite->query("delete from ussco_smart_search where session_id = '".session_id()."'");
			$bufferSize = 10;
				
			if($_GET['fst1'] == 'SuppliesFinderBrand'){
				$fkeyword = '';
			}
			
			$response = $this->_sendRequest($fkeyword , $rkey , $page , $bufferSize , $filter_category , $extraFilter , false);
		}

		return $response;
	}

	protected function extraFilter($sequence = 2 , $filterStyle = 'Keyword' , $filterValues,$keywordInterface = 'Standard'){
		$extra = '<ns:Filter displayStyle="Top" keywordInterface="'.$keywordInterface.'" sequence="'.$sequence.'" CrossReference="ALT">
                  <ns:FilterStyle>'.$filterStyle.'</ns:FilterStyle>
                  <!--Optional:-->
                  <ns:FilterDescription>'.$filterStyle.'</ns:FilterDescription>
                  <!--1 or more repetitions:-->';

		$k = 1;
		foreach($filterValues as $filterValue){
			$extra .=  '<ns:FilterValue displayStyle="Top" sequence="'.$k.'">
                     <ns:Value>'.$filterValue.'</ns:Value>
                  </ns:FilterValue>';
			$k++;
		}

		$extra .= '</ns:Filter>';
		return $extra;
	}

	protected function _sendRequest($keyword , $rkey = false , $page = 1 , $per_page = 12 , $sort = 'BM' ,$extraFilter = false , $referenceFilter = false){
		$page = (int)$page;
		if(!$page){
			$page = 1;
		}

		$per_page = (int)$per_page;
		if(!$per_page){
			$per_page = 12;
		}

		$keyword = str_ireplace(array("-","®"),"",$keyword);
		$page = (($page - 1) * $per_page) + 1;

		$filename = Mage::getBaseUrl()."/xml/search.xml";
		$xml = file_get_contents($filename);

		if($_GET['sf'] == 1){
			$keywordInterface = 'SuppliesFinder';
		}
		elseif($_GET['sf'] == 2){
			$keywordInterface = 'ImageSupplies';
		}
		else{
			$keywordInterface = 'Standard';
		}

		if($keyword){
			$keywordNode = '<ns:Filter displayStyle="Top" keywordInterface="'.$keywordInterface.'" sequence="1" CrossReference="ALT">
		                  <ns:FilterStyle>Keyword</ns:FilterStyle>
		                  <ns:FilterDescription>Keyword</ns:FilterDescription>
		                  <ns:FilterValue displayStyle="Top" sequence="1">
		                     <ns:Description>'.$keyword.'</ns:Description>
		                     <ns:Value>'.$keyword.'</ns:Value>
		                  </ns:FilterValue>';
			if($rkey){
				$keywordNode .= '<ns:FilterValue displayStyle="Top" sequence="2">
		                       <ns:Description>'.$rkey.'</ns:Description>
		                       <ns:Value>'.$rkey.'</ns:Value>
		                     </ns:FilterValue>';
			}

			$keywordNode .= '</ns:Filter>';
		}
		else{
			$keywordNode = false;
		}

		$xml = sprintf($xml,$this->username,$this->password,session_id(),$keywordNode,$extraFilter,$sort,$page,$per_page,$referenceFilter);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->searchUrl);

		curl_setopt($ch, CURLOPT_POST,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$xml);
		curl_setopt($ch, CURLOPT_HTTPHEADER,array("content-type:text/xml;content-length:".strlen($xml)));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_TIMEOUT,10);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0);

		$result = curl_exec($ch);
		$error = curl_exec($ch);

		$start_pos = strpos($result,"<getSearchResponse");
		$end_pos   = strpos($result,"</getSearchResponse>");
		$result = substr($result,$start_pos,$end_pos);
		$result = str_replace(array('</soapenv:Envelope>','</soapenv:Body>'),"",$result);
		//print_r($result); exit;

		$response = array();
		$responsObj = simplexml_load_string($result); // or die("can not parse");
		if(!$responsObj){
			return $response;
		}

		$products = array();
		$Items = $responsObj->searchResponse->ItemPage->Items->Item;

		if($Items){
			foreach($Items as $Item){
				$products_model = (string)$Item->ItemNumber;
				$list_type = 'UnitedItemNumber';
				if(!$products_model){
					$products_model = (string)$Item->DealerItemNumber;
					$list_type = 'DealerItemNumber';
				}

				if($products_model){
					$products[$products_model] = array('products_model' => $products_model ,
						    'ListPrice' => (string)$Item->ListPrice , 
						    'list_type' => $list_type ,	 
						    'products_name' => stripslashes((string)$Item->Description),
							'manufacturers_name' =>$Item->Brand->BrandDescription . " &#174;");
				}
			}
		}

		//print_r($products); exit;
		$response = array("totalItems" => $responsObj->searchResponse->ItemPage->TotalResults,
					  "page" => $page , 'perPageValue' => $per_page , 'items' => $products , 
					  'AvailableFilters' => $responsObj->searchResponse->AvailableFilters,
					  'AppliedFilters'   => $responsObj->searchResponse->AppliedFilters,
					  'AvailableSorts'   => $responsObj->searchResponse->AvailableSorts
		);

		return $response;
	}

	/**
	 * Get catalog layer model
	 *
	 * @return Mage_Catalog_Model_Layer
	 */
	public function getLayer()
	{
		$layer = Mage::registry('current_layer');
		if ($layer) {
			return $layer;
		}
		return Mage::getSingleton('catalog/layer');
	}

	/**
	 * Retrieve loaded category collection
	 *
	 * @return Mage_Eav_Model_Entity_Collection_Abstract
	 */
	public function getLoadedProductCollection()
	{
		return $this->_getProductCollection();
	}

	/**
	 * Retrieve current view mode
	 *
	 * @return string
	 */
	public function getMode()
	{
		return $this->getChild('toolbar')->getCurrentMode();
	}

	/**
	 * Need use as _prepareLayout - but problem in declaring collection from
	 * another block (was problem with search result)
	 */
	protected function _beforeToHtml()
	{
		$toolbar = $this->getToolbarBlock();

		// called prepare sortable parameters
		$collection = $this->_getProductCollection();

		// use sortable parameters
		if ($orders = $this->getAvailableOrders()) {
			$orders = array("BM" => "Best Match", "BA" => "Brand", "MP"=>"Most Popular" , "PA" => "Price Ascending" , "PD" => "Price Descending");

			$toolbar->setAvailableOrders($orders);
		}
		if ($sort = $this->getSortBy()) {
			$toolbar->setDefaultOrder($sort);
		}
		if ($dir = $this->getDefaultDirection()) {
			$toolbar->setDefaultDirection($dir);
		}
		if ($modes = $this->getModes()) {
			$toolbar->setModes($modes);
		}

		// set collection to toolbar and apply sort
		$toolbar->setCollection($collection);
		$toolbar->setSize($this->getSize());

		$this->setChild('toolbar', $toolbar);

		$filters = $this->getFilterBlock();
		$filters->setFilters($this->getFilters());

		$this->setChild("filter", $filters);

		Mage::dispatchEvent('catalog_block_smart_list_collection', array(
			'collection' => $this->_getProductCollection()
		));

		//$this->_getProductCollection()->load();
		return parent::_beforeToHtml();
	}

	/**
	 * Retrieve Toolbar block
	 *
	 * @return Mage_Catalog_Block_Product_List_Toolbar
	 */
	public function getToolbarBlock()
	{
		if ($blockName = $this->getToolbarBlockName()) {
			if ($block = $this->getLayout()->getBlock($blockName)) {
				return $block;
			}
		}
		$block = $this->getLayout()->createBlock($this->_defaultToolbarBlock, microtime());
		return $block;
	}

	/**
	 * Retrieve Toolbar block
	 *
	 * @return Mage_Catalog_Block_Product_List_Toolbar
	 */
	public function getFilterBlock()
	{
		$block = $this->getLayout()->createBlock("catalog/smart_filter", microtime());
		return $block;
	}

	/**
	 * Retrieve additional blocks html
	 *
	 * @return string
	 */
	public function getAdditionalHtml()
	{
		return $this->getChildHtml('additional');
	}

	public function getFiltersHtml()
	{
		return $this->getChildHtml('filter');
	}

	/**
	 * Retrieve list toolbar HTML
	 *
	 * @return string
	 */
	public function getToolbarHtml()
	{
		return $this->getChildHtml('toolbar');
	}

	public function setCollection($collection)
	{
		$this->_productCollection = $collection;
		return $this;
	}

	public function addAttribute($code)
	{
		$this->_getProductCollection()->addAttributeToSelect($code);
		return $this;
	}

	public function getPriceBlockTemplate()
	{
		return $this->_getData('price_block_template');
	}

	/**
	 * Retrieve Catalog Config object
	 *
	 * @return Mage_Catalog_Model_Config
	 */
	protected function _getConfig()
	{
		return Mage::getSingleton('catalog/config');
	}

	/**
	 * Prepare Sort By fields from Category Data
	 *
	 * @param Mage_Catalog_Model_Category $category
	 * @return Mage_Catalog_Block_Product_List
	 */
	public function prepareSortableFieldsByCategory($category) {
		if (!$this->getAvailableOrders()) {
			$this->setAvailableOrders($category->getAvailableSortByOptions());
		}
		$availableOrders = $this->getAvailableOrders();
		if (!$this->getSortBy()) {
			if ($categorySortBy = $category->getDefaultSortBy()) {
				if (!$availableOrders) {
					$availableOrders = $this->_getConfig()->getAttributeUsedForSortByArray();
				}
				if (isset($availableOrders[$categorySortBy])) {
					$this->setSortBy($categorySortBy);
				}
			}
		}

		return $this;
	}

	/**
	 * Retrieve block cache tags based on product collection
	 *
	 * @return array
	 */
	public function getCacheTags()
	{
		return array_merge(
		parent::getCacheTags(),
		$this->getItemsTags($this->_getProductCollection())
		);
	}
}