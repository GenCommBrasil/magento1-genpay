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

namespace Rakuten\Connector\Resources;

use Rakuten\Connector\Enum\Http\Status;
use Rakuten\Connector\Exception\ConnectorException;
use Rakuten\Connector\Resources\Responsibility\Configuration\Environment;
use Rakuten\Connector\Resources\Responsibility\Configuration\Extensible;
use Rakuten\Connector\Resources\Responsibility\Configuration\File;
use Rakuten\Connector\Resources\Responsibility\Configuration\Wrapper;

/**
 * Class Responsibility
 * @package Rakuten\Connector\Resources
 */
class Responsibility
{
    public static function http($http, $class)
    {
        switch ($http->getStatus()) {
            case Status::OK:
                return $class::success($http);
            case Status::UNPROCESSABLE:
                /** returns success because only a few parameters  */
                return $class::success($http);
            case Status::BAD_REQUEST:
                /** returns success because only a few parameters  */
                return $class::success($http);
            case Status::NOT_FOUND:
                $error = $class::error($http);
                throw new ConnectorException($error->getMessage(), $error->getCode());
            case Status::UNAUTHORIZED:
                $error = $class::error($http);
                throw new ConnectorException($error->getMessage(), $error->getCode());
            case Status::BAD_GATEWAY:
                $error = $class::error($http);
                throw new ConnectorException($error->getMessage(), $error->getCode());
            default:
                unset($class);
                throw new ConnectorException($http->getResponse(), $http->getStatus());
        }
    }

    public static function configuration()
    {
        $environment = new Environment;
        $extensible = new Extensible;
        $file = new File;
        $wrapper = new Wrapper;

        $wrapper->successor(
            $environment->successor(
                $file->successor(
                    $extensible
                )
            )
        );
        return $wrapper->handler(null, null);
    }
}
