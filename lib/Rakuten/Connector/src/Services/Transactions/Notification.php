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

namespace Rakuten\Connector\Services\Transactions;

use Rakuten\Connector\Exception\ConnectorException;
use Rakuten\Connector\Resources\Connection;
use Rakuten\Connector\Resources\Http\RakutenPay\Http;
use Rakuten\Connector\Resources\Log\Logger;
use Rakuten\Connector\Resources\Responsibility;

/**
 * Class Notification
 * @package Rakuten\Connector\Services\Transactions
 */
class Notification
{

    /**
     * @return mixed
     * @throws ConnectorException
     */
    public static function check()
    {
        Logger::info("Begin", ['service' => 'Transactions.Notification']);
        try {
            $connection = new Connection\Data();
            $http = new Http();
            Logger::info(sprintf("GET: %s", self::request($connection)), ['service' => 'Transactions.Notification']);
            $http->get(
                self::request($connection)
            );

            $response = Responsibility::http(
                $http,
                new \Rakuten\Connector\Parsers\Transaction\Notification\Request
            );
            Logger::info(
                sprintf(
                    "Date: %s, Code: %s",
                    $response->getDate(),
                    $response->getCode()
                ),
                ['service' => 'Transactions.Notification']
            );
            return $response;
        } catch (ConnectorException $exception) {
            Logger::error($exception->getMessage(), ['service' => 'Transactions.Notification']);
            throw $exception;
        }
    }

    /**
     * @param Connection\Data $connection
     * @return string
     */
    private static function request(Connection\Data $connection)
    {
        return $connection->buildNotificationTransactionRequestUrl();
    }
}
