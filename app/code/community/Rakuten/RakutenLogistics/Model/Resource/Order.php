<?php
/**
 * Created by PhpStorm.
 * User: alex.silva
 * Date: 2019-02-13
 * Time: 17:34
 */

class Rakuten_RakutenLogistics_Model_Resource_Order extends Mage_Core_Model_Resource_Db_Abstract
{
    /***
     * Initialize resource model
     */
    public function _construct()
    {
        $this->_init('rakuten_rakutenlogistics/order','entity_id');
    }
}
