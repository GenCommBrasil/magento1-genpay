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

namespace Rakuten\Connector\Resources\Http\RakutenLogistics;

use Rakuten\Connector\Exception\ConnectorException;
use Rakuten\Connector\Resources\Log\Logger;
use Rakuten\Connector\Resources\Http\AbstractHttp;
use Mage;

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
     * @throws ConnectorException
     */
    protected function curlConnection($method, $url, $timeout, $charset, array $data = null, $secureGet = true)
    {
        Logger::info('Processing curlConnection in RakutenLogistics.');
        $header = $this->getHeader();
        $methodOptions = [
            CURLOPT_HTTPGET => true,
        ];

        if (strtoupper($method) === 'POST') {
            $postData = json_encode($data);
            Logger::info('curlConnection POST in RakutenLogistics.', ['service' => $postData]);

            $methodOptions = [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postData,
            ];
        }

        $header = $header + $methodOptions;
        Logger::info(sprintf('Headers RakutenLogistics: %s', json_encode($header)), ['service' => 'HTTP.HEADER']);
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_CONNECTTIMEOUT => $timeout,
        ];

        $options = $header + $options;
        $curl = curl_init();
        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);
        $info = curl_getinfo($curl);

        $error = curl_errno($curl);
        $errorMessage = curl_error($curl);
        curl_close($curl);

        $this->setStatus((int) $info['http_code']);
        $this->setResponse((string) $response);
        Logger::info(sprintf('Response Status: %s' , $this->getStatus()), ['service' => 'HTTP.Response.Status']);
        if ($error) {
            Logger::error(sprintf('CURL Error: %s', $errorMessage), ['service' => 'RakutenLogistics']);
            throw new ConnectorException("CURL can't connect: {$errorMessage}");
        } else {
            return true;
        }
    }

    /**
     * @return array
     */
    private function getHeader()
    {
        $cnpj = Mage::getStoreConfig('payment/rakutenpay/cnpj');
        $apiKey = Mage::getStoreConfig('payment/rakutenpay/api_key');
        $auth = $cnpj . ':' . $apiKey;
        $authBase64 = base64_encode($auth);

        $header = [
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Basic ' . $authBase64,
                'Cache-Control: no-cache'
            ],
        ];
        return $header;
    }
}
