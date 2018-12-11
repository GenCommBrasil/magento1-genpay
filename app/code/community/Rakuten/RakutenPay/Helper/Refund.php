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

class Rakuten_RakutenPay_Helper_Refund extends Rakuten_RakutenPay_Helper_Data
{
    /**
     * @var array
     */
    protected $arrayPayments;
    /**
     * @var array
     */
    private $RakutenPayPaymentList;
    /**
     * @var int
     */
    private $days;
    /**
     * @var array
     */
    private $magentoPaymentList;

    /**
     * Returns payment array
     *
     * @return mixed $this->arrayPayment
     */
    public function getPaymentsArray()
    {
        return $this->arrayPayments;
    }

    /**
     * Executes the essentials functions for this helper
     *
     * @param $days
     */
    public function initialize($days)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing initialize in HelperRefund.');
        $this->days = $days;
        $this->getRakutenPayPayments();
        $this->getMagentoPayments();
        $this->requestTransactionsToRefund();
    }

    /**
     * Get RakutenPayTransaction from webservice in a date range.
     *
     * @param null $page
     *
     * @throws Exception
     */
    private function getRakutenPayPayments($page = null)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing getRakutenPayPayments in HelperRefund.');
        if (is_null($page)) {
            $page = 1;
        }
        $date = new DateTime ("now");
        $date->setTimezone(new DateTimeZone ("America/Sao_Paulo"));
        $dateInterval = "P".( string )$this->days."D";
        $date->sub(new DateInterval ($dateInterval));
        $date->setTime(00, 00, 00);
        $date = $date->format("Y-m-d");
        try {
            if (is_null($this->RakutenPayPaymentList)) {
                $this->RakutenPayPaymentList = $this->webserviceHelper()->getTransactionsByDate($page, 1000, $date);
            } else {
                $RakutenPayPaymentList = $this->webserviceHelper()->getTransactionsByDate($page, 1000, $date);
                $this->RakutenPayPaymentList->setDate($RakutenPayPaymentList->getDate());
                $this->RakutenPayPaymentList->setCurrentPage($RakutenPayPaymentList->getCurrentPage());
                $this->RakutenPayPaymentList->setTotalPages($RakutenPayPaymentList->getTotalPages());
                $this->RakutenPayPaymentList->setResultsInThisPage(
                    $RakutenPayPaymentList->getResultsInThisPage() + $this->RakutenPayPaymentList->getResultsInThisPage()
                );
                $this->RakutenPayPaymentList->setTransactions($RakutenPayPaymentList->getTransactions());
            }
            if (!is_null($this->RakutenPayPaymentList) && $this->RakutenPayPaymentList->getTotalPages() > $page) {
                $this->getRakutenPayPayments(++$page);
            }
        } catch (\Rakuten\Connector\Exception\ConnectorException $pse) {
            throw $pse;
        }
    }

    /**
     * Get magento orders in a date range.
     */
    protected function getMagentoPayments()
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing getMagentoPayments in HelperRefund.');
        $date = new DateTime ("now");
        $date->setTimezone(new DateTimeZone ("America/Sao_Paulo"));
        $dateInterval = "P".( string )$this->days."D";
        $date->sub(new DateInterval ($dateInterval));
        $date->setTime(00, 00, 00);
        $date = $date->format("Y-m-d\TH:i:s");
        $collection = Mage::getModel('sales/order')->getCollection()
            ->addAttributeToFilter('created_at', array('from' => $date, 'to' => date('Y-m-d H:i:s')));
        foreach ($collection as $order) {
            $this->magentoPaymentList[] = $order->getId();
        }
    }

    /**
     * Build a array with RakutenPayTransaction where status is refundable
     */
    private function requestTransactionsToRefund()
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing requestTransactionsToRefund in HelperRefund.');
        if (!empty($this->magentoPaymentList)) {
            foreach ($this->magentoPaymentList as $orderId) {
                $orderHandler = Mage::getModel('sales/order')->load($orderId);
                if (Mage::getStoreConfig('payment/rakutenpay/environment') == strtolower(trim($this->getOrderEnvironment($orderId)))
                ) {
                    if (!is_null(Mage::getSingleton('core/session')->getData("store_id"))) {
                        if (Mage::getSingleton('core/session')->getData("store_id") == $orderHandler->getStoreId()) {
                            if ($orderHandler->getStatus() == \Rakuten\Connector\Enum\DirectPayment\Status::APPROVED) {
                                $RakutenPaySummaryItem = $this->findRakutenPayTransactionByReference(
                                        $orderHandler->getEntityId()
                                );
                                if (!is_null($RakutenPaySummaryItem)) {
                                    $this->arrayPayments[] = $this->build($RakutenPaySummaryItem, $orderHandler);
                                }
                            }
                        }
                    } elseif ($orderHandler) {
                        if ($orderHandler->getStatus() == \Rakuten\Connector\Enum\DirectPayment\Status::APPROVED) {
                            $RakutenPaySummaryItem = $this->findRakutenPayTransactionByReference(
                                    $orderHandler->getEntityId()
                            );
                            if (!is_null($RakutenPaySummaryItem)) {
                                $this->arrayPayments[] = $this->build($RakutenPaySummaryItem, $orderHandler);
                            }
                        }
                    }
                }
            }
        }
        Mage::getSingleton('core/session')->unsetData('store_id');
    }

    /**
     * Get order environment
     *
     * @param int $orderId
     *
     * @return string Order environment
     */
    private function getOrderEnvironment($orderId)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing getOrderEnvironment in HelperRefund.');
        $reader = Mage::getSingleton("core/resource")->getConnection('core_read');
        $table = Mage::getConfig()->getTablePrefix().'rakutenpay_orders';
        $query = "SELECT environment FROM ".$table." WHERE order_id = ".$orderId;
        if ($reader->fetchOne($query) == 'Produção') {
            return "production";
        } else {
            return $reader->fetchOne($query);
        }
    }

    /**
     * Find a RakutenPayTransaction by referece
     *
     * @param $orderId
     *
     * @return mixed
     */
    private function findRakutenPayTransactionByReference($orderId)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing findRakutenPayTransactionByReference in HelperRefund.');
        foreach ($this->RakutenPayPaymentList->getTransactions() as $list) {
            if ($this->getReferenceDecryptOrderID($list->getReference()) == $orderId) {
                return $list;
            }
        }
    }

    public function build($RakutenPaySummaryItem, $order)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing build in HelperRefund.');
        $RakutenPayStatusValue = $this->getPaymentStatusFromKey($RakutenPaySummaryItem->getStatus());
        $config = "class='action' data-config='" . $order->getId() . '/'
                . $RakutenPaySummaryItem->getCode() . '/'
                . $this->getPaymentStatusFromKey($RakutenPaySummaryItem->getStatus()) . '/'
                . $RakutenPaySummaryItem->getNetAmount() . '/'
                . $RakutenPaySummaryItem->getPaymentMethod()->getType() . '/'
                . $RakutenPaySummaryItem->getPaymentId() . "'";
        $actionOrder = "<a class='edit' target='_blank' href='".$this->getEditOrderUrl($order->getId())."'>";
        $actionOrder .= $this->__('Ver detalhes')."</a>";
        $actionOrder .= "<a ".$config." href='javascript:void(0)'>";
        $actionOrder .= $this->__('Estornar')."</a>";

        return array(
            'date'           => $this->getOrderMagetoDateConvert($order->getCreatedAt()),
            'id_magento'     => $order->getIncrementId(),
            'id_rakutenpay'  => $RakutenPaySummaryItem->getCode(),
            'status_magento' => $this->getPaymentStatusToString($this->getPaymentStatusFromValue($order->getStatus())),
            'action'         => $actionOrder,
        );
    }
}
