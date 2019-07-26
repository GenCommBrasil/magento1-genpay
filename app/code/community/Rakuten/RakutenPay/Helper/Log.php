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
 * Class Rakuten_RakutenPay_Helper_Log
 */
class Rakuten_RakutenPay_Helper_Log
{
    /**
     * @param $class
     *
     * @return null|string
     */
    private function setModule($class)
    {
        $module = null;
        $option = explode('_', $class);
        $module = 'RakutenPay'.end($option).'.';

        return $module;
    }

    /**
     * @param $class
     * @param $orderId
     * @param $transactionCode
     * @param $orderState
     */
    public function setUpdateOrderLog($class, $orderId, $transactionCode, $orderState)
    {
        $phrase = "Update( OrderStateMagento: array (\n 'orderId' => ".$orderId.",\n ";
        $phrase .= "'transactionCode' => '".$transactionCode."',\n ";
        $phrase .= "'orderState' => '".$orderState."'\n ) )";
        \Rakuten\Connector\Resources\Log\Logger::info($phrase, ['service' => $this->setModule($class)]);
    }
}
