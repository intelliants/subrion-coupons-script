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

class iaCoupon extends abstractCouponsModuleFront
{
    const SORTING_SESSION_KEY = 'coupons_sorting';

    protected static $_table = 'coupons_coupons';
    protected $_itemName = 'coupons';

    public $coreSearchEnabled = true;
    public $coreSearchOptions = [
        'tableAlias' => 't1',
        'regularSearchFields' => ['title', 'title_alias', 'tags']
    ];

    private $_foundRows = 0;

    protected $_statuses = [iaCore::STATUS_ACTIVE, iaCore::STATUS_APPROVAL];
    protected $_codeStatuses = [iaCore::STATUS_ACTIVE, iaCore::STATUS_INACTIVE, self::STATUS_USED];

    public function url($action, array $listingData)
    {
        $patterns = [
            'default' => 'coupons/:action/:id/',
            'view' => 'coupon/:shop_alias/:title_alias/:id.html',
            'add' => 'coupons/add/',
            'my' => 'profile/coupons/',
            'buy' => 'coupons/buy/:id/'
        ];
        $url = iaDb::printf(
            isset($patterns[$action]) ? $patterns[$action] : $patterns['default'],
            [
                'action' => $action,
                'shop_alias' => isset($listingData['shop_alias']) ? $listingData['shop_alias'] : '',
                'title_alias' => isset($listingData['title_alias']) ? $listingData['title_alias'] : '',
                'id' => isset($listingData[self::COLUMN_ID]) ? $listingData[self::COLUMN_ID] : ''
            ]
        );

        return $this->getInfo('url') . $url;
    }

    /**
     * Return title
     *
     * @param array $data
     *
     * @return string
     */
    public function title(array $data)
    {
        $title = '';
        if (isset($data['title'])) {
            $title = $data['title'];
        }

        return $title;
    }

    /**
     * Return two url for account actions (edit, update)
     *
     * @param array $params
     *
     * @return array|bool
     */
    public function accountActions($params)
    {
        if (iaUsers::hasIdentity() && iaUsers::getIdentity()->id == $params['item']['member_id']) {
            return [$this->url('edit', $params['item']), null];
        }

        return false;
    }

    public function coreSearch($stmt, $start, $limit, $order)
    {
        $rows = $this->_getQuery($stmt, $order, $limit, $start, true);

        return [$this->foundRows(), $rows];
    }

    public function foundRows()
    {
        return $this->_foundRows;
    }

    private function _getQuery($where = '', $order = '', $limit = 1, $start = 0, $foundRows = false, $ignoreStatus = false, $ignoreIndex = false ) {
        $iaDb = &$this->iaDb;

        $sql = 'SELECT :found_rows t1.*'
            . ', t2.`title_alias` `category_alias`, t2.`title_:lang` `category_title`, t2.`parent_id` `category_parent_id`, t2.`no_follow`, t2.`num_coupons` `num` '
            . ', IF(t3.`fullname` != "", t3.`fullname`, t3.`username`) `account`, t3.`username` `account_username`'
            . ', t4.`title_alias` `shop_alias`, t4.`title_:lang` `shop_title`, t4.`shop_image` `shop_image`, t4.`website` `shop_website`, t4.`domain` `shop_domain`, t4.`affiliate_link` `shop_affiliate_link` '
            // count codes for each coupon
            . ', (SELECT COUNT(*) FROM `:table_codes` `cc` LEFT JOIN `:table_transactions` pt ON `pt`.`id` = `cc`.`transaction_id` WHERE `cc`.`coupon_id` = `t1`.`id` && `pt`.`status` = \':passed\') `activations_sold` '
            . 'FROM `:table_coupons` t1 '
            . ($ignoreIndex ? 'IGNORE INDEX (`' . $ignoreIndex . '`) ' : '')
            . 'LEFT JOIN `:table_categs` t2 ON(t2.`id` = t1.`category_id` AND t2.`status` = \'active\')'
            . 'LEFT JOIN `:table_members` t3 ON(t3.`id` = t1.`member_id`) '
            . 'LEFT JOIN `:table_shops` t4 ON(t4.`id` = t1.`shop_id`) '
            . 'WHERE :where '
            . ($order ? 'ORDER BY ' . $order . ' ' : '')
            . 'LIMIT :start, :limit';

        $stmt = [
            //"(t3.`status` = 'active' OR t3.`status` IS NULL) AND `t4`.`status` = 'active' ",
            "(t3.`status` = 'active' OR t3.`status` IS NULL) ",
        ];
        empty($where) || $stmt[] = $where;
        $ignoreStatus || $stmt[] = "(t1.`status` = 'active') ";

        $data = [
            'found_rows' => ($foundRows === true ? iaDb::STMT_CALC_FOUND_ROWS : ''),
            'table_coupons' => self::getTable(true),
            'table_codes' => $iaDb->prefix . 'coupons_codes',
            'table_categs' => $iaDb->prefix . 'coupons_categories',
            'table_shops' => $iaDb->prefix . 'coupons_shops',
            'table_members' => iaUsers::getTable(true),
            'table_transactions' => $iaDb->prefix . 'payment_transactions',
            'lang' => $this->iaCore->language['iso'],
            'where' => implode(' AND ', $stmt),
            'passed' => 'passed',
            'start' => $start,
            'limit' => $limit
        ];

        $rows = $iaDb->getAll(iaDb::printf($sql, $data));

        if ($foundRows === true) {
            $this->_foundRows = $iaDb->foundRows();
        } elseif ($foundRows == 'count') {
            $data['fields'] = 'COUNT(*) `count`';
            $data['limit'] = 1;

            $this->_foundRows = $iaDb->getOne(iaDb::printf($sql, $data));
        }

        $this->_processValues($rows);

        return $rows;
    }

    public function getById($id, $decorate = false)
    {
        $rows = $this->_getQuery("t1.`id` = '{$id}'", '', 1, 0, false, true);

        return $rows ? $rows[0] : [];
    }

    protected function _processValues(&$rows, $singleRow = false, $fieldNames = [])
    {
        parent::_processValues($rows, $singleRow, ['shop_image']);

        foreach ($rows as &$row) {
            $row['activations_left'] = $row['activations'] - (int)$row['activations_sold'];

            // discount calculations
            if ('fixed' == $row['item_discount_type']) {
                $row['discounted_price'] = $row['item_price'] - $row['item_discount'];
                $row['discount_saving'] = $row['item_discount'];
            } else {
                $row['discounted_price'] = $row['item_price'] * (100 - $row['item_discount']) / 100;
                $row['discount_saving'] = $row['item_price'] - $row['discounted_price'];
            }
        }
    }

    /**
     * Get listings by custom condition
     *
     * @param string $where
     * @param string $order
     * @param int $limit
     * @param int $start
     * @param bool $foundRows
     *
     * @return array
     */
    public function get($where = '', $order = '', $limit = 5, $start = 0, $foundRows = false, $ignoreStatus = false)
    {
        return $this->_getQuery($where, $order, $limit, $start, $foundRows, $ignoreStatus);
    }

    /**
     * Get user's listings
     *
     * @param int $memberId
     * @param int $limit
     * @param int $start
     *
     * @return array
     */
    public function getByUser($memberId, $limit = 5, $start = 0)
    {
        return $this->_getQuery('t1.`member_id` = ' . (int)$memberId, 't1.`member_id` DESC', $limit, $start, true);
    }

    public function getFavorites($ids)
    {
        $stmt = iaDb::printf("`id` IN (:ids) AND `status` IN (':active', 'available')",
            ['ids' => implode(',', $ids), 'active' => iaCore::STATUS_ACTIVE]);

        return $this->iaDb->all(iaDb::ALL_COLUMNS_SELECTION . ', (SELECT `title_alias` FROM `' . $this->iaDb->prefix . 'coupons_shops` `shops` WHERE `shops`.`id` = `shop_id`) `shop_alias`, 1 `favorite`',
            $stmt, null, null, self::getTable());
    }

    // called at the Member Details page
    public function fetchMemberListings($memberId, $start, $limit)
    {
        return [
            'items' => $this->getByUser($memberId, $limit, $start),
            'total_number' => $this->foundRows()
        ];
    }

    /**
     * Get listings by Category ID
     *
     * @param string $where
     * @param int $catId
     * @param int $start
     * @param int $limit
     * @param bool $order
     *
     * @return array
     */
    public function getByCategory($where, $catId, $start = 0, $limit = 10, $order = false)
    {
        empty($where) || $where .= ' AND ';
        $where .= is_array($catId)
            ? 't1.`category_id` IN(' . implode(',', $catId) . ')'
            : 't1.`category_id` = ' . (int)$catId;

        return $this->_getQuery($where, $order, $limit, $start, true);
    }

    public function insert(array $entryData)
    {
        $entryData['date_added'] = date(iaDb::DATETIME_FORMAT);
        $entryData['date_modified'] = date(iaDb::DATETIME_FORMAT);
        $entryData['member_id'] = iaUsers::hasIdentity() ? iaUsers::getIdentity()->id : 0;

        return parent::insert($entryData);
    }

    public function updateCounters($itemId, array $itemData, $action, $previousData = null)
    {
        $this->iaDb->update(['num_coupons' => 0, 'num_all_coupons' => 0], '', null, 'coupons_categories');

        $sql = <<<SQL
UPDATE `:prefixcoupons_categories` c SET `num_all_coupons` = (
  SELECT COUNT(*) FROM `:table_coupons` l 
    LEFT JOIN `:prefixcoupons_categories_flat` fs ON (fs.`category_id` = l.`category_id`) 
  WHERE fs.`parent_id` = c.`id` AND l.`status` = ':status'), `num_coupons` = (
  SELECT COUNT(*) FROM `:table_coupons` WHERE `category_id` = c.`id` AND `status` = ':status') 
WHERE c.`status` = ':status'
SQL;
        $sql = iaDb::printf($sql, [
            'prefix' => $this->iaDb->prefix,
            'table_coupons' => self::getTable(true),
            'status' => iaCore::STATUS_ACTIVE
        ]);

        return $this->iaDb->query($sql);
    }

    public function incrementThumbsCounter($itemId, $trigger, $columnName = 'thumbs_num')
    {
        $viewsTable = 'thumbs_log';
        $sign = ('up' == $trigger) ? '+' : '-';

        $ipAddress = $this->iaCore->factory('util')->getIp(true);
        $date = date(iaDb::DATE_FORMAT);

        if ($this->iaDb->exists('`item_id` = :id AND `ip` = :ip AND `date` = :date',
            ['id' => $itemId, 'ip' => $ipAddress, 'date' => $date], $viewsTable)
        ) {
            return false;
        }

        $this->iaDb->insert(['item_id' => $itemId, 'ip' => $ipAddress, 'date' => $date], null, $viewsTable);
        $result = $this->iaDb->update(null, iaDb::convertIds($itemId),
            [$columnName => '`' . $columnName . '` ' . $sign . ' 1'], self::getTable());

        return (bool)$result;
    }

    public function getThumbsNum($id)
    {
        return $this->iaDb->one('thumbs_num', iaDb::convertIds($id), self::getTable());
    }

    public function getSorting(&$storage, &$params)
    {
        $field = 'date_added';
        $direction = iaDb::ORDER_DESC;

        $validFields = [
            'date' => 'date_added',
            'likes' => 'thumbs_num',
            'popularity' => 'views_num'
        ];
        $validDirections = [
            'up' => iaDb::ORDER_ASC,
            'down' => iaDb::ORDER_DESC
        ];

        empty($storage[self::SORTING_SESSION_KEY][0]) || $field = $storage[self::SORTING_SESSION_KEY][0];
        empty($storage[self::SORTING_SESSION_KEY][1]) || $direction = $storage[self::SORTING_SESSION_KEY][1];

        if (isset($params['sort']) && in_array($params['sort'], array_keys($validFields))) {
            $field = $validFields[$params['sort']];

            isset($storage[self::SORTING_SESSION_KEY]) || $storage[self::SORTING_SESSION_KEY] = [];
            $storage[self::SORTING_SESSION_KEY][0] = $field;
        }
        if (isset($params['order']) && in_array($params['order'], array_keys($validDirections))) {
            $direction = $validDirections[$params['order']];

            isset($storage[self::SORTING_SESSION_KEY]) || $storage[self::SORTING_SESSION_KEY] = [];
            $storage[self::SORTING_SESSION_KEY][1] = $direction;
        }

        return [$field, $direction];
    }

    public function isSubmissionAllowed($memberId)
    {
        $result = true;
        if (iaUsers::MEMBERSHIP_ADMINISTRATOR != iaUsers::getIdentity()->usergroup_id) {
            $couponCount = $this->iaDb->one_bind(iaDb::STMT_COUNT_ROWS, '`member_id` = :member',
                ['member' => $memberId], self::getTable());

            $result = ($couponCount < $this->iaCore->get('coupons_listing_limit'));
        }

        return $result;
    }

    /**
     * Returns list of purchased coupons codes
     *
     * @param int $id coupon id
     *
     * return array
     */
    public function getCodes($id, $limit = 5, $start = 0)
    {
        $sql = <<<SQL
SELECT SQL_CALC_FOUND_ROWS `code`, `reference_id`, `date_paid`, `currency`, `operation`, `gateway`, `cc`.`status`
  FROM `{$this->iaDb->prefix}coupons_codes` `cc`
LEFT JOIN `{$this->iaDb->prefix}payment_transactions` `pt` ON `pt`.`id` = `cc`.`transaction_id`
WHERE `coupon_id` = {$id}
LIMIT {$start}, {$limit}
SQL;

        return $this->iaDb->getAll($sql);
    }

    public function getCodeStatuses()
    {
        return $this->_codeStatuses;
    }

    public function getDealOfTheDay()
    {
        $where = "`type` = 'deal' && `t1`.`status` = 'active' ";
        $deals = $this->get($where, '`views_num` DESC', 1);

        return $deals ? $deals[0] : [];
    }
}
