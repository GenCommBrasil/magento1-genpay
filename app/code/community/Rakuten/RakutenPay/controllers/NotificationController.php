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
 * Class Rakuten_RakutenPay_NotificationController
 */
class Rakuten_RakutenPay_NotificationController extends Mage_Core_Controller_Front_Action
{
    /**
     * Notification Action
     */
    public function sendAction()
    {
        \Rakuten\Connector\Resources\Log\Logger::info("Received webhook call.", ['service' => 'WEBHOOK']);
        $credential = Mage::helper('rakutenpay/credential');
        $entityHeaders = null;
        if (!function_exists('apache_request_headers')) {
            \Rakuten\Connector\Resources\Log\Logger::info("We ain't got the (apache_request_headers) method...", ['service' => 'WEBHOOK']);
            $headers = [];
            foreach ($_SERVER as $name => $value)
            {
                if (substr($name, 0, 5) == 'HTTP_')
                {
                    $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                }
            }
            $entityHeaders = $headers;
        } else {
            $entityHeaders = apache_request_headers();
        }
        \Rakuten\Connector\Resources\Log\Logger::info("Got all headers.", ['service' => 'WEBHOOK']);
        $entityBody = file_get_contents('php://input');
        \Rakuten\Connector\Resources\Log\Logger::info("Got the contents.", ['service' => 'WEBHOOK']);
        $signatureKey = $credential->getSignatureKey();
        \Rakuten\Connector\Resources\Log\Logger::info("Got the sig key.", ['service' => 'WEBHOOK']);
        $signature = hash_hmac('sha256', $entityBody, $signatureKey, true);
        \Rakuten\Connector\Resources\Log\Logger::info("Calculated the signature.", ['service' => 'WEBHOOK']);
        $signatureBase64 = base64_encode($signature);
        \Rakuten\Connector\Resources\Log\Logger::info("Encoded the signature.", ['service' => 'WEBHOOK']);
        if (empty($entityBody)) {
            \Rakuten\Connector\Resources\Log\Logger::info("Empty entity body.", ['service' => 'WEBHOOK']);
            return;
        }
        if (!array_key_exists('Signature', $entityHeaders) || $entityHeaders['Signature'] !== $signatureBase64) {
            \Rakuten\Connector\Resources\Log\Logger::info("Signature does not match.", ['service' => 'WEBHOOK']);
            $logSignature = (array_key_exists('Signature', $entityHeaders)) ? $entityHeaders['Signature'] : false;
            \Rakuten\Connector\Resources\Log\Logger::info(sprintf("Signature Local: %s | Signature Header: %s", $signatureBase64, $logSignature), ['service' => 'WEBHOOK']);
            throw new \Rakuten\Connector\Exception\ConnectorException("Signature does not match...");
        }
        \Rakuten\Connector\Resources\Log\Logger::info("All OK, processing.", ['service' => 'WEBHOOK']);
        $helper = Mage::helper('rakutenpay');
        \Rakuten\Connector\Resources\Log\Logger::info("Created helper.", ['service' => 'WEBHOOK']);
        $helper->notificationModel()->initialize($entityBody, $entityHeaders);
    }
}
