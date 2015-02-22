<?php

require_once("SmartSearch.php");

$SmartSearch = new SmartSearch();

$action = $_GET['action'];
switch($action){
	case 'updateSuppliers':
		$SmartSearch->UpdateSuppliesFinder();
		break;

	case 'updateSuppliersModels':
		$SmartSearch->UpdateSuppliesModels();
		break;
}

echo "success";