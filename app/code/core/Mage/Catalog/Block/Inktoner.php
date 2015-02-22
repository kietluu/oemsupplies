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


class Mage_Catalog_Block_Inktoner extends Mage_Core_Block_Template
{
	public $requestUrl;

	public $searchUrl;

	public $username;

	public $password;

	public $dbRead;

	public function __construct(){
		parent::_construct();

		$path = Mage::getBaseDir()."/crons/config.php";
		$path = str_ireplace("\\", "/", $path);
		include_once $path;

		$this->requestUrl = $requestUrl;
		$this->searchUrl  = $searchUrl;
		$this->username   = $username;
		$this->password   = $password;

		$this->dbRead = Mage::getSingleton("core/resource")->getConnection("core_read");
	}

	public function getSearchUrl(){
		return Mage::getUrl("catalog/inktoner");
	}

	public function getBrands(){
		$query = "select id , supplier_name from ussco_matchbook_suppliers";
		return $this->dbRead->fetchAll($query);
	}

	public function getCategories(){
		$brand_id = $this->getRequest()->getParam('id');

		$query = "select id , category_name from ussco_matchbook_suppliers_categories where matchbook_supplier_id = '$brand_id'";
		return $this->dbRead->fetchAll($query);
	}

	public function getModels(){
		$category_id = $this->getRequest()->getParam('id');

		$query = "select search_id , supplier_model from ussco_matchbook_suppliers_models where matchbook_suppliers_category_id = '$category_id'";
		return $this->dbRead->fetchAll($query);
	}

	public function getSuppliesModels(){
		$keyword   = $this->getRequest()->getParam('keyword');
		if(!$keyword){
			$keyword = $this->getRequest()->getParam('inkkeyword');
		}

		$search_id = $this->getRequest()->getParam('m');
		$supplier_name = $this->getRequest()->getParam('name');

		$suppliesModels = array();

		if($keyword){
			$keywords = explode(" ",$keyword);
			$search_string = "";
			$string = array();
			foreach((array)$keywords as $word){
				$string[] = " supplier_name LIKE '%".mysql_escape_string($word)."%' ";
			}

			if(sizeof($string) > 0){
				$search_string = " ( ( " . implode(" AND ", $string) . " ) OR supplier_name LIKE '%$search_word%' )";
			}
			else{
				$search_string = " supplier_name LIKE '%$search_word%' ";
			}

			$query  = 'select distinct search_id , supplier_name from ussco_matchbook_suppliers where '.$search_string;
			$result = $this->dbRead->fetchRow($query);
			if(!$result){
				//$this->_redirectUrl(Mage::getUrl("catalogsearch/smart?q="+$keyword));
			}
			else{
				$search_id = $result['search_id'];
				$supplier_name = $result['supplier_name'];
			}
		}

		if($search_id AND $supplier_name){
			$suppliesModels = $this->getSuppliesModelsData($supplier_name, $search_id);
		}

		return $suppliesModels;
	}

	public function getNumericSuppliers(){
		$suppliers = $this->dbRead->fetchAll("select * from ussco_matchbook_suppliers where supplier_name REGEXP '^[0-9]'");
		return $suppliers;
	}

	public function getAlphaSuppliers(){
		for ($i=65; $i<91; $i++) {
			$letters_list[] = chr($i);
		}

		$suppliers = array();
		foreach($letters_list as $letter){
			$suppliers[$letter] = $this->dbRead->fetchAll("select * from ussco_matchbook_suppliers where Lower(supplier_name) like '$letter%' ");
		}

		return $suppliers;
	}

	public function getTopBrands(){
		$top_brands = array("'HP'","'Canon'","'Epson'","'Sharp'","'Brother'","'Lexmark'","'Xerox'","'Samsung'","'Dell'","'Oki'","'Ricoh'","'Panasonic'","'Muratec'","'Zebra'","'Konica Minolta'");
		$top_brands = implode(",",$top_brands);
		$top_brands = strtolower($top_brands);

		$brandList = $this->dbRead->fetchAll("select * from ussco_matchbook_suppliers where Lower(supplier_name) IN ($top_brands) order by supplier_name asc");
		return $brandList;
	}

	public function getInkBrands(){
		$top_brands = array("'HP'","'Canon'","'Epson'","'Sharp'","'Brother'","'Lexmark'","'Xerox'","'Samsung'","'Dell'","'Oki'","'Ricoh'","'Panasonic'","'Kydcera'");
		$top_brands = implode(",",$top_brands);
		$top_brands = strtolower($top_brands);

		$brandList = $this->dbRead->fetchAll("select * from ussco_matchbook_suppliers where Lower(supplier_name) IN ($top_brands) order by supplier_name asc");
		return $brandList;
	}

	public function getSuppliesModelsData($brand , $value , $interface = 'Standard'){
		$filename = Mage::getBaseDir() . "/xml/supplies_finder.xml";
		$xml = file_get_contents($filename);

		$filter = '<ns:Filter displayStyle="Top" keywordInterface="'.$interface.'" sequence="2" CrossReference="ALT">
                  <ns:FilterStyle>SuppliesFinderBrand</ns:FilterStyle>
                  <ns:FilterDescription>SuppliesFinderBrand</ns:FilterDescription>
                  <ns:FilterValue displayStyle="Top" sequence="1">
                     <ns:Description>'.$brand.'</ns:Description>
                     <ns:Value>'.$value.'</ns:Value>
                     <ns:AvailableResults>1</ns:AvailableResults>
                     <ns:DidYouMean>1</ns:DidYouMean>
                     <ns:AutoSpellCorrect>1</ns:AutoSpellCorrect>
                  </ns:FilterValue>
               </ns:Filter>';

		$xml = sprintf($xml,$this->username,$this->password,session_id(),$filter,'BM',1,10);

		$result = $this->sendRequest($xml);
		$start_pos = strpos($result,"<getSuppliesFinderResponse");
		$end_pos   = strpos($result,"</getSuppliesFinderResponse>");
		$result = substr($result,$start_pos,$end_pos);
		$result = str_replace(array('</soapenv:Envelope>','</soapenv:Body>'),"",$result);

		$response = array();
		$responsObj = simplexml_load_string($result); // or die("can not parse");
		if(!$responsObj){
			return $response;
		}

		$Filters = $responsObj->suppliesFinderResponse->AvailableFilters->Filter->FilterValue;
		if($Filters){
			foreach($Filters as $Filter){
				$Description = (string)$Filter->Description;
				$Description = mysql_escape_string($Description);
				$Value = (string)$Filter->Value;

				$models = array();
				$SubFilters = $Filter->SubFilter->FilterValue;
				if(count($SubFilters) > 0){
					foreach($SubFilters as $SubFilter){
						$modelName  = (string)$SubFilter->Description;
						$modelName  = mysql_escape_string($modelName);
						$modelValue = (string)$SubFilter->Value;
						$models[]   = array('ModelName'=>$modelName , 'Value' => $modelValue);
					}
				}

				$response[] = array('CategoryName' => $Description , 'Value' => $Value ,'Models' => $models);
			}
		}

		return $response;
	}

	public function sendRequest($xml){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$this->searchUrl);

		curl_setopt($ch, CURLOPT_POST,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$xml);
		curl_setopt($ch, CURLOPT_HTTPHEADER,array("content-type:text/xml;content-length:".strlen($xml)));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_TIMEOUT,30);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0);

		$result = curl_exec($ch);
		$error  = curl_exec($ch);

		return $result;
	}
}