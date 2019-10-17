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

use Mage_Payment_Model_Method_Abstract as MethodAbstract;

/**
 * RakutenPay installments model
 */
class Rakuten_RakutenPay_Model_InstallmentsMethod extends MethodAbstract
{
    const DEFAULT_MINIMUM_VALUE = 10.0;
    const DEFAULT_INSTALLMENTS = 1;

    protected $_code = 'rakutenpay';
    /**
     * @var Rakuten_RakutenPay_Model_Library
     */
    private $library;

    /**
     * Construct
     */
    public function __construct()
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Constructing ModelInstallmentsMethod.');
        $this->helper = Mage::helper('rakutenpay');
        $this->library = new Rakuten_RakutenPay_Model_Library();
    }

    /**
     * Get the bigger installments list returned by the RakutenPay service
     *
     * @param $amount
     * @return bool|mixed
     * @throws Exception
     */
    public function create($amount)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing create in ModelInstallmentsMethod.');
        $this->helper = Mage::helper('rakutenpay');
        try {
            $session = \Mage::getSingleton('checkout/session');
            $minimumValue = \Mage::getStoreConfig("payment/rakutenpay_credit_card/minimum_installment");
            $maximumInstallments = \Mage::getStoreConfig("payment/rakutenpay_credit_card/maximum_installment_number");
            $installments = self::createInstallments($amount, $minimumValue, $maximumInstallments);
            $format = $this->output($installments, true);

            return $format['installments'];
        } catch (\Rakuten\Connector\Exception\ConnectorException $exception) {
            Mage::log($exception->getMessage());

            return false;
        }
    }

    /**
     * Returns the maximum number of installments
     * @param $amount
     * @param $minimumValue
     * @param $maximumInstallments
     * @return float
     */
    private function getMaxNoInstallments($amount, $minimumValue, $maximumInstallments)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing getMaxNoInstallments in ModelInstallmentsMethod.');
        $installments = $this->getInstallmentsByMinimumValue($amount, $minimumValue);
        if (!empty($maximumInstallments) && $maximumInstallments > 0) {
            if ($installments > $maximumInstallments) {
                return $maximumInstallments;
            }
        }

        return $installments;
    }

    /**
     * @param $amount
     * @param $minimumValue
     * @return float
     */
    private function getInstallmentsByMinimumValue($amount, $minimumValue)
    {
        if (is_null($minimumValue) || is_nan($minimumValue) || $minimumValue < 0) {
            $minimumValue = self::DEFAULT_MINIMUM_VALUE;
        }
        $installments = $amount / (float) $minimumValue;
        if ($amount <= (float) $minimumValue || false === $this->isInstallment()) {
            return self::DEFAULT_INSTALLMENTS;
        }

        return $installments;
    }

    /**
     * Returns an array of installments
     * @param $amount
     * @param $minimumValue
     * @param $maximumInstallments
     * @return array
     * @throws Exception
     */
    private function createInstallments($amount, $minimumValue, $maximumInstallments)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing createInstallments in ModelInstallmentsMethod.');
        $arr = [];
        if (Mage::getStoreConfig('payment/rakutenpay_credit_card/customer_interest') == 1)
        {
            $checkout = \Rakuten\Connector\Services\Transactions\Checkout::get(
                ['amount' => $amount]
            );
            $minimumInstallment = (int)Mage::getStoreConfig('payment/rakutenpay_credit_card/customer_interest_minimum_installments');
            foreach($checkout->getCreditCardPayment()->getInstallments() as $customerInterestInstallment) {
                $installment = new Rakuten\Connector\Domains\Responses\Installment;
                if ((int)$customerInterestInstallment->getQuantity() > $minimumInstallment){
                    $installment
                    ->setAmount($customerInterestInstallment->getInstallmentAmount())
                    ->setQuantity($customerInterestInstallment->getQuantity())
                    ->setTotalAmount($customerInterestInstallment->getTotal())
                    ->setInterestAmount($customerInterestInstallment->getInterestAmount())
                    ->setInterestPercent($customerInterestInstallment->getInterestPercent())
                    ->setInterestFree(false);
                }
                else {
                    $value = $amount / $customerInterestInstallment->getQuantity();
                    $value = ($value * 100) / 100;
                    $total = $value * $customerInterestInstallment->getQuantity();
                    $installment
                    ->setAmount($value)
                    ->setQuantity($customerInterestInstallment->getQuantity())
                    ->setTotalAmount($total)
                    ->setInterestAmount(0.0)
                    ->setInterestPercent(0.0)
                    ->setInterestFree(true);

                }
                $arr[] = $installment;
            }
        } else {
            $maxNoInstallments = self::getMaxNoInstallments($amount, $minimumValue, $maximumInstallments);
            for ($mo = 1; $mo <= $maxNoInstallments; $mo++) {
                $value = $amount / $mo;
                $value = ($value * 100) / 100;
                $total = $value * $mo;
                $installment = new Rakuten\Connector\Domains\Responses\Installment;
                $installment
                ->setAmount($value)
                ->setQuantity($mo)
                ->setTotalAmount($total)
                ->setInterestAmount(0.0)
                ->setInterestPercent(0.0)
                ->setInterestFree(true);
                $arr[] = $installment;
            }
        }
        return $arr;
    }

    /**
     * Return a formated output of installments
     *
     * @param array $installments
     * @param bool  $maxInstallment
     *
     * @return array
     */
    private function output($installments, $maxInstallment)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing output in ModelInstallmentsMethod.');
        return ($maxInstallment) ?
            $this->formatOutput($this->getMaxInstallment($installments)) :
            $this->formatOutput($installments);
    }

    /**
     * Format the installment to the be show in the view
     *
     * @param  array $installments
     *
     * @return array
     */
    private function formatOutput($installments)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing formatOutput in ModelInstallmentsMethod.');
        $response = $this->getOptions();
        foreach ($installments as $installment) {
            $response['installments'][] = $this->formatInstallments($installment);
        }

        return $response;
    }

    /**
     * Format a installment for output
     *
     * @param $installment
     *
     * @return array
     */
    private function formatInstallments($installment)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing formatInstallments in ModelInstallmentsMethod.');
        return array(
            'quantity'        => $installment->getQuantity(),
            'amount'          => $installment->getAmount(),
            'totalAmount'     => Rakuten\Connector\Helpers\Currency::toDecimal($installment->getTotalAmount()),
            'text'            => str_replace('.', ',', $this->getInstallmentText($installment)),
            'interestAmount'  => $installment->getInterestAmount(),
            'interestPercent' => $installment->getInterestPercent()
        );
    }

    /**
     * Mount the text message of the installment
     *
     * @param  object $installment
     *
     * @return string
     */
    private function getInstallmentText($installment)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing getInstallmentText in ModelInstallmentsMethod.');
        return sprintf(
            "%s x de R$ %.2f %s juros",
            $installment->getQuantity(),
            $installment->getAmount(),
            $this->getInterestFreeText($installment->getInterestFree()));
    }

    /**
     * Get the string relative to if it is an interest free or not
     *
     * @param string $insterestFree
     *
     * @return string
     */
    private function getInterestFreeText($insterestFree)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing getInterestFreeText in ModelInstallmentsMethod.');
        return ($insterestFree == 'true') ? 'sem' : 'com';
    }

    /**
     * Get the bigger installments list in the installments
     *
     * @param array $installments
     *
     * @return array
     */
    private function getMaxInstallment($installments)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing getMaxInstallment in ModelInstallmentsMethod.');
        $final = $current = array('brand' => '', 'start' => 0, 'final' => 0, 'quantity' => 0);
        foreach ($installments as $key => $installment) {
            if ($current['brand'] !== $installment->getCardBrand()) {
                $current['brand'] = $installment->getCardBrand();
                $current['start'] = $key;
            }
            $current['quantity'] = $installment->getQuantity();
            $current['end'] = $key;
            if ($current['quantity'] > $final['quantity']) {
                $final = $current;
            }
        }

        return array_slice(
            $installments,
            $final['start'],
            $final['end'] - $final['start'] + 1
        );
    }

    /**
     * Check if installments show is enabled
     * @return bool
     */
    public function isInstallment()
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing enabled in ModelInstallmentsMethod.');
        $isEnable = (int) Mage::getStoreConfig('payment/rakutenpay_credit_card/installments');

        return ($isEnable == 1) ? true : false;
    }
}
