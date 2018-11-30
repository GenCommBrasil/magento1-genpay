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

namespace Rakuten\Connector\Enum\DirectPayment;

use Rakuten\Connector\Enum\Enum;

/**
 * Class Status
 * @package Rakuten\Connector\Enum\DirectPayment
 */
class Status extends Enum
{
    const PENDING = "pending";
    const AUTHORIZED = "em_analise_rp";
    const APPROVED = "paga_rp";
    const COMPLETED = "disponivel_rp";
    const CHARGEBACK = "devolvida_rp";
    const CANCELLED = "cancelada_rp";
    const REFUNDED = "chargeback_debitado_rp";
    const PARTIAL_REFUNDED = "chargeback_parcial_debitado_rp";
}
