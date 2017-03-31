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
    protected $_name = 'categories';
    protected $_itemName = 'ccats';

    protected $_helperName = 'ccat';

    protected $_gridColumns = ['title', 'title_alias', 'order', 'locked', 'status'];
    protected $_gridFilters = ['status' => self::EQUAL, 'title' => self::LIKE];

    protected $_activityLog = ['item' => 'category'];

    protected $_phraseAddSuccess = 'coupon_category_added';
    protected $_phraseGridEntryDeleted = 'coupon_category_deleted';

    private $_root;


    public function init()
    {
        $this->_root = $this->getHelper()->getRoot();
        $this->_treeSettings = ['parent_id' => iaCcat::COL_PARENT_ID, 'parents' => iaCcat::COL_PARENTS];
    }

    protected function _setPageTitle(&$iaView, array $entryData, $action)
    {
        parent::_setPageTitle($iaView, $entryData, $action);

        if (iaCore::ACTION_EDIT == $action) {
            $iaView->title(iaLanguage::getf('edit_coupon_category',
                ['name' => $entryData['title_' . $iaView->language]]));
        }
    }

    protected function _entryAdd(array $entryData)
    {
        return $this->getHelper()->insert($entryData);
    }

    protected function _entryUpdate(array $entryData, $entryId)
    {
        return $this->getHelper()->update($entryData, $entryId);
    }

    protected function _modifyGridParams(&$conditions, &$values, array $params)
    {
        $conditions[] = iaDb::convertIds(iaCcat::ROOT_PARENT_ID, iaCcat::COL_PARENT_ID, false);
    }

    protected function _setDefaultValues(array &$entry)
    {
        $entry = [
            iaCcat::COL_PARENT_ID => $this->_root['id'],
            iaCcat::COL_PARENTS => '',

            'locked' => false,
            'featured' => false,
            'icon' => false,
            'status' => iaCore::STATUS_ACTIVE,
            'title_alias' => ''
        ];
    }

    protected function _preSaveEntry(array &$entry, array $data, $action)
    {
        parent::_preSaveEntry($entry, $data, $action);

        $entry[iaCcat::COL_PARENT_ID] = empty($data['tree_id']) ? $this->_root['id'] : (int)$data['tree_id'];
        $entry['locked'] = (int)$data['locked'];
        $entry['status'] = $data['status'];
        $entry['title_alias'] = iaSanitize::alias(empty($data['title_alias']) ? $data['title'][$this->_iaCore->language['iso']] : $data['title_alias']);

        // add parent alias
        if ($entry[iaCcat::COL_PARENT_ID] != $this->_root['id']) {
            $parent = $this->getHelper()->getById($entry[iaCcat::COL_PARENT_ID]);
            $entry['title_alias'] = $parent['title_alias'] . IA_URL_DELIMITER . $entry['title_alias'];
        }

        if ($this->getHelper()->exists($entry['title_alias'], $entry[iaCcat::COL_PARENT_ID], $this->getEntryId())) {
            $this->addMessage('coupon_category_already_exists');
        }

        return !$this->getMessages();
    }

    protected function _assignValues(&$iaView, array &$entryData)
    {
        parent::_assignValues($iaView, $entryData);

        $array = explode(IA_URL_DELIMITER, trim($entryData['title_alias'], IA_URL_DELIMITER));
        $entryData['title_alias'] = end($array);
    }

    protected function _getJsonAlias(array $params)
    {
        $title = isset($params['title']) ? iaSanitize::alias($params['title']) : '';
        $category = isset($params['category']) ? (int)$params['category'] : $this->getHelper()->getRootId();
        $categoryAlias = false;
        if ($category > 0) {
            $categoryAlias = $this->_iaDb->one('title_alias', iaDb::convertIds($category));
        }

        $data = [
            'id' => (isset($params['id']) && (int)$params['id'] > $this->getHelper()->getRootId() ? (int)$params['id'] : '{id}'),
            'title_alias' => ($categoryAlias ? $categoryAlias . IA_URL_DELIMITER : '') . $title,
        ];
        /*
        if ($iaCateg->existsAlias($data['title_alias']))
        {
            $output['exists'] = iaLanguage::get('coupon_category_already_exists');
        }
        */
        $alias = $this->getHelper()->url('view', $data);

        return ['data' => $alias];
    }
}
