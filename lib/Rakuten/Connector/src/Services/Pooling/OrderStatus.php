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

namespace Rakuten\Connector\Services\Pooling;

use Rakuten\Connector\Resources\Connection;
use Rakuten\Connector\Resources\Http\RakutenPay\Http;
use Rakuten\Connector\Resources\Log\Logger;
use Rakuten\Connector\Resources\Responsibility;
use Rakuten\Connector\Parsers\Pooling\Request;

/**
 * Class OrderStatus
 * @package Rakuten\Connector\Services\Pooling
 */
class OrderStatus
{

    /**
     * @param $chargeId
     * @return mixed
     * @throws \Exception
     */
    public static function check($chargeId)
    {
        Logger::info("Begin", ['service' => 'Pooling']);
        try {
            $connection = new Connection\Data();
            $http = new Http();
            Logger::info(sprintf("GET: %s", self::request($connection, $chargeId)), ['service' => 'Pooling']);
            $http->get(self::request($connection, $chargeId), 20, 'ISO-8859-1', false);

            $response = Responsibility::http(
                $http,
                new Request()
            );

            Logger::info(
                sprintf(
                    "charge_uuid: %s",
                    $chargeId
                ),
                ['service' => 'Pooling']
            );
            return $response;
        } catch (\Exception $exception) {
            Logger::error($exception->getMessage(), ['service' => 'Pooling']);
            throw $exception;
        }
    }

    /**
     * @param Connection\Data $connection
     * @param $chargeId
     * @return string
     * @throws \Exception
     */
    private static function request(Connection\Data $connection, $chargeId)
    {
        return $connection->buildOrderStatusRequestUrl($chargeId);
    }
}
