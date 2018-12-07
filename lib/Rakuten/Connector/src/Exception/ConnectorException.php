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
    // Redefine a exceção de forma que a mensagem não seja opcional
    public function __construct($message, $code = 0, \Exception $previous = null) {
        // código

        // garante que tudo está corretamente inicializado
        parent::__construct($message, $code, $previous);
    }

    public function __toString()
    {
//        $teste = ob_get_contents();
//        ob_end_clean();
//        $file = \Rakuten\Connector\Configuration\Configure::getLog()->getLocation() . '/' . Logger::DEFAULT_FILE;
//        $isWrite = file_put_contents($file, $teste, FILE_APPEND | LOCK_EX);
//        return $teste;

    }
}
