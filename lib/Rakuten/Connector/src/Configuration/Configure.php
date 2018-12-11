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

namespace Rakuten\Connector\Configuration;

use Rakuten\Connector\Domains\AccountCredentials;
use Rakuten\Connector\Domains\Charset;
use Rakuten\Connector\Domains\Environment;
use Rakuten\Connector\Resources\Responsibility;

/**
 * Class Configure
 * @package Rakuten\Connector\Configuration
 */
class Configure
{
    private static $accountCredentials;
    private static $charset;
    private static $environment;

    /**
     * @return AccountCredentials
     */
    public static function getAccountCredentials()
    {
        if (! isset(self::$accountCredentials)) {
            $configuration = Responsibility::configuration();
            self::setAccountCredentials(
                $configuration['credentials']['cnpj'],
                $configuration['credentials']['api_key']['environment'][$configuration['environment']],
                $configuration['credentials']['signature_key']['environment'][$configuration['environment']]
            );
        }

        return self::$accountCredentials;
    }

    /**
     * @param string $email
     * @param string $token
     */
    public static function setAccountCredentials($cnpj, $api_key, $signature_key)
    {
        self::$accountCredentials = new AccountCredentials;
        self::$accountCredentials->setCnpj($cnpj)
            ->setApiKey($api_key)
            ->setSignatureKey($signature_key);
    }

    /**
     * @return Environment
     */
    public static function getEnvironment()
    {
        if (! isset(self::$environment)) {
            $configuration = Responsibility::configuration();
            self::setEnvironment($configuration['environment']);
        }
        return self::$environment;
    }

    /**
     * @param string $environment
     */
    public static function setEnvironment($environment)
    {
        self::$environment = new Environment;
        self::$environment->setEnvironment($environment);
    }

    /**
     * @return Charset
     */
    public static function getCharset()
    {
        if (! isset(self::$charset)) {
            $configuration = Responsibility::configuration();
            self::setCharset($configuration['charset']);
        }
        return self::$charset;
    }

    /**
     * @param string $charset
     */
    public static function setCharset($charset)
    {
        self::$charset = new Charset;
        self::$charset->setEncoding($charset);
    }
}
