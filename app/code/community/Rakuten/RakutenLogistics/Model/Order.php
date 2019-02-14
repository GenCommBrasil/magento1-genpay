<?php
/**
 * Created by PhpStorm.
 * User: alex.silva
 * Date: 2019-02-13
 * Time: 17:34
 */


class Rakuten_RakutenLogistics_Model_Order extends Mage_Core_Model_Abstract
{
    public function __construct()
    {
        parent::__construct();
        $this->_init('rakuten_rakutenlogistics/order');
    }
}