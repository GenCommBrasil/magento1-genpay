<?php
/**
 ************************************************************************
 * Copyright [2018] [RakutenConnector]
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 ************************************************************************
 */

namespace Rakuten\Connector\Parsers;

use Mage;
use Rakuten\Connector\Resources\Log\Logger;

/**
 * Trait Order
 * @package Rakuten\Connector\Parsers
 */
trait Order
{
    /**
     * @param $request
     * @param $properties
     * @return array
     */
    public static function getData($request, $properties)
    {
        Logger::info('Processing getData in trait Order.');
        $data = [];
        $orderData = [];
        $shippingCost = 0.0;

        if (!is_null($request->getShipping()) and !is_null($request->getShipping()->getCost())) {
            $shippingCost = floatval(\Rakuten\Connector\Helpers\Currency::toDecimal($request->getShipping()->getCost()->getCost()));
        }

        $items = self::getItems($request, $properties);
        $itemsAmount = 0.0;
        foreach ($items as $item) {
            if (!is_null($item[$properties::TOTAL_AMOUNT])) {
                $itemsAmount += $item[$properties::TOTAL_AMOUNT];
            }
        }

        $taxesAmount = $request->getTaxAmount();
        if (method_exists($request, 'getInstallment') && !is_null(current($request->getInstallment())) && !is_null(current($request->getInstallment())->getInterestAmount())) {
            $taxesAmount += floatval(current($request->getInstallment())->getInterestAmount());
        }

        //items_amount
        $orderData[$properties::ITEMS_AMOUNT] = $itemsAmount;
        //shipping_amount
        $orderData[$properties::SHIPPING_AMOUNT] = $shippingCost;
        //taxes_amount
        $orderData[$properties::TAXES_AMOUNT] = $taxesAmount;
        //discount_amount
        $orderData[$properties::DISCOUNT_AMOUNT] = $request->getDiscountAmount();
        //items
        $orderData[$properties::ITEMS] = $items;
        //remote_ip
        $orderData[$properties::PAYER_IP] = \Mage::helper('core/http')->getRemoteAddr();
        //reference
        $orderData[$properties::REFERENCE] = $request->getReference();
        $data[$properties::ORDER] = $orderData;
        return $data;
    }

    private static function getItems($request, $properties)
    {
        $itemsData = [];
        $items = $request->getItems();
        if ($request->itemLenght() > 0) {
            foreach ($items as $item) {
                $itemsData[] = self::getItem($item, $properties);
            }
        }
        return $itemsData;
    }

    private static function getItem($item, $properties)
    {
        $data = [];
        $quantity = null;
        $amount = null;
        //reference
        if ($item->getId() != null) {
            $data[$properties::REFERENCE] = $item->getId();
            $product = Mage::getModel('catalog/product')->load($item->getReference());
        }
        //description
        if ($item->getDescription() != null) {
            $data[$properties::DESCRIPTION] = $item->getDescription();
        }
        //amount
        if ($item->getAmount() != null) {
            $amount = floatval(\Rakuten\Connector\Helpers\Currency::toDecimal($item->getAmount()));
            $data[$properties::AMOUNT] = $amount;
        }
        //quantity
        if ($item->getQuantity() != null) {
            $quantity = $item->getQuantity();
            $data[$properties::QUANTITY] = intval($quantity);
        }
        //total
        if (!is_null($amount) and ! is_null($quantity)) {
            $total = $amount * $quantity;
            $data[$properties::TOTAL_AMOUNT] = $total;
        }

        //categories
        $categories = [];
        $category = [];
        if (!is_null($product) and $product->getCategoryIds() != null) {
            $categoryIds = $product->getCategoryIds();
            foreach ($categoryIds as $categoryId) {
                $_cat = Mage::getModel('catalog/category')->setStoreId(Mage::app()->getStore()->getId())->load($categoryId);
                $category[$properties::NAME] = $_cat->getName();
                $category[$properties::ID] = $categoryId;
                $categories[] = $category;
            }
        } else {
            $category[$properties::NAME] = 'Outros';
            $category[$properties::ID] = 99;
            $categories[] = $category;
        }
        $data[$properties::CATEGORIES] = $categories;

        return $data;
    }
}
