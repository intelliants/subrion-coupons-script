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

if (iaView::REQUEST_HTML == $iaView->getRequestType()) {
    $iaShop = $iaCore->factoryModule('shop', IA_CURRENT_MODULE);

    switch ($iaView->name()) {
        case 'shop_view':
            $iaCoupon = $iaCore->factoryModule('coupon', IA_CURRENT_MODULE);

            // get shop by id
            if (isset($iaCore->requestPath[0]) && !empty($iaCore->requestPath[0])) {
                $shop = $iaShop->getByAlias($iaCore->requestPath[0]);
                $iaView->assign('shop', $shop);

                if (empty($shop)) {
                    return iaView::errorPage(iaView::ERROR_NOT_FOUND);
                }

                // increment views counter
                $iaShop->incrementViewsCounter($shop['id']);

                $iaCore->startHook('phpViewListingBeforeStart', [
                    'listing' => $shop['id'],
                    'item' => $iaShop->getItemName(),
                    'title' => $shop['title'],
                    'desc' => $shop['description']
                ]);

                // breadcrumb formation
                iaBreadcrumb::add(iaLanguage::get('shops'), IA_MODULE_URL . 'shops/');
                iaBreadcrumb::replaceEnd($shop['title']);

                $coupons = $couponsExpired = [];
                switch (true) {
                    case !isset($_GET['sorting']) || 'all' == $_GET['sorting']:
                        $coupons = $iaCoupon->get("`shop_id` = '{$shop['id']}' AND (`expire_date` >= NOW() OR `expire_date` = 1)", '`expire_date` ASC', 100);
                        $couponsExpired = $iaCore->get('show_expired_coupons') ? $iaCoupon->get("`shop_id` = '{$shop['id']}' AND (`expire_date` < NOW() AND `expire_date` != 0)", '`expire_date` ASC', 100) : [];

                        $couponsNum = count($coupons) + count($couponsExpired);
                        break;

                    case ('active' == $_GET['sorting']):
                        $coupons = $iaCoupon->get("`shop_id` = '{$shop['id']}' AND (`expire_date` >= NOW() OR `expire_date` = 1)", '`expire_date` ASC', 100);
                        $couponsNum = count($coupons);
                        break;

                    case ('expired' == $_GET['sorting']):
                        $couponsExpired = $iaCore->get('show_expired_coupons') ? $iaCoupon->get("`shop_id` = '{$shop['id']}' AND (`expire_date` < NOW() AND `expire_date` != 0)", '`expire_date` ASC', 100) : [];
                        $couponsNum = count($couponsExpired);
                }

                $iaView->assign('coupons', $coupons);
                $iaView->assign('coupons_expired', $couponsExpired);
                $iaView->assign('couponsNum', $couponsNum);

                // set shop meta values
                $iaView->set('description', $shop['meta_description']);
                $iaView->set('keywords', $shop['meta_keywords']);

                $iaView->set('title', $shop['title']);
                $iaView->display('shop-view');
            } else {
                return iaView::errorPage(iaView::ERROR_NOT_FOUND);
            }

            break;

        case 'shop_add':
        case 'shops':
            $letters['all'] = iaUtil::getLetters();
            $letters['active'] = (isset($iaCore->requestPath[0]) && in_array($iaCore->requestPath[0], $letters['all'])) ? $iaCore->requestPath[0] : false;

            $cause = '';
            if ($letters['active']) {
                $cause = ('0-9' == $letters['active']) ?  "(`title` REGEXP '^[0-9]') " : "(`title` LIKE '{$letters['active']}%') ";

                // breadcrumb formation
                iaBreadcrumb::add(iaLanguage::get('shops'), IA_MODULE_URL . 'shops/');
                iaBreadcrumb::replaceEnd($letters['active']);
            }

            // check for letters that have shops
            $letters['existing'] = [];
            $array = $iaDb->all('DISTINCT UPPER(SUBSTR(`title_' . $iaView->language . '`, 1, 1)) `letter`',
                "`status` = 'active'", null, null, iaShop::getTable());
            if ($array) {
                foreach ($array as $item) {
                    $letters['existing'][] = $item['letter'];
                }
            }
            $iaView->assign('letters', $letters);

            // get shops
            $shops = $iaShop->get($cause);
            $iaView->assign('shops', $shops);

            $iaView->display('shops');

            break;

        default:
            return iaView::errorPage(iaView::ERROR_NOT_FOUND);
    }
}
