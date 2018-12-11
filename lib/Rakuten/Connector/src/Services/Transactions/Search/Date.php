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

namespace Rakuten\Connector\Services\Transactions\Search;

use Rakuten\Connector\Enum\Properties\Current;
use Rakuten\Connector\Parsers\Transaction\Search\Date\Request;
use Rakuten\Connector\Resources\Connection;
use Rakuten\Connector\Resources\Http\RakutenPay\Http;
use Rakuten\Connector\Resources\Log\Logger;
use Rakuten\Connector\Resources\Responsibility;
use Rakuten\Connector\Exception\ConnectorException;

/**
 * Class Date
 * @package Rakuten\Connector\Services\Transactions\Search
 */
class Date
{

    /**
     * @param array $options
     * @return mixed
     * @throws ConnectorException
     */
    public static function search(
        array $options
    ) {
        Logger::info("Begin", ['service' => 'Transactions.Search.Date']);
        try {
            $connection = new Connection\Data();
            $http = new Http();
            Logger::info(
                sprintf(
                    "GET: %s",
                    self::request($connection, $options)
                ),
                ['service' => 'Transactions.Search.Date']
            );
            $http->get(
                self::request($connection, $options), 20, 'ISO-8859-1', false
            );

            $response = Responsibility::http(
                $http,
                new Request
            );

            Logger::info(
                sprintf(
                    "Date: %s, Results in this page: %s, Total pages: %s",
                    $response->getDate(),
                    $response->getResultsInThisPage(),
                    $response->getTotalPages()
                ),
                ['service' => 'Transactions.Search.Date']
            );
            return $response;
        } catch (ConnectorException $exception) {
            Logger::error($exception->getMessage(), ['service' => 'Transactions.Search.Date']);
            throw $exception;
        }
    }

    /**
     * @param Connection\Data $connection
     * @param $params
     * @return string
     */
    private static function request(Connection\Data $connection, $params)
    {
        return sprintf(
            "%s/?%s%s%s",
            $connection->buildTransactionSearchRequestUrl(),
            sprintf("%s=%s", Current::SEARCH_INITIAL_DATE, $params["initial_date"]),
            !isset($params["final_date"]) ? '' : sprintf("&%s=%s", Current::SEARCH_FINAL_DATE, $params["final_date"]),
            !isset($params["page"]) ? '' : sprintf("&%s=%s", Current::SEARCH_PAGE, $params["page"])
        );
    }
}
