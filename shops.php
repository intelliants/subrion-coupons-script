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
    $iaShop = $iaCore->factoryItem('shop');

    switch ($iaView->name()) {
        case 'shop_view':
            $iaCoupon = $iaCore->factoryItem('coupon');

            // get shop by id
            if (isset($iaCore->requestPath[0]) && !empty($iaCore->requestPath[0])) {

                $shop = $iaShop->getByAlias($iaCore->requestPath[0]);
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
                        $coupons = $iaCoupon->get("`shop_id` = '{$shop['id']}' && (`expire_date` IS NULL || `expire_date` >= NOW())", '`expire_date` ASC', 100);
                        $couponsExpired = $iaCore->get('show_expired_coupons') ? $iaCoupon->get("`shop_id` = '{$shop['id']}' && `expire_date` IS NOT NULL && `expire_date` < NOW()", '`expire_date` ASC', 100) : [];

                        $couponsNum = count($coupons) + count($couponsExpired);
                        break;

                    case ('active' == $_GET['sorting']):
                        $coupons = $iaCoupon->get("`shop_id` = '{$shop['id']}' && (`expire_date` IS NULL || `expire_date` >= NOW())", '`expire_date` ASC', 100);
                        $couponsNum = count($coupons);
                        break;

                    case ('expired' == $_GET['sorting']):
                        $couponsExpired = $iaCore->get('show_expired_coupons') ? $iaCoupon->get("`shop_id` = {$shop['id']} && (`expire_date` IS NOT NULL && `expire_date` < NOW()) ", '`expire_date` ASC', 100) : [];
                        $couponsNum = count($couponsExpired);
                }

                $iaView->assign('coupons', $coupons);
                $iaView->assign('coupons_expired', $couponsExpired);
                $iaView->assign('couponsNum', $couponsNum);

                $sections = $iaCore->factory('field')->getTabs($iaShop->getItemName(), $shop);
                $iaView->assign('sections', $sections);

                // set shop meta values
                $iaView->set('description', $shop['meta_description_' . $iaView->language]);
                $iaView->set('keywords', $shop['meta_keywords_' . $iaView->language]);

                $iaView->set('title', $shop['title_' . $iaView->language]);

                $iaView->assign('item', $shop);

                $iaView->display('shop-view');
            } else {
                return iaView::errorPage(iaView::ERROR_NOT_FOUND);
            }

            break;

        case 'shops':

            // gets current page and defines start position
            $pagination = [
                'limit' => $iaCore->get('coupons_shops_per_page', 20),
                'url' => $iaCore->factory('page', iaCore::FRONT)->getUrlByName('shops') . '?page={page}'
            ];
            $page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
            $start = (max($page, 1) - 1) * $pagination['limit'];

            $shopsList = $iaShop->get('', null, $pagination['limit'], $start, true);
            $pagination['total'] = $iaShop->foundRows();

            $iaView->assign('pagination', $pagination);
            $iaView->assign('shops', $shopsList);

            $iaView->display('shops');

            break;

        default:
            return iaView::errorPage(iaView::ERROR_NOT_FOUND);
    }

    $iaView->set('filtersItemName', $iaShop->getItemName());
}
