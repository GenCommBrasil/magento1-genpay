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
 * Class Rakuten_RakutenPay_Helper_Webservice
 */
class Rakuten_RakutenPay_Helper_Webservice extends Rakuten_RakutenPay_Helper_Data
{
    /**
     * @var Rakuten_RakutenPay_Model_Library
     */
    private $library;

    /**
     * Rakuten_RakutenPay_Helper_Webservice constructor.
     */
    public function __construct()
    {
        parent::__construct();
        \Rakuten\Connector\Resources\Log\Logger::info('Constructing HelperWebservice.');
        $this->library = new Rakuten_RakutenPay_Model_Library();
    }

    /**
     * @param $transactionCode
     *
     * @return \Rakuten\Connector\Services\Transactions\Notification
     */
    public function getNotification()
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing getNotification in HelperWebservice.');
        return \Rakuten\Connector\Services\Transactions\Notification::check();
    }

    /**
     * @param $page
     * @param $maxPageResults
     * @param $initialDate
     *
     * @return null|string
     * @throws Exception
     */
    public function getTransactionsByDate($page, $maxPageResults, $initialDate)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing getTransactionsByDate in HelperWebservice.');
        $response = null;
        try {
            $response = \Rakuten\Connector\Services\Transactions\Search\Date::search(
                array('initial_date' => $initialDate, 'page' => $page, 'max_per_page' => $maxPageResults)
            );
        } catch (Exception $e) {
            if (trim($e->getMessage()) == '[HTTP 401] - UNAUTHORIZED') {
                throw new Exception($e->getMessage());
            }
        }

        return $response;
    }

    /**
     * @param $transactionCode
     *
     * @return \Rakuten\Connector\Services\Transactions\Refund
     */
    public function refundRequest($transactionCode, $refundValue = null, $kind = 'total',
            $reason = 'merchant_other', $bankData = null, $paymentId = null)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing refundRequest in HelperWebservice.');
        return \Rakuten\Connector\Services\Transactions\Refund::create(
            $transactionCode,
            $refundValue,
            $kind,
            $reason,
            $bankData,
            $paymentId
        );
    }

    /**
     * @param $class
     * @param $transactionCode
     *
     * @return mixed|string
     * @throws Exception
     */
    public function requestRakutenPayService($class, $transactionCode)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing requestRakutenPayService in HelperWebservice.');
        try {
            if ($class == 'Rakuten_RakutenPay_Adminhtml_RefundController') {
                return \Rakuten\Connector\Services\Transactions\Refund::create($transactionCode);
            } elseif ($class == 'Rakuten_RakutenPay_Model_NotificationMethod') {
                return \Rakuten\Connector\Services\Transactions\Notification::check();
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function poolingRequest($chargeId)
    {
        \Rakuten\Connector\Resources\Log\Logger::info("Processing poolingRequest in HelperWebservice", ['service' => "Webservice.Pooling"]);
        return \Rakuten\Connector\Services\Pooling\OrderStatus::check($chargeId);
    }
}
