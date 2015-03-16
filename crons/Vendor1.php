<?php

global $root_path;
require($root_path."demo/app/Mage.php");

Mage::init('admin');
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

$dataDir   = $root_path."sftp/xmlitems";
$brandCsv  = $root_path."sftp/brand.csv";
$base_path = "http://content.oppictures.com/Master_Images/Master_Variants/Variant_500/";

$dbRead  = Mage::getSingleton("core/resource")->getConnection("core_read");
$dbWrite = Mage::getSingleton("core/resource")->getConnection("core_write");

class Vendor1{

	private $dbRead;

	private $dbWrite;

	private $brands;

	public $root_path;

	public function Vendor1(){
		global $dbWrite , $dbRead , $brandCsv;

		$this->dbRead  = $dbRead;
		$this->dbWrite = $dbWrite;

		$this->brands  = $this->readBrandCsv($brandCsv);
	}

	public function importProducts(){
		global $dataDir;

		$products = array();
		if($handle = opendir($dataDir)){
			while (false !== ($file = readdir($handle))){
				if(strlen($file) > 4 and file_exists($dataDir."/".$file) and stristr($file,"xml")){
					$xml_path = $dataDir."/".$file;
					$products[] = $xml_path;
				}
			}
		}

		//print_r($products); exit;
		$result = $this->dbRead->fetchRow("select last_id from cron");
		$start  = $result['last_id'];
		if($start >= count($products)){
			$this->dbWrite->query("update cron set last_id = 0 , total = '".count($products)."'");
			$start = 0;
		}
		else{
			$this->dbWrite->query("update cron set total = '".count($products)."'");
		}
		
		$end = $start + 10000;

		if((int)@$_GET['end']){
			$end = $start + (int)$_GET['end'];
		}

		for($i = $start; $i < $end; $i++){
			if($products[$i]){
				$productXml  = $this->readProuctXml($products[$i]);
				$products_id = $this->insertProduct($productXml);
			}

			$this->dbWrite->query("update cron set last_id = '$i' , last_run = now()");
		}
	}

	public function fetchProductBySKU($sku){
		global $dataDir;
		$product_path = $dataDir."/".$sku.".xml";

		//print $product_path; exit;
		$productXml  = $this->readProuctXml($product_path);
		$products_id = $this->insertProduct($productXml , true);

		return $products_id;
	}

	public function insertProduct($product , $force = false){
		$new_model = mysql_escape_string($product['prefix_number'] . $product['stock_number']);
		if(!$new_model){
			return false;
		}

		$productModel = Mage::getModel("catalog/product");
		$products_id  = $productModel->getIdBySku($new_model);
		if($products_id && $force == false){
			return $products_id;
		}

		print $new_model. " -- $products_id <br />";
		try{
			$category_ids = array();
			//product categories
			$categories = $product['categories'];

			for($i = 0; $i < count($categories); $i+=3){
				$category_ids[] = $this->insertProductToCategory($categories[$i] , $categories[$i+1] , $categories[$i+2]);
			}

			$manufacturers_name = $this->brands[$product['BrandId']]['name'];
			$manufacturers_logo = "manufacturers/".$this->brands[$product['BrandId']]['logo'];
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

			$product_description .= "<p>".$product['notes']['Long_Selling_Copy']."</p>";
			$product_description .= "<br /><table>";
			$i = 0;
			foreach($product['Attributes'] as $key => $value){
				$product_description .= '<td><b>'.$key.'</b></td>';
				$product_description .= '<td>'.$value.'</td>';
				$i++;
				if($i%2 == 0){
					$product_description .= "</tr><tr>";
				}
			}
			$product_description .='</tr></table>';

			$productModel
			->setWebsiteIds(array(1)) //website ID the product is assigned to, as an array
			->setAttributeSetId($productModel->getDefaultAttributeSetId()) //ID of a attribute set named 'default'
			->setTypeId('simple') //product type
			->setCreatedAt(strtotime('now')) //product creation time

			->setSku($new_model) //SKU
			->setName($product['Long_Item_Description']) //product name
			->setWeight($product['Weight'])
			->setStatus(1) //product status (1 - enabled, 2 - disabled)
			->setTaxClassId(4) //tax class (0 - none, 1 - default, 2 - taxable, 4 - shipping)
			->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH) //catalog and search visibility
			->setColor(24)
			->setCountryOfManufacture('US') //country of manufacture (2-letter country code)

			->setPrice($product['ListAmount']) //price in form 11.22
			->setCost($product['ListAmount']) //price in form 11.22
			->setMsrpEnabled(1) //enable MAP
			->setMsrpDisplayActualPriceType(1) //display actual price (1 - on gesture, 2 - in cart, 3 - before order confirmation, 4 - use config)
			->setMsrp($product['ListAmount']) //Manufacturer's Suggested Retail Price
			->setUnitCode($product['ListUnitCode'])
			->setNewsFromDate(strtotime('now'))
			->setNewsToDate(strtotime('+120 Days'))
			->setVendorCode('21316')

			->setDescription($product_description)
			->setShortDescription($this->getProductDescription($product['notes']))
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

					if(file_exists($dest) AND $productImage != 'NOA.JPG'){
						$productModel->addImageToMediaGallery($dest, array('image','thumbnail','small_image'), false, false); //assigning image, thumb and small image to media gallery
					}
				}
			}

			$productModel->save();

			if(!$products_id){
				$stockQty = 200;
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

				$product_id = $productModel->getId();
				$productModel = Mage::getModel("catalog/product")->load($product_id);

				$attributes_array = $this->insertUpdateAttributes($product['Filters']);
				foreach($attributes_array as $key => $value){
					$productModel->setData($key , $value);
				}

				$productModel->save();
			}

			return $productModel->getId();
		}
		catch(Exception $e){
			print $e->getMessage();
			Mage::log($e->getMessage());
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

	public function getProductDescription($notes){
		$html = '<table class="table">
               <tr><td><ul>';

		$html .= '<li>'.$notes['Selling_Point_1'].'</li>';

		if($notes['Selling_Point_2']){
			$html .='<li>'.$notes['Selling_Point_2'].'</li>';
		}

		if($notes['Selling_Point_3']){
			$html .='<li>'.$notes['Selling_Point_3'].'</li>';
		}

		if($notes['Selling_Point_4']){
			$html .='<li>'.$notes['Selling_Point_4'].'</li>';
		}

		if($notes['Selling_Point_5']){
			$html .='<li>'.$notes['Selling_Point_5'].'</li>';
		}

		$html .='</ul></td></tr></table>';
			
		return $html;
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

	public function readBrandCsv($path){
		$fp = fopen($path , "r") or die("file not found");

		$brands = array();
		while(!feof($fp)){
			$row = fgetcsv($fp);
			$brands[$row[1]] = array('name' => $row[0] , 'logo' => $row[2]);
		}

		return $brands;
	}

	public function readProuctXml($xml_path){
		global $base_path;

		$xmlContent = file_get_contents($xml_path);
		if(!$xmlContent){
			return;
		}

		$xmlContent = str_ireplace(array("us:","oa:"),"", $xmlContent);
		$xmlProduct = simplexml_load_string($xmlContent);

		$product = array();

		$Items = $xmlProduct->DataArea->ItemMaster->ItemMasterHeader->ItemID;
		foreach($Items as $Item){
			$attributes = $Item->attributes();

			if($attributes->agencyRole == 'Product_Number'){
				$product['product_number'] = (String)$Item->ID;
			}
			elseif($attributes->agencyRole == 'Prefix_Number'){
				$product['prefix_number'] = (String)$Item->ID;
			}
			elseif($attributes->agencyRole == 'Stock_Number_Butted'){
				$product['stock_number'] = (String)$Item->ID;
			}
			elseif($attributes->agencyRole == 'Manufacturer_Sku_Number'){
				$product['manu_sku_number'] = (String)$Item->ID;
			}
		}

		$Classifications = $xmlProduct->DataArea->ItemMaster->ItemMasterHeader->Classification;
		foreach($Classifications as $Classification){
			$attributes = $Classification->attributes();
			$code = $Classification->Codes->Code;;

			if($attributes->type == 'Product_Line'){
				$product['product_name'] = (String)$code[0];
			}
			elseif($attributes->type == 'SKU_Group'){
				$notes = $Classification->Note;
				foreach($notes as $note){
					$status = (String)$note->attributes()->status;
					$product['notes'][$status] = (String)$note[0];
				}
			}
			elseif($attributes->type == 'ECDB3'){
				$i = 0;
				foreach($Classification->Codes as $Codes){
					$product['categories'][$i]   = trim((String)$Codes->Code[0]);
					$product['categories'][$i+1] = trim((String)$Codes->Code[1]);
					$product['categories'][$i+2] = trim((String)$Codes->Code[2]);

					$i+=3;
				}
			}
		}

		$Specifications = $xmlProduct->DataArea->ItemMaster->ItemMasterHeader->Specification;
		foreach($Specifications as $Specification){
			$properties = $Specification->Property;

			foreach ($properties as $property){
				$pAttributes = $property->attributes();
				$NameValue = $property->NameValue;
				$attributes = $NameValue->attributes();

				if(!$NameValue){
					$Descriptions = $property->Description;
					foreach($Descriptions as $Description){
						$attributes   = $Description->attributes();

						if($attributes->type[0] == 'Product_Description'){
							$product['Product_Description'] = (String)$Description[0];
						}
						elseif($attributes->type[0] == 'Long_Item_Description'){
							$product['Long_Item_Description'] = (String)$Description[0];
						}
						elseif($attributes->type[0] == 'Item_Consolidated_Copy'){
							$product['Item_Consolidated_Copy'] = (String)$Description[0];
						}
						elseif($attributes->type[0] == 'Package_Includes'){
							$product['Attributes']['Package Includes'] = (String)$Description[0];
						}
					}
				}

				if($attributes->name[0]){
					$product['Attributes'][(String)$attributes->name[0]] = (String)$NameValue[0];
				}

				if($pAttributes->sequence[0]){
					$product['Filters'][(int)$pAttributes->sequence[0]][(String)$attributes->name[0]][] = (String)$NameValue[0];
				}
			}
		}

		$product['ItemStatus'] = (String)$xmlProduct->DataArea->ItemMaster->ItemMasterHeader->ItemStatus->Code[0];
		$DrawingAttachments  = $xmlProduct->DataArea->ItemMaster->ItemMasterHeader->DrawingAttachment;
		foreach($DrawingAttachments as $DrawingAttachment){
			$product['ProductImages'][] = (String)$DrawingAttachment->FileName[0];
		}
		$Attachments =(String)$xmlProduct->DataArea->ItemMaster->ItemMasterHeader->Attachment->FileName[0];
		$Attachments = explode(";",$Attachments);
		foreach($Attachments as $Attachment){
			$product['ProductImages'][] = $Attachment;
		}
		
		$product['ProductImages'] = array_reverse($product['ProductImages']);

		$product['Keywords'] = (String)$xmlProduct->DataArea->ItemMaster->ItemMasterHeader->Keywords;
		$product['BrandId']  = (String)$xmlProduct->DataArea->ItemMaster->ItemMasterHeader->BrandId;

		$GlobalItem = $xmlProduct->DataArea->ItemMaster->GlobalItem;
		$product['Width'] = (String)$GlobalItem->ItemDimensions->WidthMeasure;
		$product['Length'] = (String)$GlobalItem->ItemDimensions->LengthMeasure;
		$product['Height'] = (String)$GlobalItem->ItemDimensions->HeightMeasure;
		$product['Weight'] = (String)$GlobalItem->ItemDimensions->WeightMeasure; //lbs

		$Dimensions = $xmlProduct->DataArea->ItemMaster->ItemLocation->Packaging->Dimensions;
		$product['Attributes']['Carton Weight'] = (String)$Dimensions->Weight;
		$product['Attributes']['Item Weight'] = (String)$GlobalItem->ItemWeight;
		$product['Attributes']['Carton Pack Quantity'] = (String)$xmlProduct->DataArea->ItemMaster->ItemLocation->Packaging->PerPackageQuantity;
		$product['Attributes']['Country of Origin'] = (String)$GlobalItem->CountryOriginCode;

		//$product['Attributes']['Box Weight'] = (String)$xmlProduct->DataArea->ItemMaster->ItemLocation->UnitPackaging->Dimensions->WeightMeasure;
		//$product['Attributes']['Box Pack Quantity'] = (String)$xmlProduct->DataArea->ItemMaster->ItemLocation->UnitPackaging->PerPackageQuantity;

		$ItemList = $xmlProduct->DataArea->ItemMaster->ItemList;
		$product['ListAmount'] = (String)$ItemList->ListAmount;
		$product['ListUnitCode'] = (String)$ItemList->ListUnitCode;
		$product['VendorShortName'] = (String)$ItemList->VendorShortName;
		$product['VendorNumber'] = (String)$ItemList->VendorNumber;

		$product['Warranty']['Indicator'] = (String)$xmlProduct->DataArea->ItemMaster->WarrantyInfo->WarrantyIndicator;
		$product['Warranty']['Comments'] = (String)$xmlProduct->DataArea->ItemMaster->WarrantyInfo->WarrantyComments;

		return $product;
	}

	public function copyFiles($day){
		//connect to vender1 ftp server
		$ftp_host = "sftp.ussco.com";
		$ftp_user = "OEMSPPLY";
		$ftp_pwd  = "15PPF69D";

		$conn = ftp_connect($ftp_host) or die("Can not connect to ftp server");

		ftp_login($conn , $ftp_user , $ftp_pwd) or die("Can not login to ftp server");
		ftp_get($conn , $this->root_path.'sftp/live/INVPOSASCII' , '../DAILYINV/INVPOSASCII' , FTP_ASCII) or die("Can not copy INVPOSASCII file from ftp server");

		if($day == 'Sun'){
			echo "Extracting xml files";

			ftp_get($conn , $this->root_path.'sftp/ecdb.individual_relationships.zip' , '../xmlrelns/ecdb.individual_relationships.zip' , FTP_BINARY) or die("Can not copy files from ftp server");
			exec("unzip -o ".$this->root_path."sftp/ecdb.individual_relationships.zip -d ".$this->root_path."sftp/xmlrelns");

			ftp_get($conn , $this->root_path.'sftp/ecdb.individual_items.zip' , '../xmlitems/ecdb.individual_items.zip' , FTP_BINARY) or die("Can not copy files from ftp server");
			exec("unzip -o ".$this->root_path."sftp/ecdb.individual_items.zip -d ".$this->root_path."sftp/xmlitems");
		}

		ftp_close($conn);
		return 1;
	}

	public function updateInventory(){
		$qty_file = $this->root_path . 'sftp/live/INVPOSASCII';
		$fp = fopen($qty_file,"r") or die("can not found qty file");
		$live_qty = '';

		$this->dbWrite->query("update catalog_product_entity_int SET value = 1 where attribute_id = 96 and entity_type_id = 4");

		$i = 0;
		while(!feof($fp)){
			$row = fgets($fp);
			$row = trim($row);
			$array = preg_split("/\s\s+/",$row,2);

			$model = trim(strtolower($array[0]));
			$model = substr($model,1,strlen($model));
			$model = strtoupper($model);

			$live_qty = trim($array[1]);
			$live_qty = substr($live_qty,2,strlen($live_qty));
			$live_qty = str_ireplace("Y","Y,",$live_qty);
			$live_qty = str_ireplace('N','N,',$live_qty);

			$quantities = explode(",",$live_qty);
			foreach($quantities as $index => $qty){
				$quantities[$index + 1] = intval(str_ireplace(array("Y","N"),"",$qty));
			}

			$product_qty = $quantities[16] + $quantities[1] + $quantities[9] + $quantities[5] + $quantities[25] + $quantities[11] + $quantities[53] +
			$quantities[50] + $quantities[51] + $quantities[27] + $quantities[31] + $quantities[17] + $quantities[12] + $quantities[42] +
			$quantities[6] + $quantities[29] + $quantities[47] + $quantities[48] + $quantities[15];

			$model = mysql_escape_string($model);

			$productModel = Mage::getModel("catalog/product");
			$product_id = $productModel->getIdBySku($model);
			if($product_id){
				$vender2_qty = $this->dbRead->fetchRow("select (city_StLouis+city_Carlisle+city_Fresno+city_dallas) as qty from catalog_product_quantities where product_id = '$product_id'");
				$product_qty += $vender2_qty['qty'];
				$this->dbWrite->query("update cataloginventory_stock_item SET qty = '$product_qty' where product_id = '".$product_id."'");
			}
		}

		fclose($fp);
		return 1;
	}

	public function removeProducts(){

	}

	public function importImages(){
		global $dataDir , $base_path;

		$products = $this->dbRead->query("select p.entity_id, p.sku from catalog_product_entity p
										  inner join catalog_product_entity_int pi on (p.entity_id = pi.entity_id)
										  where attribute_id = 96 and attribute_set_id = 4 and pi.value = 1");
		if($products){
			foreach($products as $product){
				$product_id = $product['entity_id'];
				$productModel = Mage::getModel("catalog/product")->load($product_id);

				//$productModel->setMediaGallery (array('images'=>array (), 'values'=>array ())); //media gallery initialization

				$product_path = $dataDir."/".$product['sku'].".xml";
				$productData  = $this->readProuctXml($product_path);

				$needSave = false;
				$mediaAttribute = array('thumbnail','small_image','image');
				$count = 0;
				foreach($productData['ProductImages'] as $productImage){
					$productImage = trim($productImage);
					$source = $base_path.$productImage;
					$dest   = $this->root_path."demo/media/catalog/product/".$productImage;

					if(!file_exists($dest) AND $productImage != 'NOA.JPG'){
						@copy($source, $dest);
						if($count == 0){
							$productModel->addImageToMediaGallery($dest, $mediaAttribute , false, false); //assigning image, thumb and small image to media gallery
						}
						else{
							$productModel->addImageToMediaGallery($dest, null , false, false); //assigning image, thumb and small image to media gallery
						}

						$needSave = true;
					}

					$count++;
				}

				if($needSave){
					$productModel->save();
				}
			}
		}
	}

	public function insertCrossSellsProducts(){
		$dataDir = $this->root_path."sftp/xmlrelns";

		$productRelations = array();
		$productRelations[0] = null;
		
		if($handle = opendir($dataDir)){
			while (false !== ($file = readdir($handle))){
				if(strlen($file) > 4 and file_exists($dataDir."/".$file) and stristr($file,"xml")){
					$xml_path = $dataDir."/".$file;
					$productRelations[] = $xml_path;
				}
			}
		}

		$start = @$_REQUEST['start'];
		$end   = @$_REQUEST['end'];
		if(!$start || !$end){
			$start = 1;
			$end = 20;
		}
		
		$this->dbRead->query("delete from catalog_product_link where link_type_id = 4");

		for($i = $start; $i < $end; $i++){
			$xml_path = $productRelations[$i];
			if($xml_path){
				$count = $this->readRelationXml($xml_path);
				print $xml_path . " -- ".$count . "<br />";
			}
		}
	}

	private function readRelationXml($xml_path){
		$xmlContent = file_get_contents($xml_path);
		if(!$xmlContent){
			return -1;
		}

		$xmlContent = str_ireplace(array("us:","oa:"),"", $xmlContent);
		$xmlProduct = simplexml_load_string($xmlContent);

		$ProductRelationships = $xmlProduct->DataArea->ProductRelationship;
		foreach($ProductRelationships as $ProductRelationship){
			$prefix_number = (String)$ProductRelationship->PrefixNumber;
			$stock_number  = (String)$ProductRelationship->StockNumberButted;

			$product_sku  = mysql_escape_string($prefix_number . $stock_number); 
			$product = $this->dbRead->fetchRow("Select entity_id from catalog_product_entity Where sku = '$product_sku'");
				
			if(!$product || !$product['entity_id']){
				continue;
			}

			$Relationships = $ProductRelationship->Relationship;

			$product_skus = array();
			foreach($Relationships as $Relationship){
				$RelationshipMembers = $Relationship->RelationshipMember;

				foreach($RelationshipMembers as $RelationshipMember){
					$xell_prefix_number = (String)$RelationshipMember->PrefixNumber;
					$xell_stock_number  = (String)$RelationshipMember->StockNumberButted;

					$relation_model = mysql_escape_string($xell_prefix_number . $xell_stock_number);
					$product_skus[] = "'".$relation_model."'";
				}
			}
			
			if($product_skus){
				$product_skus_str = implode(",", $product_skus);
				$relExist = $this->dbRead->fetchAll("select entity_id from catalog_product_entity Where sku IN ($product_skus_str)");
				
				if($relExist){
					foreach ($relExist as $row){
						$this->dbWrite->query("insert IGNORE into catalog_product_link SET product_id = '".$row['entity_id']."' , linked_product_id = '".$product['entity_id']."' , link_type_id = 4");
						
						$this->dbWrite->query("insert IGNORE into catalog_product_link SET product_id = '".$product['entity_id']."' , linked_product_id = '".$row['entity_id']."' , link_type_id = 4");
					}
				}
			}
		}

		return 1;
	}
}