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
$installer->startSetup();

// table prefix
$tp = (string)Mage::getConfig()->getTablePrefix();

$new_table =  $tp . 'rakutenpay_orders';

// Checks for the rakutenpay_orders table if it does not exist is created
$sql = "CREATE TABLE IF NOT EXISTS `" . $new_table . "` (
         `entity_id` int(11) NOT NULL AUTO_INCREMENT,
         `order_id` int(11),
         `transaction_code` varchar(80) NOT NULL,
         `sent` int DEFAULT 0,
         `environment` varchar(40),
         `batch_label_url` TEXT DEFAULT NULL,
         `calculation_code` TEXT DEFAULT NULL,
         `order_invoice_serie` TEXT DEFAULT NULL,
         `order_invoice_number` TEXT DEFAULT NULL,
         `order_invoice_key` TEXT DEFAULT NULL,
         `order_invoice_cfop` TEXT DEFAULT NULL,
         `order_invoice_date` DATE DEFAULT NULL,
         `order_invoice_value_base_icms` TEXT DEFAULT NULL,
         `order_invoice_value_icms` TEXT DEFAULT NULL,
         `order_invoice_value_base_icms_st` TEXT DEFAULT NULL,
         `order_invoice_value_icms_st` TEXT DEFAULT NULL,
         PRIMARY KEY (`entity_id`)
         ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

$table = $installer->getTable('sales/quote_address');

$installer->getConnection()
    ->addColumn($table, 'rakutenfee_amount', array(
        'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale' => 2,
        'precision' => 14,
        'unsigned' => true,
        'nullable' => false,
        'comment' => 'Fee Amount',
    ));
$installer->getConnection()
    ->addColumn($table, 'base_rakutenfee_amount', array(
        'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale' => 2,
        'precision' => 14,
        'unsigned' => true,
        'nullable' => false,
        'comment' => 'Base Rakuten Fee Amount',
    ));
$table = $installer->getTable('sales/order');

$installer->getConnection()
    ->addColumn($table, 'rakutenfee_amount', array(
        'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale' => 2,
        'precision' => 14,
        'unsigned' => true,
        'nullable' => false,
        'comment' => 'Rakuten Fee Amount',
    ));
$installer->getConnection()
    ->addColumn($table, 'base_rakutenfee_amount', array(
        'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale' => 2,
        'precision' => 14,
        'unsigned' => true,
        'nullable' => false,
        'comment' => 'Base Rakuten Fee Amount',
    ));
$installer->run($sql);
$installer->endSetup();
