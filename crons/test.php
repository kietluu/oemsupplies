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

$attributes = $dbRead->fetchAll("select distinct attribute_id from catalog_product_index_eav_idx where attribute_id not in (select attribute_id from catalog_eav_attribute)");
if($attributes){
	foreach($attributes as $attribute){
		$dbWrite->query("delete from catalog_product_index_eav where attribute_id = '".$attribute['attribute_id']."'");
	}
}

$attributes = $dbRead->fetchAll("select distinct attribute_id from catalog_product_index_eav where attribute_id not in (select attribute_id from catalog_eav_attribute)");
if($attributes){
	foreach($attributes as $attribute){
		$dbWrite->query("delete from catalog_product_index_eav where attribute_id = '".$attribute['attribute_id']."'");
	}
}

$attributes = $dbRead->fetchAll("select distinct attribute_id from catalog_product_entity_int where attribute_id not in (select attribute_id from catalog_eav_attribute)");
if($attributes){
	foreach($attributes as $attribute){
		$dbWrite->query("delete from catalog_product_entity_int where attribute_id = '".$attribute['attribute_id']."'");
	}
}

echo "success";