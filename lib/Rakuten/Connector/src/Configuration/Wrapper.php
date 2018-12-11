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

namespace Rakuten\Connector\Configuration;
use Mage;

class Wrapper
{
    const ENV = "production";
    const EMAIL = "your_rakutenpay_email";
    const TOKEN_PRODUCTION = "your_production_token";
    const TOKEN_SANDBOX = "your_sandbox_token";
    const APP_ID_PRODUCTION = "your_production_application_id";
    const APP_ID_SANDBOX = "your_sandbox_application_id";
    const APP_KEY_PRODUCTION = "your_production_application_key";
    const APP_KEY_SANDBOX = "your_sandbox_application_key";
    const CHARSET = "UTF-8";

    public static function get_env()
    {
        return Mage::getStoreConfig('payment/rakutenpay/environment');
    }
}
