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
 * Class Rakuten_RakutenLogistics_Helper_Data
 */
class Rakuten_RakutenLogistics_Helper_Data extends Mage_Shipping_Helper_Data
{
    /**
     * @param Mage_Sales_Model_Order $order
     * @param $batchCode
     * @param $batchLabelUrl
     * @param $trackingUrl
     * @throws Mage_Core_Exception
     */
    private function saveBatch(Mage_Sales_Model_Order $order, $batchCode, $batchLabelUrl, $trackingUrl)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing saveBatch in HelperData.');
        $order->setBatchLabelUrl($batchLabelUrl);
        $order->addStatusHistoryComment($trackingUrl)->setIsCustomerNotified(true);
        $payment = $order->getPayment();
        $order->save();
        $payment
            ->setAdditionalInformation('batch_code', $batchCode)
            ->setAdditionalInformation('batch_print_url', $batchLabelUrl)
            ->setAdditionalInformation('batch_tracking_url', $trackingUrl)
            ->save();
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return bool
     * @throws Mage_Core_Exception
     */
    private function hasBatch(Mage_Sales_Model_Order $order)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing hasBatch in HelperData.');
        $helper = Mage::helper('rakutenlogistics/webservice');
        $result = $helper->orderDetail($order);
        if ($result['status'] == 'OK') {
            $content = $result['content'];
            $batchCode = $content['batch_code'];
            $batchLabelUrl = $content['batch_print_url'];
            $trackingUrl = $content['tracking_print_url'];
            $this->saveBatch($order, $batchCode, $batchLabelUrl, $trackingUrl);

            return true;
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return Mage::getConfig()->getModuleConfig("Rakuten_RakutenLogistics")->version;
    }

    /**
     * @param $code
     */
    public function saveCalculationCode($code)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing saveCalculationCode in HelperData.');
        Mage::getSingleton('core/session')->setCalculationCode($code);
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return string|null
     */
    public function getCalculationCode(Mage_Sales_Model_Order $order)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing getCalculationCode in HelperData.');
        $rakutenOrder = Mage::getModel('rakuten_rakutenlogistics/order')->load($order->getId(), 'order_id');

        return $rakutenOrder->getCalculationCode();
    }

    /**
     * @param $order
     * @param $request
     * @return mixed
     */
    public function insertInvoiceData($order, $request)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing insertInvoiceData in HelperData.');
        if(!empty($order->getOrderInvoiceSerie())){
            
            $invoice = [
                'series' => $order->getOrderInvoiceSerie(),
                'number' => $order->getOrderInvoiceNumber(),
                'key' => $order->getOrderInvoiceKey(),
                'cfop' => $order->getOrderInvoiceCfop(),
                'date' => $order->getOrderInvoiceDate(),
                'valueBaseICMS' => $order->getOrderInvoiceValueBaseIcms(),
                'valueICMS' => $order->getOrderInvoiceValueIcms(),
                'valueBaseICMSST' => $order->getOrderInvoiceValueBaseIcmsSt(),
                'valueICMSST' => $order->getOrderInvoiceValueIcmsSt(),
            ];    

            $request['order']['invoice'] = $invoice;
        }

        return $request;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return mixed
     * @throws Exception
     */
    public function generateBatch(Mage_Sales_Model_Order $order)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing generateBatch in HelperData.');
        $hasBatch = $this->hasBatch($order);
        if ($hasBatch) {
            \Rakuten\Connector\Resources\Log\Logger::info(sprintf('Batch exists. #Order: %s', $order->getIncrementId()));
            return $hasBatch;
        }

        \Rakuten\Connector\Resources\Log\Logger::info('Creating Batch in HelperData.');
        $helper = Mage::helper('rakutenlogistics/webservice');
        $bashData = $helper->createBatch($order);

        if (false === $bashData) {
            return $bashData;
        }

        if (isset($bashData['status']) && ($bashData['status'] == 'OK')) {
            $content = array_shift($bashData['content']);
            $trackingObjects = array_shift($content['tracking_objects']);
            $labelUrl = $content['print_url'];
            $batchCode = $content['code'];
            $trackingUrl = $trackingObjects['tracking_url'];

            $order->setBatchLabelUrl($labelUrl);
            $order->addStatusHistoryComment($trackingUrl)->setIsCustomerNotified(true);
            $payment = $order->getPayment();
            $order->save();
            $payment
                ->setAdditionalInformation('batch_code', $batchCode)
                ->setAdditionalInformation('print_url', $labelUrl)
                ->setAdditionalInformation('tracking_url', $trackingUrl)
                ->save();
        }
        else {
            $messages = array_shift($bashData['messages']);
            \Rakuten\Connector\Resources\Log\Logger::error('Error generating batch for Order #' . $order->getIncrementId() . ':<br> '. $messages['text']);
            Mage::getSingleton('adminhtml/session')->addError('Error generating batch for Order #' . $order->getIncrementId() . ':<br> '. $messages['text']);
        }
    }

    /**
     * @param $shippingMethod
     * @return bool
     */
    public function isRakutenShippingMethod($shippingMethod)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing isRakutenShippingMethod in HelperData');
        $shippingMethod = explode('_', $shippingMethod);
        return count($shippingMethod) == 3 && $shippingMethod[0] == 'rakuten';
    }

    /**
     * @param $shippingMethod
     * @return string
     */
    public function getRakutenShippingMethodCode($shippingMethod)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing getRakutenShippingMethodCode in HelperData');
        if($this->isRakutenShippingMethod($shippingMethod)){
            $shippingMethod = explode('_', $shippingMethod);
            return $shippingMethod[2];
        }

        $emptyCode = '';
        return $emptyCode;
    }

    /**
     * @param $fullAddress
     * @return array
     */
    public function parseStreet($fullAddress)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing parseStreet in HelperData');
        $fullAddress = explode(', ', $fullAddress);
        $street = $fullAddress[0];
        $number = isset($fullAddress[1]) ? $fullAddress[1] : '';

        return array(
            'street' => $street,
            'number' => $number,
        );
    }
}
