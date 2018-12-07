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

namespace Rakuten\Connector\Services\Checkout;

use Rakuten\Connector\Exception\ConnectorException;
use Rakuten\Connector\Helpers\Crypto;
use Rakuten\Connector\Resources\Connection;
use Rakuten\Connector\Resources\Http\RakutenPay\Http;
use Rakuten\Connector\Resources\Log\Logger;
use Rakuten\Connector\Resources\Responsibility;

/**
 * Class Payment
 * @package Rakuten\Connector\Services\Checkout
 */
class Payment
{

    /**
     * @param \Rakuten\Connector\Domains\Requests\Payment $payment
     * @param bool $onlyCode
     * @return string
     * @throws ConnectorException
     */
    public static function checkout(\Rakuten\Connector\Domains\Requests\Payment $payment, $onlyCode)
    {
        Logger::info("Begin", ['service' => 'Checkout']);
        try {
            $connection = new Connection\Data();
            $http = new Http();
            Logger::info(sprintf("POST: %s", self::request($connection)), ['service' => 'Checkout']);
            Logger::info(
                sprintf(
                    "Params: %s",
                    json_encode(Crypto::encrypt(\Rakuten\Connector\Parsers\Checkout\Request::getData($payment)))
                ),
                ['service' => 'Checkout']
            );
            $http->post(
                self::request($connection),
                \Rakuten\Connector\Parsers\Checkout\Request::getData($payment),
                20,
                \Rakuten\Connector\Configuration\Configure::getCharset()->getEncoding()
            );

            $response = Responsibility::http(
                $http,
                new \Rakuten\Connector\Parsers\Checkout\Request
            );

            if ($onlyCode) {
                Logger::info(sprintf("Code: %s", current($response)), ['service' => 'Checkout']);
                return $response;
            }
            Logger::info(
                sprintf("Checkout URL: %s", self::response($connection, $response)),
                ['service' => 'Checkout']
            );
            return self::response($connection, $response);
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
        return $connection->buildPaymentRequestUrl();
    }

    /**
     * @param Connection\Data $connection
     * @param $response
     * @return string
     */
    private static function response(Connection\Data $connection, $response)
    {
        return $connection->buildPaymentResponseUrl() . "?code=" . $response->getCode();
    }
}
