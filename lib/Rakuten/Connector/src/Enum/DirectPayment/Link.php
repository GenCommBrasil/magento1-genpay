<?php

namespace Rakuten\Connector\Enum\DirectPayment;

/**
 * Class Link
 * @package Rakuten\Connector\Enum\DirectPayment
 */
class Link
{
    const DASHBOARD_LINK_PRODUCTION = 'https://dashboard.genpay.com.br/sales/';
    const DASHBOARD_LINK_SANDBOX = 'https://dashboard-sandbox.genpay.com.br/sales/';

    const SANDBOX = 'sandbox';
    const PRODUCTION = 'production';

    /**
     * @var array
     */
    private static $mappingLink = [
        self::SANDBOX => self::DASHBOARD_LINK_SANDBOX,
        self::PRODUCTION => self::DASHBOARD_LINK_PRODUCTION,
    ];

    /**
     * @param $environment
     * @return bool|string
     */
    public static function getDashboardLink($environment)
    {
        return isset(self::$mappingLink[$environment]) ? self::$mappingLink[$environment] : false;
    }
}