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

class Rakuten_RakutenPay_Adminhtml_RefundController extends Mage_Adminhtml_Controller_Action
{
    /**
     * @var int
     */
    private $days;
    /**
     * @var Rakuten_RakutenPay_Helper_Log
     */
    private $log;
    /**
     * @var Rakuten_RakutenPay_Helper_Refund
     */
    private $refund;

    /**
     * Rakuten_RakutenPay_Adminhtml_ConciliationController constructor.
     */
    public function _construct()
    {
        $this->log = new Rakuten_RakutenPay_Helper_Log();
    }

    public function doRefundAction()
    {
        $this->builder();
        if ($this->getRequest()->getPost('data')) {
            $data = current($this->getRequest()->getPost('data'));
            try {
                if (array_key_exists('doc', $data)) {
                    $bankData = ['document' => $data['doc'], 'bank_code' => $data['bank'],
                        'bank_agency' => $data['agency'], 'bank_number' => $data['number']];
                } else {
                    $bankData = null;
                }
                $paymentId = null;
                if (array_key_exists('payment_id', $data)) {
                    $paymentId = $data['payment_id'];
                }
                $value = null;
                if (array_key_exists('value', $data)) {
                    $value = floatval($data['value']);
                    if ($value < 0.01) {
                        $value = null;
                    }
                }
                $this->refund->updateOrderStatusMagentoRefund($data['id'], $data['code'], $data['status'], floatval($data['value']),
                        $data['type'], $data['reason'], $bankData, $paymentId);

            } catch (\Rakuten\Connector\Exception\ConnectorException $pse) {
                print json_encode(array(
                        "status" => false,
                        "err"    => trim($pse->getMessage()),
                    )
                );
                exit();
            }
            $this->doPostAction();
            exit();
        }
        print json_encode(array(
                "status" => false,
                "err"    => true,
            )
        );
        exit();
    }

    private function builder()
    {
        $this->refund = Mage::helper('rakutenpay/refund');
        if ($this->getRequest()->getPost('days')) {
            $this->days = $this->getRequest()->getPost('days');
        }
    }

    public function doPostAction()
    {
        $this->builder();
        if ($this->days) {
            $this->log->setSearchTransactionLog(get_class($this->refund), $this->days);
            try {
                $this->refund->initialize($this->days);
                if (!$this->refund->getPaymentsArray()) {
                    print json_encode(array("status" => false));
                    exit();
                }
                print $this->refund->getTransactionGrid($this->refund->getPaymentsArray());
            } catch (\Rakuten\Connector\Exception\ConnectorException $e) {
                print json_encode(array(
                        "status" => false,
                        "err"    => trim($e->getMessage()),
                    )
                );
            }
        }
    }

    public function indexAction()
    {
        Mage::getSingleton('core/session')->setData(
            'store_id',
            Mage::app()->getRequest()->getParam('store')
        );
        $this->loadLayout();
        $this->_setActiveMenu('rakutenpay_menu')->renderLayout();
    }
}
