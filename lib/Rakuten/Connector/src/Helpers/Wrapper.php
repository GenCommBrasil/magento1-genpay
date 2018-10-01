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

namespace Rakuten\Connector\Helpers;

/**
 * Class Wrapper
 * @package Rakuten\Connector\Helpers
 */
class Wrapper
{
    /**
     * @param $config
     * @return array
     */
    public static function environment($config)
    {
        return [
            'environment' => $config::get_env()
        ];
    }

    /**
     * @param $config
     * @return array
     */
    public static function credentials($config)
    {
        return [
            'credentials' => [
                'email' => $config::EMAIL,
                'token' => [
                    'environment' => [
                        'production' => $config::TOKEN_PRODUCTION,
                        'sandbox' => $config::TOKEN_SANDBOX
                    ]
                ],
                'appId' => [
                    'environment' => [
                        'production' => $config::APP_ID_PRODUCTION,
                        'sandbox' => $config::APP_ID_SANDBOX
                    ]
                ],
                'appKey' => [
                    'environment' => [
                        'production' => $config::APP_KEY_PRODUCTION,
                        'sandbox' => $config::APP_KEY_SANDBOX
                    ]
                ]
            ]
        ];
    }

    /**
     * @param $config
     * @return array
     */
    public static function charset($config)
    {
        return [
            'charset' => $config::CHARSET
        ];
    }

    /**
     * @param $config
     * @return array
     */
    public static function log($config)
    {
        return [
            'log' => [
                'active' => $config::get_log_active(),
                'location' => $config::get_log_location()
            ]
        ];
    }
}
