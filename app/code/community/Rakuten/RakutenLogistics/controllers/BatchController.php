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
                $helper->generateBatch($order);
            }
            else {
                \Rakuten\Connector\Resources\Log\Logger::error('Order #' . $order->getIncrementId() .
                    ' Batch not created.');
                Mage::getSingleton('adminhtml/session')->addError('Order #'. $order->getIncrementId() .
                    ' Batch not created.');
            }
        }
        Mage::getSingleton('adminhtml/session')->addSuccess('Batch generated successfully!'); 

        $this->_redirect('adminhtml/sales_order/index');
    }
}