<?php

$production = true;

if($production){
	$requestUrl = 'https://ws.ussco.com/eCatalog/masterData/001/sync';
	$searchUrl  = 'https://ws.ussco.com/eCatalog/catalog/001/sync';

	$username = "002307_prod01";
	$password = "Avmw7V8Z";
}
else{
	$requestUrl = 'https://ppd2-ws.ussco.com/eCatalog/masterData/001/sync';
	$searchUrl  = 'https://ppd2-ws.ussco.com/eCatalog/catalog/001/sync';

	$username = "002307_test01";
	$password = "7tP2bMj8";
}