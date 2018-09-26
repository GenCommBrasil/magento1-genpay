<?php
/**
 ************************************************************************
 * Copyright [2017] [RakutenConnector]
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

/**
 * Interface Method
 * @package Rakuten\Connector\Resources\Http
 */
interface Method
{
    /**
     * @param $url
     * @param array $data
     * @param int $timeout
     * @param string $charset
     * @return bool
     * @throws \Exception
     */
    public function post($url, array $data = [], $timeout = 20, $charset = 'ISO-8859-1');

    /**
     * @param $url
     * @param int $timeout
     * @param string $charset
     * @param bool $secureGet
     * @return bool
     * @throws \Exception
     */
    public function get($url, $timeout = 20, $charset = 'ISO-8859-1', $secureGet = true);
}
