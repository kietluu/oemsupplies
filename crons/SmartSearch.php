<?php

require("../app/Mage.php");
Mage::init('admin');
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

$dbRead  = Mage::getSingleton("core/resource")->getConnection("core_read");
$dbWrite = Mage::getSingleton("core/resource")->getConnection("core_write");

class SmartSearch {

	private $dbRead;

	private $dbWrite;

	public $requestUrl;

	public $searchUrl;

	public $username;

	public $password;

	public $is_production = true;

	public function SmartSearch(){
		global $dbWrite , $dbRead;

		$this->dbRead  = $dbRead;
		$this->dbWrite = $dbWrite;
		
		include_once Mage::getBaseDir()."/crons/config.php";

		$this->requestUrl = $requestUrl;
		$this->searchUrl  = $searchUrl;
		$this->username   = $username;
		$this->password   = $password;
	}

	public function UpdateSuppliesFinder(){
		$filename = Mage::getBaseDir() . "/xml/supplies_finder.xml";
		$xml = file_get_contents($filename);

		$filter = '<ns:Filter sequence="1" keywordInterface="SuppliesFinder">
                  <ns:FilterStyle>SuppliesFinderBrand</ns:FilterStyle>
                  <ns:FilterDescription>SuppliesFinderBrand</ns:FilterDescription>
                  <ns:FilterValue sequence="1">
                     <ns:Description> </ns:Description>
                     <ns:Value></ns:Value>
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
			print_r($result);
			exit;
		}

		$Filters = $responsObj->suppliesFinderResponse->AvailableFilters->Filter->FilterValue;
		if($Filters){
			foreach($Filters as $Filter){
				$Description = (string)$Filter->Description;
				$Description = mysql_escape_string($Description);
				$Value = (string)$Filter->Value;

				$isExist = $this->dbRead->fetchRow("select search_id from ussco_matchbook_suppliers where supplier_name = '$Description'");
				if($isExist){
					$this->dbWrite->query("Update ussco_matchbook_suppliers set search_id = '$Value' , dateofmodification = now() Where supplier_name = '$Description'");
				}
				else{
					$this->dbWrite->query("Insert ussco_matchbook_suppliers set search_id = '$Value' , supplier_name = '$Description' , dateofmodification = now()");
				}
			}
		}

		echo "success";
	}

	public function UpdateSuppliesModels($start=0){
		if(!$start){
			$start = (int)$_GET['start'];
		}

		$this->dbWrite->query("truncate table ussco_matchbook_suppliers_categories");
		$this->dbWrite->query("truncate table ussco_matchbook_suppliers_models");

		$brands = $this->dbRead->fetchAll("select id , supplier_name , search_id from ussco_matchbook_suppliers where 1=1 order by supplier_name asc limit $start , 800");
		foreach($brands as $brand){
			$Description = mysql_escape_string($brand['supplier_name']);
			$Value = $brand['search_id'];

			$matchbook_supplier_id = $brand['id'];
			$modelCategories = $this->getSuppliesModels($Description , $Value);

			if(is_array($modelCategories) and count($modelCategories) > 0){
				foreach($modelCategories as $modelCategory){
					$this->dbWrite->query("Insert into ussco_matchbook_suppliers_categories set category_name = '".$modelCategory['CategoryName']."', matchbook_supplier_id = '".$matchbook_supplier_id."'");
					$matchbook_suppliers_category_id = $this->dbWrite->lastInsertId();

					foreach($modelCategory['Models'] as $model){
						$this->dbWrite->query("Insert into ussco_matchbook_suppliers_models set matchbook_suppliers_category_id = '$matchbook_suppliers_category_id', search_id = '".$model['Value']."' , supplier_model = '".$model['ModelName']."'");
					}
				}
			}
		}

		echo "success";
	}

	public function getSuppliesModels($brand , $value){
		$filename = Mage::getBaseDir() . "/xml/supplies_finder.xml";
		$xml = file_get_contents($filename);

		$filter = '<ns:Filter displayStyle="Top" keywordInterface="Standard" sequence="2" CrossReference="ALT">
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

	public function getSuppliesProducts($filter , $page = 1 , $per_page = 10 , $sort = 'BM'){
		$page = (int)$page;
		if(!$page){
			$page = 1;
		}

		$per_page = (int)$per_page;
		if(!$per_page){
			$per_page = 10;
		}

		$page = (($page - 1) * $per_page) + 1;

		$filename = Mage::getBaseDir()."/xml/supplies_finder.xml";
		$xml = file_get_contents($filename);
		$xml = sprintf($xml,$this->username,$this->password,session_id(),$filter,$sort,$page,$per_page);

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

		$products = array();
		$Items = $responsObj->suppliesFinderResponse->ItemPage->Items->Item;

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
						    'products_name' => stripslashes(replaceSpecial((string)$Item->Description)),
							'manufacturers_name' => replaceSpecial($Item->Brand->BrandDescription) . " &#174;");
				}
			}
		}

		//print_r($products); exit;
		$response = array("totalItems" => $responsObj->suppliesFinderResponse->ItemPage->TotalResults,
					  "page" => $page , 'perPageValue' => $per_page , 'items' => $products , 
					  'AvailableFilters' => $responsObj->suppliesFinderResponse->AvailableFilters,
					  'AppliedFilters'   => $responsObj->suppliesFinderResponse->AppliedFilters,
					  'AvailableSorts'   => $responsObj->suppliesFinderResponse->AvailableSorts
		);

		return $response;
	}

	public function replaceSpecial($str){
		$str = trim($str);
		$chunked = str_split($str,1);
		$str = "";
		foreach($chunked as $chunk){
			$num = ord($chunk);
			// Remove non-ascii & non html characters
			if ($num >= 32 && $num <= 123){
				$str.=$chunk;
			}
		}
		return mysql_escape_string($str);
	}

	public function extraFilter($sequence = 2 , $filterStyle = 'Keyword' , $filterValues,$keywordInterface = 'Standard'){
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
	
	public function updateListRequest($list_name , $start , $limit = 2000){
		$filename = Mage::getBaseDir()."/xml/update_list.xml";
		$xml = file_get_contents($filename);
		
		if($list_name == 'OemCustomSearchList'){
			$listItems = $this->ListCustomItems($start , $limit);
		}
		else{
			$listItems = $this->ListItems($start , $limit);
		}
		
		if(strlen($listItems) <= 0){
			return;
		}
		
		$xml = sprintf($xml,$this->username,$this->password,$list_name,$listItems);
		$result = $this->sendRequest($xml , $this->requestUrl);
		
		$start_pos = strpos($result,"<updateItemListResponse");
		$end_pos   = strpos($result,"</updateItemListResponse>");
		$result = substr($result,$start_pos,$end_pos);
		$result = str_replace(array('</soapenv:Envelope>','</soapenv:Body>'),"",$result);
		$result = str_replace(' xmlns="http://ws.ussco.com/eCatalog/catalog/1"','',$result);
		
		$response = array();
		
		$responsObj = simplexml_load_string($result) or die("can not parse");
		if(!$responsObj){
			return $response;
		}
		
		foreach($responsObj->itemListResponse->List->ListItem as $item){
			if($item->ResultStatus->StatusCode == 410){
				$this->dbWrite->query("update catalog_product_entity set ussco_added = 0 where sku = '".$item->ItemNumber."'");
			}
		}
	}
	
	public function ListItems($startLimit = 0 , $endLimit = 1000){
		$listItems = '';
		
		$catalog_entities = $this->dbRead->fetchRow("select group_concat(entity_id) as entity_id from catalog_product_entity_int where attribute_id = 135 and value != '21316'");
		$catalog_entities_str = $catalog_entities['entity_id'];
	
		$products  = $this->dbRead->fetchRows("select sku from catalog_product_entity Where entity_id not in ($catalog_entities_str) limit $startLimit , $endLimit");
		foreach ($products as $product){
			$listItems .= '<ns:ListItem><ns:ItemNumber><![CDATA['.$product['sku'].']]></ns:ItemNumber></ns:ListItem>';
		}
	
		return $listItems;
	}
	
	public function ListCustomItems($startLimit = 0 , $endLimit = 1000){
		$listItems = '';
		
		$catalog_entities = $this->dbRead->fetchRow("select group_concat(entity_id) as entity_id from catalog_product_entity_int where attribute_id = 135 and value != '21316'");
		$catalog_entities_str = $catalog_entities['entity_id'];
		
		$products  = $this->dbRead->fetchRows("select sku from catalog_product_entity Where ussco_added = 1 and entity_id in ($catalog_entities_str) limit $startLimit , $endLimit");
		foreach ($products as $product){
			$listItems .= '<ns:ListItem><ns:DealerItemNumber><![CDATA['.$product['sku'].']]></ns:DealerItemNumber></ns:ListItem>';
		}
		
		return $listItems;
	}
	
	function updateRequest($endLimit = 500 , $ussco_added = 0){
		for($i = 0; $i < 50000; $i += $endLimit){
			$startLimit = $i;
			
			$products = $this->dbRead->fetchRows("select sku from catalog_product_entity Where ussco_added = $ussco_added limit $startLimit , $endLimit");
			if(!$products){
				return;
			}
			
			$listItem = '';
			foreach($products as $product){
				$keywords   = str_split($product['sku'],3);
				$keywords[] = substr($product['sku'],3,strlen($product['sku']));
				
				$keywords = implode(",",$keywords);
				
				$listItem .= '<ns:Item UpdateStyle="Insert">'.
							 	'<ns:DealerItemNumber><![CDATA['.$product['sku'].']]></ns:DealerItemNumber>'.
							 	'<ns:DealerDescription><![CDATA['.$products_name.']]></ns:DealerDescription>'.
							 	'<ns:DealerKeywords><![CDATA['.$keywords.']]></ns:DealerKeywords>'.
							 	'<ns:StockedItem>Y</ns:StockedItem>'.
							 '</ns:Item>';
			}
			
			$filename = Mage::getBaseDir()."/xml/custom_list.xml";
			$xml = file_get_contents($filename);
			
			$xml = sprintf($xml,$this->username,$this->password,$listItem);
			$result = $this->sendRequest($xml , $this->requestUrl);
			
			$start_pos = strpos($result,"<updateItemResponse");
			$end_pos   = strpos($result,"</updateItemResponse>");
			$result = substr($result,$start_pos,$end_pos);
			$result = str_replace(array('</soapenv:Envelope>','</soapenv:Body>'),"",$result);
			$result = str_replace(' xmlns="http://ws.ussco.com/eCatalog/catalog/1"','',$result);
			
			$responsObj = simplexml_load_string($result) or die("can not parse $result $xml");
			
			$arr = array();
			$arrs = array();
			$i = 0;
			foreach($responsObj->itemResponse->Item as $item){
				if($item->ResultStatus->StatusCode == 230){
					$arr[] = "'" . $item->DealerItemNumber . "'";
				}
				else{
					$arrs[] = $item->DealerItemNumber;
					$i++;
				}
			}
			
			$item_string = implode(",",$arr);
			if($item_string){
				$this->dbWrite->query("update catalog_product_entity SET ussco_added = 1 where sku IN ($item_string)");
			}
		}
		
		return 1;
	}

	public function sendRequest($xml , $url = false){
		$ch = curl_init();
		if($url){
			curl_setopt($ch, CURLOPT_URL,$url);
		}
		else{
			curl_setopt($ch, CURLOPT_URL,$this->searchUrl);
		}

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