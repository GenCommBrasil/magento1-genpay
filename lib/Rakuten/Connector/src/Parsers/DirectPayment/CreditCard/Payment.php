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

namespace Rakuten\Connector\Parsers\DirectPayment\CreditCard;

use Rakuten\Connector\Helpers\Characters;
use Rakuten\Connector\Resources\Log\Logger;
use Rakuten\Connector\Enum\DirectPayment\Method;

/**
 * Trait Payment
 * @package Rakuten\Connector\Parsers\DirectPayment\CreditCard
 */
trait Payment
{
    /**
     * @param $request
     * @param $properties
     * @return array
     */
    public static function getData($request, $properties)
    {
        Logger::info('Processing getData in trait Payment.');
        $data = [];
        $payment_array = [];
        $payment_values = [];
        $options = [];

        //method
        $payment_values[$properties::DIRECT_PAYMENT_METHOD] = Method::CREDIT_CARD;
        //amount
        if (method_exists($request, 'getTotal') and ! is_null($request->getTotal())) {
            $payment_values[$properties::AMOUNT] = floatval($request->getTotal());
        }
        //installments
        if (!is_null(current($request->getInstallment())->getQuantity())) {
            $payment_values[$properties::INSTALLMENT_QUANTITY] = intval(current($request->getInstallment())->getQuantity());
        }
        if (!is_null(current($request->getInstallment())->getInterestAmount()) && 0.00 != (float) current($request->getInstallment())->getInterestAmount()){
            $installment = [];
            $current_installment = current($request->getInstallment());
            $installment[$properties::QUANTITY] = intval($current_installment->getQuantity());
            $installment[$properties::INTEREST_PERCENT] = floatval($current_installment->getInterestPercent());
            $installment[$properties::INTEREST_AMOUNT] = floatval($current_installment->getInterestAmount());
            $installment[$properties::INSTALLMENT_AMOUNT] = floatval($current_installment->getValue());
            $installment[$properties::TOTAL]= floatval($current_installment->getTotalValue());

            $payment_values[$properties::INSTALLMENTS] = $installment;
        }
        //brand
        if (!is_null($request->getBrand())) {
            $payment_values[$properties::BRAND] = $request->getBrand();
        }
        //token
        if (!is_null($request->getToken())) {
            $payment_values[$properties::CREDIT_CARD_TOKEN] = $request->getToken();
        }
        //cvv
        if (!is_null($request->getCvv())) {
            $payment_values[$properties::CVV] = $request->getCvv();
        }
        //holder_name
        if (!is_null($request->getHolder()) and !is_null($request->getHolder()->getName())) {
            $payment_values[$properties::CREDIT_CARD_HOLDER_NAME] = $request->getHolder()->getName();
        }
        //holder_document
        if (!is_null($request->getHolder()->getDocuments()) and ! is_null($request->getHolder()->getDocuments())) {
            $payment_values[$properties::CREDIT_CARD_HOLDER_CPF] = Characters::hasSpecialChars($request->getHolder()->getDocuments()->getIdentifier());
        }

        //options
        $options[$properties::NEW_CARD] = false;
        $options[$properties::SAVE_CARD] = false;
        $options[$properties::RECURRENCY] = false;
        $payment_values[$properties::OPTIONS] = $options;

        $payment_array[] = $payment_values;
        $data[$properties::PAYMENTS] = $payment_array;

        return $data;
    }
}
