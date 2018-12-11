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

use Mage;
use Rakuten\Connector\Exception\ConnectorException;
use Rakuten\Connector\Resources\Log\Logger;

/**
 * Class EnvironmentInformation
 * @package Rakuten\Connector\Helpers
 */
class EnvironmentInformation
{
    const PHPINFO_INFO_CONFIGURATION = 'INFO_CONFIGURATION';

    /**
     * @return string
     */
    public static function getPHPVersion()
    {
        return phpversion();
    }

    /**
     * @return string
     */
    public static function getMagentoVersion()
    {
        return Mage::getVersion();
    }

    /**
     * @return string
     */
    public static function getRakutenPayVersion()
    {
        return Mage::getConfig()->getModuleConfig("Rakuten_RakutenPay")->version;
    }

    /**
     * @return string
     */
    public static function getRakutenLogisticsVersion()
    {
        return Mage::getConfig()->getModuleConfig("Rakuten_RakutenLogistics")->version;
    }

    /**
     * @see http://www.php.net/manual/en/function.phpinfo.php#87463
     *
     * @param null $constant
     * @return array
     */
    public static function phpinfoToArray($constant = null)
    {
        $infoArray = [];
        try {
            ob_start();
            if (is_null($constant)) {
                phpinfo();
            } else {
                phpinfo(constant($constant));
            }
            $infoArray = array();
            $infoLines = explode("\n", strip_tags(ob_get_clean(), "<tr><td><h2>"));
            $cat = "General";
            foreach ($infoLines as $line) {
                // new cat?
                preg_match("~<h2>(.*)</h2>~", $line, $title) ? $cat = $title[1] : null;
                if (preg_match("~<tr><td[^>]+>([^<]*)</td><td[^>]+>([^<]*)</td></tr>~", $line, $val)) {
                    $infoArray[$cat][$val[1]] = $val[2];
                } elseif (preg_match("~<tr><td[^>]+>([^<]*)</td><td[^>]+>([^<]*)</td><td[^>]+>([^<]*)</td></tr>~", $line, $val)) {
                    $infoArray[$cat][$val[1]] = array("local" => $val[2], "master" => $val[3]);
                }
            }

            return $infoArray;
        } catch (ConnectorException $e) {
            Logger::error('Error in phpinfoToArray in EnvironmentInformation: ' . $e->getMessage());
            return $infoArray;
        }
    }
}