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

namespace Rakuten\Connector\Domains\Requests\DirectPayment;

use Rakuten\Connector\Domains\Requests\DirectPayment\CreditCard\Request;

/**
 * Class CreditCard
 * @package Rakuten\Connector\Domains\Requests\DirectPayment
 */
class CreditCard extends Request
{
    /**
     * @return string
     * @throws \Exception
     */
    public function register()
    {
        return \Rakuten\Connector\Services\DirectPayment\CreditCard::checkout($this);
    }
}
