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
 * Class Document
 * @package Rakuten\Connector\Helpers
 */
class Document
{
    /**
     * Format original document and return it as an array, with it "washed" value
     * and type
     * @param string $document
     * @return array
     * @throws ConnectorException
     */
    public static function formatDocument($document)
    {
        Logger::info('Processing formatDocument.');
        $documentNumbers = preg_replace('/[^0-9]/', '', $document);
        switch (strlen($documentNumbers)) {
            case 14:
                return ['number' => $documentNumbers, 'type' => 'CNPJ'];
                break;
            case 11:
                return ['number' => $documentNumbers, 'type' => 'CPF'];
                break;
            default:
                Logger::error('Invalid document');
                throw new ConnectorException('Invalid document');
                break;
        }
    }
}
