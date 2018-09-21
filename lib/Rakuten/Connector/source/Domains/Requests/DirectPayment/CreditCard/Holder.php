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

namespace RakutenConnector\Domains\Requests\DirectPayment\CreditCard;

use RakutenConnector\Helpers\InitializeObject;

/**
 * Trait Holder
 * @package RakutenConnector\Domains\Requests\DirectPayment\CreditCard
 */
trait Holder
{
    /**
     * @var
     */
    private $holder;

    /**
     * @return \RakutenConnector\Domains\Requests\Adapter\DirectPayment\Holder
     */
    public function setHolder()
    {
        $this->holder = InitializeObject::initialize(
            $this->holder,
            '\RakutenConnector\Domains\DirectPayment\CreditCard\Holder'
        );
        return new \RakutenConnector\Domains\Requests\Adapter\DirectPayment\Holder($this->holder);
    }

    /**
     * @return \RakutenConnector\Domains\DirectPayment\CreditCard\Holder
     */
    public function getHolder()
    {
        return $this->holder;
    }
}