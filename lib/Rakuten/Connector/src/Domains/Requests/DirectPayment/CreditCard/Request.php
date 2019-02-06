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

namespace Rakuten\Connector\Domains\Requests\DirectPayment\CreditCard;

use Rakuten\Connector\Domains\Requests\BasicData;
use Rakuten\Connector\Domains\Requests\Commissioning;
use Rakuten\Connector\Domains\Requests\Discount;
use Rakuten\Connector\Domains\Requests\Tax;
use Rakuten\Connector\Domains\Requests\DirectPayment\Mode;
use Rakuten\Connector\Domains\Requests\DirectPayment\Sender;
use Rakuten\Connector\Domains\Requests\Item;
use Rakuten\Connector\Domains\Requests\Notification;
use Rakuten\Connector\Domains\Requests\ReceiverEmail;
use Rakuten\Connector\Domains\Requests\Redirect;
use Rakuten\Connector\Domains\Requests\Reference;
use Rakuten\Connector\Domains\Requests\Requests;
use Rakuten\Connector\Domains\Requests\Shipping;

/**
 * Class Request
 * @package Rakuten\Connector\Domains\Requests\DirectPayment\CreditCard
 */
class Request implements Requests
{
    use Billing;
    use BasicData;
    use Commissioning;
    use Discount;
    use Installment;
    use Holder;
    use Item;
    use Mode;
    use Notification {
        Notification::getUrl as getNotificationUrl;
        Notification::setUrl as setNotificationUrl;
        Notification::getUrl insteadof Redirect;
        Notification::setUrl insteadof Redirect;
    }
    use ReceiverEmail;
    use Sender;
    use Shipping;
    use Tax;
    use Reference;
    use Redirect {
        Redirect::getUrl as getRedirectUrl;
        Redirect::setUrl as setRedirectUrl;
    }
    use CreditCard;
}
