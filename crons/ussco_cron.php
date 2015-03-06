<?php
require_once ("SmartSearch.php");

$SmartSearch = new SmartSearch ();

$action = $_GET ['action'];
switch ($action) {
	case 'updateSuppliers' :
		$SmartSearch->UpdateSuppliesFinder ();
		break;
	
	case 'updateSuppliersModels' :
		$SmartSearch->UpdateSuppliesModels ();
		break;
	
	case 'updateOemList' :
		$SmartSearch->updateListRequest ('OemSearchList');
		break;
	
	case 'updateCustomList' :
		$SmartSearch->updateListRequest ('OemCustomSearchList');
		break;
	
	case 'addCustomItems' :
		$SmartSearch->updateRequest ();
		break;
}

echo "success";