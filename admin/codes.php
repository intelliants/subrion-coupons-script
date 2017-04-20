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

    protected $_table = 'coupons_codes';

    protected $_gridColumns = 'cc.`id`, c.`title_:lang` `coupon`, cc.`code`, pt.`reference_id`, pt.`date_paid`, pt.`currency`, pt.`operation`, pt.`gateway`, cc.`status`, m.`fullname` `member`, 1 `update`, 1 `delete`';
    protected $_gridFilters = ['status' => self::EQUAL, 'code' => self::LIKE];
    protected $_gridSorting = ['date_paid' => ['date_paid', 'pt'], 'member' => ['fullname', 'm'], 'reference_id' => ['reference_id', 'pt']];
    protected $_gridQueryMainTableAlias = 'cc';


    public function init()
    {
        $this->_gridSorting['coupon'] = ['title_' . $this->_iaCore->language['iso'], 'c'];
    }

    protected function _modifyGridParams(&$conditions, &$values, array $params)
    {
        if (!empty($params['member'])) {
            $conditions[] = 'pt.`member_id` = :member';
            $values['member'] = $params['member'];
        }
    }

    protected function _gridQuery($columns, $where, $order, $start, $limit)
    {
        $sql = <<<SQL
SELECT :columns
  FROM `:table_codes` cc
LEFT JOIN `:table_transactions` pt ON (pt.`id` = cc.`transaction_id`)
LEFT JOIN `:table_coupons` c ON (c.`id` = cc.`coupon_id`)
LEFT JOIN `:table_members` m ON (m.`id` = pt.`member_id`)
WHERE :where
GROUP BY cc.`id` :order
LIMIT :start, :limit
SQL;
        $sql = iaDb::printf($sql, [
            'table_transactions' => $this->_iaDb->prefix . 'payment_transactions',
            'table_codes' => $this->_iaDb->prefix . $this->getTable(),
            'table_coupons' => $this->_iaDb->prefix . 'coupons_coupons',
            'table_members' => iaUsers::getTable(true),
            'columns' => str_replace(':lang', $this->_iaCore->language['iso'], $columns),
            'where' => $where,
            'order' => $order,
            'start' => (int)$start,
            'limit' => (int)$limit
        ]);

        return $this->_iaDb->getAll($sql);
    }
}
