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

class Rakuten_RakutenPay_Adminhtml_AjaxController extends Mage_Adminhtml_Controller_Action
{
    private $log;

    public function indexAction()
    {
        $this->log = Mage::helper('rakutenpay/log');
        $helper = Mage::helper('rakutenpay');

        $origin = $this->getRequest()->getPost('origin');
        $sendmail = $this->getRequest()->getPost('sendmail');
        $days = $this->getRequest()->getPost('days');

        // Saves the day searching for the global variable that receives the array
        if ($days) {
            $_SESSION['days'] = $days;
            $helper->setInitialDate($days);
        }

        if ($origin == 'refund') {
            echo $this->getRefundGrid($days);
        }
    }

    private function getRefundGrid($days)
    {
        $refund = Mage::helper('rakutenpay/refund');

        if ($json = $this->getRequest()->getPost('json')) {
            foreach ($json as $value) {
                $refund->updateOrderStatusMagento(get_class($refund), $value['id'], $value['code']);
            }

            $refund->setInitialDate($_SESSION['days']);
        } else {
            if ($_SESSION['days'] != 0) {
                $this->log->setSearchTransactionLog(get_class($refund), $days);
            }
        }

        try {

            if ($refundArray = $refund->getArrayPayments()) {
                return $refund->getTransactionGrid($refundArray);
            } else {
                return 'run';
            }
        } catch (\Rakuten\Connector\Exception\ConnectorException $e) {
            return trim($e->getMessage());
        }
    }
}
