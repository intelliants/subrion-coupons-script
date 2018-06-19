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
    protected $_name = 'categories';

    protected $_helperName = 'ccat';

    protected $_gridColumns = ['title', 'title_alias', 'num_coupons', 'num_all_coupons', 'order', 'locked', 'level', 'status'];
    protected $_gridFilters = ['status' => self::EQUAL, 'title' => self::LIKE];

    protected $_activityLog = ['item' => 'category'];

    protected $_phraseAddSuccess = 'coupon_category_added';
    protected $_phraseGridEntryDeleted = 'coupon_category_deleted';


    protected function _setPageTitle(&$iaView, array $entryData, $action)
    {
        $itemTitle = isset($entryData['title_' . $iaView->language])
            ? $entryData['title_' . $iaView->language]
            : null;

        $iaView->title(iaLanguage::getf($action . '_coupon_category', ['name' => $itemTitle], $iaView->title()));
    }

    protected function _insert(array $entryData)
    {
        return $this->getHelper()->insert($entryData);
    }

    protected function _update(array $entryData, $entryId)
    {
        return $this->getHelper()->update($entryData, $entryId);
    }

    protected function _delete($entryId)
    {
        return $this->getHelper()->delete($entryId);
    }

    protected function _modifyGridParams(&$conditions, &$values, array $params)
    {
        $conditions[] = iaDb::convertIds(iaCcat::ROOT_PARENT_ID, iaCcat::COL_PARENT_ID, false);
    }

    protected function _setDefaultValues(array &$entry)
    {
        $entry = [
            'locked' => false,
            'featured' => false,
            'icon' => false,
            'status' => iaCore::STATUS_ACTIVE,
            'title_alias' => '',

            iaCcat::COL_PARENT_ID => $this->getHelper()->getRootId()
        ];
    }

    protected function _preSaveEntry(array &$entry, array $data, $action)
    {
        parent::_preSaveEntry($entry, $data, $action);

        $entry[iaCcat::COL_PARENT_ID] = empty($data['tree_id']) ? $this->getHelper()->getRootId() : (int)$data['tree_id'];
        $entry['locked'] = (int)$data['locked'];
        $entry['status'] = $data['status'];
        $entry['title_alias'] = iaSanitize::alias(empty($data['title_alias']) ? $data['title'][$this->_iaCore->language['iso']] : $data['title_alias']);

        // add parent alias
        if ($entry[iaCcat::COL_PARENT_ID] != $this->getHelper()->getRootId()) {
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

        $iaView->assign('tree', $this->getHelper()->getTreeVars($this->getEntryId(), $entryData, $this->getPath()));
    }

    protected function _getJsonSlug(array $params)
    {
        $title = isset($params['title']) ? iaSanitize::alias($params['title']) : '';

        $category = isset($params['category']) ? (int)$params['category'] : $this->getHelper()->getRootId();
        $categorySlug = $category != $this->getHelper()->getRootId()
            ? $this->_iaDb->one('title_alias', iaDb::convertIds($category))
            : '';

        $data = [
            'id' => (isset($params['id']) && (int)$params['id'] != $this->getHelper()->getRootId() ? (int)$params['id'] : '{id}'),
            'title_alias' => ($categorySlug ? $categorySlug . IA_URL_DELIMITER : '') . $title,
        ];
        /*
        if ($iaCateg->existsAlias($data['title_alias']))
        {
            $output['exists'] = iaLanguage::get('coupon_category_already_exists');
        }
        */

        $slug = $this->getHelper()->url('view', $data);

        return ['data' => $slug];
    }
}
