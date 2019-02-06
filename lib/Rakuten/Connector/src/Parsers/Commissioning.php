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

namespace Rakuten\Connector\Parsers;

use Rakuten\Connector\Domains\Requests\Requests;
use Rakuten\Connector\Resources\Log\Logger;

/**
 * Trait Commissioning
 * @package Rakuten\Connector\Parsers
 */
trait Commissioning
{
    /**
     * @param Requests $request
     * @param $properties
     * @return array
     */
    public static function getData(Requests $request, $properties)
    {
        Logger::info('Processing getData in trait Commissionings.');
        $commissionings = [];
        $data = [];
        if (!is_null($request->getKind())) {
            if (!is_null($request->getReference())) {
                $data[$properties::REFERENCE] = $request->getReference();
            }
            $data[$properties::KIND] = $request->getKind();

            if (!is_null($request->getCommissioningAmount())) {
                $data[$properties::AMOUNT] = floatval($request->getCommissioningAmount());
            }

            if (!is_null($request->getCalculationCode())) {
                $data[$properties::CALCULATION_CODE] = $request->getCalculationCode();
            }

            if (!is_null($request->getPostageServiceCode())) {
                $data[$properties::POSTAGE_SERVICE_CODE] = $request->getPostageServiceCode();
            }
            $commissionings[$properties::COMMISSIONINGS][] = $data;
        }

        return $commissionings;
    }
}
