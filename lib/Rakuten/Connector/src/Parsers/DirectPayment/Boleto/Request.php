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

use Rakuten\Connector\Enum\Properties\Constants;
use Rakuten\Connector\Parsers\Basic;
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
    use Basic;
    use Payment;
    use Customer;
    use Order;

    /**
     * @param Boleto $boleto
     * @return array
     */
    public static function getData(Boleto $boleto)
    {
        $data = [];

        $properties = new Constants();

        return array_merge(
            $data,
            Basic::getData($boleto, $properties),
            Payment::getData($boleto, $properties),
            Customer::getData($boleto, $properties),
            Order::getData($boleto, $properties)
        );
    }

    /**
     * @param Http $http
     * @return Response
     */
    public static function success(Http $http)
    {
        Logger::info($http->getResponse(), ["service" => "HTTP_RESPONSE"]);
        $data = json_decode($http->getResponse(), true);

        $payment = $data["payments"][0];
        $charge_url = \Rakuten\Connector\Resources\Builder\DirectPayment\Payment::getRequestUrl() . '/' . $data['charge_uuid'];

        return (new Response)->setResult($data['result'])
                ->setId($data['charge_uuid'])
                ->setCharge($charge_url)
                ->setBillet($payment['billet']['download_url'])
                ->setBilletUrl($payment['billet']['url']);
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
