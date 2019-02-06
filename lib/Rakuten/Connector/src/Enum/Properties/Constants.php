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

namespace Rakuten\Connector\Enum\Properties;

/**
 * Class Constants
 * @package Rakuten\Connector\Enum\Properties
 */
class Constants
{

    /**
     * Shipping address street
     */
    const BILLING_ADDRESS_STREET = "billingAddress.street";

    /**
     * Shipping address number
     */
    const BILLING_ADDRESS_NUMBER = "billingAddress.number";

    /**
     * Shipping address complement
     */
    const BILLING_ADDRESS_COMPLEMENT = "billingAddress.complement";

    /**
     *  Shipping address city
     */
    const BILLING_ADDRESS_CITY = "billingAddress.city";

    /**
     *  Shipping address state
     */
    const BILLING_ADDRESS_STATE = "billingAddress.state";

    /**
     *  Shipping address district
     */
    const BILLING_ADDRESS_DISTRICT = "billingAddress.district";

    /**
     * Shipping address postal code
     */
    const BILLING_ADDRESS_POSTAL_CODE = "billingAddress.postalCode";

    /**
     *  Shipping address country
     */
    const BILLING_ADDRESS_COUNTRY = "billingAddress.country";

    /**
     *  Currency
     */
    const CURRENCY = "currency";

    /**
     *  Extra amount
     */
    const CURRENCY_EXTRA_AMOUNT = "extraAmount";

    /**
     * Credit card holder name for credit card direct payment
     */
    const CREDIT_CARD_HOLDER_NAME = 'holder_name';

    /**
     * Credit card holder birth date for credit card direct payment
     */
    const CREDIT_CARD_HOLDER_BIRTH_DATE = 'creditCard.holder.birthDate';

    /**
     * Credit card holder cpf for credit card direct payment
     */
    const CREDIT_CARD_HOLDER_CPF = 'holder_document';

    /**
     * Credit card holder area code for credit card direct payment
     */
    const CREDIT_CARD_HOLDER_AREA_CODE = 'creditCard.holder.areaCode';

    /**
     * Credit card holder phone for credit card direct payment
     */
    const CREDIT_CARD_HOLDER_PHONE = 'creditCard.holder.phone';

    /**
     * Credit card token for credit card direct payment
     */
    const CREDIT_CARD_TOKEN = "token";

    /**
     * Credit card token for credit card direct payment
     */
    const CREDIT_CARD_BRAND= "brand";

    /**
     * Credit card token for credit card direct payment
     */
    const CREDIT_CARD_CVV= "cvv";

    /**
     *  Payment mode
     */
    const DIRECT_PAYMENT_MODE = "method";

    /**
     *  Payment method
     */
    const DIRECT_PAYMENT_METHOD = "method";

    /**
     * Installment quantity for credit card payment
     */
    const INSTALLMENT_QUANTITY = "installments_quantity";

    /**
     * Installment value for credit card payment
     */
    const INSTALLMENT_VALUE = "installment.value";

    /**
     * Installment no interest installment quantity for credit card payment
     */
    const INSTALLMENT_NO_INTEREST_INSTALLMENT_QUANTITY = "installment.noInterestInstallmentQuantity";

    /**
     *  Item id
     */
    const ITEM_ID = "item[%s].id";

    /**
     *  Item description
     */
    const ITEM_DESCRIPTION = "item[%s].description";

    /**
     *  Item amount
     */
    const ITEM_AMOUNT = "item[%s].amount";

    /**
     *  Item quantity
     */
    const ITEM_QUANTITY = "item[%s].quantity";

    /**
     * Item weight
     */
    const ITEM_WEIGHT = "item[%s].weight";

    /**
     *  Notification URL
     */
    const NOTIFICATION_URL = "webhook_url";

    /**
     *  Bank name
     */
    const ONLINE_DEBIT_BANK_NAME = "bank.name";

    /**
     * Receiver email
     */
    const RECEIVER_EMAIL = 'receiver.email';

    /**
     *  Receiver public key
     */
    const RECEIVER_PUBLIC_KEY = "receiver[%s].publicKey";

    /**
     *  Receiver split amount
     */
    const RECEIVER_SPLIT_AMOUNT = "receiver[%s].split.amount";

    /**
     *  Receiver split rate percent
     */
    const RECEIVER_SPLIT_RATE_PERCENT = "receiver[%s].split.ratePercent";

    /**
     *  Receiver split fee percent
     */
    const RECEIVER_SPLIT_FEE_PERCENT = "receiver[%s].split.feePercent";

    /**
     * Redirect Url
     */
    const REDIRECT_URL = "redirectURL";

    /**
     *  Reference
     */
    const REFERENCE = "reference";

    /**
     * Order tag
     */
    const ORDER_TAG = 'Pedido#';

    /**
     * Sender name
     */
    const SENDER_NAME = "sender.name";

    /**
     * Sender email
     */
    const SENDER_EMAIL = "email";

    /**
     * Sender hash
     */
    const SENDER_HASH = "sender.hash";

    /**
     * Sender ip number
     */
    const SENDER_IP = "sender.ip";

    /**
     *  Sender area code
     */
    const SENDER_PHONE_AREA_CODE = "sender.areaCode";

    /**
     * Sender phone number
     */
    const SENDER_PHONE_NUMBER = "sender.phone";

    /**
     *  Sender CPF
     */
    const SENDER_DOCUMENT_CPF = "sender.CPF";

    /**
     * Sender CNPJ
     */
    const SENDER_DOCUMENT_CNPJ = "sender.CNPJ";

    /**
     * Shipping type
     */
    const SHIPPING_TYPE = "shipping.type";

    /**
     * Shipping cost
     */
    const SHIPPING_COST = "shipping.cost";

    /**
     * Shipping address street
     */
    const SHIPPING_ADDRESS_STREET = "shipping.address.street";

    /**
     * Shipping address number
     */
    const SHIPPING_ADDRESS_NUMBER = "shipping.address.number";

    /**
     * Shipping address complement
     */
    const SHIPPING_ADDRESS_COMPLEMENT = "shipping.address.complement";

    /**
     *  Shipping address city
     */
    const SHIPPING_ADDRESS_CITY = "shipping.address.city";

    /**
     *  Shipping address state
     */
    const SHIPPING_ADDRESS_STATE = "shipping.address.state";

    /**
     *  Shipping address district
     */
    const SHIPPING_ADDRESS_DISTRICT = "shipping.address.district";

    /**
     * Shipping address postal code
     */
    const SHIPPING_ADDRESS_POSTAL_CODE = "shipping.address.postalCode";

    /**
     *  Shipping address country
     */
    const SHIPPING_ADDRESS_COUNTRY = "shipping.address.country";

    /**
     *  Primary Key
     */
    const PRIMARY_RECEIVER_PUBLIC_KEY = "primaryReceiver.publicKey";

    /**
     * Grand total
     */
    const AMOUNT = "amount";

    /**
     * Payment fingerprint
     */
    const FINGERPRINT = "fingerprint";

    /**
     * Payment list
     */
    const PAYMENTS = "payments";

    /**
     * Customer
     */
    const CUSTOMER = "customer";

    /**
     * Document
     */
    const DOCUMENT = "document";

    /**
     * Name
     */
    const NAME = "name";

    /**
     * Birth date
     */
    const BIRTH_DATE = "birth_date";

    /**
     * Payer IP
     */
    const PAYER_IP = "payer_ip";

    /**
     * Remote IP
     */
    const REMOTE_IP = "remote_ip";

    /**
     * Addresses
     */
    const ADDRESSES = "addresses";

    /**
     * Phones
     */
    const PHONES = "phones";

    /**
     * Kind
     */
    const KIND = "kind";

    /**
     * country_code
     */
    const COUNTRY_CODE = "country_code";

    /**
     * area_code
     */
    const AREA_CODE = "area_code";

    /*
     * number
     */
    const PHONE_NUMBER = "number";

    /**
     * Billing
     */
    const BILLING = "billing";

    /**
     * Shipping
     */
    const SHIPPING = "shipping";


    /**
     * street
     */
    const STREET= "street";

    /**
     * number
     */
    const NUMBER= "number";

    /**
     * complement
     */
    const COMPLEMENT= "complement";

    /**
     * district
     */
    const DISTRICT= "district";

    /**
     * city
     */
    const CITY= "city";

    /**
     * state
     */
    const STATE= "state";

    /**
     * zipcode
     */
    const ZIPCODE= "zipcode";

    /**
     * country
     */
    const COUNTRY= "country";

    /**
     * Contact
     */
    const CONTACT = "contact";

    /**
     * Order
     */
    const ORDER = "order";

    /**
     * Items
     */
    const ITEMS = "items";

    /**
     * Shipping amount
     */
    const SHIPPING_AMOUNT = "shipping_amount";

    /**
     * Taxes amount
     */
    const TAXES_AMOUNT = "taxes_amount";

    /**
     * Discount amount
     */
    const DISCOUNT_AMOUNT = "discount_amount";

    /**
     * Total amount
     */
    const TOTAL_AMOUNT = "total_amount";

    /**
     * description
     */
    const DESCRIPTION= "description";

    /**
     * quantity
     */
    const QUANTITY= "quantity";

    /**
     * total
     */
    const TOTAL= "total";

    /**
     * Items amount
     */
    const ITEMS_AMOUNT = "items_amount";

    /**
     * Expires on
     */
    const EXPIRES_ON = "expires_on";

    /**
     * Brand
     */
    const BRAND = "brand";

    /**
     * CVV
     */
    const CVV = "cvv";

    /**
     * ID
     */
    const ID = "id";

    /**
     * Categories
     */
    const CATEGORIES = "categories";

    /**
     * Business Name
     */
    const BUSINESS_NAME = "business_name";

    /**
     * Options
     */
    const OPTIONS = "options";

    /**
     * Save card
     */
    const SAVE_CARD = "save_card";

    /**
     * New card
     */
    const NEW_CARD = "new_card";

    /**
     * Recurrency
     */
    const RECURRENCY = "recurrency";

    /**
     * Interest percent
     */
    const INTEREST_PERCENT = "interest_percent";

    /**
     * Installments object
     */
    const INSTALLMENTS = "installments";

    /**
     * Interest amount
     */
    const INTEREST_AMOUNT = "interest_amount";

    /**
     * Installment amount
     */
    const INSTALLMENT_AMOUNT = "installment_amount";

    /**
     * Commissionings
     */
    const COMMISSIONINGS = "commissionings";

    /**
     * Calculation Code
     */
    const CALCULATION_CODE = "calculation_code";

    /**
     * Postage Service Code
     */
    const POSTAGE_SERVICE_CODE = "postage_service_code";
}
