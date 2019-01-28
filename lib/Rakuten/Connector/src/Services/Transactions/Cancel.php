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
use Rakuten\Connector\Parsers\Transaction\Cancel\Request;
use Rakuten\Connector\Resources\Connection;
use Rakuten\Connector\Resources\Http\RakutenPay\Http;
use Rakuten\Connector\Resources\Log\Logger;
use Rakuten\Connector\Resources\Responsibility;


/**
 * Class Cancel
 * @package Rakuten\Connector\Services\Transactions
 */
class Cancel
{
    public static function create($code)
    {
        Logger::info("Begin", ['service' => 'Cancel']);
        try {
            $connection = new Connection\Data();
            $http = new Http();

            Logger::info(sprintf("POST: %s", self::request($connection, $code)), ['service' => 'Cancel']);

            $http->post(
                self::request($connection, $code),
                null
            );

            $response = Responsibility::http(
                $http,
                new Request
            );

            Logger::info(sprintf("Result: %s", current($response)), ['service' => 'Cancel']);
            return $response;
        } catch (ConnectorException $exception) {
            Logger::error($exception->getMessage(), ['service' => 'Cancel']);
            throw $exception;
        }
    }

    /**
     * @param Connection\Data $connection
     * @return string
     */
    private static function request(Connection\Data $connection, $id)
    {
        return $connection->buildCancelRequestUrl($id);
    }
}
