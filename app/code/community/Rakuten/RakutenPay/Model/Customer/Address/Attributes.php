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
class Rakuten_RakutenPay_Model_Customer_Address_Attributes
{
    /**
     * Return Address attribute
     * @author Gabriela D'Ávila (http://davila.blog.br)
     * @return array
     */
    public function toOptionArray()
    {
        $fields = Mage::helper('rakutenpay/internal')->getFields('customer_address');
        $options = [];

        foreach ($fields as $key => $value) {
            if (!is_null($value['frontend_label'])) {
                //in multiline cases, it allows to specify what each line means (i.e.: street, number)
                if ($value['attribute_code'] == 'street') {
                    $streetLines = Mage::getStoreConfig('customer/address/street_lines');
                    for ($i = 1; $i <= $streetLines; $i++) {
                        $options[] = ['value' => 'street_'.$i, 'label' => 'Street Line '.$i];
                    }
                } else {
                    $options[] = [
                        'value' => $value['attribute_code'],
                        'label' => $value['frontend_label'] . ' (' . $value['attribute_code'] . ')'
                    ];
                }
            }
        }
        return $options;
    }
}