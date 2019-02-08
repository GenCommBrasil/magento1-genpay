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
 * Class Rakuten_RakutenLogistics_BatchController
 */
class Rakuten_RakutenLogistics_BatchController extends Mage_Adminhtml_Controller_Action
{
    /**
     * @void
     */
    public function createBatchAction()
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing createBatchAction in BatchController.');
        $orderIds = $this->getRequest()->getParam('order_ids');  
        $helper = Mage::helper('rakutenlogistics/data');
        foreach ($orderIds as $orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            if ($helper->isRakutenShippingMethod($order->getShippingMethod())) {
                $this->generateBatch($order);
            }
            else {
                \Rakuten\Connector\Resources\Log\Logger::error('Order #' . $order->getIncrementId() .
                    ' dont use a Rakuten Logistics shipping method.');
                Mage::getSingleton('adminhtml/session')->addError('Order #'. $order->getIncrementId() .
                    ' dont use a Rakuten Logistics shipping method.');
            }
        }
        Mage::getSingleton('adminhtml/session')->addSuccess('Batch generated successfully!'); 

        $this->_redirect('adminhtml/sales_order/index');
    }

    /**
     * @param $order
     * @void
     */
    private function generateBatch($order)
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing generateBatch in BatchController.');
        $helper = Mage::helper('rakutenlogistics/webservice');
        $bashData = $helper->createBatch($order);

        if ($bashData->status == 'OK') {
            $labelUrl = $bashData->content[0]->print_url;
            $trackingUrl = $bashData->content[0]->tracking_objects[0]->tracking_url;

            $order->setBatchLabelUrl($labelUrl);
            $order->addStatusHistoryComment($trackingUrl)->setIsCustomerNotified(true);
            $order->save();
        }
        else {
            \Rakuten\Connector\Resources\Log\Logger::error('Error generating batch for Order #' . $order->getIncrementId() . ':<br> '. $bashData->messages[0]->text);
            Mage::getSingleton('adminhtml/session')->addError('Error generating batch for Order #' . $order->getIncrementId() . ':<br> '. $bashData->messages[0]->text);
        }
    }
}