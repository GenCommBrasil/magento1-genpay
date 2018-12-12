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

namespace Rakuten\Connector\Services\DirectPayment;

use Rakuten\Connector\Resources\Connection;
use Rakuten\Connector\Resources\Http\RakutenPay\Http;
use Rakuten\Connector\Resources\Log\Logger;
use Rakuten\Connector\Resources\Responsibility;
use Rakuten\Connector\Parsers\DirectPayment\Boleto\Request;
use Rakuten\Connector\Helpers\Crypto;
use Rakuten\Connector\Exception\ConnectorException;

/**
 * Class Boleto
 * @package Rakuten\Connector\Services\DirectPayment
 */
class Boleto
{

    /**
     * @param \Rakuten\Connector\Domains\Requests\DirectPayment\Boleto $payment
     * @return mixed
     * @throws ConnectorException
     */
    public static function checkout(
        \Rakuten\Connector\Domains\Requests\DirectPayment\Boleto $payment
    ) {
        Logger::info("Begin", ['service' => 'DirectPayment.Boleto']);
        try {
            $connection = new Connection\Data();
            $http = new Http();
            Logger::info(sprintf("POST: %s", self::request($connection)), ['service' => 'DirectPayment.Boleto']);
            Logger::info(
                sprintf(
                    "Params: %s",
                    json_encode(Crypto::encrypt(Request::getData($payment)))
                ),
                ['service' => 'Checkout']
            );

            $http->post(
                self::request($connection),
                Request::getData($payment)
            );

            $response = Responsibility::http(
                $http,
                new Request
            );

            Logger::info(
                sprintf("Boleto Payment URL: %s", $response->getBillet()),
                ['service' => 'DirectPayment.Boleto']
            );

            Logger::info(
                sprintf("Boleto Link URL: %s", $response->getBilletUrl()),
                ['service' => 'DirectPayment.Boleto']
            );

            return $response;
        } catch (ConnectorException $exception) {
            Logger::error($exception->getMessage(), ['service' => 'Session']);
            throw $exception;
        }
    }

    /**
     * @param Connection\Data $connection
     * @return string
     */
    private static function request(Connection\Data $connection)
    {
        return $connection->buildDirectPaymentRequestUrl();
    }
}
