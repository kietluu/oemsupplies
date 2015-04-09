<?php 

$products_id = (int)$_GET['products_id'];
if(!$products_id){
	echo "invalid product";
	exit;
}

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
	<tr class="even">
		<td>Carlisle</td>
		<td align="center"><?php echo $qtyBox['city_Carlisle'];?></td>
	</tr>
	<tr class="even">
		<td>Dallas</td>
		<td align="center"><?php echo $qtyBox['city_dallas'];?></td>
	</tr>
	<tr class="odd">
		<td>Fresno</td>
		<td align="center"><?php echo $qtyBox['city_Fresno'];?></td>
	</tr>
	<tr class="odd">
		<td>St. Louis</td>
		<td align="center"><?php echo $qtyBox['city_StLouis'];?></td>
	</tr>
</table>