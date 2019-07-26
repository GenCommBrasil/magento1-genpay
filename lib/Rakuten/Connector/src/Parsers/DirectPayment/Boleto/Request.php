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

namespace Rakuten\Connector\Parsers\DirectPayment\Boleto;

use Rakuten\Connector\Enum\Http\Status;
use Rakuten\Connector\Enum\Properties\Constants;
use Rakuten\Connector\Parsers\Basic;
use Rakuten\Connector\Parsers\Commissioning;
use Rakuten\Connector\Parsers\Customer;
use Rakuten\Connector\Parsers\Error;
use Rakuten\Connector\Parsers\Order;
use Rakuten\Connector\Parsers\Parser;
use Rakuten\Connector\Resources\Http\RakutenPay\Http;
use Rakuten\Connector\Parsers\Transaction\Boleto\Response;
use Rakuten\Connector\Domains\Requests\DirectPayment\Boleto;
use Rakuten\Connector\Resources\Log\Logger;

/**
 * Class Request
 * @package Rakuten\Connector\Parsers\DirectPayment\Boleto
 */
class Request extends Error implements Parser
{
    use Commissioning;
    use Basic;
    use Payment;
    use Customer;
    use Order;

    /**
     * @param Http $http
     * @return Response
     */
    private static function processError(Http $http)
    {
        $response = self::getResponse();
        $data = json_decode($http->getResponse(), true);

        $code = isset($data['result_code']['code']) ? $data['result_code']['code'] : "";

        return $response
            ->setCode($code)
            ->setResult($data['result'])
            ->setResultMessage(implode(' - ', $data['result_messages']));
    }

    /**
     * @param Http $http
     * @return Response
     */
    private static function processSuccess(Http $http)
    {
        $response = self::getResponse();
        $data = json_decode($http->getResponse(), true);

        $payment = $data["payments"][0];
        $chargeUrl = \Rakuten\Connector\Resources\Builder\DirectPayment\Payment::getRequestUrl() . '/' . $data['charge_uuid'];

        return $response->setResult($data['result'])
            ->setId($data['charge_uuid'])
            ->setCharge($chargeUrl)
            ->setOrderStatus($payment['status'])
            ->setBillet($payment['billet']['download_url'])
            ->setBilletUrl($payment['billet']['url'])
            ->setResultMessage(implode(' - ', $data['result_messages']));
    }

    /**
     * @return Response
     */
    private static function getResponse()
    {
        return new Response();
    }

    /**
     * @param Boleto $boleto
     * @return array
     */
    public static function getData(Boleto $boleto)
    {
        Logger::info('Processing getData in trait Request.');
        $data = [];
        $properties = new Constants();
        $data = array_merge(
            $data,
            Basic::getData($boleto, $properties),
            Payment::getData($boleto, $properties),
            Customer::getData($boleto, $properties),
            Order::getData($boleto, $properties)
        );

        $commissioning = Commissioning::getData($boleto, $properties);
        if (!is_null($commissioning)) {

            return array_merge($data, $commissioning);
        }

        return $data;
    }

    /**
     * @param Http $http
     * @return Response
     */
    public static function success(Http $http)
    {
        Logger::info($http->getResponse(), ["service" => "HTTP_RESPONSE"]);
        if ($http->getStatus() == Status::OK) {

            return self::processSuccess($http);
        }

        return self::processError($http);
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
