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

class iaCoupon extends abstractCouponsModuleAdmin
{
    protected static $_table = 'coupons_coupons';

    protected $_itemName = 'coupons';

    protected $_activityLog = ['item' => 'coupon', 'icon' => 'tag'];
    protected $_statuses = [iaCore::STATUS_ACTIVE, iaCore::STATUS_APPROVAL, self::STATUS_SUSPENDED];

    protected $_urlPatterns = [
        'default' => ':action/:id/',
        'view' => 'coupon/:shop_alias:title_alias/:id.html',
        'edit' => 'edit/?id=:id',
        'add' => 'add/'
    ];

    public $dashboardStatistics = ['icon' => 'tag'];


    public function gridRead($params, $columns, array $filterParams = [], array $persistentConditions = [])
    {
        $columns = '*, ';
        $columns .= "(SELECT `title` FROM `{$this->_iaDb->prefix}coupons_categories` `cats` WHERE `cats`.`id` = `category_id`) `category_title`, ";
        $columns .= "(SELECT `username` FROM `{$this->_iaDb->prefix}members` `members` WHERE `members`.`id` = `member_id`) `member`, ";
        $columns .= "1 `update`, 1 `delete` ";

        return parent::gridRead($params, $columns, $filterParams);
    }

    public function url($action, $data = [])
    {
        $data['action'] = $action;
        $data['shop_alias'] = !isset($data['shop_alias']) ? '' : $data['shop_alias'] . IA_URL_DELIMITER;
        $data['category_alias'] = !isset($data['category_alias']) ? '' : $data['category_alias'] . IA_URL_DELIMITER;

        unset($data['title']);

        if (!isset($this->_urlPatterns[$action])) {
            $action = 'default';
        }

        return $this->getInfo('url') . iaDb::printf($this->_urlPatterns[$action], $data);
    }

    public function getById($id, $process = true)
    {
        $sql = <<<SQL
SELECT t1.*, IF(acc.`fullname` != '', acc.`fullname`, acc.`username`) `account`, t3.`title` `shop` 
  FROM `:table_coupons` t1 
LEFT JOIN `:prefixmembers` acc ON t1.`member_id` = acc.`id` 
LEFT JOIN `:prefixcoupons_shops` t3 ON t1.`shop_id` = t3.`id` 
WHERE t1.`id` = :id
SQL;
        $sql = iaDb::printf($sql, [
            'prefix' => $this->iaDb->prefix,
            'table_coupons' => self::getTable(true),
            'id' => intval($id),
        ]);

        return $this->iaDb->getRow($sql);
    }

    public function updateCounters($itemId, array $itemData, $action, $previousData = null)
    {
        $this->iaDb->update(['num_coupons' => 0, 'num_all_coupons' => 0], '', null, 'coupons_categories');

        $sql =
            'UPDATE `:prefixcoupons_categories` c SET ' .
            '`num_all_coupons` = (' .
                'SELECT COUNT(*) FROM `:table_coupons` l ' .
                'LEFT JOIN `:prefixcoupons_categories_flat` fs ' .
                'ON fs.`category_id` = l.`category_id` ' .
                'WHERE fs.`parent_id` = c.`id` ' .
                ($this->iaCore->get('show_expired_coupons') ? '': 'AND l.`expire_date` >= NOW() ') .
                "AND l.`status` = ':status'" .
            '),' .
            '`num_coupons` = (' .
                'SELECT COUNT(*) FROM `:table_coupons` ' .
                'WHERE `category_id` = c.`id` ' .
                ($this->iaCore->get('show_expired_coupons') ? '': 'AND `expire_date` >= NOW() ') .
                "AND `status` = ':status'" .
            ') ' .
            "WHERE c.`status` = ':status'";

        $sql = iaDb::printf($sql, [
            'prefix' => $this->iaDb->prefix,
            'table_coupons' => self::getTable(true),
            'status' => iaCore::STATUS_ACTIVE
        ]);

        return $this->iaDb->query($sql);
    }

    protected function _get()
    {
        $sql = <<<SQL
SELECT coupon.`id`, coupon.`title_alias`, shop.`title_alias` `shop_alias` 
  FROM `:table_coupons` coupon 
LEFT JOIN `:prefixcoupons_shops` shop ON (coupon.`shop_id` = shop.`id`)
WHERE coupon.`status` = ':status'
SQL;
        $sql = iaDb::printf($sql, [
            'prefix' => $this->iaDb->prefix,
            'table_coupons' => self::getTable(true),
            'status' => iaCore::STATUS_ACTIVE,
        ]);

        return $this->iaDb->getAll($sql);
    }

    public function getSitemapEntries()
    {
        $result = [];

        if ($rows = $this->_get()) {
            foreach ($rows as $row) {
                $result[] = $this->url('view', $row);
            }
        }

        return $result;
    }
}
