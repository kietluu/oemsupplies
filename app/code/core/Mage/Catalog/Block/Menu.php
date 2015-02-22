<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2014 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Catalog_Block_Menu extends Mage_Core_Block_Template
{

	public function __construct(){
		parent::_construct();

		$this->dbRead = Mage::getSingleton("core/resource")->getConnection("core_read");
	}

	public function getFurnitureBrands(){
		$top_brands = array("'FireKing'","'Rubbermaid'","'Bush'","'Mayline'","'deflect-o'","'Crown'");
		$top_brands = implode(",",$top_brands);
		$top_brands = strtolower($top_brands);

		$brandList = $this->dbRead->fetchAll("SELECT option_id , value from `eav_attribute_option_value` WHERE `option_id` IN
											  ( SELECT option_id FROM eav_attribute_option WHERE attribute_id = 81 and Lower(value) IN ($top_brands) )");
		return $brandList;
	}

	public function getTechnologyBrands(){
		$top_brands = array("'Belkin'","'3M'","'HP'","'Brother'","'Xerox'","'Logitech'");
		$top_brands = implode(",",$top_brands);
		$top_brands = strtolower($top_brands);

		$brandList = $this->dbRead->fetchAll("SELECT option_id , value from `eav_attribute_option_value` WHERE `option_id` IN
											  ( SELECT option_id FROM eav_attribute_option WHERE attribute_id = 81 and Lower(value) IN ($top_brands) )");
		return $brandList;
	}

	public function getOfficeSuppliesBrands(){
		$top_brands = array("'BIC'","'AT-A-GLANCE'","'Avery'","'Smead'","'Swingline'","'Tombow'");
		$top_brands = implode(",",$top_brands);
		$top_brands = strtolower($top_brands);

		$brandList = $this->dbRead->fetchAll("SELECT option_id , value from `eav_attribute_option_value` WHERE `option_id` IN
											  ( SELECT option_id FROM eav_attribute_option WHERE attribute_id = 81 and Lower(value) IN ($top_brands) )");
		return $brandList;
	}

	public function getMaintenanceBrands(){
		$top_brands = array("'Starbucks'","'Dixie'","'Air Wick'","'PapaNicholas Coffee'","'LYSOL Brand'","'Energizer'");
		$top_brands = implode(",",$top_brands);
		$top_brands = strtolower($top_brands);

		$brandList = $this->dbRead->fetchAll("SELECT option_id , value from `eav_attribute_option_value` WHERE `option_id` IN
											  ( SELECT option_id FROM eav_attribute_option WHERE attribute_id = 81 and Lower(value) IN ($top_brands) )");
		return $brandList;
	}
}