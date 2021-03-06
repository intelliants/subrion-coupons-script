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

class iaShop extends abstractModuleAdmin implements iaCouponsModule
{
    protected static $_table = 'coupons_shops';

    protected $_itemName = 'shop';

    protected $_statuses = [iaCore::STATUS_ACTIVE, iaCore::STATUS_INACTIVE, self::STATUS_SUSPENDED];

    private $patterns = [
        'default' => ':action/:id/',
        'view' => 'shop/:title_alias.html',
        'edit' => 'edit/?id=:id',
        'add' => 'add/',
    ];

    public $dashboardStatistics = ['icon' => 'cart'];


    public function url($action, $data = [])
    {
        $data['action'] = $action;
        unset($data['title']);

        if (!isset($this->patterns[$action])) {
            $action = 'default';
        }

        $url = iaDb::printf($this->patterns[$action], $data);

        return $this->getInfo('url') . $url;
    }

    public function getSitemapEntries()
    {
        $result = [];

        $stmt = '`status` = :status';
        $this->iaDb->bind($stmt, ['status' => iaCore::STATUS_ACTIVE]);
        if ($rows = $this->iaDb->all(['title_alias'], $stmt, null, null, self::getTable())) {
            foreach ($rows as $row) {
                $result[] = $this->url('view', $row);
            }
        }

        return $result;
    }
}
