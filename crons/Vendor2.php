<?php

global $root_path;
require($root_path."demo/app/Mage.php");

Mage::init('admin');
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

$dataDir = $root_path."sftp/spn";
$category_path = $root_path."sftp/spn/Catergories.csv";

$dbRead  = Mage::getSingleton("core/resource")->getConnection("core_read");
$dbWrite = Mage::getSingleton("core/resource")->getConnection("core_write");

class Vendor2{

	private $dbRead;

	private $dbWrite;

	public $root_path;

	public function Vendor2(){
		global $dbWrite , $dbRead , $brandCsv;

		$this->dbRead  = $dbRead;
		$this->dbWrite = $dbWrite;
	}

	public function importProducts(){
		global $dataDir;

		$filename = false;
		$prefix   = "DataDelivery_1715863_".date("Ymd");
		if($handle = opendir($dataDir)){
			while (false !== ($file = readdir($handle))){
				if(strlen($file) > 4 and file_exists($dataDir."/".$file)){
					if(stristr($file , $prefix)){
						$filename = $dataDir."/".$file;
						break;
					}

					if(stristr($file , "DataDelivery_1715863") and !stristr($file , date("Ymd")) and !stristr($file , date("Ymd",(time()-(24*60*60))))){
						$name = $dataDir."/".$file;
						print $name . "<br />";
						//unlink($name);
					}
				}
			}
		}

		$products = array();
		$unmapped = array();
		$skipped_products = array();
		$notifyProducts   = array();

		$fp = fopen($filename, "r") or die("Can not open file $filename");
		$i = 0;
		$models = array();
		while(!feof($fp)){
			$row = fgetcsv($fp);
			$i++;
			if($i == 1){
				continue;
			}

			$product  = $this->readProuctCSV($row);
			if($product['product_status'] == 'Y'){
				$models[] = "'" . mysql_escape_string($product['prefix_number'] . $product['stock_number']) . "'";
			}

			$products_id = $this->insertProduct($product);
			//break;
		}

		if(count($models) > 0){

		}
	}

	public function insertProduct($product){
		$new_model = mysql_escape_string($product['prefix_number'] . $product['stock_number']);
		if(!$new_model){
			return false;
		}
		
		print $new_model. "<br />";

		//print_r($product); exit;
		$productModel = Mage::getModel("catalog/product");
		$products_id  = $productModel->getIdBySku($new_model);
		if($products_id){
			$productModel = Mage::getModel("catalog/product")->load($products_id);
			
			if($productModel->getData('vendor_code') == '21316'){
				//vendor 1 azerty item
				return $products_id;
			}
			
			$stockQty = $product['quantity'];
			if (!($stockItem = $productModel->getStockItem())) {
				$stockItem = Mage::getModel('cataloginventory/stock_item');
				$stockItem->assignProduct($productModel)
				->setData('stock_id', 1)
				->setData('store_id', 1);
			}
			$stockItem->setData('qty', $stockQty)
			->setData('is_in_stock', $stockQty > 0 ? 1 : 0)
			->setData('manage_stock', 1)
			->setData('use_config_manage_stock', 0)
			->save();
			
			$quantities = $product['quantities'];
			$checkExist = $this->dbRead->fetchRow("select id from catalog_product_quantities Where product_id = '$products_id'");
			if($checkExist){
				$this->dbWrite->query("Update catalog_product_quantities SET city_dallas = '".$quantities['city_dallas']."' ,  city_StLouis = '".$quantities['city_StLouis']."' , city_Carlisle = '".$quantities['city_Carlisle']."' , city_Fresno = '".$quantities['city_Fresno']."' Where product_id = '$products_id'");
			}
			else{
				$this->dbWrite->query("Insert into catalog_product_quantities SET city_dallas = '".$quantities['city_dallas']."' ,  city_StLouis = '".$quantities['city_StLouis']."' , city_Carlisle = '".$quantities['city_Carlisle']."' , city_Fresno = '".$quantities['city_Fresno']."' , product_id = '$products_id'");
			}
			
			return $products_id;
		}

		print "$products_id <br />";
		try{
			$category_ids = array();
			//product categories
			$categories = $product['categories'];

			for($i = 0; $i < count($categories); $i+=3){
				$category_ids[] = $this->insertProductToCategory($categories[$i] , $categories[$i+1] , $categories[$i+2]);
			}

			$manufacturers_name = $product['brand_name'];
			$manufacturers_name = $this->replaceSpecial($manufacturers_name);

			$manufacturers_id = 0;
			if($manufacturers_name){
				$manufacturers_id = $this->insertUpdateManufacturer($manufacturers_name , $manufacturers_logo);
			}

			global $base_path;
			foreach($product['ProductImages'] as $productImage){
				$productImage = trim($productImage);
				$source = $base_path.$productImage;
				$dest   = $this->root_path."demo/media/catalog/product/".$productImage;
				if(@!file_exists($dest)){
					@copy($source, $dest);
				}
			}
			
			$product_description = $product['product_description'];

			$productModel
			->setWebsiteIds(array(1)) //website ID the product is assigned to, as an array
			->setAttributeSetId($productModel->getDefaultAttributeSetId()) //ID of a attribute set named 'default'
			->setTypeId('simple') //product type
			->setCreatedAt(strtotime('now')) //product creation time

			->setSku($new_model) //SKU
			->setName($product['product_name']) //product name
			->setWeight($product['Weight'])
			->setStatus(1) //product status (1 - enabled, 2 - disabled)
			->setTaxClassId(4) //tax class (0 - none, 1 - default, 2 - taxable, 4 - shipping)
			->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH) //catalog and search visibility
			->setColor(24)
			->setCountryOfManufacture('US') //country of manufacture (2-letter country code)

			->setPrice($product['product_price']) //price in form 11.22
			->setCost($product['product_price']) //price in form 11.22
			->setMsrpEnabled(1) //enable MAP
			->setMsrpDisplayActualPriceType(1) //display actual price (1 - on gesture, 2 - in cart, 3 - before order confirmation, 4 - use config)
			->setMsrp($product['product_price']) //Manufacturer's Suggested Retail Price
			->setUnitCode($product['ListUnitCode'])
			->setNewsFromDate(strtotime('now'))
			->setNewsToDate(strtotime('+120 Days'))
			->setVendorCode('21315')

			->setDescription($product_description[1])
			->setShortDescription($product_description[0])
			->setCategoryIds($category_ids); //assign product to categories
				
			if($manufacturers_id){
				$productModel->setManufacturer($manufacturers_id); //manufacturer id
			}

			if($products_id){
				$productModel->setUpdatedAt(strtotime('now')); //product update time
			}
			else{
				$productModel->setMediaGallery (array('images'=>array (), 'values'=>array ())); //media gallery initialization
				foreach($product['ProductImages'] as $productImage){
					$dest = $this->root_path.'demo/media/catalog/product/'.$productImage;
				
					if($productImage and file_exists($dest) AND $productImage != 'NOA.JPG'){
						$productModel->addImageToMediaGallery($dest, array('image','thumbnail','small_image'), false, false); //assigning image, thumb and small image to media gallery
					}
				}
			}

			$productModel->save();

			if(!$products_id){
				$stockQty = $product['quantity'];
				if (!($stockItem = $productModel->getStockItem())) {
					$stockItem = Mage::getModel('cataloginventory/stock_item');
					$stockItem->assignProduct($productModel)
					->setData('stock_id', 1)
					->setData('store_id', 1);
				}
				$stockItem->setData('qty', $stockQty)
				->setData('is_in_stock', $stockQty > 0 ? 1 : 0)
				->setData('manage_stock', 1)
				->setData('use_config_manage_stock', 0)
				->save();

				//$product_id = $productModel->getId();
				//$productModel = Mage::getModel("catalog/product")->load($product_id);

				/* $product['Attributes'] = array('Brand Name' => $product['brand_name'] , 'Product Type' => $product['product_type']);
				$attributes_array = $this->insertUpdateAttributes($product['Attributes']);
				foreach($attributes_array as $key => $value){
					$productModel->setData($key , $value);
				} */

				//$productModel->save();
				
				$quantities = $product['quantities'];
				$checkExist = $this->dbRead->fetchRow("select id from catalog_product_quantities Where product_id = '$products_id'");
				if($checkExist){
					$this->dbWrite->query("Update catalog_product_quantities SET city_dallas = '".$quantities['city_dallas']."' ,  city_StLouis = '".$quantities['city_StLouis']."' , city_Carlisle = '".$quantities['city_Carlisle']."' , city_Fresno = '".$quantities['city_Fresno']."' Where product_id = '$products_id'");
				}
				else{
					$this->dbWrite->query("Insert into catalog_product_quantities SET city_dallas = '".$quantities['city_dallas']."' ,  city_StLouis = '".$quantities['city_StLouis']."' , city_Carlisle = '".$quantities['city_Carlisle']."' , city_Fresno = '".$quantities['city_Fresno']."' , product_id = '$products_id'");
				}
			}

			return $productModel->getId();
		}
		catch(Exception $e){
			print $e->getMessage();
			print $e->getTraceAsString();
			Mage::log($e->getTraceAsString());
			
			exit;
			return false;
		}
	}

	public function insertUpdateAttributes($attributes){
		$attributes_array = array();

		foreach($attributes as $sequence => $value){
			if($sequence > 84){
				continue;
			}

			list($option_name , $option_value) = each($value);
			if(is_array($option_value)){
				$option_value = $option_value[0];
			}

			$attribute_code = str_replace(' ', '_', $option_name);
			$attribute_code = preg_replace('/[^A-Za-z0-9_]/', '', $attribute_code);
			$attribute_code = substr($attribute_code,0,29);
			$attribute_code = strtolower($attribute_code);

			$attributeInfo = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product',$attribute_code);

			if($attributeInfo->getId() == null){
				$installer = new Mage_Catalog_Model_Resource_Setup();
				$result    = $installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, $attribute_code, array(
					            'group' => 'Attribute Details', 
					            'type' => 'int',
					            'attribute_set' =>  'Default',
					            'backend' => '',
					            'frontend' => '',
					            'label' => $option_name,
					            'input' => 'select',
					            'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
					            'visible' => true,
					            'required' => false,
					            'user_defined' => true,
					            'default' => '1',
					            'searchable' => true,
					            'filterable' => true,
					            'comparable' => true,
                                'filterable_in_search' => true,
					            'visible_on_front' => false,
					            'visible_in_advanced_search' => true,
					            'used_in_product_listing' => false,
					            'unique' => false,
								'position' => $sequence,	
					            'apply_to' => 'simple',  // Apply to simple product type
								'option' => array('values' => array($option_value))
				));
			}
			else{
				$attribute_id = $attributeInfo->getId();
				$_optionArr = array('value'=>array(), 'order'=>array(), 'delete'=>array());
				$option_value = mysql_escape_string($option_value);
				$isExist = $this->dbRead->fetchRow("select o.option_id from eav_attribute_option_value ov
											inner join eav_attribute_option o on (ov.option_id = o.option_id)
											where value = '$option_value' and attribute_id = '$attribute_id'");
				if(!$isExist){
					$key = $i + 1;
					$_optionArr['value']['option_'.$key] = array($option_value);
					$attributeInfo->setOption($_optionArr);
					$attributeInfo->save();
				}
			}

			$isExist = $this->dbRead->fetchRow("select o.option_id from eav_attribute_option_value ov
											inner join eav_attribute_option o on (ov.option_id = o.option_id)
											where value = '$option_value' and attribute_id = '$attribute_id'");
			$attributes_array[$attribute_code] = $isExist['option_id'];
		}

		return $attributes_array;
	}

	public function insertUpdateManufacturer($manufacturers_name){
		$attribute_id  = Mage::getModel('eav/entity_attribute')->getIdByCode('catalog_product', "manufacturer");

		$isExist = $this->dbRead->fetchRow("select o.option_id from eav_attribute_option_value ov
											inner join eav_attribute_option o on (ov.option_id = o.option_id)
											where value = '$manufacturers_name' and attribute_id = '$attribute_id'");
		if(!$isExist){
			$attributeInfo = Mage::getModel('eav/entity_attribute')->load($attribute_id);

			$_optionArr = array('value'=>array(), 'order'=>array(), 'delete'=>array());
			$_optionArr['value']['option_1'] = array($manufacturers_name);
			$attributeInfo->setOption($_optionArr);
			$attributeInfo->save();

			$isExist = $this->dbRead->fetchRow("select o.option_id from eav_attribute_option_value ov
											inner join eav_attribute_option o on (ov.option_id = o.option_id)
											where value = '$manufacturers_name' and attribute_id = '$attribute_id'");
			return $isExist['option_id'];
		}
		else{
			return $isExist['option_id'];
		}
	}

	public function insertProductToCategory($categories_id1 , $categories_id2 , $categories_id3){
		$parent_id = $this->insertUpdateCategory($categories_id1 , 2);
		if($categories_id2){
			$categories_id = $this->insertUpdateCategory($categories_id2 , $parent_id);
			if($categories_id3){
				$sub_categories_id = $this->insertUpdateCategory($categories_id3 , $categories_id);
			}
			else{
				$sub_categories_id = $categories_id;
			}
		}
		else{
			$sub_categories_id = $parent_id;
		}

		if($sub_categories_id){
			return $sub_categories_id;
		}
		else{
			return $parent_id;
		}
	}

	public function insertUpdateCategory($category_name , $parent_id){
		$category_name = utf8_decode($category_name);
		$category_name = trim($category_name);
		$category_name = mysql_escape_string($category_name);
		if(!$category_name){
			return false;
		}

		$category = Mage::getModel('catalog/category')->loadByAttribute('name' , $category_name);
		if(!$category){
			try{
				$category = Mage::getModel('catalog/category');
				$category->setName($category_name);
				$category->setIsActive(1);
				$category->setDisplayMode('PRODUCTS');
				$category->setIncludeInMenu(1);
				$category->setIsAnchor(1); //for active achor
				$category->setStoreId(Mage::app()->getStore()->getId());
				$parentCategory = Mage::getModel('catalog/category')->load($parent_id);
				$category->setPath($parentCategory->getPath());
				$category->setData('parent_id',$parent_id);
				$category->setAttributeSetId($category->getDefaultAttributeSetId()); // default for category
				$category->save();

				$categories_id = $category->getEntityId();
			}
			catch(Exception $e){
				Mage::log($e->getMessage());
				return false;
			}
		}
		else{
			$categories_id = $category->getEntityId();
		}

		return $categories_id;
	}


	public function getProductDescription($product , $short , $desc , $notes){
		$html1 = '<table class="table">
               <tr><td><ul>';
		$html1 .= '<li>'.$notes.'</li>';
		if($short){
			$html1 .='<li>'.$short.'</li>';
		}
		$html1 .='</ul></td></tr></table>';

		$html2 = $desc. "<br />";
		$html2 .= '<table rules="rows" width="100%"><tr>';
		$i = 0;
		foreach($product as $key => $value){
			$html2 .= '<td><b>'.$key.': </b></td>';
			$html2 .= '<td>'.$value.'</td>';
			$i++;
			if($i%2 == 0){
				$html .= "</tr><tr>";
			}
		}
		$html2 .= "</tr>";
		$html2 .='</table>';

		return array($html1 , $html2);
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

	function readProuctCSV($row){
		$product = array();

		$Dimensions = explode("x",$row[10]);
		$product['Width']  = $Dimensions[0];
		$product['Length'] = $Dimensions[1];
		$product['Height'] = $Dimensions[2];
		$product['Weight'] = $row[11];
		$product['Country Of Origin']  = $row[20];
		$product['UPC']  = $row[19];

		$product['product_description']  = $this->getProductDescription($product , $row[5] , $row[6] , $row[4]);

		$product['product_type']  = trim($row[13]);
		$product['stock_number']  = trim(str_ireplace(array("-",".","_"),"",$row[1]));
	    $parts = explode("#", $product['stock_number']);
	    $product['stock_number'] = $parts[0];
	
		$product['prefix_number'] = trim($row[8]);
		$product['product_price'] = $row[2];
		$product['product_name']  = $row[6];
		$product['brand_name']  = $row[7];
		$product['quantity']    = $row[19];

		$product['quantities']  = array('city_StLouis'=>$row[15],'city_Carlisle'=>$row[16],'city_Fresno'=>$row[18],'city_dallas'=>$row[17]);

		$row[14] = trim($row[14]);
		if($row[14] == 'N/N/N/N' || $row[14] == 'N\N\N\N'){
			$product['product_status'] = 'N';
		}
		else{
			$product['product_status'] = 'Y';
		}

		$categories = $this->getCategoryByType($row[13] , $product['product_description']);
		$product['categories'] = explode("^",$categories);

		$product['ProductImages'][] = $row[23];
		$product['Warranty'] = 'N';

		return $product;
	}

	public function getCategoryByType($product_type , $desc){
		global $category_path;

		$fp = fopen($category_path , "r");
		if(!$fp){
			return false;
		}

		while(!feof($fp)){
			$row = fgetcsv($fp);

			$type = trim($row[0]);
			$type = strtolower($type);

			$product_type = trim($product_type);
			$product_type = strtolower($product_type);

			if($type == $product_type){
				$categories = trim($row[1]);
			}
		}

		if($categories == 'Will not USE'){
			if(stristr($desc,'Waste')){
				$categories = 'Technology^Imaging Supplies & Parts^Waste Collection';
			}
			elseif(stristr($desc,'Photocunductor')){
				$categories = 'Technology^Imaging Supplies & Parts^Imaging Drums/Photoconductors';
			}
			elseif(stristr($desc,'Printhead')){
				$categories = 'Technology^Imaging Supplies & Parts^Imaging Drums/Printheads';
			}
			elseif(stristr($desc,'Staple Cartridge')){
				$categories = 'Technology^Imaging Supplies & Parts^Staple Cartridges for Printer/Fax/Copier';
			}
		}

		return $categories;
	}

	public function copyFiles(){
		//connect to vender2 ftp server
		$ftp_host = "ftp1.suppliesnet.net";
		$ftp_user = "sn";
		$ftp_pwd  = 'FTP$sn!2010';

		$conn = ftp_connect($ftp_host) or die("Can not connect to ftp server");

		ftp_login($conn , $ftp_user , $ftp_pwd) or die("Can not login to ftp server");

		ftp_get($conn , $this->root_path.'sftp/Images_L-600px.zip' , '/Product Web Images/Images_L-600px.zip' , FTP_BINARY) or die("Can not copy Images_L-600px.zip file from ftp server");

		exec("unzip -o ".$this->root_path."/sftp/Images_L-600px.zip -d ".$this->root_path."/sftp/");

		ftp_close($conn);
		return 1;
	}

	public function removeProducts(){

	}

	public function importImages(){

	}
}