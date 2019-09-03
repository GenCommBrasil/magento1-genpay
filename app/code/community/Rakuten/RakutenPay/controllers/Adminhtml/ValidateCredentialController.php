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

use Rakuten\Connector\Resources\Log\Logger;

class Rakuten_RakutenPay_AdminHtml_ValidateCredentialController extends Mage_Adminhtml_Controller_Action
{
    public function checkAction()
    {
        Logger::info("Processing checkAction in ValidateCredentialController.");
        $this->getResponse()->clearHeaders()->setHeader(
            'Content-type',
            'application/json'
        );
        $message = 'Credenciais inválidas, verifique e tente novamente.';

        try {
            $document = $this->_request->getParam('taxvat');
            $apiKey = $this->_request->getParam('apiKey');
            $signature = $this->_request->getParam('signature');
            $environment = $this->_request->getParam('environment');

            $response = \Rakuten\Connector\Services\Transactions\Authorization::authorizationValidate($document, $apiKey, $signature, $environment);

            if ($response->getResult() == \Rakuten\Connector\Enum\DirectPayment\Message::SUCCESS) {
                $message = 'Credenciais validadas com sucesso.';
            }

            Logger::info(sprintf("Message: %s", $message));
            $response = [
                'success' => true,
                'message' => $message,
            ];

        } catch (\Exception $e) {
            Logger::error($message);
            $response = [
                'success' => false,
                'message' => $message,
            ];
        }

        $this->getResponse()->setBody(
            Mage::helper('core')->jsonEncode($response)
        );

    }
}
