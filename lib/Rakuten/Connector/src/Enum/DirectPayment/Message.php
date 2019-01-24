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

namespace Rakuten\Connector\Enum\DirectPayment;

use Rakuten\Connector\Enum\Enum;

/**
 * Class Message
 * @package Rakuten\Connector\Enum\DirectPayment
 */
class Message extends Enum
{
    const SUCCESS = 'success';
    const DECLINED = 'declined';
    const FAILURE = 'failure';

    /**
     * @var array
     *
     */
    private static $mappingMessages = [
        self::DECLINED => 'Sua compra foi cancelada, Cartão de Crédito não foi Autorizado. Para mais informações entre em contato com a loja.',
        ];

    /**
     * @param $result
     * @return mixed
     */
    public static function getMessages($result)
    {
        return (isset(self::$mappingMessages[$result]) ? self::$mappingMessages[$result] : $result);
    }
}
