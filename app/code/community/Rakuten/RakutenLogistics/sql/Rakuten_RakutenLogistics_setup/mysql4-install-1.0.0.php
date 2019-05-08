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

$installer = $this;
$setup = new Mage_Catalog_Model_Resource_Setup('core_setup');
$installer->startSetup();   

$heightAttr = array (
    'type' => 'decimal',
    'group' => 'General',
    'input' => 'text',
    'label' => 'Height',
    'required' => true,
    'user_defined' => true,
    'default' => true,
    'unique' => false,
    'global' => '1',
    'visible' => true,
    'comparable' => true
);
$setup->addAttribute('catalog_product','height',$heightAttr);

$widthAttr = array (
    'type' => 'decimal',
    'group' => 'General',
    'input' => 'text',
    'label' => 'Width',
    'required' => true,
    'user_defined' => true,
    'default' => true,
    'unique' => false,
    'global' => '1',
    'visible' => true,
    'comparable' => true
);
$setup->addAttribute('catalog_product','width',$widthAttr);

$lengthAttr = array (
    'type' => 'decimal',
    'group' => 'General',
    'input' => 'text',
    'label' => 'Length',
    'required' => true,
    'user_defined' => true,
    'default' => true,
    'unique' => false,
    'global' => '1',
    'visible' => true,
    'comparable' => true
);
$setup->addAttribute('catalog_product','length',$lengthAttr);
$installer->endSetup();