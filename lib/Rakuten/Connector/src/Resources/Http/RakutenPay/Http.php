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

use Rakuten\Connector\Resources\Http\AbstractHttp;
use Rakuten\Connector\Resources\Log\Logger;
use Rakuten\Connector\Helpers\JsonFormat;
use Rakuten\Connector\Resources\Http\RakutenPay\Validator\ResponseValidate;
use Rakuten\Connector\Exception\ConnectorException;
use Mage;

/**
 * Class Http
 * @package Rakuten\Connector\Resources\Http\RakutenPay
 */
class Http extends AbstractHttp
{
    /**
     * @param $method
     * @param $url
     * @param $timeout
     * @param $charset
     * @param array|null $data
     * @param bool $secureGet
     * @return bool|mixed
     * @throws \Exception
     */
    protected function curlConnection($method, $url, $timeout, $charset, array $data = null, $secureGet = true)
    {
        Logger::info('Processing curlConnection in RakutenPay.');
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_CONNECTTIMEOUT => $timeout,
            //CURLOPT_TIMEOUT => $timeout
        ];

        try {
            if (strtoupper($method) === 'POST') {
                $data = JsonFormat::getJson($data);
            }

            $methodOptions = $this->getCurlHeader($method, $url, $data);
            $options = ($options + $methodOptions);
            Logger::info(sprintf('Headers RakutenPay: %s', json_encode($options)), ['service' => 'HTTP.HEADER']);
            $curl = curl_init();
            curl_setopt_array($curl, $options);
            $response = curl_exec($curl);
            $info = curl_getinfo($curl);

            ResponseValidate::validateSignature($method, $info, $data, $secureGet);

            $error = curl_errno($curl);
            $errorMessage = curl_error($curl);
            curl_close($curl);

            $filter = ResponseValidate::checkErrors($info['http_code'], $response, $error, $errorMessage);
            $this->setStatus($filter['status']);
            $this->setResponse($filter['response']);
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
    protected function getCurlHeader($method, $url, $data = null)
    {
        $cnpj = Mage::getStoreConfig('payment/rakutenpay/cnpj');
        $apiKey = Mage::getStoreConfig('payment/rakutenpay/api_key');
        $auth = $cnpj . ':' . $apiKey;
        $authBase64 = base64_encode($auth);

        if (strtoupper($method) === 'POST') {
            Logger::info(sprintf("POST: %s", $data), ['service' => 'HTTP_POST']);
            $sigKey = \Mage::getStoreConfig('payment/rakutenpay/signature_key');
            $signature = hash_hmac('sha256', $data, $sigKey, true);
            $signatureBase64 = base64_encode($signature);

            return [
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Signature: ' . $signatureBase64,
                    'Authorization: Basic ' . $authBase64,
                    'Cache-Control: no-cache'
                ],
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $data,
            ];
        }
        Logger::info(sprintf('Processing Request. URL %s', $url), ['service' => $method]);

        return [
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Basic ' . $authBase64,
                'Cache-Control: no-cache'
            ],
            CURLOPT_HTTPGET => true
        ];
    }
}
