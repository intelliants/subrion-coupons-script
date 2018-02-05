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

class iaCoupon extends abstractModuleFront implements iaCouponsModule
{
    const SORTING_SESSION_KEY = 'coupons_sorting';

    protected static $_table = 'coupons_coupons';
    protected static $_tableCodes = 'coupons_codes';

    protected $_itemName = 'coupon';

    public $coreSearchEnabled = true;
    public $coreSearchOptions = [
        'tableAlias' => 't1',
        'regularSearchFields' => ['title', 'title_alias', 'tags', 'description'],
        'customColumns' => ['category', 'keywords']
    ];

    protected $_statuses = [iaCore::STATUS_ACTIVE, iaCore::STATUS_APPROVAL];
    protected $_codeStatuses = [iaCore::STATUS_ACTIVE, iaCore::STATUS_INACTIVE, self::STATUS_USED];

    private $_foundRows = 0;


    public static function getTableCodes()
    {
        return self::$_tableCodes;
    }

    public function getUrl(array $data)
    {
        return $this->url('view', $data);
    }

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

    public function coreSearchTranslateColumn($column, $value)
    {
        switch ($column) {
            case 'keywords':
                $iaField = $this->iaCore->factory('field');

                $fieldsList = ['title', 'short_description', 'description', 'tags', 'meta_description', 'meta_keywords'];

                $multilingualFields = $iaField->getMultilingualFields($this->getItemName());
                $value = "'%" . iaSanitize::sql($value) . "%'";

                $cond = [];
                foreach ($fieldsList as $fieldName) {
                    $fieldName = in_array($fieldName, $multilingualFields)
                        ? $fieldName . '_' . $this->iaView->language
                        : $fieldName;

                    $cond[] = ['col' => ':column', 'cond' => 'LIKE', 'val' => $value, 'field' => $fieldName];
                }

                return $cond;

            case 'category':
                $iaCcat = $this->iaCore->factoryItem('ccat');

                $sqlSubquery = sprintf('(SELECT `child_id` FROM `%s` WHERE `parent_id` = %d)',
                    $iaCcat->getTableFlat(true), $value);

                return ['col' => ':column', 'cond' => 'IN', 'val' => $sqlSubquery, 'field' => 'category_id'];
        }
    }

    public function foundRows()
    {
        return $this->_foundRows;
    }

    private function _getQuery($where = '', $order = '', $limit = 1, $start = 0, $foundRows = false, $ignoreStatus = false, $ignoreIndex = false) {
        $iaDb = &$this->iaDb;

        $sql = 'SELECT :found_rows t1.*'
            . ', t2.`title_alias` `category_alias`, t2.`title_:lang` `category_title`, t2.`parent_id` `category_parent_id`, t2.`no_follow`, t2.`num_coupons` `num` '
            . ', IF(t3.`fullname` != "", t3.`fullname`, t3.`username`) `account`, t3.`username` `account_username`'
            . ', t4.`title_alias` `shop_alias`, t4.`title_:lang` `shop_title`, t4.`shop_image` `shop_image`, t4.`website` `shop_website`, t4.`domain` `shop_domain`, t4.`affiliate_link` `shop_affiliate_link` '
            // count codes for each coupon
            . ', (SELECT COUNT(*) FROM `:table_codes` cc LEFT JOIN `:table_transactions` pt ON (pt.`id` = cc.`transaction_id`) WHERE cc.`coupon_id` = t1.`id` && pt.`status` = \':passed\') `activations_sold` '
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

        if (true == $foundRows) {
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

            if ('deal' == $row['type']) {
                $this->_assignCouponCodeVars($row);
            }

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

    protected function _assignCouponCodeVars(&$row)
    {
        if (iaUsers::hasIdentity()) {
            $this->iaCore->factory('transaction');

            $transaction = $this->iaDb->row_bind(iaDb::ALL_COLUMNS_SELECTION,
                'member_id = :member && `item` = :item && `item_id` = :id AND `amount` >= :price',
                ['member' => iaUsers::getIdentity()->id, 'item' => 'coupons', 'id' => $row['id'], 'price' => $row['cost']], iaTransaction::getTable());

            if (isset($transaction['status']) && iaTransaction::PASSED == $transaction['status']) {
                $row['coupon_code'] = ($couponCode = $this->getCode($transaction['id'], 'transaction_id'))
                    ? $couponCode['code']
                    : iaLanguage::get('error');
            }
        }

        $row['buy_code_link'] = isset($transaction['status']) && iaTransaction::PENDING == $transaction['status']
            ? IA_URL . 'pay/' . $transaction['sec_key'] . IA_URL_DELIMITER
            : $this->getInfo('url') . 'coupon/buy/' . $row['id'] . IA_URL_DELIMITER;
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
        $where = 't1.`id` IN (' . implode(',', $ids) . ')';

        return $this->_getQuery($where);
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
    public function getByCategory($where, $categoryId, $start = 0, $limit = 10, $order = false)
    {
        empty($where) || $where .= ' AND ';

        if ($this->iaCore->get('coupons_show_children')) {
            $iaCcat = $this->iaCore->factoryItem('ccat');

            $where .= sprintf('t1.`category_id` IN (SELECT `child_id` FROM `%s` WHERE `parent_id` = %d)',
                $iaCcat->getTableFlat(true), $categoryId);
        } else {
            $where .= 't1.`category_id` = ' . (int)$categoryId;
        }

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
        $this->_checkIfCountersNeedUpdate($action, $itemData, $previousData, $this->iaCore->factoryItem('ccat'));
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
        return (int)$this->iaDb->one('thumbs_num', iaDb::convertIds($id), self::getTable());
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
        if (iaUsers::hasIdentity() && iaUsers::MEMBERSHIP_ADMINISTRATOR != iaUsers::getIdentity()->usergroup_id) {
            $couponCount = $this->iaDb->one_bind(iaDb::STMT_COUNT_ROWS, '`member_id` = :member',
                ['member' => $memberId], self::getTable());

            $result = ($couponCount < $this->iaCore->get('coupons_listing_limit'));
        }

        return $result;
    }

    public function getCode($value, $key = 'id')
    {
        return $this->iaDb->row(iaDb::ALL_COLUMNS_SELECTION, iaDb::convertIds($value, $key), self::$_tableCodes);
    }

    /**
     * Returns list of purchased coupons codes
     *
     * @param int $id coupon id
     *
     * return array
     */
    public function getCodes($couponId)
    {
        $sql = <<<SQL
SELECT SQL_CALC_FOUND_ROWS cc.`id`, cc.`code`, cc.`status`,
    t.`reference_id`, t.`date_paid`, t.`currency`, t.`amount`,
    m.`fullname` `owner`
  FROM `{$this->iaDb->prefix}coupons_codes` cc
LEFT JOIN `{$this->iaDb->prefix}payment_transactions` t ON (t.`id` = cc.`transaction_id`)
LEFT JOIN `{$this->iaDb->prefix}members` m ON (m.`id` = t.`member_id`)
WHERE `coupon_id` = {$couponId}
GROUP BY cc.`id`
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

    public function postPayment($plan, array $transaction)
    {
        $this->_issueCouponCode($transaction['item_id'], $transaction['id']);
        iaDebug::log('POST PAYMENT', $transaction);
    }

    protected function _issueCouponCode($couponId, $transactionId)
    {
        $couponEntry = [
            'coupon_id' => $couponId,
            'transaction_id' => $transactionId,
            'code' => $this->_generateCode(),
            'status' => iaCore::STATUS_ACTIVE
        ];

        $this->iaDb->insert($couponEntry, null, self::$_tableCodes);
    }

    protected function _generateCode()
    {
        return strtoupper(iaUtil::generateToken(7));
    }
}
