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

class Rakuten_RakutenLogistics_Block_Adminhtml_Sales_Order_View extends Mage_Adminhtml_Block_Sales_Order_View
{
    public function  __construct()
    {
        parent::__construct();
        $isActive = (bool)Mage::getStoreConfig('carriers/rakutenlogistics_settings/active');
        if ($isActive) {

            $order = Mage::registry('current_order');
            $url = Mage::helper("adminhtml")->getUrl('rakutenlogistics/invoiceData/edit/order_increment_id/' . $order->getIncrementId());
            $this->_addButton('button_id', array(
                'label' => "GenLog Invoice",
                'onclick' => 'setLocation(\'' . $url . '\')',
                'class' => 'go'
            ), 0, 100, 'header', 'header');
        }
    }
}
