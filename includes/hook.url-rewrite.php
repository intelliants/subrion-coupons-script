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

if ($iaView->url) {
    $package = $iaCore->getModules('coupons');
    if ($package['name'] == $iaCore->get('default_package') && ($iaView->url[0] . IA_URL_DELIMITER == $package['url'])) {
        array_shift($iaView->url);
    }

    $alias = implode(IA_URL_DELIMITER, $iaView->url);
    if ($alias && $iaDb->exists('`title_alias` = :alias AND `status` = :status', ['alias' => $alias, 'status' => iaCore::STATUS_ACTIVE], 'coupons_categories')) {
        $pageName = 'coupons_home';

        if ($pageUrl = $iaDb->one_bind('alias', '`name` = :page AND `status` = :status', ['page' => $pageName, 'status' => iaCore::STATUS_ACTIVE], 'pages')) {
            $pageUrl = explode(IA_URL_DELIMITER, trim($pageUrl, IA_URL_DELIMITER));
            $pageUrl = array_shift($pageUrl);

            $isHomepage = ($pageName == $iaCore->get('home_page'));

            $iaView->name($isHomepage ? $pageName : $pageUrl);
            $iaCore->requestPath = $iaView->url;
        }
    }
}
