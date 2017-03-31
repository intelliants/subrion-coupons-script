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

class iaCcat extends iaAbstractHelperCategoryHybrid
{
    protected static $_table = 'coupons_categories';

    protected $_moduleName = 'coupons';

    protected $_itemName = 'ccats';

    protected $_moduleUrl = 'coupons/categories/';

    protected $_flatStructureEnabled = true;

    private $patterns = [
        'default' => ':location_alias:title_alias/',
    ];


    public function url($action, array $data)
    {
        $data['action'] = $action;
        $data['location_alias'] = (isset($data['location_alias']) ? $data['location_alias'] . IA_URL_DELIMITER : '');

        unset($data['location'], $data['title'], $data['alias']);

        if (!isset($this->patterns[$action])) {
            $action = 'default';
        }

        $url = trim(iaDb::printf($this->patterns[$action], $data), IA_URL_DELIMITER) . IA_URL_DELIMITER;

        return $this->getinfo('url') . $url;
    }

    public function insert(array $itemData)
    {
        $itemData['order'] = $this->iaDb->getMaxOrder(self::getTable()) + 1;

        return parent::insert($itemData);
    }

    public function exists($alias, $parentId, $id = false)
    {
        return $id
            ? (bool)$this->iaDb->exists('`title_alias` = :alias AND `parent_id` = :parent AND `id` != :id', ['alias' => $alias, 'parent' => $parentId, 'id' => $id], self::getTable())
            : (bool)$this->iaDb->exists('`title_alias` = :alias AND `parent_id` = :parent', ['alias' => $alias, 'parent' => $parentId], self::getTable());
    }

    public function getSitemapEntries()
    {
        $result = [];

        $stmt = '`status` = :status AND `parent_id` > 0';
        $this->iaDb->bind($stmt, ['status' => iaCore::STATUS_ACTIVE]);
        if ($rows = $this->iaDb->all(['title_alias'], $stmt, null, null, self::getTable())) {
            foreach ($rows as $row) {
                $result[] = $this->url(null, $row);
            }
        }

        return $result;
    }
}
