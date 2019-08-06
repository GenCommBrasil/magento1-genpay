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
 * @property Mage_Sales_Model_Order order
 */
class Rakuten_RakutenPay_Model_PaymentMethod extends Mage_Payment_Model_Method_Abstract
{
    const RAKUTEN_LOGISTICS_CODE = 'rakuten_rakutenlogistics_';
    const RAKUTEN_LOGISTICS_KIND = 'rakuten_logistics';

    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = false;
    protected $_canRefund = false;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = true;
    protected $_canUseInternal = true;
    protected $_canVoid = true;
    protected $_code = 'rakutenpay_default_lightbox';
    protected $_isGateway = true;
    /**
     * @var Mage_Sales_Model_Order
     */
    protected $order;
    /**
     * @var Rakuten_RakutenPay_Helper_Data
     */
    private $helper;
    /**
     * @var Rakuten_RakutenPay_Model_Library
     */
    private $library;

    protected $_session;

    /**
     * Rakuten_RakutenPay_Model_PaymentMethod constructor.
     */
    public function __construct()
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Constructing PaymentMethod.');
        $this->library = new Rakuten_RakutenPay_Model_Library();
        $this->helper = new Rakuten_RakutenPay_Helper_Data();
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param $chargeId
     * @throws Exception
     */
    public function addRakutenPayOrders(Mage_Sales_Model_Order $order)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing addRakutenPayOrders.');
        $helper = Mage::helper('rakutenlogistics/data');
        $calculationCode = null;
        $environment = $this->library->getEnvironment();

        if ($helper->isRakutenShippingMethod($order->getShippingMethod())) {
            $calculationCode = Mage::getSingleton('core/session')->getCalculationCode();
        }

        $rakutenOrder = Mage::getModel('rakuten_rakutenpay/order')->load($order->getId(), 'order_id');
        $rakutenOrder->setOrderId($order->getId());
        $rakutenOrder->setCalculationCode($calculationCode);
        $rakutenOrder->setEnvironment($environment);
        $rakutenOrder->setIncrementId($order->getIncrementId());
        $rakutenOrder->save();
    }

    /**
     * @param Mage_Sales_Model_Order $order
     */
    public function clearCheckoutSession(Mage_Sales_Model_Order $order)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing clearCheckoutSession.');
        $cart = Mage::getSingleton('checkout/cart');
        foreach (Mage::getSingleton('checkout/session')->getQuote()->getItemsCollection() as $item) {
            $cart->removeItem($item->getId());
        }
        $cart->save();
        $order->save();
    }

    /**
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing getOrderPlaceRedirectUrl.');
        return Mage::getUrl('rakutenpay/payment/request');
    }

    /**
     * Retrieve checkout type from system.xml
     *
     * @return mixed
     */
    public function getPaymentCheckoutType()
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing getPaymentCheckoutType.');
        return $this->library->getPaymentCheckoutType();
    }

    /**
     * @return mixed
     */
    public function getPaymentSession()
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing getPaymentSession.');
        return \Mage::getSingleton('checkout/session');
    }

    /**
     * @return \Rakuten\Connector\Domains\Requests\DirectPayment\Boleto|\Rakuten\Connector\Domains\Requests\DirectPayment\CreditCard
     */
    public function paymentDefault()
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing paymentDefault.');
        $payment = new \Rakuten\Connector\Domains\Requests\Payment();

        return $this->payment($payment);
    }

    /**
     * @param \Rakuten\Connector\Domains\Requests\DirectPayment\Boleto|\Rakuten\Connector\Domains\Requests\DirectPayment\CreditCard|\Rakuten\Connector\Domains\Requests\Payment $payment
     *
     * @return \Rakuten\Connector\Domains\Requests\DirectPayment\Boleto|\Rakuten\Connector\Domains\Requests\DirectPayment\CreditCard
     */
    private function payment($payment)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing payment.');
        $helper = Mage::helper('rakutenlogistics/data');
        $shippingAddress = $this->order->getShippingAddress();
        if ($shippingAddress === false) {
            $shippingAddress = $this->order->getBillingAddress();
        }

        \Rakuten\Connector\Resources\Log\Logger::info('Setting payment info...');
        $payment->setReference($this->order->getIncrementId());
        \Rakuten\Connector\Resources\Log\Logger::info('Reference set.');
        $payment->setCurrency('BRL');
        \Rakuten\Connector\Resources\Log\Logger::info('Currency set.');
        $payment->setTotal($this->order->getGrandTotal());
        \Rakuten\Connector\Resources\Log\Logger::info('Total set.');
        $this->setItems($payment);
        \Rakuten\Connector\Resources\Log\Logger::info('Items set.');
        $payment->setSender()->setName($this->order->getCustomerName());
        \Rakuten\Connector\Resources\Log\Logger::info('Name set.');
        $payment->setDiscountAmount($this->order->getDiscountAmount());
        \Rakuten\Connector\Resources\Log\Logger::info('Discount Amount set.');
        $payment->setTaxAmount($this->order->getTaxAmount());
        \Rakuten\Connector\Resources\Log\Logger::info('Tax Amount set.');
        $payment->setSender()->setEmail($this->order->getCustomerEmail());
        \Rakuten\Connector\Resources\Log\Logger::info('Email set.');
        $phone = $this->helper->formatPhone($shippingAddress->getTelephone());
        \Rakuten\Connector\Resources\Log\Logger::info('Phone formatted.');
        $payment->setSender()->setPhone()->withParameters($phone['areaCode'], $phone['number']);
        \Rakuten\Connector\Resources\Log\Logger::info('Phone set.');
        $orderAddress = new Rakuten_RakutenPay_Model_OrderAddress($this->order);
        \Rakuten\Connector\Resources\Log\Logger::info('Order address created.');
        $payment->setShipping()->setAddress()->instance($orderAddress->getShippingAddress());
        \Rakuten\Connector\Resources\Log\Logger::info('Shipping set.');
        $payment->setBilling()->setAddress()->instance($orderAddress->getBillingAddress());
        \Rakuten\Connector\Resources\Log\Logger::info('Billing set.');
        $payment->setShipping()->setType()->withParameters(SHIPPING_TYPE);
        \Rakuten\Connector\Resources\Log\Logger::info('Shipping type set.');
        $payment->setShipping()->setCost()->withParameters(number_format($this->order->getShippingAmount(), 2, '.',
            ''));
        \Rakuten\Connector\Resources\Log\Logger::info('Shipping costs set.');
        $payment->setNotificationUrl($this->getNotificationURL());
        \Rakuten\Connector\Resources\Log\Logger::info('Callback URL set.');
        $payment->setSender()->setBirthdate($this->order->getCustomerDob());
        \Rakuten\Connector\Resources\Log\Logger::info('DOB set.');

        if ($helper->isRakutenShippingMethod($this->order->getShippingMethod())) {
            \Rakuten\Connector\Resources\Log\Logger::info('ShippingMethod is RakutenLogistics.');
            $postageServiceCode = str_replace(self::RAKUTEN_LOGISTICS_CODE, '', $this->order->getShippingMethod());

            $payment->setKind(self::RAKUTEN_LOGISTICS_KIND);
            \Rakuten\Connector\Resources\Log\Logger::info('Kind set.');
            $payment->setCommissioningAmount((float) $this->order->getShippingAmount());
            \Rakuten\Connector\Resources\Log\Logger::info('Commissioning Amount set.');
            $payment->setCalculationCode(Mage::getSingleton('core/session')->getCalculationCode());
            \Rakuten\Connector\Resources\Log\Logger::info('Calculation Code set.');
            $payment->setPostageServiceCode($postageServiceCode);
            \Rakuten\Connector\Resources\Log\Logger::info('Postage Service Code Code set.');
        }
        \Rakuten\Connector\Resources\Log\Logger::info('All info set, returning.');

        return $payment;
    }

    /**
     * @param \Rakuten\Connector\Domains\Requests\DirectPayment\Boleto|\Rakuten\Connector\Domains\Requests\DirectPayment\CreditCard $payment
     */
    private function setItems($payment)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing setItems.');
        foreach ($this->order->getAllVisibleItems() as $product) {
            $payment->addItems()->withParameters(
                'SKU' . \Rakuten\Connector\Helpers\StringFormat::removeAccents($product->getSku()),
                $product->getProduct()->getId(),
                substr($product->getName(), 0, 254),
                (float)$product->getQtyOrdered(),
                number_format((float)$product->getPrice(), 2, '.', ''),
                round($product->getWeight())
            );
        }
    }

    private function getNotificationURL()
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing getNotificationURL.');
        $notificationPath = Mage::getStoreConfig('payment/rakutenpay/notification');

        if ($notificationPath) {
            $notificationUrl = $notificationPath;
        } else {
            $notificationUrl = Mage::app()->getStore(0)->getBaseUrl().'rakutenpay/notification/send/';
        }

        return $notificationUrl;
    }

   /**
    * Get the direct payment method (boleto or credit card)
    * and instantiate the respective payment object
    * @param string $paymentMethod
    * @param array $paymentData
    * @return \Rakuten\Connector\Domains\Requests\DirectPayment\Boleto
    *           || \Rakuten\Connector\Domains\Requests\DirectPayment\CreditCard $payment
    * @throws \Rakuten\Connector\Exception\ConnectorException
    */
    public function paymentDirect($paymentMethod, $paymentData)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing payment for ' . $paymentMethod);
        try {
            $payment = null;

            switch ($paymentMethod) {
                case 'rakutenpay_boleto':
                    $formatedDocument = \Rakuten\Connector\Helpers\Document::formatDocument($this->order->getCustomerTaxvat());
                    $payment = new \Rakuten\Connector\Domains\Requests\DirectPayment\Boleto();
                    $payment->setFingerprint($paymentData['fingerprint']);
                    $payment->setSender()->setDocument()->withParameters(
                        $formatedDocument['type'],
                        $formatedDocument['number']
                    );
                    $payment->setSender()->setHash($paymentData['boletoHash']);
                    break;

                case 'rakutenpay_credit_card':
                    $formatedDocument = \Rakuten\Connector\Helpers\Document::formatDocument($paymentData['creditCardDocument']);

                    $payment = new \Rakuten\Connector\Domains\Requests\DirectPayment\CreditCard();
                    $payment->setFingerprint($paymentData['fingerprint']);
                    $payment->setToken($paymentData['creditCardToken']);
                    $payment->setCvv($paymentData['creditCardCode']);
                    $payment->setBrand($paymentData['creditCardBrand']);
                    $payment
                        ->setInstallment()
                        ->withParameters(
                            $paymentData['creditCardInstallment'],
                            number_format($paymentData['creditCardInstallmentValue'], 2, '.', ''),
                            null,
                            number_format($paymentData['creditCardInterestPercent'], 2, '.', ''),
                            number_format($paymentData['creditCardInterestAmount'], 2, '.', ''),
                            number_format($paymentData['creditCardInstallmentTotalValue'], 2, '.', '')
                        );
                    $payment->setHolder()->setBirthdate($paymentData['creditCardBirthdate']);
                    $payment->setHolder()->setName($paymentData['creditCardHolder']);
                    $payment->setHolder()->setDocument()->withParameters(
                        $formatedDocument['type'],
                        $formatedDocument['number']
                    );
                    $payment->setSender()->setDocument()->withParameters(
                        $formatedDocument['type'],
                        $formatedDocument['number']
                    );
                    $orderAddress = new Rakuten_RakutenPay_Model_OrderAddress($this->order);
                    $payment->setSender()->setHash($paymentData['creditCardHash']);
                    break;
            }

            /** @var \Rakuten\Connector\Domains\Requests\DirectPayment\Boleto|\Rakuten\Connector\Domains\Requests\DirectPayment\CreditCard $payment */
            \Rakuten\Connector\Resources\Log\Logger::info('Processing done.');

            return $this->payment($payment);
        } catch (\Rakuten\Connector\Exception\ConnectorException $e) {
            \Rakuten\Connector\Resources\Log\Logger::error(sprintf('Exeception: %s', $e->getMessage()), ['service' => 'PaymentMethod.paymentDirect']);
        }
    }

    /**
     * @param \Rakuten\Connector\Domains\Requests\DirectPayment\Boleto|\Rakuten\Connector\Domains\Requests\DirectPayment\CreditCard|\Rakuten\Connector\Domains\Requests\Payment $payment
     *
     * @param bool $code
     *
     * @return bool|\Rakuten\Connector\Domains\Requests\DirectPayment\Boleto $response
     */
    public function paymentRegister($payment, $code = false)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing paymentRegister.');
        $response = false;
        try {
            if ($code) {
                /** @var \Rakuten\Connector\Domains\Requests\Payment $response */
                $response = $payment->register(true);
            } else {
                /** @var \Rakuten\Connector\Domains\Requests\DirectPayment\Boleto $payment */
                $response = $payment->register();
            }
        } catch (\Rakuten\Connector\Exception\ConnectorException $exception) {
            Mage::logException($exception);
        }

        return $response;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     *
     * @return Mage_Sales_Model_Order
     */
    public function setOrder(Mage_Sales_Model_Order $order)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing setOrder.');
        return $this->order = $order;
    }

    /**
     * getter for $_session (must be public to be instatiated in blocks)
     * @return type
     */
    public function getSession()
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing getSession.');
        if (is_null($this->_session) || empty($this->_session)) {
            $this->_session = $this->getPaymentSession()->getResult();
        }
        return $this->_session;
    }

    /**
     * Return status (enabled or disabled) from the Inovarti One Step Checkout module
     *
     * @return boolean
     */
    public function hasOneStepCheckout()
    {
        return (Mage::getStoreConfig("onestepcheckout/general/is_enabled") == 1) ? true : false;
    }
}
