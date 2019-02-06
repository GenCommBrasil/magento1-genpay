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

class Rakuten_RakutenLogistics_Helper_Data extends Mage_Shipping_Helper_Data
{

    public function getVersion()
    {
        return Mage::getConfig()->getModuleConfig("Rakuten_RakutenLogistics")->version;
    }

    public function saveCalculationCode($code)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing saveCalculationCode in HelperData.');
        Mage::getSingleton('core/session')->setCalculationCode($code);
    }

    public function getCalculationCode()
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing getCalculationCode in HelperData.');
       return Mage::getSingleton('core/session')->getCalculationCode();
    }

    public function insertInvoiceData($order, $request)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing insertInvoiceData in HelperData.');
        if(!empty($order->getOrderInvoiceSerie())){
            
            $invoice = [
                'series' => $order->getOrderInvoiceSerie(),
                'number' => $order->getOrderInvoiceNumber(),
                'key' => $order->getOrderInvoiceKey(),
                'cfop' => $order->getOrderInvoiceCfop(),
                'date' => $order->getOrderInvoiceDate(),
                'valueBaseICMS' => $order->getOrderInvoiceValueBaseIcms(),
                'valueICMS' => $order->getOrderInvoiceValueIcms(),
                'valueBaseICMSST' => $order->getOrderInvoiceValueBaseIcmsSt(),
                'valueICMSST' => $order->getOrderInvoiceValueIcmsSt(),
            ];    

            $request['order']['invoice'] = $invoice;
        }

        return $request;
    }


    public function isRakutenShippingMethod($shippingMethod)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing isRakutenShippingMethod in HelperData');
        $shippingMethod = explode('_', $shippingMethod);
        return count($shippingMethod) == 3 && $shippingMethod[0] == 'rakuten';
    }

    public function getRakutenShippingMethodCode($shippingMethod)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing getRakutenShippingMethodCode in HelperData');
        if($this->isRakutenShippingMethod($shippingMethod)){
            $shippingMethod = explode('_', $shippingMethod);
            return $shippingMethod[2];
        }

        $emptyCode = '';
        return $emptyCode;
    }

    public function parseStreet($fullAddress)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing parseStreet in HelperData');
        $fullAddress = explode(', ', $fullAddress);
        $street = $fullAddress[0];
        $number = isset($fullAddress[1]) ? $fullAddress[1] : '';

        return array(
            'street' => $street,
            'number' => $number,
        );
    }
}
