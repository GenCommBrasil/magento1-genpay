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
class Rakuten_RakutenPay_Helper_Internal extends Mage_Core_Helper_Abstract
{
    /**
     * Get fields from a given entity
     * @author Gabriela D'Ávila (http://davila.blog.br)
     * @param $type
     * @return mixed
     */
    public static function getFields($type = 'customer_address')
    {
        $entityType = Mage::getModel('eav/config')->getEntityType($type);
        $entityTypeId = $entityType->getEntityTypeId();
        $attributes = Mage::getResourceModel('eav/entity_attribute_collection')->setEntityTypeFilter($entityTypeId);

        return $attributes->getData();
    }
}
