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
 * Class Rakuten_RakutenLogistics_InvoiceDataController
 */
class Rakuten_RakutenLogistics_InvoiceDataController extends Mage_Adminhtml_Controller_Action
{
    protected function _construct()
    {
        parent::_construct();
    }

    public function editAction()
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing editAction in InvoiceDataController.');
        $helper = Mage::helper('rakutenlogistics/data');
        $orderIncrementId = $this->getRequest()->getParam('order_increment_id');
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);

        try {

            if (!$helper->isRakutenShippingMethod($order->getShippingMethod())) {
                throw new \Rakuten\Connector\Exception\ConnectorException('Shipping Method is not Rakuten Logistics.');
            }

            $rakutenOrder = Mage::getModel('rakuten_rakutenlogistics/order')->load($order->getId(), 'order_id');

            if (!$rakutenOrder->getOrderId()) {
                throw new \Rakuten\Connector\Exception\ConnectorException('Rakuten Order not found.');
            }

            Mage::register('order_data', $rakutenOrder);
            $this->loadLayout();

            $this->_addBreadcrumb(
                'Sales',
                'Invoice Data'
            );
            $this->_addContent($this->getLayout()->createBlock('rakuten_rakutenlogistics/adminhtml_invoiceData_edit'));
            $this->renderLayout();
        } catch (\Rakuten\Connector\Exception\ConnectorException $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("adminhtml/sales_order/view", ['order_id'=> $order->getId()]));
        }
    }

    public function saveAction()
    {
        \Rakuten\Connector\Resources\Log\Logger::info('Processing saveAction in InvoiceDataController.');
        $orderIncrementId = $this->getRequest()->getParam('order_increment_id');  
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
        $rakutenOrder = Mage::getModel('rakuten_rakutenpay/order')->load($order->getId(), 'order_id');

        if ($order->getId() && $rakutenOrder->getOrderId()) {
            $parameters = $this->getRequest()->getParams();

            $rakutenOrder->setOrderInvoiceSerie($parameters['order_invoice_serie']);
            $rakutenOrder->setOrderInvoiceNumber($parameters['order_invoice_number']);
            $rakutenOrder->setOrderInvoiceKey($parameters['order_invoice_key']);
            $rakutenOrder->setOrderInvoiceCfop($parameters['order_invoice_cfop']);
            $rakutenOrder->setOrderInvoiceDate($parameters['order_invoice_date']);
            $rakutenOrder->setOrderInvoiceValueBaseIcms($parameters['order_invoice_value_base_icms']);
            $rakutenOrder->setOrderInvoiceValueIcms($parameters['order_invoice_value_icms']);
            $rakutenOrder->setOrderInvoiceValueBaseIcmsSt($parameters['order_invoice_value_base_icms_st']);
            $rakutenOrder->setOrderInvoiceValueIcmsSt($parameters['order_invoice_value_icms_st']);
            $rakutenOrder->save();
            Mage::getSingleton('adminhtml/session')->addSuccess('Order invoice data saved.');
            $this->_redirect('adminhtml/sales_order/index');
        } else {
            Mage::getSingleton('adminhtml/session')->addError('Order not found.');
            $this->_redirect('*/*/');
        }
    }
}