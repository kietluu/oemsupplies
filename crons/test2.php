<?php

error_reporting(1);

if($_SERVER['HTTP_HOST'] != 'localhost'){
	$root_path = "/home/oemsuppl/public_html/";
}
else{
	$root_path = "E:/xampp2/htdocs/usasupplies/";
}

set_time_limit(1500);
ini_set("memory_limit", "1024M");

require($root_path."demo/app/Mage.php");

Mage::init('admin');
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

$dbRead  = Mage::getSingleton("core/resource")->getConnection("core_read");
$dbWrite = Mage::getSingleton("core/resource")->getConnection("core_write");

$category = $dbRead->fetchRow("select distinct entity_id from catalog_category_entity where parent_id = 4");
$category_id = $category['entity_id'];

$products = $dbRead->fetchAll("select distinct product_id from catalog_category_product where category_id = '$category_id'");
$productIds = array();
if($products){
	foreach($products as $product){
		$productIds[] = $product['product_id'];
	}
}

//print_r($productIds);

$productIdsStr = implode(",", $productIds);
$valuesIds = $dbRead->fetchRow("select group_concat(distinct value) as ids from catalog_product_entity_int where entity_id in ($productIdsStr)");
$valuesIdsStr = implode(",", $valuesIds);
//print_r($valuesIds);

$_query = "select distinct ao.option_id , aov.value from eav_attribute_option ao inner join eav_attribute_option_value aov on (ao.option_id = aov.option_id) where attribute_id = 137 and aov.option_id in ($valuesIdsStr)";

$attribute_options = $dbRead->fetchAll($_query);

print_r($attribute_options);

echo "success";