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

namespace Rakuten\Connector;

use Rakuten\Connector\Exception\ConnectorException;
use Rakuten\Connector\Helpers\Validate;
use Rakuten\Connector\Resources\Framework\ContentManagementSystems;
use Rakuten\Connector\Resources\Framework\Language;
use Rakuten\Connector\Resources\Framework\Module;

/**
 * Class Library
 * @package Rakuten\Connector
 */
class Library
{

    /**
     *
     */
    const VERSION = "3.0.0";
    /**
     * @var
     */
    private static $module;
    /**
     * @var
     */
    private static $cms;

    /**
     * @throws \Exception
     */
    final public static function initialize()
    {
        //Basic configuration
        if (!defined('RP_BASEPATH')) {
            define('RP_BASEPATH', __DIR__);
        }
        if (!defined('RP_CONFIG_PATH')) {
            define('RP_CONFIG_PATH', RP_BASEPATH . "/Configuration/");
        }
        if (!defined('RP_CONFIG')) {
            define('RP_CONFIG', RP_CONFIG_PATH . "Properties/Conf.xml");
        }
        if (!defined('RP_RESOURCES')) {
            define('RP_RESOURCES', RP_CONFIG_PATH . "Properties/Resources.xml");
        }
        //Validates for cUrl and SimpleXml.
        self::validate();
        //Garbage Collection
        gc_enable();
    }

    /**
     * @return bool
     * @throws \Exception
     */
    final public static function validate()
    {
        try {
            Validate::cUrl();
            Validate::simpleXml();
            return true;
        } catch (ConnectorException $exception) {
            throw new ConnectorException(
                "RakutenPay Library PHP component exception",
                ['PSLE'],
                $exception
            );
        }
    }

    /**
     * @return string
     */
    final public static function libraryVersion()
    {
        return self::VERSION;
    }

    /**
     * @return string
     */
    final public static function phpVersion()
    {
        return (new Language)->getRelease();
    }

    /**
     * @return Module
     */
    public static function moduleVersion()
    {
        if (is_null(self::$module)) {
            return self::$module = new Module();
        }
        return self::$module;
    }

    /**
     * @return ContentManagementSystems
     */
    public static function cmsVersion()
    {
        if (is_null(self::$cms)) {
            return self::$cms = new ContentManagementSystems();
        }
        return self::$cms;
    }
}
