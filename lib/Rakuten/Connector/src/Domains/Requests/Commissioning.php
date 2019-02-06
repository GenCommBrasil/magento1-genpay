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
 * Trait Commissioning
 * @package Rakuten\Connector\Domains\Requests
 */
trait Commissioning
{
    /**
     * @var string
     */
    private $calculationCode;

    /**
     * @var float
     */
    private $commissioningAmount;

    /**
     * @var string
     */
    private $kind;

    /**
     * @var string
     */
    private $postageServiceCode;

    /**
     * @return float
     */
    public function getCommissioningAmount()
    {
        return $this->commissioningAmount;
    }

    /**
     * @param float $commissioningAmount
     */
    public function setCommissioningAmount($commissioningAmount)
    {
        $this->commissioningAmount = floatval($commissioningAmount);
    }

    /**
     * @return string
     */
    public function getCalculationCode()
    {
        return $this->calculationCode;
    }

    /**
     * @param string $calculationCode
     */
    public function setCalculationCode($calculationCode)
    {
        $this->calculationCode = $calculationCode;
    }

    /**
     * @return string
     */
    public function getKind()
    {
        return $this->kind;
    }

    /**
     * @param string $kind
     */
    public function setKind($kind)
    {
        $this->kind = $kind;
    }

    /**
     * @return string
     */
    public function getPostageServiceCode()
    {
        return $this->postageServiceCode;
    }

    /**
     * @param string $postageServiceCode
     */
    public function setPostageServiceCode($postageServiceCode)
    {
        $this->postageServiceCode = $postageServiceCode;
    }

}
