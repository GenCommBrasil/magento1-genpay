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

/**
 * Class Rakuten_RakutenLogistics_Helper_Environment
 */
class Rakuten_RakutenLogistics_Helper_Environment extends Rakuten_RakutenLogistics_Enum_Endpoints
{
    /**
     * @var array
     */
    private static $endpointsProductionMapping = [
        self::NAME_BATCH => self::PRODUCTION_BATCH,
        self::NAME_BATCH_DETAIL => self::PRODUCTION_BATCH_DETAIL,
        self::NAME_CARRIER_METHODS => self::PRODUCTION_CARRIER_METHODS,
        self::NAME_CARRIER_PRICES => self::PRODUCTION_CARRIER_PRICES,
    ];

    /**
     * @var array
     */
    private static $endpointsSandboxMapping = [
        self::NAME_BATCH => self::SANDBOX_BATCH,
        self::NAME_BATCH_DETAIL => self::SANDBOX_BATCH_DETAIL,
        self::NAME_CARRIER_METHODS => self::SANDBOX_CARRIER_METHODS,
        self::NAME_CARRIER_PRICES => self::SANDBOX_CARRIER_PRICES,

    ];

    /**
     * @param $endpointName
     * @return mixed
     */
    public static function getEndpoint($endpointName)
    {
        try {
            \Rakuten\Connector\Resources\Log\Logger::info('Processing getEndpoint in Environment.');
            $environment = Mage::getStoreConfig('payment/rakutenpay/environment');
            if ($environment == 'production') {
                return self::$endpointsProductionMapping[$endpointName];
            }

            return self::$endpointsSandboxMapping[$endpointName];
        } catch (\Rakuten\Connector\Exception\ConnectorException $e) {
            \Rakuten\Connector\Resources\Log\Logger::error(sprintf('Environment Endpoint: %s', $e->getMessage()));
        }
    }
}
