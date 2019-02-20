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
 * Class Rakuten_RakutenLogistics_Enum_Endpoints
 */
class Rakuten_RakutenLogistics_Enum_Endpoints
{
    /**
     * Name for Endpoints
     */
    const NAME_BATCH = 'batch';
    const NAME_BATCH_DETAIL = 'batchDetail';
    const NAME_CARRIER_METHODS = 'carrierMethods';
    const NAME_CARRIER_PRICES = 'carrierPrices';

    /**
     * Endpoints Sandbox
     */
    const SANDBOX_BATCH = "https://oneapi-sandbox.rakutenpay.com.br/logistics/batch";
    const SANDBOX_BATCH_DETAIL = "https://oneapi-sandbox.rakutenpay.com.br/logistics/order/";
    const SANDBOX_CARRIER_METHODS = "https://oneapi-sandbox.rakutenpay.com.br/logistics/client/info";
    const SANDBOX_CARRIER_PRICES = "https://oneapi-sandbox.rakutenpay.com.br/logistics/calculation";

    /**
     * Endpoints Production
     */
    const PRODUCTION_BATCH = "https://api.rakuten.com.br/logistics/batch";
    const PRODUCTION_BATCH_DETAIL = "https://api.rakuten.com.br/logistics/order/";
    const PRODUCTION_CARRIER_METHODS = "https://api.rakuten.com.br/logistics/client/info";
    const PRODUCTION_CARRIER_PRICES = "https://api.rakuten.com.br/logistics/calculation";
}
