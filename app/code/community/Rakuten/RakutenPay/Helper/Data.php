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
    private $arrayPaymentStatusList = array(
        "pending" => "pending",
        "authorized" => "payment_review",
        "approved" => "processing",
        "completed" => "complete",
        "chargeback" => "canceled",
        "cancelled" => "canceled",
        "refunded" => "closed",
        "partial_refunded" => "closed"
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
     *
     */
    public function checkTransactionAccess()
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing checkTransactionAccess.');
        $module = 'RakutenPay - ';
        $configUrl = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/system_config/edit/section/payment/');
        $email = $this->paymentModel()->getConfigData('email');
        $token = $this->paymentModel()->getConfigData('token');
        if ($email) {
            if (!$token) {
                $message = $module.$this->__('Preencha o token.');
                Mage::getSingleton('core/session')->addError($message);
                Mage::app()->getResponse()->setRedirect($configUrl);
            }
        } else {
            $message = $module.$this->__('Preencha o e-mail do vendedor.');
            Mage::getSingleton('core/session')->addError($message);
            if (!$token) {
                $message = $module.$this->__('Preencha o token.');
                Mage::getSingleton('core/session')->addError($message);
            }
            Mage::app()->getResponse()->setRedirect($configUrl);
        }
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
     * @param $paymentRequest
     *
     * @return mixed
     */
    public function getDiscount($paymentRequest)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing getDiscount.');
        $storeId = Mage::app()->getStore()->getStoreId();
        if (Mage::getStoreConfig('payment/rakutenpay/discount_credit_card', $storeId) == 1) {
            $creditCard = (double)Mage::getStoreConfig('payment/rakutenpay/discount_credit_card_value', $storeId);
            if ($creditCard && $creditCard != 0.00) {
                $paymentRequest->addPaymentMethodConfig('CREDIT_CARD', $creditCard, 'DISCOUNT_PERCENT');
            }
        }
        if (Mage::getStoreConfig('payment/rakutenpay/discount_electronic_debit', $storeId) == 1) {
            $eft = (double)Mage::getStoreConfig('payment/rakutenpay/discount_electronic_debit_value', $storeId);
            if ($eft && $eft != 0.00) {
                $paymentRequest->addPaymentMethodConfig('EFT', $eft, 'DISCOUNT_PERCENT');
            }
        }
        if (Mage::getStoreConfig('payment/rakutenpay/discount_boleto', $storeId) == 1) {
            $boleto = (double)Mage::getStoreConfig('payment/rakutenpay/discount_boleto_value', $storeId);
            if ($boleto && $boleto != 0.00) {
                $paymentRequest->addPaymentMethodConfig('BOLETO', $boleto, 'DISCOUNT_PERCENT');
            }
        }
        if (Mage::getStoreConfig('payment/rakutenpay/discount_deposit_account', $storeId)) {
            $deposit = (double)Mage::getStoreConfig('payment/rakutenpay/discount_deposit_account_value', $storeId);
            if ($deposit && $deposit != 0.00) {
                $paymentRequest->addPaymentMethodConfig('DEPOSIT', $deposit, 'DISCOUNT_PERCENT');
            }
        }
        if (Mage::getStoreConfig('payment/rakutenpay/discount_balance', $storeId)) {
            $balance = (double)Mage::getStoreConfig('payment/rakutenpay/discount_balance_value', $storeId);
            if ($balance && $balance != 0.00) {
                $paymentRequest->addPaymentMethodConfig('BALANCE', $balance, 'DISCOUNT_PERCENT');
            }
        }

        return $paymentRequest;
    }

    /**
     * @param $idOrder
     *
     * @return mixed
     */
    public function getEditOrderUrl($idOrder)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing getEditOrderUrl.');
        $adminhtmlUrl = Mage::getSingleton('adminhtml/url');
        $url = $adminhtmlUrl->getUrl('adminhtml/sales_order/view', array('order_id' => $idOrder));

        return $url;
    }

    /**
     * @param $key
     *
     * @return bool|mixed
     */
    public function getPaymentStatusFromKey($key)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing getPaymentStatusFromKey.');
        if (array_key_exists($key, $this->arrayPaymentStatusList)) {
            return $this->arrayPaymentStatusList[$key];
        }

        return false;
    }

    /**
     * @param $value
     *
     * @return bool|int
     */
    public function getPaymentStatusFromValue($value)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing getPaymentStatusFromValue.');
        $key = array_search($value, $this->arrayPaymentStatusList);
        return $key;
    }

    /**
     * @param $key
     *
     * @return bool|string
     */
    public function getPaymentStatusToString($key)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing getPaymentStatusToString.');
        if (array_key_exists($key, $this->arrayPaymentStatusList)) {
            switch ($this->arrayPaymentStatusList[$key]) {
                case 'pending':
                    return $this->__('Pendente');
                case 'payment_review':
                    return $this->__('Análise de Pagamento');
                case 'processing':
                    return $this->__('Processando');
                case 'canceled':
                    return $this->__('Cancelado');
                case 'complete':
                    return $this->__('Completo');
                case 'closed':
                    return $this->__('Fechado');
            }
        }

        return false;
    }

    /**
     * @param $reference
     *
     * @return bool|string
     */
    public function getReferenceDecrypt($reference)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing getReferenceDecrypt.');
        return '';
    }

    /**
     * @param $reference
     *
     * @return mixed
     */
    public function getReferenceDecryptOrderID($reference)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing getReferenceDecryptOrderID.');
        return $reference;
    }

    /**
     * @return mixed
     */
    public function getStoreReference()
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing getStoreReference.');
        return Mage::getStoreConfig('rakuten_rakutenpay/store/reference');
    }

    /**
     * @param $array
     *
     * @return string
     */
    public function getTransactionGrid($array)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing getTransactionGrid.');
        $dataSet = '[';
        $j = 1;
        foreach ($array as $info) {
            $i = 1;
            $dataSet .= ($j > 1) ? ',[' : '[';
            foreach ($info as $item) {
                $dataSet .= (count($info) != $i) ? '"'.$item.'",' : '"'.$item.'"';
                $i++;
            }
            $dataSet .= ']';
            $j++;
        }
        $dataSet .= ']';

        return $dataSet;
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


    public function updateOrderStatusMagento($class, $incrementId, $transactionCode, $orderStatus, $amount = false)
    {
        try {
            $orderId = $this->getOrderId($incrementId);

            \Rakuten\Connector\Resources\Log\Logger::info(
                "Updating order with orderId: " . $orderId .
                "; Status: "                    . $orderStatus .
                "; Amount: "                    . $amount .
                "; transactionCode: "           . $transactionCode,
                ['service' => 'WEBHOOK']);

            if ($this->getLastStatusOrder($orderId) != $orderStatus) {
                \Rakuten\Connector\Resources\Log\Logger::info(
                    "Order status has changed, so we notify the customer.",
                    ['service' => 'WEBHOOK']
                );
                $this
                    ->notifyCustomer($orderId, $orderStatus, $orderStatus == 'canceled');

                Mage::helper('rakutenpay/log')
                ->setUpdateOrderLog($class, $orderId, $transactionCode, $orderStatus);
            } else {
                \Rakuten\Connector\Resources\Log\Logger::info(
                    "Order status has not changed.",
                    ['service' => 'WEBHOOK']
                );
            }

            if ($amount) {
                \Rakuten\Connector\Resources\Log\Logger::info("Amount was updated, so we update the amount.", ['service' => 'WEBHOOK']);
                $this->setOrderPaymentValue($orderId, $amount);
            } else {
                \Rakuten\Connector\Resources\Log\Logger::info("Amount has not changed.", ['service' => 'WEBHOOK']);
            }

            $this->setTransactionRecord($orderId, $transactionCode, false, $amount);
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
        \Rakuten\Connector\Resources\Log\Logger::info('Processing updateOrderStatusMagentoCancel.');
        try {
            \Rakuten\Connector\Services\Transactions\Cancel::create($transactionCode)
            ->getResult();

            if ($result['result'] === 'failure') {
                Mage::throwException(Mage::helper('adminhtml')->__('Ocorreu uma falha na tentativa de cancelar o pedido de boleto.'));
            }

            $this->setTransactionRecord($orderId, $transactionCode);
        } catch (\Rakuten\Connector\Exception\ConnectorException $pse) {
            throw $pse;
        }
    }

    /**
     * @param $orderId
     *
     * @return mixed
     */
    protected function getLastStatusOrder($orderId)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing getLastStatusOrder.');
        $order = Mage::getModel('sales/order')->load($orderId);

        return $order->getStatus();
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
     * @param $orderStatus
     */
    private function notifyCustomer($orderId, $orderStatus, $cancel = false)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing notifyCustomer.');
        if ($cancel) {
            $order = Mage::getModel('sales/order')->load($orderId);
            $order->cancel();
            $order->save();
        }
        $status = $orderStatus;
        $comment = null;
        $notify = true;
        $order = Mage::getModel('sales/order')->load($orderId);
        $order->addStatusToHistory($status, $comment, $notify);
        $order->sendOrderUpdateEmail($notify, $comment);
        // Makes the notification of the order of historic displays the correct date and time
        Mage::app()->getLocale()->date();
        $order->save();
    }

    /**
     * @param      $orderId
     * @param bool $transactionCode
     * @param bool $send
     */
    final public function setTransactionRecord($orderId, $transactionCode = false, $send = false)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing setTransactionRecord.');
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $writeConnection = $resource->getConnection('core_write');
        $table = $resource->getTableName(self::TABLE_NAME);
        //Select sent column from rakutenpay_orders to verify if exists a register
        $query = "SELECT `order_id`, `sent` FROM `$table` WHERE `order_id` = $orderId";
        $result = $readConnection->fetchAll($query);
        if (!empty($result)) {
            if ($send == true) {
                $sent = $result[0]['sent'] + 1;
                $value = "sent = '".$sent."'";
            } else {
                $value = "transaction_code = '".$transactionCode."'";
            }
            $sql = "UPDATE `".$table."` SET ".$value." WHERE order_id = ".$orderId;
        } else {
            $environment = ucfirst(Mage::getStoreConfig('payment/rakutenpay/environment'));
            if ($send == true) {
                $column = " (`order_id`, `sent`, `environment`) ";
                $values = " (`$orderId`, 1, `$environment`) ";
            } else {
                $column = " (order_id, transaction_code, environment) ";
                $values = " (`$orderId', `$transactionCode`, `$environment`) ";
            }
            $sql = "INSERT INTO $table $column VALUES $values";
        }
        $writeConnection->query($sql);
    }

    /**
     * @param $action
     *
     * @return string
     */
    protected function alertConciliation($action)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing alertConciliation.');
        $message = $this->__('Não foi possível executar esta ação. Utilize a conciliação de transações primeiro');
        $message .= $this->__(' ou tente novamente mais tarde.');

        return $message;
    }

    /**
     * @param $date
     *
     * @return false|string
     */
    protected function getOrderMagetoDateConvert($date)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing getOrderMagetoDateConvert.');
        return date("d/m/Y H:i:s", Mage::getModel("core/date")->timestamp($date));
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
     * Format original document and return it as an array, with it "washed" value
     * and type
     * @param string $document
     * @return array
     * @throws Exception
     */
    public function formatDocument($document)
    {
       \Rakuten\Connector\Resources\Log\Logger::info('Processing formatDocument.');
       $documentNumbers = preg_replace('/[^0-9]/', '', $document);
       switch (strlen($documentNumbers)) {
            case 14:
                return ['number' => $documentNumbers, 'type' => 'CNPJ'];
                break;
            case 11:
                return ['number' => $documentNumbers, 'type' => 'CPF'];
                break;
            default:
                throw new \Rakuten\Connector\Exception\ConnectorException('Invalid document');
                break;
        }
    }

    protected function getOrderId($incrementId)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing getOrderId.');
        $order = Mage::getModel('sales/order')->loadByIncrementId($incrementId);
        $orderId = $order->getId();
        \Rakuten\Connector\Resources\Log\Logger::info(sprintf('incrementId: %s | orderId: %s', $incrementId, $orderId));

        return $orderId;
    }
}
