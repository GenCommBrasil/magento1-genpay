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
    * Performs a function that checks if the credentials are correct.
    */
    public function adminSystemConfigPaymentSave()
    {
        /** @var Rakuten_RakutenPay_Helper_Credential $credential */
        $credential = Mage::helper('rakutenpay/credential');
        if (!Mage::getStoreConfig("payment/rakutenpay/init")) {
            Mage::getConfig()->saveConfig('payment/rakutenpay/init', 1);
        }

        if ($credential->getEmail() && $credential->getDocument()
            && $credential->getApiKey() && $credential->getSignatureKey()) {
            Mage::helper('rakutenpay')->checkCredentials();
        } else {
            throw new Exception("Certifique-se de que o e-mail e token foram preenchidos.");
        }
    }

    public function adminOrderAfterSave($observer)
    {
        \Rakuten\Connector\Configuration\Configure::setEnvironment(Mage::getStoreConfig('payment/rakutenpay/environment'));
        $order = $observer->getEvent()->getOrder();

        if (!$order->getId()) {
            //order not saved in the database
            return $this;
        }

        $paymentMethod = $order->getPayment()->getMethod();

        if ($paymentMethod === 'rakutenpay_boleto' || $paymentMethod === 'rakutenpay_credit_card'){
            /* @var $order Mage_Sales_Model_Order */

            \Rakuten\Connector\Resources\Log\Logger::info('Processing admin orderAfterSave');

            $oldStatus = $order->getOrigData('status');
            $newStatus = $order->getStatus();

            \Rakuten\Connector\Resources\Log\Logger::info(sprintf('OldStatus: %s', $oldStatus));
            \Rakuten\Connector\Resources\Log\Logger::info(sprintf('NewStatus: %s', $newStatus));

            if ($oldStatus != $newStatus) {
                $magentoCancelStatus = array('canceled', 'closed');

                if (in_array($newStatus, $magentoCancelStatus)) {
                    if ($paymentMethod === 'rakutenpay_boleto' && $oldStatus === 'pending') {
                        $cancel =
                            Mage::helper('rakutenpay')
                            ->updateOrderStatusMagentoCancel(
                                $order->getId(),
                                $order->getPayment()->getAdditionalInformation('rakutenpay_id'),
                                $newStatus);
                    } else {
                        $cancel =
                            Mage::helper('rakutenpay')
                            ->updateOrderStatusMagentoRefund(
                                $order->getId(),
                                $order->getPayment()->getAdditionalInformation('rakutenpay_id'),
                                $newStatus,
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

    public function notificationChangeStatus(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        \Rakuten\Connector\Resources\Log\Logger::info("State: " . $order->getState() . " - Status: " . $order->getStatus(), ['service' => 'Change.Status']);
    }
}
