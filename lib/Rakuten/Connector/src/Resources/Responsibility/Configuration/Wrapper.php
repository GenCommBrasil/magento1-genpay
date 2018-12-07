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

namespace Rakuten\Connector\Resources\Responsibility\Configuration;

use Rakuten\Connector\Resources\Responsibility\Handler;

/**
 * Class Wrapper
 * @package Rakuten\Connector\Resources\Responsibility\Configuration
 */
class Wrapper implements Handler
{
    private $successor;

    public function successor($next)
    {
        $this->successor = $next;
        return $this;
    }

    public function handler($action, $class)
    {
        if (class_exists('Rakuten\Connector\Configuration\Wrapper')) {
            $configWrapper = new \Rakuten\Connector\Configuration\Wrapper;
            return array_merge(
                \Rakuten\Connector\Helpers\Wrapper::environment($configWrapper),
                \Rakuten\Connector\Helpers\Wrapper::credentials($configWrapper),
                \Rakuten\Connector\Helpers\Wrapper::charset($configWrapper)
            );
        }
        return $this->successor->handler($action, $class);
    }
}
