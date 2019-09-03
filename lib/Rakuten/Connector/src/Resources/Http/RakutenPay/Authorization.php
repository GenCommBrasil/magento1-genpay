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

namespace Rakuten\Connector\Resources\Http\RakutenPay;

use Rakuten\Connector\Resources\Log\Logger;
use Rakuten\Connector\Exception\ConnectorException;
use Rakuten\Connector\Resources\Http\Response;

/**
 * Class Authorization
 * @package Rakuten\Connector\Resources\Http\RakutenPay
 */
class Authorization extends Response
{
    const SANDBOX = 'https://oneapi-sandbox.rakutenpay.com.br/';
    const PRODUCTION = 'https://api.rakuten.com.br/';
    const RAKUTENPAY_DIRECT_PAYMENT = 'rpay/v1/charges';

    /**
     * @var string
     */
    private $document;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $signature;

    /**
     * @var string
     */
    private $environment;

    /**
     * Authorization constructor.
     * @param $document
     * @param $apiKey
     * @param $signature
     * @param $environment
     */
    public function __construct($document, $apiKey, $signature, $environment)
    {
        $this->document = $document;
        $this->apiKey = $apiKey;
        $this->signature = $signature;
        $this->environment = $environment;
    }

    /**
     * @return bool
     */
    public function authorizationValidate()
    {
        Logger::info('Processing authorizationValidate in RakutenPay.');
        $options = [
            CURLOPT_URL => $this->getUrl(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_SSL_VERIFYPEER => false,
        ];

        try {
            Logger::info(sprintf("GET: %s", $this->getUrl()));
            $methodOptions = $this->getCurlHeader();
            $options = ($options + $methodOptions);
            Logger::info(sprintf('Headers RakutenPay: %s', json_encode($options)), ['service' => 'HTTP.HEADER']);
            $curl = curl_init();
            curl_setopt_array($curl, $options);
            $response = curl_exec($curl);
            $info = curl_getinfo($curl);

            $error = curl_errno($curl);
            $errorMessage = curl_error($curl);
            curl_close($curl);

            if ($error) {
                Logger::error(sprintf("CURL can't connect: %s", $errorMessage));
                throw new ConnectorException("CURL can't connect: $errorMessage");
            }

            $this->setStatus($info['http_code']);
            $this->setResponse($response);
            Logger::info(sprintf('Response Status: %s', $this->getStatus()), ['service' => 'HTTP.Response.Status']);

            return true;
        } catch (ConnectorException $e) {
            Logger::error($e->getMessage());
        }
    }

    /**
     * @param $method
     * @param $url
     * @param null $data
     * @return array
     */
    protected function getCurlHeader()
    {
        $auth = $this->document . ':' . $this->apiKey;
        $authBase64 = base64_encode($auth);

        return [
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Basic ' . $authBase64,
                'Cache-Control: no-cache'
            ],
            CURLOPT_HTTPGET => true
        ];
    }

    /**
     * @return string
     */
    private function getUrl()
    {
        if ($this->environment == "sandbox") {

            return self::SANDBOX . self::RAKUTENPAY_DIRECT_PAYMENT;
        }

        return self::PRODUCTION . self::RAKUTENPAY_DIRECT_PAYMENT;
    }
}
