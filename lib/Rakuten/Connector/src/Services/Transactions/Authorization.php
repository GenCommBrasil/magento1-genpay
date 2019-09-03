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
use Rakuten\Connector\Resources\Log\Logger;
use Rakuten\Connector\Resources\Responsibility;
use Rakuten\Connector\Parsers\Transaction\Authorization\Request;
use Rakuten\Connector\Resources\Http\RakutenPay\Authorization as Http;

/**
 * Class Authorization
 * @package Rakuten\Connector\Services\Transactions
 */
class Authorization
{
    /**
     * @param $document
     * @param $apiKey
     * @param $signature
     * @param $environment
     * @return mixed
     * @throws ConnectorException
     */
    public static function authorizationValidate($document, $apiKey, $signature, $environment)
    {
        Logger::info("Begin", ['service' => 'Transactions.Authorization']);
        try {
            $http = new Http($document, $apiKey, $signature, $environment);

            $http->authorizationValidate();

            return Responsibility::http(
                $http,
                new Request
            );
        }
        catch (ConnectorException $exception) {
            Logger::error($exception->getMessage(), ['service' => 'Session']);
            throw $exception;
        }
    }
}