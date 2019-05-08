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
 * Class Rakuten_RakutenLogistics_Adminhtml_BatchController
 */
class Rakuten_RakutenLogistics_Adminhtml_BatchController extends Mage_Adminhtml_Controller_Action
{
    /**
     * @var int
     */
    private $days;

    /**
     * @var Rakuten_RakutenLogistics_Helper_Batch
     */
    private $batch;

    public function _construct()
    {
        $this->batch = new Rakuten_RakutenLogistics_Helper_Batch();
    }

    /**
     * @void
     */
    private function builder()
    {
        $this->batch = Mage::helper('rakutenlogistics/batch');
        if ($this->getRequest()->getPost('days')) {
            $this->days = $this->getRequest()->getPost('days');
        }
    }

    /**
     * @void
     */
    public function indexAction()
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing indexAction in Admin BatchController.');
        Mage::getSingleton('core/session')->setData(
            'store_id',
            Mage::app()->getRequest()->getParam('store')
        );
        $this->loadLayout();
        $this->_setActiveMenu('rakutenpay_menu')->renderLayout();
    }

    /**
     * @void
     */
    public function doBatchAction()
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing doBatchAction in Admin BatchController.');
        try {
            $this->builder();
            $this->batch->initialize($this->days);
            $orderIds = $this->getRequest()->getPost('batch');
            $this->batch->generateBatch($orderIds);
            $this->getResponse()->setBody(
                Mage::helper('core')->jsonEncode($this->batch->getOrdersLogistics()));
        } catch (\Rakuten\Connector\Exception\ConnectorException $e) {
            \Rakuten\Connector\Resources\Log\Logger::error($e->getMessage());
        }
    }

    /**
     * @void
     */
    public function doPostAction()
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing doPostAction in Admin BatchController.');
        $this->builder();
        if ($this->days) {
            $this->batch->initialize($this->days);
            $this->getResponse()->setBody(
                Mage::helper('core')->jsonEncode($this->batch->getOrdersLogistics()));
        }
    }

    /**
     * @void
     */
    public function doBatchAdminAction()
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing doBatchAdminAction in BatchController.');
        $id = $this->getRequest()->getParam('order_id');
        $helper = Mage::helper('rakutenlogistics/data');
        $order = Mage::getModel('sales/order')->load($id);

        if ($helper->isRakutenShippingMethod($order->getShippingMethod())) {
            $helper->generateBatch($order);
        }
        else {
            \Rakuten\Connector\Resources\Log\Logger::error('Order #' . $order->getIncrementId() .
                ' Batch not created.');
            Mage::getSingleton('adminhtml/session')->addError('Order #'. $order->getIncrementId() .
                ' Batch not created.');
        }
        Mage::getSingleton('adminhtml/session')->addSuccess('Batch generated successfully!');

        $this->_redirect('adminhtml/sales_order/index');
    }
}