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

/**
 * Class Rakuten_RakutenLogistics_Model_Shipping_Carrier_RakutenLogistics
 */
class Rakuten_RakutenLogistics_Model_Shipping_Carrier_RakutenLogistics
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{

    /**
     * @var bool
     */
    protected $_isFixed = true;

    /**
     * @var string
     */
    protected $_code = 'rakuten_rakutenlogistics';

    /**
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return bool|false|Mage_Core_Model_Abstract|Mage_Shipping_Model_Rate_Result|null
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        $isActive = (bool) Mage::getStoreConfig('carriers/rakutenlogistics_settings/active');

        if (false === $isActive) {
            return $isActive;
        }

        \Rakuten\Connector\Resources\Log\Logger::info('Processing collectRates in RakutenLogistics');
        $result = Mage::getModel('shipping/rate_result');

        $webserviceHelper = Mage::helper('rakutenlogistics/webservice');
        $carriers = $webserviceHelper->getCarrierPrices();

        $shippingOptions = $carriers['content']['shipping_options'];
        $dataHelper = Mage::helper('rakutenlogistics/data');
        $dataHelper->saveCalculationCode($carriers['content']['code']);

        foreach($shippingOptions as $option) {
            $rate = Mage::getModel('shipping/rate_result_method');

            $rate->setCarrier($this->_code);
            $rate->setCarrierTitle($option['logistics_operator_type']);
            $rate->setMethod($option['postage_service_code']);
            $rate->setMethodTitle($option['postage_service_display_name']);
            $rate->setPrice($option['final_cost']);
            $rate->setCost(0);
            $result->append($rate);
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing getAllowedMethods in RakutenLogistics');
        $helper = Mage::helper('rakutenlogistics/webservice');

        $methods = $helper->getApiCarrierMethods();
        
        $result = [];
        foreach($methods as $method){
            $result[$method['postage_service_code']] = $method['postage_service_name'];
        }

        return $result;
    }
}
