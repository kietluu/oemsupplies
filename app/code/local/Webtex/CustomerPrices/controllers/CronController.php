<?php

class Webtex_CustomerPrices_CronController extends Mage_Core_Controller_Front_Action
{

	public function cronImportAction()
	{
		$request = $this->getRequest();

		$path		= $request->getParam('path', false);
		$delimiter	= $request->getParam('delimiter', ';');
		$enclosure	= $request->getParam('enclosure', '"');

	try {
			$io = new Varien_Io_File();
			$io->streamOpen(Mage::getBaseDir().$path, 'r');
			$io->streamLock(true);
			$map = $io->streamReadCsv($delimiter, $enclosure);
			$prodModel = Mage::getSingleton('catalog/product');
			$db = Mage::getSingleton('core/resource')->getConnection('core_write');
			$db->query('set foreign_key_checks = 0');
			$db->query("delete from {$db->getTableName('customerprices_prices')}");
			$errors = array();
			while($data = $io->streamReadCsv($delimiter, $enclosure)){
				$prod = $prodModel->loadByAttribute('sku', $data[1]);
				if(!$prod || !$prod->getId()){
					continue;
				}
				$customer = Mage::getModel('customer/customer')->setWebsiteId(1)->loadByEmail($data[0]);
				if(!$customer->getId()){
				    Mage::log('Customer not exist - '.$data[0],null,'Customerprices_import.log');
				    $errors[] = $data[0];
				    continue;
				}
				$prices = Mage::getModel('customerprices/prices');
				$prices->setProductId($prod->getId());
				$prices->setCustomerEmail($data[0]);
				$prices->setCustomerId($customer->getId());
				$prices->setQty($data[2]);
				$prices->setPrice($data[3]);
				$prices->setSpecialPrice($data[4]);
				$prices->save();
			}

            echo 'FINISHED'."\n";
            if(count($errors)){
                echo "Check Customerprices_import.log for errors";
            }

        }
        catch (Mage_Core_Exception $e) {
            echo $e->getMessage();
        }
        catch (Exception $e) {
            echo $e->getMessage();
        }

    }

}
