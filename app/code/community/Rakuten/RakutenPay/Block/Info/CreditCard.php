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
 * Class Rakuten_RakutenPay_Block_Info_CreditCard
 *
 * Info block for credit card payment
 */
class Rakuten_RakutenPay_Block_Info_CreditCard extends Mage_Payment_Block_Info
{

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('rakuten/rakutenpay/info/credit_card.phtml');
    }

    public function getCreditCardNum()
    {
        if (empty($this->getInfo())) {

            return null;
        }

        return $this->getInfo()->getCcNumberEnc();
    }

    public function getBrand()
    {
        if (empty($this->getInfo())) {

            return null;
        }

        return strtoupper($this->getInfo()->getCcType());
    }

    public function getInstallments()
    {
        if (empty($this->getInfo())) {

            return null;
        }

        return $this->getInfo()->getAdditionalInformation('installments');
    }

    public function getApprovedDate()
    {
        if (empty($this->getInfo())) {

            return null;
        }

        $date = $this->getInfo()->getAdditionalInformation('approved_date');
        if (empty($date)) {

            return null;
        }
        $date = new \DateTime($date);

        return $date->format("d/m/Y H:i:s");
    }

    public function getLabelUrl()
    {
        if (empty($this->getInfo())) {

            return null;
        }

        return $this->getInfo()->getAdditionalInformation('print_url');
    }

    public function getTrackingUrl()
    {
        if (empty($this->getInfo())) {
            return null;
        }

        return $this->getInfo()->getAdditionalInformation('tracking_url');
    }

    public function getDashboardLink()
    {
        $environment = Mage::getStoreConfig('payment/rakutenpay/environment');
        if (empty($this->getInfo())) {
            return null;
        }

        return \Rakuten\Connector\Enum\DirectPayment\Link::getDashboardLink($environment) . $this->getInfo()->getAdditionalInformation('rakutenpay_id');
    }
}
