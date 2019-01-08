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

namespace Rakuten\Connector\Resources\Log;

use Rakuten\Connector\Enum\Log\Level;
use Rakuten\Connector\Exception\ConnectorException;
use Mage;

/**
 * It simply delegates all log-level-specific methods to the `log` method to
 * reduce boilerplate code that a simple Logger that does the same thing with
 * messages regardless of the error level has to implement.
 */
class Logger implements LoggerInterface
{

    const DEFAULT_FILE = "rakuten.log";

    /**
     * @var string
     */
    private static $logString = "";

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array  $context
     *
     * @return null
     */
    public static function emergency($message, array $context = array())
    {
        self::log(Level::EMERGENCY, $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array  $context
     *
     * @return null
     */
    public static function alert($message, array $context = array())
    {
        self::log(Level::ALERT, $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array  $context
     *
     * @return null
     */
    public static function critical($message, array $context = array())
    {
        self::log(Level::CRITICAL, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array  $context
     *
     * @return null
     */
    public static function error($message, array $context = array())
    {
        self::log(Level::ERROR, $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array  $context
     *
     * @return null
     */
    public static function warning($message, array $context = array())
    {
        self::log(Level::WARNING, $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array  $context
     *
     * @return null
     */
    public static function notice($message, array $context = array())
    {
        self::log(Level::NOTICE, $message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array  $context
     *
     * @return null
     */
    public static function info($message, array $context = array())
    {
        self::log(Level::INFO, $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array  $context
     *
     * @return null
     */
    public static function debug($message, array $context = array())
    {
        self::log(Level::DEBUG, $message, $context);
    }

    /**
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return bool|null
     * @throws ConnectorException
     */
    public static function log($level, $message, array $context = array())
    {
        try {
            self::write(self::location(), self::message($level, $message, $context));
        } catch (ConnectorException $exception) {
            throw $exception;
        }
    }

    /**
     * @return string
     */
    public static function getContent()
    {
        return self::$logString;
    }

    /**
     * Verify if has a location in configuration file
     * @return string
     */
    public static function location()
    {
        $logDir = Mage::getBaseDir('var') . DIRECTORY_SEPARATOR . 'log';
        $logFile = $logDir . DIRECTORY_SEPARATOR . self::DEFAULT_FILE;

        return $logFile;
    }

    /**
     * Make a message
     * @param $level
     * @param $message
     * @param array $context
     * @return string
     */
    private static function message($level, $message, array $context = array())
    {

        $dateTime = new \DateTime('NOW');
        return sprintf(
            "\n%1s RakutenConnector.%s[%1s]: %s", //"%1sRakutenConnector.%2s[%3s]: %4s"
            $dateTime->format("d/m/Y H:i:s"),
            !array_key_exists("service", $context)? '' :sprintf("%1s", $context['service']),
            $level,
            $message
        );
    }

    /**
     * Write in file
     * @param $file
     * @param $message
     * @throws ConnectorException
     */
    private static function write($file, $message)
    {
        self::$logString .= $message;
        if (self::isFileWrite()) {
            $isWrite = file_put_contents($file, $message, FILE_APPEND | LOCK_EX);
            if (false === $isWrite) {
                throw new ConnectorException('Error: Could not write to log.');
            }
        }
    }

    /**
     * @return bool
     */
    private static function isFileWrite()
    {
        $fileWrite = (int) \Mage::getConfig()->getNode('default/log/file_write/active');
        if ($fileWrite == 1) {
            return true;
        }

        return false;
    }
}
