<?php
/**
 ************************************************************************
 * Copyright [2017] [RakutenConnector]
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

namespace RakutenConnector\Domains\Requests\Adapter\DirectPayment;

use RakutenConnector\Domains\Requests\Sender\Customer;
use RakutenConnector\Domains\Requests\Sender\Document;
use RakutenConnector\Domains\Requests\Sender\Hash;
use RakutenConnector\Domains\Requests\Sender\Ip;
use RakutenConnector\Domains\Requests\Sender\Phone;

/**
 * Class Sender
 * @package RakutenConnector\Domains\Requests\Adapter\DirectPayment
 */
class Sender
{
    use Customer;
    use Document;
    use Hash;
    use Ip;
    use Phone;

    private $sender;

    /**
     * Sender constructor.
     * @param $sender
     */
    public function __construct($sender)
    {
        $this->sender = $sender;
    }
}
