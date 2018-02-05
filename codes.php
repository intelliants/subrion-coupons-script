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
    $iaView->display('codes');

    $iaCoupon = $iaCore->factoryItem('coupon');

    $pagination = [
        'total' => 0,
        'limit' => 20,
        'start' => 0,
        'url' => IA_SELF . '?page={page}'
    ];

    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
    $pagination['start'] = ($page - 1) * $pagination['limit'];

    if (!isset($iaCore->requestPath[0]) || empty($iaCore->requestPath[0])) {
        return iaView::errorPage(iaView::ERROR_NOT_FOUND);
    }

    // get coupon info
    $couponId = (int)$iaCore->requestPath[0];
    $coupon = $iaCoupon->get("t1.`id` = {$couponId}", '', 1, 0, false, true);

    if (empty($coupon)) {
        return iaView::errorPage(iaView::ERROR_NOT_FOUND);
    }

    $coupon = array_shift($coupon);
    if ($coupon['id'] != iaUsers::getIdentity()->id) {
        return iaView::errorPage(iaView::ERROR_FORBIDDEN);
    }

    // get coupon codes
    $coupon['codes'] = $iaCoupon->getCodes($coupon['id']);

    $iaView->assign('statuses', $iaCoupon->getCodeStatuses());
    $iaView->assign('item', $coupon);
    $iaView->assign('pagination', $pagination);
}
