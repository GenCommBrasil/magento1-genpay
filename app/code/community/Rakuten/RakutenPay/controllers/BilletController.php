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
 * Class Rakuten_RakutenPay_BilletController
 */
class Rakuten_RakutenPay_BilletController extends Mage_Core_Controller_Front_Action
{
    /**
     * @return array
     * @throws ErrorException
     */
    public function getBilletAction()
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing getBilletAction.');
        $billet = $this->getRequest()->getPost('data');
        if (!empty($billet)) {
            $http = new \Rakuten\Connector\Resources\Http\RakutenPay\Http();
            $http->get($billet, 20, 'ISO-8859-1', false);
            $response = \Rakuten\Connector\Resources\Responsibility::http($http, $this->getBillet());
            $this->getResponse()->clearHeaders()->setHeader('Content-type',"text/html; charset=utf-8");
            $this->getResponse()->setBody($response);
        } else {
            \Rakuten\Connector\Resources\Log\Logger::error(sprintf('getBilletAction: Billet is: %s', $billet));
        }
    }
    /**
     * @return \Rakuten\Connector\Parsers\DirectPayment\Boleto\Billet
     */
    private function getBillet()
    {
        return new \Rakuten\Connector\Parsers\DirectPayment\Boleto\Billet();
    }
}