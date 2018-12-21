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
 * Class Rakuten_RakutenPay_Model_Cron_Order_State
 */
class Rakuten_RakutenPay_Model_Cron_OrderState
{
    /**
     * @var Rakuten_RakutenPay_Helper_Webservice
     */
    private $webservice;

    /**
     * @var
     */
    private $helper;

    /**
     * Rakuten_RakutenPay_Model_Cron_OrderState constructor.
     */
    public function __construct()
    {
        $this->webservice = Mage::helper('rakutenpay/webservice');
        $this->helper = Mage::helper('rakutenpay');
    }

    /**
     * @return array
     */
    protected function getFilterState()
    {
        return [
            Mage_Sales_Model_Order::STATE_NEW,
            Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
            Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW,
        ];
    }

    /**
     * return @void
     */
    public function updateOrderState()
    {
        if ($this->isActive()) {
            \Rakuten\Connector\Resources\Log\Logger::info("Processing updateOrderState in OrderState", ['service' => 'Pooling']);
            $orderCollection = Mage::getModel('sales/order')->getCollection()
                ->addAttributeToFilter('state', ['in' => $this->getFilterState()]);
            \Rakuten\Connector\Resources\Log\Logger::info("Count Orders: " . count($orderCollection), ['service' => 'Pooling']);

            foreach ($orderCollection as $order) {
                $addtionalInformation = $order->getPayment()->getAdditionalInformation();
                if (isset($addtionalInformation[ 'rakutenpay_id']) && !empty($addtionalInformation[ 'rakutenpay_id'])) {
                    $response = $this->webservice->poolingRequest($addtionalInformation['rakutenpay_id']);
                    $this->helper->notificationModel()->initialize(json_encode($response->getResult()), false);
                }
            }
        }
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        $isActive = (int) Mage::getConfig()->getNode('default/cron/update_order_state/active');
        \Rakuten\Connector\Resources\Log\Logger::info('updateOrderState => ' . $isActive, ['service' => 'Pooling']);
        if ($isActive == 1) {
            return true;
        }

        return false;
    }
}