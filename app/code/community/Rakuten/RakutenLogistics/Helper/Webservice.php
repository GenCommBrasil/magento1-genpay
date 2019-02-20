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
 * Class Rakuten_RakutenLogistics_Helper_Webservice
 */
class Rakuten_RakutenLogistics_Helper_Webservice extends Mage_Core_Helper_Abstract
{
    /**
     * @return \Rakuten\Connector\Resources\Http\RakutenLogistics\Http
     * @throws Exception
     */
    private function getHttp()
    {
        return new \Rakuten\Connector\Resources\Http\RakutenLogistics\Http();
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getApiCarrierMethods()
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing getApiCarrierMethods in Webservice');
        $url = Rakuten_RakutenLogistics_Helper_Environment::getEndpoint(Rakuten_RakutenLogistics_Enum_Endpoints::NAME_CARRIER_METHODS);

        $http = $this->getHttp();
        $http->get($url);
        $response = json_decode($http->getResponse(), true);

        return $response['content']['shipping_options'];
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getCarrierPrices()
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing getCarrierPrices in Webservice');
        $url = Rakuten_RakutenLogistics_Helper_Environment::getEndpoint(Rakuten_RakutenLogistics_Enum_Endpoints::NAME_CARRIER_PRICES);

        $cart = Mage::getModel('checkout/cart')->getQuote();
        $cartInfo['destination_zipcode'] = $cart->getShippingAddress()->getPostcode();
        $cartInfo['postage_service_codes'] = array();
        $cartInfo['products'] = array();
        foreach ($cart->getAllItems() as $item) {
            $product = $item->getProduct();
            $catalogProduct = Mage::getModel('catalog/product')->load($product->getId());
            $productInfo['code'] = $product->getSku();
            $productInfo['quantity'] = $item->getQty();
            $productInfo['dimensions']['weight'] = $product->getWeight();
            $productInfo['dimensions']['width'] = $catalogProduct->getWidth();
            $productInfo['dimensions']['height'] = $catalogProduct->getHeight();
            $productInfo['dimensions']['length'] = $catalogProduct->getLength();
            $cartInfo['products'][] = $productInfo;
        }

        $http = $this->getHttp();
        $http->post($url, $cartInfo, $timeout = 20);
        $response = json_decode($http->getResponse(), true);
        \Rakuten\Connector\Resources\Log\Logger::info(sprintf('RakutenLogistics: %s', $response), ["service" => "HTTP_RESPONSE"]);

        return $response;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return bool|mixed
     * @throws \Rakuten\Connector\Exception\ConnectorException
     */
    public function createBatch(Mage_Sales_Model_Order $order)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing createBash in Webservice');
        $helper = Mage::helper('rakutenlogistics/data');
        $shippingMethod = $order->getShippingMethod();
        $rakutenShippingMethodCode = $helper->getRakutenShippingMethodCode($shippingMethod);

        if (empty($rakutenShippingMethodCode)) {
            return false;
        }

        $calculationCode = $helper->getCalculationCode($order);
        if (!$calculationCode) {
            return false;
        }

        if ($order->getState() != Mage_Sales_Model_Order::STATE_PROCESSING) {
            return false;
        }

        $shippingAddress = $order->getShippingAddress();
        $street = $helper->parseStreet($shippingAddress->getStreetFull());

        $request = [
            'calculation_code' => $calculationCode,
            'postage_service_code' => $rakutenShippingMethodCode,
            'order' => [
                'code' => $order->getIncrementId(),
                'customer_order_number' => $order->getIncrementId(),
                'charge_external_payments' => false,
                'total_value' => $order->getGrandTotal(),

                'customer' => [
                    'first_name' => $order->getCustomerFirstname(),
                    'last_name' => $order->getCustomerLastname(),
                    'cpf' => $order->getCustomerTaxvat(),
                ],
                'delivery_address' => [
                    'first_name' => $order->getCustomerFirstname(),
                    'last_name' => $order->getCustomerLastname(),
                    'street' => $street['street'],
                    'number' => $street['number'],
                    'complement' => '___',
                    'city' => $shippingAddress->getCity(),
                    'zipcode' => $shippingAddress->getPostcode(),
                    'email' => $order->getCustomerEmail(),
                    'phone' => $shippingAddress->getTelephone(),
                    'fax' => $shippingAddress->getFax(),
                ],
            ],
        ];
        $request['order']['delivery_address']['state'] = 
            Mage::helper('rakutenlogistics/address')
                ->getRegionAbbreviation($shippingAddress->getRegion());

        if (Mage::helper('core')->isModuleEnabled('Rakuten_RakutenPay')) {
            $transactionCode = $order->getPayment()->getAdditionalInformation('rakutenpay_id');
            if ($transactionCode) {
                $request['order']['payments_charge_id'] = $transactionCode;
            }
        }
        $request = $helper->insertInvoiceData($order, $request);
        $url = Rakuten_RakutenLogistics_Helper_Environment::getEndpoint(Rakuten_RakutenLogistics_Enum_Endpoints::NAME_BATCH);
        $http = $this->getHttp();
        $http->post($url, [$request]);
        $response = json_decode($http->getResponse(), true);

        return $response;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return mixed
     * @throws \Rakuten\Connector\Exception\ConnectorException
     */
    public function orderDetail(Mage_Sales_Model_Order $order)
    {
        $url = Rakuten_RakutenLogistics_Helper_Environment::getEndpoint(Rakuten_RakutenLogistics_Enum_Endpoints::NAME_BATCH_DETAIL);
        $parameter = $order->getIncrementId();
        $http = $this->getHttp();
        $http->get($url . $parameter);
        $response = json_decode($http->getResponse(), true);

        return $response;
    }
}
