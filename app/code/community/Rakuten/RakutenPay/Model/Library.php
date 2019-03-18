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

/**
 * Class Rakuten_RakutenPay_Model_Library
 */
class Rakuten_RakutenPay_Model_Library
{
    /**
     * Rakuten_RakutenPay_Model_Library constructor.
     */
    public function __construct()
    {
        defined("SHIPPING_TYPE") or define("SHIPPING_TYPE", 3);
        defined("SHIPPING_COST") or define("SHIPPING_COST", 0.00);
        defined("CURRENCY") or define("CURRENCY", "BRL");
        \Rakuten\Connector\Library::initialize();
        \Rakuten\Connector\Library::cmsVersion()->setName('Magento')->setRelease(Mage::getVersion());
        \Rakuten\Connector\Library::moduleVersion()->setName('RakutenPay')->setRelease(Mage::getConfig()->getModuleConfig("Rakuten_RakutenPay")->version);
        \Rakuten\Connector\Configuration\Configure::setCharset('UTF-8');
        $this->setCharset();
        $this->setEnvironment();
    }

    /**
     * @void
     */
    private function setCharset()
    {
        \Rakuten\Connector\Configuration\Configure::setCharset('UTF-8');
    }

    /**
     * @void
     */
    private function setEnvironment()
    {
        \Rakuten\Connector\Configuration\Configure::setEnvironment(Mage::getStoreConfig('payment/rakutenpay/environment'));
    }

    /**
     * @return mixed
     */
    public function getCharset()
    {
        return 'UTF-8';
    }

    /**
     * @return mixed
     */
    public function getEnvironment()
    {
        return Mage::getStoreConfig('payment/rakutenpay/environment');
    }

    /**
     * @return mixed
     */
    public function getLog()
    {
        return Mage::getStoreConfig('payment/rakutenpay/log');
    }

    /**
     * @return mixed
     */
    public function getPaymentCheckoutType()
    {
        return Mage::getStoreConfig('payment/rakutenpay_default_lightbox/checkout');
    }
}
