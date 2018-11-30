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
 * Class Rakuten_RakutenPay_Model_Cron_Order_Status
 */
class Rakuten_RakutenPay_Model_Cron_OrderStatus
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
     * Rakuten_RakutenPay_Model_Cron_OrderStatus constructor.
     */
    public function __construct()
    {
        $this->webservice = Mage::helper('rakutenpay/webservice');
        $this->helper = Mage::helper('rakutenpay');
    }

    /**
     * @return array
     */
    protected function getFilterStatus()
    {
        return [
            \Rakuten\Connector\Enum\DirectPayment\Status::APPROVED,
            \Rakuten\Connector\Enum\DirectPayment\Status::CANCELLED,
            \Rakuten\Connector\Enum\DirectPayment\Status::PARTIAL_REFUNDED,
            \Rakuten\Connector\Enum\DirectPayment\Status::REFUNDED
        ];
    }

    /**
     * return @void
     */
    public function updateOrderStatus()
    {
        if ($this->isActive()) {
            \Rakuten\Connector\Resources\Log\Logger::info("Processing updateOrderStatus in OrderStatus", ['service' => 'Pooling']);
            $orderCollection = Mage::getModel('sales/order')->getCollection()
                ->addFieldToFilter('status', ['nin' => $this->getFilterStatus()]);
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
        $isActive = (int) Mage::getConfig()->getNode('default/cron/update_order_status/active');
        \Rakuten\Connector\Resources\Log\Logger::info('updateOrderStatus => ' . $isActive, ['service' => 'Pooling']);
        if ($isActive == 1) {
            return true;
        }

        return false;
    }
}