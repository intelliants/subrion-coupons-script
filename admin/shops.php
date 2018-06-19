<?php
/******************************************************************************
 *
 * Subrion Coupons & Deals Software
 * Copyright (C) 2018 Intelliants, LLC <https://intelliants.com>
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
    protected $_name = 'shops';

    protected $_helperName = 'shop';

    protected $_gridColumns = ['title', 'title_alias', 'date_added', 'status'];

    protected $_gridFilters = ['status' => self::EQUAL, 'title' => self::LIKE];

    protected $_activityLog = ['icon' => 'cart', 'item' => 'shop'];

    private $_fields;

    protected function _gridQuery($columns, $where, $order, $start, $limit)
    {
        $sql = <<<SQL
SELECT  :columns, (SELECT COUNT(*) FROM :table_coupons WHERE s.`id` = :table_coupons.`shop_id`) `coupons_num`
  FROM `:table_shops` s
LIMIT :start, :limit
SQL;
        $sql = iaDb::printf($sql, [
            'table_shops' => $this->_iaDb->prefix . $this->getTable(),
            'table_coupons' => $this->_iaDb->prefix . 'coupons_coupons',
            'columns' => str_replace(':lang', $this->_iaCore->language['iso'], $columns),
            'where' => $where,
            'order' => $order,
            'start' => (int)$start,
            'lang' => $this->_iaCore->language['iso'],
            'limit' => (int)$limit
        ]);

        return $this->_iaDb->getAll($sql);
    }

    public function __construct()
    {
        parent::__construct();

        $this->_fields = $this->_iaField->get($this->getItemName());
    }

    protected function _setPageTitle(&$iaView, array $entryData, $action)
    {
        iaCore::ACTION_EDIT == $action
            ? $iaView->title(iaLanguage::getf('edit_shop', ['name' => $entryData['title_' . $iaView->language]]))
            : parent::_setPageTitle($iaView, $entryData, $action);
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

    protected function _modifyGridParams(&$conditions, &$values, array $params)
    {
        if (!empty($params['member'])) {
            $conditions[] = iaDb::convertIds($params['member'], 'member_id');
        }
    }

    protected function _setDefaultValues(array &$entry)
    {
        $entry = [
            'member_id' => iaUsers::getIdentity()->id,
            'featured' => false,
            'status' => iaCore::STATUS_ACTIVE
        ];
    }

    protected function _assignValues(&$iaView, array &$entryData)
    {
        parent::_assignValues($iaView, $entryData);

        $websiteFieldExists = false;

        foreach ($this->_fields as $field) {
            if ('website' == $field['name']) {
                $websiteFieldExists = true;
                break;
            }
        }

        $iaView->assign('statuses', $this->getHelper()->getStatuses());
        $iaView->assign('website_field_exists', $websiteFieldExists);
    }

    protected function _preSaveEntry(array &$entry, array $data, $action)
    {
        parent::_preSaveEntry($entry, $data, $action);

        if (!empty($data['title_alias'])) {
            $entry['title_alias'] = $data['title_alias'];
        } elseif (!empty($data['website']) && 'http://' != $data['website']) {
            $entry['title_alias'] = $data['website'];
            $entry['title_alias'] = $entry['title_alias'] ? str_ireplace('www.', '', parse_url($entry['title_alias'], PHP_URL_HOST)) : '';
        } else {
            $entry['title_alias'] = $data['title'][$this->_iaCore->language['iso']];
        }

        $entry['title_alias'] = iaSanitize::alias($entry['title_alias']);

        return !$this->getMessages();
    }

    protected function _getJsonAlias(array $params)
    {
        $result = ['alias' => ''];

        $title = isset($params['title']) ? $params['title'] : '';
        if (!isset($params['alias'])) {
            if ('website' == $params['type'] && $title) {
                $title = str_ireplace('www.', '', parse_url($title, PHP_URL_HOST));
            }

            $title = iaSanitize::alias($title);
        }

        $data = [
            'id' => (isset($params['id']) && (int)$params['id'] > 0 ? (int)$params['id'] : '{id}'),
            'title_alias' => $title
        ];

        if (!isset($params['alias'])
            && $this->_iaDb->exists('`title_alias` = :alias AND `id` != :id', ['alias' => $data['title_alias'], 'id' => (int)$params['id']], $this->getTable())) {
            $result['exists'] = iaLanguage::get('coupon_shop_already_exists');
        }

        $result['data'] = $this->getHelper()->url('view', $data);

        return $result;
    }
}
