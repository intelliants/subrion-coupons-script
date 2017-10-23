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

class iaShop extends abstractModuleFront implements iaCouponsModule
{
    protected static $_table = 'coupons_shops';

    protected $_itemName = 'shops';

    public $coreSearchEnabled = true;
    public $coreSearchOptions = [
        'tableAlias' => 't1',
        'regularSearchFields' => ['title', 'title_alias', 'domain'],
        'customColumns' => ['keywords']
    ];

    private $_foundRows = 0;

    protected $_statuses = [
        iaCore::STATUS_ACTIVE,
        iaCore::STATUS_INACTIVE,
        iaCore::STATUS_APPROVAL,
        self::STATUS_SUSPENDED
    ];

    private $_patterns = [
        'default' => ':action/:id/',
        'view' => 'shop/:title_alias.html',
        'edit' => 'edit/?id=:id',
        'add' => 'add/'
    ];


    public function insert(array $entryData)
    {
        $entryData['date_added'] = date(iaDb::DATETIME_FORMAT);
        $entryData['date_modified'] = date(iaDb::DATETIME_FORMAT);
        $entryData['member_id'] = iaUsers::hasIdentity() ? iaUsers::getIdentity()->id : 0;

        return parent::insert($entryData);
    }

    /**
     * Method return url for some pages
     *
     * @param string $action
     * @param array $data
     *
     * @return string
     */
    public function url($action, array $data)
    {
        $data['action'] = $action;
        unset($data['title']);

        isset($this->_patterns[$action]) || $action = 'default';

        $url = iaDb::printf($this->_patterns[$action], $data);

        return $this->getInfo('url') . $url;
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
            return [$this->url(iaCore::ACTION_EDIT, $params['item']), null];
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
        if ('keywords' == $column) {
            $fieldsList = ['title', 'description', 'website'];

            $multilingualFields = $this->iaCore->factory('field')->getMultilingualFields($this->getItemName());
            $value = "'%" . iaSanitize::sql($value) . "%'";

            $cond = [];
            foreach ($fieldsList as $fieldName) {
                $fieldName = in_array($fieldName, $multilingualFields)
                    ? $fieldName . '_' . $this->iaView->language
                    : $fieldName;

                $cond[] = ['col' => ':column', 'cond' => 'LIKE', 'val' => $value, 'field' => $fieldName];
            }

            return $cond;
        }
    }

    public function foundRows()
    {
        return $this->_foundRows;
    }

    private function _getQuery($where = '', $order = '', $limit = 1, $start = 0, $foundRows = false, $ignoreIndex = false) {
        $iaDb = &$this->iaDb;

        $sql = 'SELECT :found_rows t1.*, '
            . 't2.`fullname` `account`, t2.`username` `account_username`, '
            . '(SELECT COUNT(*) FROM `:table_coupons` coupons WHERE `coupons`.`shop_id` = t1.`id`) `num_coupons` '
            . 'FROM :table_shops as `t1` '
            . ($ignoreIndex ? 'IGNORE INDEX (`' . $ignoreIndex . '`) ' : '')
            . 'LEFT JOIN `:table_members` t2 ON (t2.`id` = t1.`member_id`) '
            . 'WHERE :where '
            . ($order ? 'ORDER BY ' . $order . ' ' : '')
            . 'LIMIT :start, :limit ';

        $stmt = ["(t2.`status` = 'active' OR t2.`status` IS NULL)"];
        empty($where) || $stmt[] = $where;
        $stmt[] = "t1.`status` = 'active' ";

        $data = [
            'found_rows' => ($foundRows === true ? 'SQL_CALC_FOUND_ROWS' : ''),
            'table_shops' => self::getTable(true),
            'table_members' => iaUsers::getTable(true),
            'table_coupons' => $iaDb->prefix . 'coupons_coupons',
            'where' => implode(' AND ', $stmt),
            'start' => $start,
            'limit' => $limit,
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

    public function getById($id, $decorate = true)
    {
        $rows = $this->_getQuery('t1.`id` = ' . (int)$id);

        return $rows ? $rows[0] : $rows;
    }

    public function getByAlias($alias)
    {
        $listing = $this->_getQuery("t1.`title_alias` = '{$alias}'");

        return ($listing ? $listing[0] : false);
    }

    public function getByTitle($title)
    {
        return $this->iaDb->row(iaDb::ALL_COLUMNS_SELECTION, iaDb::convertIds($title, 'title_' . $this->iaView->language), self::getTable());
    }

    /**
     * Get popular listings with limit
     *
     * @param string $where
     * @param int $limit
     * @param int $start
     *
     * @return array
     */
    public function getPopular($where = '1', $limit = 5, $start = 0)
    {
        return $this->_getQuery($where, 't1.`views_num` DESC', $limit, $start, false);
    }

    public function get($where = '', $order = null, $limit = 5, $start = 0, $found_rows = false)
    {
        is_null($order) && $order = 't1.`title_' . $this->iaView->language . '`';

        return $this->_getQuery($where, $order, $limit, $start, $found_rows);
    }

    public function getFeatured($limit)
    {
        return $this->_getQuery('t1.`featured` = 1 AND t1.`featured_end` > NOW()',
            '`title_' . $this->iaView->language . '`', $limit);
    }
}
