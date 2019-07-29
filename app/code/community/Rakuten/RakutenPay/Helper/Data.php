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

class Rakuten_RakutenPay_Helper_Data extends Mage_Payment_Helper_Data
{
    /**
     *
     */
    const REFUND_CLASS = "Rakuten_RakutenPay_Helper_Refund";
    /**
     *
     */
    const CANCELED_CLASS = "Rakuten_RakutenPay_Helper_Canceled";
    /**
     *
     */
    const TABLE_NAME = "rakutenpay_orders";
    /**
     * @var array
     */
    protected $arrayPayments = array();
    /**
     * @var array
     */
    private $arrayPaymentStateList = array(
        \Rakuten\Connector\Enum\DirectPayment\State::PENDING => Mage_Sales_Model_Order::STATE_NEW,
        \Rakuten\Connector\Enum\DirectPayment\State::AUTHORIZED => Mage_Sales_Model_Order::STATE_NEW,
        \Rakuten\Connector\Enum\DirectPayment\State::APPROVED => Mage_Sales_Model_Order::STATE_PROCESSING,
        \Rakuten\Connector\Enum\DirectPayment\State::COMPLETED => Mage_Sales_Model_Order::STATE_COMPLETE,
        \Rakuten\Connector\Enum\DirectPayment\State::CHARGEBACK => Mage_Sales_Model_Order::STATE_CANCELED,
        \Rakuten\Connector\Enum\DirectPayment\State::CANCELLED => Mage_Sales_Model_Order::STATE_CANCELED,
        \Rakuten\Connector\Enum\DirectPayment\State::REFUNDED => Mage_Sales_Model_Order::STATE_CLOSED,
        \Rakuten\Connector\Enum\DirectPayment\State::PARTIAL_REFUNDED => Mage_Sales_Model_Order::STATE_PROCESSING,
    );

    /**
     * Rakuten_RakutenPay_Helper_Data constructor.
     */
    public function __construct()
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing __construct in HelperData.');
        $this->environmentNotification();
    }

    /**
     *
     */
    private function environmentNotification()
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing environmentNotification.');
        \Rakuten\Connector\Resources\Log\Environment::logInfoVersions();
        $configuration = (int) \Mage::getConfig()->getNode('default/log/configuration/active');
        if ($configuration == 1) {
            \Rakuten\Connector\Resources\Log\Environment::logInfoPHPConfiguration();
        }
        $environment = Mage::getStoreConfig('payment/rakutenpay/environment');
        //Define table name with their prefix
        $tp = (string)Mage::getConfig()->getTablePrefix();
        $table = $tp.'adminnotification_inbox';
        $sql = "SELECT notification_id  FROM `".$table."` WHERE title LIKE '%[Rakuten_RakutenPay]%'";
        $readConnection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $results = $readConnection->fetchOne($sql);
        //Verify the environment
        if ($environment == "sandbox") {
            if (empty($results)) {
                $this->insertEnvironmentNotice($table);
                Mage::app()->getResponse()->setRedirect(Mage::helper('core/url')->getCurrentUrl());
            } elseif ($results != $this->getEnvironmentIncrement($table)) {
                $this->removeEnvironmentNotice($table, $results);
                $this->insertEnvironmentNotice($table);
                Mage::app()->getResponse()->setRedirect(Mage::helper('core/url')->getCurrentUrl());
            }
        } elseif ($environment == 'production') {
            if ($results) {
                $this->removeEnvironmentNotice($table, $results);
                Mage::app()->getResponse()->setRedirect(Mage::helper('core/url')->getCurrentUrl());
            }
        }
    }

    /**
     * @param $table
     */
    private function insertEnvironmentNotice($table)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing insertEnvironmentNotice.');
        // force default time zone
        Mage::app()->getLocale()->date();
        $date = date("Y-m-d H:i:s");
        $title = $this->__("[Rakuten_RakutenPay] Suas transações serão feitas em um ambiente de testes.");
        $description = $this->__("Nenhuma das transações realizadas nesse ambiente tem valor monetário.");
        $sql = "INSERT INTO `".$table."` (severity, date_added, title, description, is_read, is_remove)
                VALUES (4, '$date', '$title', '$description', 0, 0)";
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $connection->query($sql);
        unset($connection);
    }

    /**
     * @param $table
     *
     * @return mixed
     */
    private function getEnvironmentIncrement($table)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing getEnvironmentIncrement.');
        $sql = "SELECT MAX(notification_id) AS 'max_id' FROM `".$table."`";
        $readConnection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $results = $readConnection->fetchAll($sql);

        return $results[0]['max_id'];
    }

    /**
     * @param $table
     * @param $id
     */
    private function removeEnvironmentNotice($table, $id)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing removeEnvironmentNotice.');
        $sql = "DELETE FROM `".$table."` WHERE notification_id = ".$id;
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $connection->query($sql);
        unset($connection);
    }

    /**
     * @throws Exception
     */
    final public function checkCredentials()
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing checkCredentials.');
        $date = new DateTime ("now");
        $date->setTimezone(new DateTimeZone ("America/Sao_Paulo"));
        $date->sub(new DateInterval ('P1D'));
        $date->setTime(00, 00, 00);
        $date = $date->format("Y-m-d\TH:i:s");
        $useCache = Mage::app()->useCache();
        if ($useCache['config']) {
            Mage::app()->getCacheInstance()->flush();
        }
        try {
                $this->webserviceHelper()->getTransactionsByDate(1, 1, $date);
            Mage::getConfig()->saveConfig('rakuten_rakutenpay/store/credentials', 1);
        } catch (\Rakuten\Connector\Exception\ConnectorException $e) {
            Mage::getConfig()->saveConfig('rakuten_rakutenpay/store/credentials', 0);
            throw new \Rakuten\Connector\Exception\ConnectorException($e->getMessage());
        }
    }

    /**
     * @return Mage_Core_Helper_Abstract
     */
    final public function webserviceHelper()
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing webserviceHelper.');
        return Mage::helper('rakutenpay/webservice');
    }

    /**
     * @return false|Mage_Core_Model_Abstract
     */
    final public function paymentModel()
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing paymentModel.');
        return Mage::getModel('Rakuten_RakutenPay_Model_PaymentMethod');
    }

    /**
     * @param $phone
     *
     * @return array
     */
    public function formatPhone($phone)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing formatPhone.');
        $phone = preg_replace('/[^0-9]/', '', $phone);
        $ddd = '';
        if (strlen($phone) > 9) {
            if (substr($phone, 0, 1) == 0) {
                $phone = substr($phone, 1);
            }
            $ddd = substr($phone, 0, 2);
            $phone = substr($phone, 2);
        }

        return array('areaCode' => $ddd, 'number' => $phone);
    }

    /**
     * @param $key
     *
     * @return bool|mixed
     */
    public function getPaymentStateFromKey($key)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing getPaymentStateFromKey.');
        if (array_key_exists($key, $this->arrayPaymentStateList)) {
            return $this->arrayPaymentStateList[$key];
        }

        return false;
    }

    /**
     * @param $value
     *
     * @return bool|int
     */
    public function getPaymentStateFromValue($value)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing getPaymentStateFromValue.');
        $key = array_search($value, $this->arrayPaymentStateList);
        return $key;
    }

    /**
     * @param $key
     *
     * @return bool|string
     */
    public function getPaymentStateToString($key)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing getPaymentStateToString.');
        if (array_key_exists($key, $this->arrayPaymentStateList)) {
            switch ($this->arrayPaymentStateList[$key]) {
                case 'pending':
                    return $this->__('Pendente');
                case Mage_Sales_Model_Order::STATE_PENDING_PAYMENT:
                    return $this->__('Pagamento Pendente');
                case Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW:
                    return $this->__('Análise de Pagamento');
                case Mage_Sales_Model_Order::STATE_PROCESSING:
                    return $this->__('Processando');
                case Mage_Sales_Model_Order::STATE_CANCELED:
                    return $this->__('Cancelado');
                case Mage_Sales_Model_Order::STATE_COMPLETE:
                    return $this->__('Completo');
                case Mage_Sales_Model_Order::STATE_CLOSED:
                    return $this->__('Fechado');
            }
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing getVersion.');
        return Mage::getConfig()->getModuleConfig("Rakuten_RakutenPay")->version;
    }

    /**
     * @return Mage_Core_Model_Abstract
     */
    public function installmentsModel()
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing installmentsModel.');
        return Mage::getSingleton('Rakuten_RakutenPay_Model_InstallmentsMethod');
    }

    /**
     * @return Mage_Core_Model_Abstract
     */
    final public function notificationModel()
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing notificationModel.');
        return Mage::getSingleton('Rakuten_RakutenPay_Model_NotificationMethod');
    }


    public function updateOrderStateMagento($class, $incrementId, $transactionCode, $orderState, $amount = false, $approvedDate = false)
    {
        try {
            $orderId = $this->getOrderId($incrementId);

            \Rakuten\Connector\Resources\Log\Logger::info(
                "Updating order with orderId: " . $orderId .
                "; State: "                    . $orderState .
                "; Amount: "                    . $amount .
                "; transactionCode: "           . $transactionCode,
                ['service' => 'WEBHOOK']);

            if ($this->getLastStateOrder($orderId) != $orderState) {
                \Rakuten\Connector\Resources\Log\Logger::info(
                    "Order state has changed, so we notify the customer.",
                    ['service' => 'WEBHOOK']
                );
                $this
                    ->notifyCustomer($orderId, $orderState, $transactionCode, $orderState == 'canceled', $approvedDate);

                Mage::helper('rakutenpay/log')
                ->setUpdateOrderLog($class, $orderId, $transactionCode, $orderState);
                $this->setTransactionRecord($orderId, $transactionCode);
            } else {
                \Rakuten\Connector\Resources\Log\Logger::info(
                    "Order state has not changed.",
                    ['service' => 'WEBHOOK']
                );
            }

            if ($transactionCode == \Rakuten\Connector\Enum\DirectPayment\State::PARTIAL_REFUNDED) {
                $this->updateOrderStatusPartialRefunded($orderId, $orderState, $transactionCode, $amount);
            }

            if ($amount) {
                \Rakuten\Connector\Resources\Log\Logger::info("Amount was updated, so we update the amount.", ['service' => 'WEBHOOK']);
                $this->setOrderPaymentValue($orderId, $amount);
            } else {
                \Rakuten\Connector\Resources\Log\Logger::info("Amount has not changed.", ['service' => 'WEBHOOK']);
            }
        } catch (\Rakuten\Connector\Exception\ConnectorException $pse) {
            \Rakuten\Connector\Resources\Log\Logger::error("Exception: " . var_export($pse, true), ['service' => 'WEBHOOK']);
            throw $pse;
        }
    }

    public function updateOrderStatusMagentoRefund($orderId, $transactionCode, $orderStatus, $amount,
            $kind = 'total', $reason = 'merchant_other', $bankData = null, $paymentId = null)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing updateOrderStatusMagentoRefund.');
        try {
            $order = Mage::getModel('sales/order')->load($orderId);
            $paymentMethod = $order->getPayment()->getMethod();
            if ($paymentMethod === 'rakutenpay_boleto' && $bankData == null) {
                Mage::throwException(Mage::helper('adminhtml')->__('Use o menu de estorno do RakutenPay para estornos de boleto'));
            }

            $result =
                $this
                ->webserviceHelper()
                ->refundRequest(
                    $transactionCode,
                    $amount,
                    $kind,
                    $reason,
                    $bankData,
                    $paymentId)
                ->getResult();

            if ($result['result'] === 'failure') {
                Mage::throwException(Mage::helper('adminhtml')->__('Ocorreu uma falha na tentativa de criar o estorno.'));
            }

            $this->setTransactionRecord($orderId, $transactionCode);
        } catch (\Rakuten\Connector\Exception\ConnectorException $pse) {
            throw $pse;
        }
    }

    public function updateOrderStatusMagentoCancel($orderId, $transactionCode, $orderStatus) {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing updateOrderStatusMagentoCancel.', ['service' => 'Cancel']);
        try {
            $result = \Rakuten\Connector\Services\Transactions\Cancel::create($transactionCode)
            ->getResult();

            if ($result['result'] === 'failure') {
                \Mage::throwException(Mage::helper('adminhtml')->__('Ocorreu uma falha na tentativa de cancelar o pedido de boleto.'));
            }

            $this->setTransactionRecord($orderId, $transactionCode);
        } catch (\Rakuten\Connector\Exception\ConnectorException $e) {
            \Rakuten\Connector\Resources\Log\Logger::error(sprintf('Error for cancel Order: %s - Status: %s', $orderId, $orderStatus), ['service' => 'Cancel']);
            throw $e;
        }
    }

    /**
     * @param $orderId
     * @param $orderState
     * @param $transactionCode
     * @param $amount
     * @throws Exception
     */
    protected function updateOrderStatusPartialRefunded($orderId, $orderState, $transactionCode, $amount)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing updateOrderStatusPartialRefunded.', ['service' => 'WEBHOOK']);
        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->load($orderId);
        if ($amount != (float) $order->getTotalRefunded()) {

            $amountRefunded = abs($amount);
            $comment = "Estorno Parcial no RakutenPay - Valor: R$ " . number_format($amountRefunded, 2, ',', '.');
            if (!is_null($order->getTotalRefunded() || !empty($order->getTotalRefunded()))) {
                $totalRefunded = abs($order->getTotalRefunded());
                $amountRefunded = (float) $amountRefunded - $totalRefunded;
                $comment = "Estorno Parcial no RakutenPay no Valor: R$ "
                    . number_format($amountRefunded, 2, ',', '.') .
                    " - Estorno anterior: R$ " .
                    number_format($totalRefunded, 2, ',', '.');
            }
            \Rakuten\Connector\Resources\Log\Logger::info($comment, ['service' => 'WEBHOOK']);
            $history = $order->addStatusHistoryComment($comment, $orderState);
            $history->setIsCustomerNotified(true);
            $order->sendOrderUpdateEmail(true, $comment);
            $order->save();
            $this->setTransactionRecord($orderId, $transactionCode);
        }
    }

    /**
     * @param $orderId
     *
     * @return mixed
     */
    protected function getLastStateOrder($orderId)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing getLastStateOrder.');
        $order = Mage::getModel('sales/order')->load($orderId);

        return $order->getState();
    }

    protected function setOrderPaymentValue($orderId, $amount)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing setOrderPaymentValue.');
        $order = Mage::getModel('sales/order')->load($orderId);
        if ($amount > 0) {
            $order->setTotalPaid($amount);
        } elseif ($amount < 0) {
            $order->setTotalRefunded($amount);
        }
        $order->save();
    }

    /**
     * @param $orderId
     * @param $orderState
     * @param $transactionCode
     * @param $amount
     * @param bool $cancel
     * @param string $approvedDate
     * @throws Exception
     */
    private function notifyCustomer($orderId, $orderState, $transactionCode, $cancel, $approvedDate)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing notifyCustomer.');
        if ($cancel) {
            $order = Mage::getModel('sales/order')->load($orderId);
            $order->cancel();
            $order->save();
        }
        $status = $orderState;
        $comment = null;
        $notify = true;
        if ($transactionCode == \Rakuten\Connector\Enum\DirectPayment\State::REFUNDED) {
            $comment = "Estorno total do pedido no RakutenPay";
        }

        /** @var $order Mage_Sales_Model_Order */
        $order = Mage::getModel('sales/order')->load($orderId);

        $history = $order->addStatusHistoryComment($comment, $status);
        $history->setIsCustomerNotified($notify);

        /** @see https://github.com/r-martins/PagSeguro-Magento-Transparente/pull/180  setData() */
        $order->setData('state', $orderState);
        $order->sendOrderUpdateEmail($notify, $comment);
        // Makes the notification of the order of historic displays the correct date and time
        Mage::app()->getLocale()->date();
        $order->save();

        if ($orderState == Mage_Sales_Model_Order::STATE_PROCESSING) {
            $this->createInvoice($order);
        }

        if ($approvedDate !== false) {
            \Rakuten\Connector\Resources\Log\Logger::info("Setting Date for Approved Status.");
            $payment = $order->getPayment();
            $payment
                ->setAdditionalInformation('approved_date', $approvedDate)
                ->save();
        }
    }

    /**
     * @param $orderId
     * @param bool $transactionCode
     * @param bool $chargeId
     * @param bool $incrementId
     */
    final public function setTransactionRecord($orderId, $transactionCode = null, $chargeId = null, $incrementId = null)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing setTransactionRecord. With: ' . $transactionCode);
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('core_read');
        $write = $resource->getConnection('core_write');
        $table = $resource->getTableName(self::TABLE_NAME);
        $fields = [];

        $select = $read->select()
            ->from(['rakutenOrder' => $table])
            ->where('rakutenOrder.order_id = ?', $orderId);
        $result = $read->fetchAll($select);
        if (count($result)) {
            $fields['transaction_code'] = $transactionCode;
            if ($chargeId != null) {
                $fields['charge_uuid'] = $chargeId;
            }
            $where = $write->quoteInto('order_id = ? ', $orderId);
            $write->update($table, $fields, $where);
        } else {
            $environment = ucfirst(Mage::getStoreConfig('payment/rakutenpay/environment'));
            $write->insert(
                $table, [
                    'order_id' => $orderId,
                    'charge_uuid' => $chargeId,
                    'increment_id' => $incrementId,
                    'transaction_code' => $transactionCode,
                    'environment' => $environment,
                ]
            );
        }
    }

    public function getRakutenPayDirectPaymentJs()
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing getRakutenPayDirectPaymentJs.');
         if (Mage::getStoreConfig('payment/rakutenpay/environment') === 'production') {
            return 'https://static.rakutenpay.com.br/rpayjs/rpay-latest.min.js';
        }

        return 'https://static.rakutenpay.com.br/rpayjs/rpay-latest.dev.min.js';
    }

    /**
     * @param $incrementId
     * @return mixed
     */
    protected function getOrderId($incrementId)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing getOrderId.');
        $order = Mage::getModel('sales/order')->loadByIncrementId($incrementId);
        $orderId = $order->getId();
        \Rakuten\Connector\Resources\Log\Logger::info(sprintf('incrementId: %s | orderId: %s', $incrementId, $orderId));

        return $orderId;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return bool
     * @throws Exception
     */
    protected function createInvoice(Mage_Sales_Model_Order $order)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing createInvoice.', ['service' => 'Invoice']);
        try {
            if (!$order->canInvoice()) {
                \Rakuten\Connector\Resources\Log\Logger::info('Order cannot be invoiced.', ['service' => 'Invoice']);
                $order->addStatusHistoryComment('O pedido não pode ser faturado.', false);
                $order->save();

                return false;
            }

            \Rakuten\Connector\Resources\Log\Logger::info('Generate Invoice.', ['service' => 'Invoice']);
            $order->getPayment()->setSkipTransactionCreation(false);
            $invoice = $order->prepareInvoice();
            $invoice->getOrder()->setCustomerNoteNotify(true);
            $invoice->getOrder()->setIsInProcess(true);
            $invoice->sendEmail(true, '');
            $history = $order->addStatusHistoryComment('Fatura gerada pelo RakutenPay no Status Processing.', false);
            $history->setIsCustomerNotified(true);
            $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
            $invoice->register();

            Mage::getModel('core/resource_transaction')
                ->addObject($invoice)
                ->addObject($order)
                ->save();

            return true;
        } catch (\Rakuten\Connector\Exception\ConnectorException $e) {
            \Rakuten\Connector\Resources\Log\Logger::error(sprintf('Exception createInvoice: %s', $e->getMessage()), ['service' => 'Invoice']);
            $order->addStatusHistoryComment('Invoice: Exception occurred during createInvoice. Exception message: '.$e->getMessage(), false);
            $order->save();
        }
    }
}
