<?php 

/*

Plumrocket Inc.

NOTICE OF LICENSE

This source file is subject to the End-user License Agreement
that is available through the world-wide-web at this URL:
http://wiki.plumrocket.net/wiki/EULA
If you are unable to obtain it through the world-wide-web, please
send an email to support@plumrocket.com so we can send you a copy immediately.

@package    Plumrocket_Base-v1.x.x
@copyright  Copyright (c) 2015 Plumrocket Inc. (http://www.plumrocket.com)
@license    http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 
*/


class Plumrocket_Base_Model_Observer{protected $_customer=null;protected $_inStock=true;public function systemConfigLoad($observer){$controller=$observer->getEvent()->getControllerAction();$section=$this->_getSection($controller);if($this->_hasS($section)){$current=$section->getName();$_key=$current.'/general/'.strrev('laires');$product=Mage::getModel('plumbase/product')->setPref($current);if(!Mage::getStoreConfig($_key,0)){if($s=$product->loadSession()){$config=Mage::getConfig();$config->saveConfig($_key,$s,'default',0);$config->reinit();Mage::app()->reinitStores();$controller->getResponse()->setRedirect(Mage::helper('core/url')->getCurrentUrl());}}else{$product=Mage::getModel('plumbase/product')->load($product->getName());if(!$product->isInStock()||!$product->isCached()){$product->checkStatus();}}if(!$product->isInStock()){$product->disable();}if(!$product->isInStock()){Mage::getSingleton('adminhtml/session')->addError($product->getDescription());}}}protected function _getSection($controller){$req=$controller->getRequest();$current=$req->getParam('section');$website=$req->getParam('website');$store=$req->getParam('store');Mage::getSingleton('adminhtml/config_data')->setSection($current)->setWebsite($website)->setStore($store);$configFields=Mage::getSingleton('adminhtml/config');$sections=$configFields->getSections($current);if(!$current){$sections=(array)$sections;usort($sections,array($this,'_sort'));$permissions=Mage::getSingleton('admin/session');foreach($sections as $sec){$code=$sec->getName();if(!$code or trim($code)==""){continue;}if($permissions->isAllowed('system/config/'.$code)){$current=$code;$controller->getRequest()->setParam('section',$current);$section=$sec;break;}}}else{$section=$sections->$current;}return $section;}public function customer(){if(empty($this->_customer)){$this->_customer=1;}return 'customer';}public function systemConfigBeforeSave($observer){$controller=$observer->getEvent()->getControllerAction();$section=$controller->getRequest()->getParam('section');if(!$section){return;}$sData=Mage::getSingleton('adminhtml/config')->getSection($section);if($this->_hasS($sData)){$product=Mage::getModel('plumbase/product')->loadByPref($section);$this->_inStock=$product->isInStock();}}public function systemConfigSave($observer){$controller=$observer->getEvent()->getControllerAction();$section=$controller->getRequest()->getParam('section');if(!$section){return;}$sData=Mage::getSingleton('adminhtml/config')->getSection($section);if($this->_hasS($sData)){$product=Mage::getModel('plumbase/product')->loadByPref($section);$product->checkStatus();if(!$product->isInStock()){$product->disable();}else{if(!$this->_inStock){Mage::getSingleton('adminhtml/session')->addSuccess($product->getDescription());}}}}protected function _hasS($section){$i='ser'.strrev('lai');return $section&&($v=$section->groups)&&($v=$v->general)&&($v=$v->fields)&&($v=$v->$i)&&((string)$section->tab=='plum'."rock".'et');}protected function _sort($a,$b){return (int)$a->sort_order<(int)$b->sort_order?-1:((int)$a->sort_order>(int)$b->sort_order?1:0);}}