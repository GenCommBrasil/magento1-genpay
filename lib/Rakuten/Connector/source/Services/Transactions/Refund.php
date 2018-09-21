<?php
/**
 ************************************************************************
 * Copyright [2017] [RakutenConnector]
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

namespace RakutenConnector\Services\Transactions;

use RakutenConnector\Parsers\Transaction\Refund\Request;
use RakutenConnector\Resources\Connection;
use RakutenConnector\Resources\RakutenPay\Http;
use RakutenConnector\Resources\Log\Logger;
use RakutenConnector\Resources\Responsibility;

/**
 * Class Refund
 * @package RakutenConnector\Services\Transactions
 */
class Refund
{

    /**
     * @param \RakutenConnector\Domains\Requests\Payment $payment
     * @param bool $onlyCode
     * @return string
     * @throws \Exception
     */
    public static function create($code, $value, $kind, $reason, $bankData, $paymentId)
    {
        Logger::info("Begin", ['service' => 'Refund']);
        try {
            $connection = new Connection\Data();
            $http = new Http();
            Logger::info(sprintf("POST: %s", self::request($connection, $code)), ['service' => 'Refund']);
            $data = ['reason' => $reason, 'amount' => $value, 'kind' => $kind];
            if ($kind === 'partial' || $bankData) {
                if ($bankData) {
                    $partialPayment = [['id' => $paymentId, 'amount' => $value, 'bank_account' => $bankData]];
                } else {
                    $partialPayment = [['id' => $paymentId, 'amount' => $value]];
                }
                $data['payments'] = $partialPayment;
            }
            Logger::info(
                sprintf(
                    "Params: %s",
                    json_encode($data)
                ),
                ['service' => 'Refund']
            );
            $http->post(
                self::request($connection, $code),
                $data
            );

            $response = Responsibility::http(
                $http,
                new Request
            );

            Logger::info(sprintf("Result: %s", current($response)), ['service' => 'Refund']);
            return $response;
        } catch (\Exception $exception) {
            Logger::error($exception->getMessage(), ['service' => 'Refund']);
            throw $exception;
        }
    }

    /**
     * @param Connection\Data $connection
     * @return string
     */
    private static function request(Connection\Data $connection, $id)
    {
        return $connection->buildRefundRequestUrl($id);
    }
}
