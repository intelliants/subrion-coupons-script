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
    protected $_name = 'coupons';

    protected $_helperName = 'coupon';

    protected $_gridColumns = ['title', 'title_alias', 'expire_date', 'date_added', 'type', 'short_description', 'status', 'reported_as_problem', 'reported_as_problem_comments'];
    protected $_gridFilters = ['status' => self::EQUAL, 'type' => self::EQUAL, 'title' => self::LIKE];
    protected $_gridQueryMainTableAlias = 'cc';

    protected $_phraseAddSuccess = 'coupon_added';

    protected $_activityLog = ['icon' => 'tag', 'item' => 'coupon'];

    private $_iaCcat;


    protected function _gridQuery($columns, $where, $order, $start, $limit)
    {

        $sql = <<<SQL
SELECT :columns, c.`title_:lang` `category`, m.`fullname` `member`
  FROM `:table_coupons` cc
  LEFT JOIN `:table_categories` c ON (cc.`category_id` = c.`id`)
  LEFT JOIN `:table_members` m ON (m.`id` = cc.`member_id`)
WHERE :where
GROUP BY cc.`id` :order
LIMIT :start, :limit
SQL;
        $sql = iaDb::printf($sql, [
            'table_categories' => $this->_iaDb->prefix . 'coupons_categories',
            'table_coupons' => $this->_iaDb->prefix . $this->getTable(),
            'table_members' => iaUsers::getTable(true),
            'columns' => str_replace(':lang', $this->_iaCore->language['iso'], $columns),
            'where' => $where,
            'order' => $order,
            'start' => (int)$start,
            'lang' => $this->_iaCore->language['iso'],
            'limit' => (int)$limit
        ]);

        return $this->_iaDb->getAll($sql);
    }

    public function init()
    {
        $this->_iaCcat = $this->_iaCore->factoryItem('ccat');
    }

    protected function _entryAdd(array $entryData)
    {
        return $this->getHelper()->insert($entryData);
    }

    protected function _entryUpdate(array $entryData, $entryId)
    {
        return $this->getHelper()->update($entryData, $entryId);
    }

    protected function _entryDelete($entryId)
    {
        return $this->getHelper()->delete($entryId);
    }

    protected function _modifyGridParams(&$conditions, &$values, array $params)
    {
        if (!empty($params['member'])) {
             $conditions[] = iaDb::convertIds($params['member'], 'member_id');
        }
    }

    protected function _setDefaultValues(array &$entry)
    {
        $entry = [
            'shop_id' => 0,
            'member_id' => iaUsers::getIdentity()->id,
            'category_id' => $this->_iaCcat->getRootId(),
            'sponsored' => false,
            'featured' => false,
            'status' => iaCore::STATUS_ACTIVE,
            'expire_date' => date(iaDb::DATETIME_SHORT_FORMAT, strtotime('+1 week'))
        ];
    }

    protected function _preSaveEntry(array &$entry, array $data, $action)
    {
        parent::_preSaveEntry($entry, $data, $action);

        $entry['category_id'] = (int)$data['tree_id'];
        $entry['shop_id'] = 0;

        $entry['title_alias'] = empty($data['title_alias']) ? $data['title'][$this->_iaCore->language['iso']] : $data['title_alias'];
        $entry['title_alias'] = iaSanitize::alias($entry['title_alias']);

        // validate chosen shop
        if (!empty($data['shop'])) {
            if ($shopData = $this->_iaDb->row(iaDb::ID_COLUMN_SELECTION, iaDb::convertIds($data['shop'], 'title_' . $this->_iaCore->language['iso']), 'coupons_shops')) {
                $entry['shop_id'] = $shopData['id'];
            } else {
                $this->addMessage('coupon_shop_incorrect');
            }
        } else {
            $this->addMessage('coupon_shop_empty');
        }

        return !$this->getMessages();
    }

    protected function _assignValues(&$iaView, array &$entryData)
    {
        parent::_assignValues($iaView, $entryData);

        $shopName = empty($_POST['shop'])
            ? $this->_iaDb->one('title_' . $iaView->language, iaDb::convertIds($entryData['shop_id']), 'coupons_shops')
            : $_POST['shop'];

        $iaView->assign('shopName', $shopName);
        $iaView->assign('statuses', $this->getHelper()->getStatuses());
        $iaView->assign('tree', $this->getHelper()->getTreeVars($entryData));
    }

    protected function _getJsonAlias(array $params)
    {
        $title = isset($params['title']) ? $params['title'] : '';
        $title = iaSanitize::alias($title);

        $shop = isset($params['shop']) ? $params['shop'] : '';
        $shopAlias = $this->_iaDb->one('title_alias', iaDb::convertIds($shop, 'title_' . $this->_iaCore->language['iso']), 'coupons_shops');
        if (empty($shopAlias)) {
            $shopAlias = iaLanguage::get('shop_incorrect');
        }

        $data = [
            'id' => (isset($params['id']) && (int)$params['id'] ? (int)$params['id'] : $this->_iaDb->getNextId(iaCoupon::getTable(true))),
            'shop_alias' => $shopAlias,
            'title_alias' => $title
        ];

        $url = $this->getHelper()->url('view', $data);

        return ['data' => $url];
    }

    protected function _getJsonShops(array $params)
    {
        $result = [];

        if (isset($params['q'])) {
            $column = 'title_' . $this->_iaCore->language['iso'];
            $stmt = "`$column` LIKE '" . iaSanitize::sql($params['q']) . "%' ORDER BY `$column`";

            $result['options'] = $this->_iaDb->onefield($column, $stmt, 0, 15, 'coupons_shops');
        }

        return $result;
    }
}
