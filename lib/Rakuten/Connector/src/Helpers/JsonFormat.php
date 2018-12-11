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

use Rakuten\Connector\Exception\ConnectorException;
use Rakuten\Connector\Resources\Log\Logger;

/**
 * Class JsonFormat
 * @package Rakuten\Connector\Helpers
 */
class JsonFormat
{
    /**
     * @var array
     */
    private static $installmentsToFloat = [
        'interest_percent',
        'interest_amount',
        'installment_amount',
        'total',
    ];

    /**
     * Json encode and Check if const exists based from PHP Version
     *
     * @param array $data
     * @return mixed|string
     */
    public static function getJson(array $data)
    {
        Logger::info('Processing getJson in JsonFormat.');
        if (defined('JSON_PRESERVE_ZERO_FRACTION')) {

            return  json_encode($data, JSON_PRESERVE_ZERO_FRACTION);
        }

        /** For PHP Version < 5.6 */
        return self::preserveZeroFractionInstallments($data);
    }

    /**
     * @param array $data
     * @return mixed|string
     */
    private static function preserveZeroFractionInstallments(array $data)
    {
        $jsonData = json_encode($data);
        try {
            $payments = $data['payments'];
            foreach ($payments as $item) {
                if (!array_key_exists('installments', $item)) {
                    break;
                }
                $jsonData = self::installmentsToFloat($item['installments'], $jsonData);
            }

            return $jsonData;
        } catch (ConnectorException $e) {
            Logger::error($e->getMessage(), ['service' => 'json_encode']);

            return $jsonData;
        }
    }

    /**
     * @param array $installments
     * @param $jsonData
     * @return mixed|string
     */
    private static function installmentsToFloat(array $installments, $jsonData)
    {
        Logger::info('Processing installmentsToFloat in JsonFormat.');
        foreach (self::$installmentsToFloat as $field) {
            if (array_key_exists($field, $installments)) {
                $jsonData = str_replace('"' . $field . '":'. $installments[$field], '"' . $field . '":'. number_format($installments[$field], 2, ".", "") . '', $jsonData);
            }
        }

        return $jsonData;
    }
}
