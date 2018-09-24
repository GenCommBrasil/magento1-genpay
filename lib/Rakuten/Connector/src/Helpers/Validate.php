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

namespace Rakuten\Connector\Helpers;

use Rakuten\Connector\Resources\Log\Logger;

class Validate
{
    final public static function cUrl()
    {
        Logger::info('Processing cUrl in Validate.');
        if (!function_exists('curl_init')) {
            throw new \Exception(
                'RakutenPay Library cURL library is required.',
                '[cURL]'
            );
        }
    }

    final public static function simpleXml()
    {
        Logger::info('Processing simpleXml in Validate.');
        if (!extension_loaded('simplexml')) {
            throw new \Exception(
                'RakutenConnector Library simple xml is required.',
                '[SimpleXml]'
            );
        }
    }
}
