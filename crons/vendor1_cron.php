<?php

if($_SERVER['HTTP_HOST'] != 'localhost'){
	$root_path = "/home/oemsuppl/public_html/";
}
else{
	$root_path = "E:/xampp2/htdocs/usasupplies/";
}

set_time_limit(1500);

require_once("Vendor1.php");

$Vendor1 = new Vendor1();
$Vendor1->root_path = $root_path;

$action = $_GET['action'];
if(!$action){
	$action = $argv[1];
}

echo $action;

switch($action){
	case 'importProducts':
		$Vendor1->importProducts();
		break;

	case 'updateQty':
		$Vendor1->updateInventory();
		break;

	case 'copyFiles':
		$Vendor1->copyFiles();
		break;

	case 'removeProducts':
		$Vendor1->removeProducts();
		break;

	case 'importImages':
		$Vendor1->importImages();
		break;

	case 'updateRelation':
		$Vendor1->insertCrossSellsProducts();
		break;
		
	case 'fetchProductBySKU':
		$Vendor1->fetchProductBySKU($_GET['sku']);
		break;
}

echo "success";