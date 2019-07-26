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
    protected function getIgnoreState()
    {
        return [
            Mage_Sales_Model_Order::STATE_CANCELED,
            Mage_Sales_Model_Order::STATE_CLOSED,
        ];
    }

    /**
     * @void
     */
    public function updateOrderState()
    {
        \Rakuten\Connector\Resources\Log\Logger::info("Processing updateOrderState in OrderState", ['service' => 'Pooling']);
        $dir = Mage::getBaseDir('var') . DIRECTORY_SEPARATOR . 'cache';
        $lockFile = $dir . DIRECTORY_SEPARATOR . 'lock';
        $f = fopen($lockFile, 'w') or die ('Cannot create lock file');
        if (flock($f, LOCK_EX | LOCK_NB)) {
            $this->execute();
        }
    }

    /**
     * @void
     */
    protected function execute()
    {
        \Rakuten\Connector\Resources\Log\Logger::info("Processing execute in OrderState", ['service' => 'Pooling']);
        if ($this->isActive() && $this->getDays()) {
            $rakutenOrders = $this->getOrders();
            foreach ($rakutenOrders as $rakutenOrder) {
                $chargeId = $rakutenOrder['charge_uuid'];
                if ($chargeId) {
                    $response = $this->webservice->poolingRequest($chargeId);
                    $this->helper->notificationModel()->initialize(json_encode($response->getResult()), false);
                    //TODO remove ELSE New Release
                } else {
                    $order = $this->getOrderById($rakutenOrder['order_id']);
                    $addtionalInformation = $order->getPayment()->getAdditionalInformation();
                    if (isset($addtionalInformation[ 'rakutenpay_id']) && !empty($addtionalInformation['rakutenpay_id'])) {
                        $this->updateRakutenPayOrder(
                            $rakutenOrder['order_id'],
                            $addtionalInformation['rakutenpay_id'],
                            $order->getIncrementId()
                        );
                        $response = $this->webservice->poolingRequest($addtionalInformation['rakutenpay_id']);
                        $this->helper->notificationModel()->initialize(json_encode($response->getResult()), false);
                    }
                }
            }
        }
    }

    /**
     * @return bool
     */
    protected function isActive()
    {
        $isActive = (int) Mage::getConfig()->getNode('default/cron/update_order_state/active');
        \Rakuten\Connector\Resources\Log\Logger::info('updateOrderState => ' . $isActive, ['service' => 'Pooling']);
        if ($isActive == 1) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    protected function getDays()
    {
        $days = Mage::getConfig()->getNode('default/cron/update_order_state/days');
        \Rakuten\Connector\Resources\Log\Logger::info('updateOrderState days: ' . $days, ['service' => 'Pooling']);

        return $days;
    }

    /**
     * @return mixed
     */
    protected function getOrders()
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing getOrders in OrderState.', ['service' => 'Pooling']);
        $resource = Mage::getSingleton('core/resource');
        $environment = Mage::getStoreConfig('payment/rakutenpay/environment');
        $read = $resource->getConnection('core_read');
        $order = $resource->getTableName('sales/order');
        $rakutenOrder = $resource->getTableName('rakuten_rakutenpay/order');
        $select = $read->select()
            ->from(['order' => $order], ['entity_id', 'increment_id', 'state', 'created_at'])
            ->join(['rakutenOrder' => $rakutenOrder], 'order.entity_id = rakutenOrder.order_id', ['order_id', 'transaction_code', 'charge_uuid'])
            ->where('order.state NOT IN (?)', $this->getIgnoreState())
            ->where('rakutenOrder.environment = ?', $environment)
            ->where('DATEDIFF(NOW(), order.created_at) <= ?', $this->getDays());

        return $read->fetchAll($select);
    }

    /**
     * TODO remove implementation in new Release
     *
     * @param $orderId
     * @return Mage_Core_Model_Abstract
     */
    protected function getOrderById($orderId)
    {
        $order = Mage::getModel('sales/order')->load($orderId);

        return $order;
    }

    /**
     * TODO remove implementation in new Release
     *
     * @param $orderId
     * @param $chargeId
     * @param $incrementId
     */
    protected function updateRakutenPayOrder($orderId, $chargeId, $incrementId)
    {
        \Rakuten\Connector\Resources\Log\Logger::info(sprintf(
            ' orderId: %s; charge_uuid: %s; incProcessing updateRakutenPayOrder;rementId: %s',
            $orderId, $chargeId, $incrementId
        ));
        $rakutenOrder = Mage::getModel('rakuten_rakutenpay/order')->load($orderId, 'order_id');
        $rakutenOrder->setChargeUuid($chargeId);
        $rakutenOrder->setIncrementId($incrementId);
        $rakutenOrder->save();
    }
}