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

namespace Rakuten\Connector\Exception;

use Rakuten\Connector\Resources\Log\Logger;

/**
 * Class Exception
 * @package Rakuten\Connector\Exception
 */
class ConnectorException extends \Exception
{
    /**
     * ConnectorException constructor.
     * @param $message
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct($message, $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->writeLog();
    }

    /**
     * @return bool|int
     */
    protected function writeLog()
    {
        Logger::error($this->getMessage(), ['service' => 'Exception']);
        $file = Logger::location();
        $isWrite = file_put_contents($file, Logger::getContent(), FILE_APPEND | LOCK_EX);

        return $isWrite;
    }
}
