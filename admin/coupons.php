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
    protected $_itemName = 'coupons';

    protected $_helperName = 'coupon';

    protected $_gridColumns = ['title', 'title_alias', 'expire_date', 'date_added', 'coupon_type', 'short_description', 'status', 'reported_as_problem', 'reported_as_problem_comments'];
    //protected $_gridColumns = '`id`, `title`, `title_alias`, `expire_date`, `date_added`, (:sql_category) `category`, `coupon_type`, (:sql_member) `member`, `short_description`, `status`, `reported_as_problem`, `reported_as_problem_comments`';
    protected $_gridFilters = ['status' => self::EQUAL, 'coupon_type' => self::EQUAL, 'title' => self::LIKE];

    protected $_phraseAddSuccess = 'coupon_added';

    protected $_activityLog = ['icon' => 'tag', 'item' => 'coupon'];

    private $_iaCcat;


    public function init()
    {
        $this->_iaCcat = $this->_iaCore->factoryModule('ccat', $this->getModuleName(), iaCore::ADMIN);

        $this->_treeSettings = ['parent_id' => iaCcat::COL_PARENT_ID, 'parents' => iaCcat::COL_PARENTS];
    }

    protected function _entryAdd(array $entryData)
    {
        $entryData['date_added'] = date(iaDb::DATETIME_FORMAT);
        $entryData['date_modified'] = date(iaDb::DATETIME_FORMAT);

        return parent::_entryAdd($entryData);
    }

    protected function _entryUpdate(array $entryData, $entryId)
    {
        $entryData['date_modified'] = date(iaDb::DATETIME_FORMAT);

        return parent::_entryUpdate($entryData, $entryId);
    }

    public function updateCounters($entryId, array $entryData, $action, $previousData = null)
    {
        if (in_array($action, iaCore::ACTION_ADD, iaCore::ACTION_EDIT)) {
            $this->_iaCore->startHook('phpAddItemAfterAll', [
                'type' => iaCore::ADMIN,
                'listing' => $entryId,
                'item' => $this->getItemName(),
                'data' => $entryData,
                'old' => $previousData
            ]);
        }

        $this->getHelper()->updateCounters($entryId, $entryData, $action, $previousData);
    }

    protected function _unpackGridColumnsArray()
    {
        $prefix = $this->_iaDb->prefix;

        $sqlCategory = 'SELECT `title_' . $this->_iaCore->language['iso'] . '` FROM `' . $prefix . 'coupons_categories` c WHERE c.`id` = `category_id`';
        $sqlMember = 'SELECT `username` FROM `' . $prefix . iaUsers::getTable() . '` m WHERE m.`id` = `member_id`';

        $columns = parent::_unpackGridColumnsArray() . ', (:sql_category) `category`, (:sql_member) `member`';
        $columns = str_replace([':sql_category', ':sql_member'], [$sqlCategory, $sqlMember], $columns);

        return $columns;
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

        $entry['title_alias'] = empty($data['title_alias']) ? $data['title'] : $data['title_alias'];
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
    }

    protected function _getTreeVars(array $entryData)
    {
        $category = empty($entryData['category_id'])
            ? $this->_iaCcat->getRoot()
            : $this->_iaCcat->getById($entryData['category_id']);

        return [
            'url' => IA_ADMIN_URL . 'coupons/categories/tree.json?noroot',
            'nodes' => $category[iaCcat::COL_PARENTS],
            'id' => $category['id'],
            'title' => $category['title']
        ];
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
