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

namespace Rakuten\Connector\Domains\Requests;

/**
 * Trait Discount
 * @package Rakuten\Connector\Domains\Requests
 */
trait Discount
{
    /**
     * @var float
     */
    private $discountAmount;

    /**
     * @return float
     */
    public function getDiscountAmount()
    {
        return $this->discountAmount;
    }

    /**
     * @param $discountAmount
     */
    public function setDiscountAmount($discountAmount)
    {
        $discountAmount = number_format(abs($discountAmount), 2, ".", "");
        $this->discountAmount = (float) $discountAmount;
    }
}
