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
 * Class Rakuten_RakutenLogistics_Helper_Batch
 */
class Rakuten_RakutenLogistics_Helper_Batch extends Mage_Admin_Helper_Data
{
    /**
     * @var array
     */
    protected $arrayPayments;

    /**
     * @var int
     */
    private $days;

    /**
     * @var array
     */
    private $orders = [];

    /**
     * @return array
     */
    public function getOrdersLogistics()
    {
        return $this->orders;
    }

    /**
     * Executes the essentials functions for this helper
     *
     * @param $days
     */
    public function initialize($days)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing initialize in Batch.');
        $this->days = $days;
        $this->getOrders();
    }

    /**
     * @param array $orderIds
     */
    public function generateBatch(array $orderIds)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing initialize in generateBatch in Batch.');
        $helper = Mage::helper('rakutenlogistics/data');
        foreach ($orderIds as $id) {
            $order = Mage::getModel('sales/order')->load($id);
            $helper->generateBatch($order);
        }
        $this->getOrders();
    }

    /**
     * Get magento orders in a date range.
     */
    protected function getOrders()
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing initialize in getOrders in Batch.');
        $resource = Mage::getSingleton('core/resource');
        $write = $resource->getConnection('core_write');
        $order = $resource->getTableName('sales/order');
        $rakutenOrder = $resource->getTableName('rakuten_rakutenlogistics/order');
        $payment = $resource->getTableName('sales/order_payment');

        $select = $write->select()
            ->from(['order' => $order], ['entity_id', 'increment_id', 'customer_firstname', 'customer_lastname', 'state', 'created_at'])
            ->join(['payment' => $payment], 'order.entity_id = payment.entity_id', ['additional_information'])
            ->join(['rakutenOrder' => $rakutenOrder], 'order.entity_id = rakutenOrder.order_id', ['calculation_code', 'order_id'])
            ->where('DATEDIFF(NOW(), order.created_at) <= ?', $this->days)
            ->where('order.state = ?', Mage_Sales_Model_Order::STATE_PROCESSING);

        $data = $write->fetchAll($select);
        foreach ($data as $key => $item) {
            if (!$item['calculation_code']) {
                continue;
            }
            if (!$this->hasBatchCode($item['additional_information'])) {
                $this->orders[$key]['orderId'] = $item['order_id'];
                $this->orders[$key]['incrementId'] = $item['increment_id'];
                $this->orders[$key]['status'] = $item['state'];
                $this->orders[$key]['billingName'] = $item['customer_firstname'] . ' ' . $item['customer_lastname'];
                $createdAt = new \DateTime($item['created_at']);
                $this->orders[$key]['createdAt'] = $createdAt->format('d/m/Y H:m:s');
                $this->orders[$key]['calculationCode'] = $item['calculation_code'];
            }
        }
        if (count($this->orders)) {
            $this->orders = array_values($this->orders);
        }
    }

    /**
     * @param string $additionalInformation
     * @return bool
     */
    protected function hasBatchCode($additionalInformation)
    {
        $field = "batch_code";
        if (strpos($additionalInformation, $field) !== false) {

            return true;
        }

        return false;
    }
}
