<?php
/******************************************************************************
 *
 * Subrion Coupons & Deals Software
 * Copyright (C) 2017 Intelliants, LLC <https://intelliants.com>
 *
 * This file is part of Subrion Coupons & Deals Software.
 *
 * This program is a commercial software and any kind of using it must agree
 * to the license, see <https://subrion.pro/license.html>.
 *
 * This copyright notice may not be removed from the software source without
 * the permission of Subrion respective owners.
 *
 *
 * https://subrion.pro/product/coupons-script.html
 *
 ******************************************************************************/

class iaBackendController extends iaAbstractControllerModuleBackend
{
    protected $_name = 'codes';
    protected $_itemName = 'codes';

    protected $_gridColumns = ['code', 'status', 'order', 'multilingual', 'delete' => 'removable'];
    protected $_gridFilters = ['status' => self::EQUAL, 'title' => self::LIKE];


    protected function _gridRead($params)
    {
        $sql = <<<SQL
SELECT SQL_CALC_FOUND_ROWS `cc`.`id`, `c`.`title`, `code`, `reference_id`, `date_paid`, `currency`, `operation`, `gateway`, `cc`.`status`,
	1 `update`, 1 `delete`
  FROM `:cc` `cc`
LEFT JOIN `:pt` `pt`
  ON `pt`.`id` = `cc`.`transaction_id`
LEFT JOIN `:coupons` `c`
  ON `c`.`id` = `cc`.`coupon_id`
LIMIT :start, :limit
SQL;

        $sql = iaDb::printf($sql, [
            'pt' => $this->_iaDb->prefix . 'payment_transactions',
            'cc' => $this->_iaDb->prefix . 'coupons_codes',
            'coupons' => $this->_iaDb->prefix . 'coupons_coupons',
            'start' => $params['start'],
            'limit' => $params['limit']
        ]);

        return $this->_iaDb->getAll($sql);
    }
}
