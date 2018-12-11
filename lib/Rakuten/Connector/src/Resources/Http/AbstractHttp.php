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

namespace Rakuten\Connector\Resources\Http;

use Rakuten\Connector\Resources\Log\Logger;
use Rakuten\Connector\Exception\ConnectorException;

/**
 * Class AbstractHttp
 * @package RakutenPay\Resources
 */
abstract class AbstractHttp extends Response implements Method
{
    /**
     * Http constructor.
     * @throws ConnectorException
     */
    public function __construct()
    {
        Logger::info('Constructing Http in RakutenConnector.');
        if (!function_exists('curl_init')) {
            throw new ConnectorException('RakutenConnector Library: cURL library is required.');
        }
    }

    /**
     * @param $method
     * @param $url
     * @param $timeout
     * @param $charset
     * @param array|null $data
     * @param bool $secureGet
     * @return mixed
     */
    abstract protected function curlConnection($method, $url, $timeout, $charset, array $data = null, $secureGet = true);

    /**
     * @param $url
     * @param array $data
     * @param int $timeout
     * @param string $charset
     * @return bool
     * @throws ConnectorException
     */
    public function post($url, array $data = [], $timeout = 20, $charset = 'ISO-8859-1')
    {
        Logger::info('Processing post.');
        return $this->curlConnection('POST', $url, $timeout, $charset, $data);
    }

    /**
     * @param $url
     * @param int $timeout
     * @param string $charset
     * @return bool
     * @throws ConnectorException
     */
    public function get($url, $timeout = 20, $charset = 'ISO-8859-1', $secureGet = true)
    {
        return $this->curlConnection('GET', $url, $timeout, $charset, null, $secureGet);
    }
}
