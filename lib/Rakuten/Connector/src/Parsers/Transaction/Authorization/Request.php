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

namespace Rakuten\Connector\Parsers\Transaction\Authorization;

use Rakuten\Connector\Parsers\Error;
use Rakuten\Connector\Parsers\Parser;
use Rakuten\Connector\Resources\Http\RakutenPay\Http;
use Rakuten\Connector\Resources\Log\Logger;
use Rakuten\Connector\Parsers\Transaction\Authorization\Response;

/**
 * Class Request
 * @package Rakuten\Connector\Parsers\Transaction\Authorization
 */
class Request extends Error implements Parser
{
    /**
     * @param \Rakuten\Connector\Resources\Http\RakutenPay\Http $http
     * @return Response
     */
    public static function success(Http $http)
    {
        Logger::info($http->getResponse(), ["service" => "HTTP_RESPONSE"]);
        $data = json_decode($http->getResponse(), true);

        $response = (new Response)
        ->setResult($data['result']);

        return $response;
    }

    /**
     * @param Http $http
     * @return mixed|\Rakuten\Connector\Domains\Error
     */
    public static function error(Http $http)
    {
        return parent::error($http);
    }
}