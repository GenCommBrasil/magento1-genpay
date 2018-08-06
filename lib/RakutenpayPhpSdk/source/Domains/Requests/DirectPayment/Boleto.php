<?php
/**
 ************************************************************************
 * Copyright [2017] [RakutenPay]
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

namespace RakutenPay\Domains\Requests\DirectPayment;

use RakutenPay\Domains\Requests\DirectPayment\Boleto\Request;

/**
 * Class Payment
 * @package RakutenPay\Domains\Requests
 */
class Boleto extends Request
{
    /**
     * @return string
     * @throws \Exception
     */
    public function register()
    {
        return \RakutenPay\Services\DirectPayment\Boleto::checkout($this);
    }
}
