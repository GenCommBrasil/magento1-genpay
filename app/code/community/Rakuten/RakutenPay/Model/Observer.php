<?php
/**
 ************************************************************************
 * Copyright [2017] [RakutenPay]
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

class Rakuten_RakutenPay_Model_Observer
{

    protected $libPath;

    /**
     * Rakuten_RakutenPay_Model_Observer constructor.
     */
    public function __construct()
    {
        $this->libPath = Mage::getBaseDir('lib'). '/Rakuten/Connector/vendor/autoload.php';
    }

    public function addAutoloader()
    {
        include_once($this->libPath);
        return $this;
    }

    /**
     * Query the existing transaction codes with the id of the request and assembles an array with these codes.
     * @param object $observer - It is an object of Event of observe.
     */
    public function salesOrderGridCollectionLoadBefore($observer)
    {
        $collection = $observer->getOrderGridCollection();
        $select = $collection->getSelect();
        $tableCollection = Mage::getSingleton('core/resource')->getTableName('rakutenpay_orders');
        $select->joinLeft(
            array('payment' => $tableCollection),
            'payment.order_id = main_table.entity_id',
            array('payment_code'=>'transaction_code',
            'payment_environment' => 'environment')
        );
    }

    /**
    * Performs a function that checks if the credentials are correct.
    */
    public function adminSystemConfigPaymentSave()
    {
        if (!Mage::getStoreConfig("payment/rakutenpay/init")) {
            Mage::getConfig()->saveConfig('payment/rakutenpay/init', 1);
        }

        if (Mage::getStoreConfig("payment/rakutenpay/email") && Mage::getStoreConfig("payment/rakutenpay/cnpj")
            && Mage::getStoreConfig("payment/rakutenpay/api_key") && Mage::getStoreConfig("payment/rakutenpay/signature_key")) {
            Mage::helper('rakutenpay')->checkCredentials();
        } else {
            throw new Exception("Certifique-se de que o e-mail e token foram preenchidos.");
        }
    }

    public function adminOrderAfterSave($observer)
    {
        \RakutenConnector\Configuration\Configure::setEnvironment(Mage::getStoreConfig('payment/rakutenpay/environment'));
        $order = $observer->getEvent()->getOrder();

        if (!$order->getId()) {
            //order not saved in the database
            return $this;
        }

        $paymentMethod = $order->getPayment()->getMethod();

        if ($paymentMethod === 'rakutenpay_boleto' || $paymentMethod === 'rakutenpay_credit_card'){
            /* @var $order Mage_Sales_Model_Order */

            \RakutenConnector\Resources\Log\Logger::info('Processing admin orderAfterSave');

            $OldStatus=$order->getOrigData('status');
            $NewStatus=$order->getStatus();

            \RakutenConnector\Resources\Log\Logger::info(sprintf('OldStatus: %s', $OldStatus));
            \RakutenConnector\Resources\Log\Logger::info(sprintf('NewStatus: %s', $NewStatus));

            if($OldStatus!=$NewStatus){
                $magentoCancelStatus = array('chargeback', 'refunded', 'canceled');
                $rpayCancelStatus = array('chargeback_debitado_rp', 'devolvida_rp');

                if(in_array($NewStatus, $magentoCancelStatus) && !in_array($OldStatus, $rpayCancelStatus)){
                    if ($paymentMethod === 'rakutenpay_boleto' && $OldStatus === 'pending') {
                        $cancel =
                            Mage::helper('rakutenpay')
                            ->updateOrderStatusMagentoCancel(
                                $order->getId(),
                                $order->getPayment()->getAdditionalInformation('rakutenpay_id'),
                                $NewStatus);
                    } else {
                        $cancel =
                            Mage::helper('rakutenpay')
                            ->updateOrderStatusMagentoRefund(
                                $order->getId(),
                                $order->getPayment()->getAdditionalInformation('rakutenpay_id'),
                                $NewStatus,
                                (float)$order->getGrandTotal());
                    }
                }
            }
        }

        return $this;
    }

    public function adminFilterPaymentMethod($observer)
    {
        $method_code = $observer->getEvent()->getMethodInstance()->getCode();

        if ($method_code === 'rakutenpay_boleto' || $method_code === 'rakutenpay_credit_card') {
            $result = $observer->getEvent()->getResult();
            $result->isAvailable = false;
        }
    }
}
