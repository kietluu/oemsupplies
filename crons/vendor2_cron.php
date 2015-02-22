<?php

if($_SERVER['HTTP_HOST'] != 'localhost'){
	$root_path = "/home/oemsuppl/public_html/";
}
else{
	$root_path = "E:/xampp2/htdocs/usasupplies/";
}

require_once("Vendor2.php");

$Vendor2 = new Vendor2();
$Vendor2->root_path = $root_path;

$action = $_GET['action'];
if(!$action){
	$action = $argv[1];
}

switch($action){
	case 'importProducts':
		$Vendor2->importProducts();
		break;

	case 'copyFiles':
		$Vendor2->copyFiles();
		break;

	case 'removeProducts':
		$Vendor2->removeProducts();
		break;

	case 'importImages':
		$Vendor2->importImages();
		break;

	case 'updateRelation':
		$Vendor2->updateRelation();
		break;
}

echo "success";