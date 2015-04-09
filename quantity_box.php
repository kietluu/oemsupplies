<?php 

require("app/Mage.php");
Mage::init('admin');
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

$products_id = (int)$_GET['products_id'];
if(!$products_id){
	echo "invalid product";
	exit;
}

$dbRead  = Mage::getSingleton("core/resource")->getConnection("core_read");
$product_model = Mage::getModel("catalog/product")->load($products_id);

if($product_model->getData('vendor_code') != '21316'){
	include_once 'quantity_box_2.php';
	exit;
}

$products_model = $product_model->getData('sku');

$qty_file = '../sftp/live/INVPOSASCII';
$fp = fopen($qty_file,"r") or die("can not found qty file");
$live_qty = '';

$i = 0;
while(!feof($fp)){
	$row = fgets($fp);
	$row = trim($row);
	$array = preg_split("/\s\s+/",$row,2);
	
	$model1 = trim(strtolower($array[0]));
	$model2 = 'd'.trim(strtolower($products_model));
	
	if($model1 == $model2){
		$live_qty = trim($array[1]);
		break;
	}
}
fclose($fp);

$live_qty = substr($live_qty,2,strlen($live_qty));
$live_qty = str_ireplace("Y","Y,",$live_qty);
$live_qty = str_ireplace('N','N,',$live_qty);

$quantities = explode(",",$live_qty);
foreach($quantities as $index => $qty){
	$quantities[$index + 1] = intval(str_ireplace(array("Y","N"),"",$qty));
}
unset($quantities[0]);

$qtyBox = $dbRead->fetchRow("select * from catalog_product_quantities Where product_id = '$products_id'");
?>
<style type="text/css">
  table td {
  	 font-size: 12px;
  	 font-family: arial;
  }
  table .odd{
     background-color:#e7e7e7;
  }
  table .even{
      background-color:#fff;
  }
</style>
<div>
	<span style="float:left;">Availbility Summary</span>
	<span style="float:right;"><a href="javascript:window.close();">Close Window</a></span>
</div>
<div style="clear:both;padding-top:15px;"></div>

<table width="100%" cellpadding="1" cellspacing="0" border="1" style="border-collapse:collapse;">
	<tr style="background-color:#8DB4E3;">
		<th style="color:#fff;" align="left">City</th>
		<th style="color:#fff;">Quantity</th>
	</tr>
	<tr class="odd">
		<td>Albany</td>
		<td align="center"><?php echo $quantities[16];?></td>
	</tr>
	<tr class="even">
		<td>Atlanta</td>
		<td align="center"><?php echo $quantities[1];?></td>
	</tr>
	<tr class="odd">
		<td>Baltimore</td>
		<td align="center"><?php echo $quantities[9];?></td>
	</tr>
	<tr class="even">
		<td>Boston</td>
		<td align="center"><?php echo $quantities[5];?></td>
	</tr>
	<tr class="even">
		<td>Carlisle</td>
		<td align="center"><?php echo $qtyBox['city_Carlisle'];?></td>
	</tr>
	<tr class="odd">
		<td>Charlotte</td>
		<td align="center"><?php echo $quantities[53];?></td>
	</tr>
		<tr class="odd">
		<td>Chicago</td>
		<td align="center"><?php echo $quantities[25];?></td>
	</tr>
	<tr class="even">
		<td>Cleveland</td>
		<td align="center"><?php echo $quantities[11];?></td>
	</tr>
	<tr class="even">
		<td>Cranbury</td>
		<td align="center"><?php echo $quantities[50];?></td>
	</tr>
	<tr class="even">
		<td>Dallas</td>
		<td align="center"><?php echo $quantities[27] + $qtyBox['city_Fresno'];?></td>
	</tr>
	<tr class="odd">
		<td>Denver</td>
		<td align="center"><?php echo $quantities[51];?></td>
	</tr>
	<tr class="odd">
		<td>Fresno</td>
		<td align="center"><?php echo $qtyBox['city_Fresno'];?></td>
	</tr>
	<tr class="odd">
		<td>Ft. Lauderdale</td>
		<td align="center"><?php echo $quantities[31];?></td>
	</tr>
	<tr class="even">
		<td>Kansas City</td>
		<td align="center"><?php echo $quantities[17];?></td>
	</tr>
	<tr class="odd">
		<td>Los Angeles</td>
		<td align="center"><?php echo $quantities[6];?></td>
	</tr>
	<tr class="even">
		<td>Memphis</td>
		<td align="center"><?php echo $quantities[42];?></td>
	</tr>
	<tr class="odd">
		<td>Minneapolis</td>
		<td align="center"><?php echo $quantities[12];?></td>
	</tr>
	<tr class="even">
		<td>Phoenix</td>
		<td align="center"><?php echo $quantities[29];?></td>
	</tr>
	<tr class="odd">
		<td>Sacramento</td>
		<td align="center"><?php echo $quantities[47];?></td>
	</tr>
	<tr class="even">
		<td>Salt Lake City</td>
		<td align="center"><?php echo $quantities[48];?></td>
	</tr>
	<tr class="odd">
		<td>St. Louis</td>
		<td align="center"><?php echo $qtyBox['city_StLouis'] + $quantities[15];?></td>
	</tr>
</table>