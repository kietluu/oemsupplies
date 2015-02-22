<?php
/**
 * Webtex
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Webtex EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.webtexsoftware.com/LICENSE-1.0.html
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@webtex.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.webtexsoftware.com/ for more information
 * or send an email to sales@webtexsoftware.com
 *
 * @category   Webtex
 * @package    Webtex_PricesPerCustomer
 * @copyright  Copyright (c) 2010 Webtex (http://www.webtexsoftware.com/)
 * @license    http://www.webtex.com/LICENSE-1.0.html
 */

/**
 * Prices Per Customer extension
 *
 * @category   Webtex
 * @package    Webtex_PricesPerCustomer
 * @author     Webtex Dev Team <dev@webtex.com>
 */

class Webtex_CustomerPrices_Block_Catalog_Product_View_Type_Grouped extends Mage_Catalog_Block_Product_View_Type_Grouped
{
    public function getCustomTierPriceHtml($product = null)
    {
        if (is_null($product)) {
            $product = $this->getProduct();
        }
        return $this->_getPriceBlock($product->getTypeId())
            ->setTemplate('customerprices/customertierprices.phtml')
            ->setProduct($product)
            ->setInGrouped($this->getProduct()->isGrouped())
            ->toHtml();
    }

    public function getCustomTierPrices($product = null)
    {
        if (is_null($product)) {
            $product = $this->getProduct();
        }

        $prices  = $product->getFormatedCustomTierPrice();

        $res = array();
        if (is_array($prices)) {
            foreach ($prices as $price) {
                $price['qty'] = $price['qty']*1;

                if ($product->getPrice() != $product->getFinalPrice()) {
                    $productPrice = $product->getFinalPrice();
                } else {
                    $productPrice = $product->getPrice();
                }

                if ($price['price']<$productPrice) {
                    $price['savePercent'] = ceil(100 - (( 100/$productPrice ) * $price['price'] ));

                    $tierPrice = Mage::app()->getStore()->convertPrice(
                        Mage::helper('tax')->getPrice($product, $price['price'])
                    );
                    $price['formated_price'] = Mage::app()->getStore()->formatPrice($tierPrice);
                    $price['formated_price_incl_tax'] = Mage::app()->getStore()->formatPrice(
                        Mage::app()->getStore()->convertPrice(
                            Mage::helper('tax')->getPrice($product, $price['price'], true)
                        )
                    );

                    if (Mage::helper('catalog')->canApplyMsrp($product)) {
                        $oldPrice = $product->getFinalPrice();
                        $product->setPriceCalculation(false);
                        $product->setPrice($tierPrice);
                        $product->setFinalPrice($tierPrice);

                        $this->getLayout()->getBlock('product.info')->getPriceHtml($product);
                        $product->setPriceCalculation(true);

                        $price['real_price_html'] = $product->getRealPriceHtml();
                        $product->setFinalPrice($oldPrice);
                    }

                    $res[] = $price;
                }
            }
        }
        return array_reverse($res);
    }

}