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

class Rakuten_RakutenPay_Model_Quote_Address_Total_Rakutenfee extends Mage_Sales_Model_Quote_Address_Total_Abstract 
{
    protected $_code = 'rakutenfee';

    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);

        $this->_setAmount(0);
        $this->_setBaseAmount(0);

        $items = $this->_getAddressItems($address);
        if (!count($items)) {
            return $this;
        }

        $quote = $address->getQuote();
        $post = Mage::app()->getRequest()->getPost();

        if (isset($post['payment']['credit_card_interest_amount'])) {
            $interestAmount = (float) $post['payment']['credit_card_interest_amount'];

            $address->setRakutenfeeAmount($interestAmount);
            $address->setBaseRakutenfeeAmount($interestAmount);
            $quote->setRakutenfeeAmount($interestAmount);
            $quote->setBaseRakutenfeeAmount($interestAmount);
        }

        $address->setGrandTotal($address->getGrandTotal() + $address->getRakutenfeeAmount());
        $address->setBaseGrandTotal($address->getBaseGrandTotal() + $address->getBaseRakutenfeeAmount());

        return $this;
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $amount = $address->getRakutenfeeAmount();
        if (!$amount) {
            return $this;
        }
        $address->addTotal(array(
            'code' => $this->getCode(),
            'title' => "Taxa de Juros",
            'value'=> $amount,
        ));
        return $this;
    }
}