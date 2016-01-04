<?php
//##copyright##

$iaCoupon = $iaCore->factoryPackage('coupon', IA_CURRENT_PACKAGE);

// process ajax actions
if (iaView::REQUEST_JSON == $iaView->getRequestType())
{
	if (!in_array($_GET['trigger'], array('up', 'down')))
	{
		return iaView::errorPage(iaView::ERROR_INTERNAL);
	}

	if ($iaCoupon->incrementThumbsCounter((int)$_GET['id'], $_GET['trigger']))
	{
		$output = array(
			'message' => iaLanguage::get('thumbs_vote_accepted'),
			'error' => false,
			'rating' => (int)$iaCoupon->getThumbsNum((int)$_GET['id'])
		);
	}
	else
	{
		$output = array('message' => iaLanguage::get('thumbs_already_voted'), 'error' => true);
	}

	$iaView->assign($output);
}

if (iaView::REQUEST_HTML == $iaView->getRequestType())
{
	$iaCateg = $iaCore->factoryPackage('ccat', IA_CURRENT_PACKAGE);

	iaLanguage::set('no_my_coupons', str_replace('{%URL%}', IA_PACKAGE_URL . 'coupons/add/', iaLanguage::get('no_my_coupons')));

	$pagination = array(
		'total' => 0,
		'limit' => $iaCore->get('coupons_per_page', 2),
		'start' => 0,
		'url' => IA_SELF . '?page={page}'
	);

	$page = isset($_GET['page']) && is_numeric($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
	$pagination['start'] = ($page - 1) * $pagination['limit'];

	$template = iaView::DEFAULT_ACTION;

	$stmt = $iaCore->get('show_expired_coupons') ? '' : 't1.`expire_date` >= NOW() OR t1.`expire_date` = 0';

	switch ($iaView->name())
	{
		case 'new_coupons':
			// get new coupons
			$coupons = $iaCoupon->getCoupons($stmt, 't1.`date_added` DESC', $pagination['limit'], $pagination['start'], true);
			$iaView->assign('coupons', $coupons);

			$pagination['total'] = $iaCoupon->foundRows();

			break;

		case 'popular_coupons':
			// get popular coupons
			$coupons = $iaCoupon->getCoupons($stmt, 't1.`views_num` DESC', $pagination['limit'], $pagination['start'], true);
			$iaView->assign('coupons', $coupons);

			$pagination['total'] = $iaCoupon->foundRows();

			break;

		case 'printable_coupons':
			// get printable coupons
			$coupons = $iaCoupon->getCoupons("`coupon_type` = 'printable'", 't1.`date_added` DESC', $pagination['limit'], $pagination['start'], true);
			$iaView->assign('coupons', $coupons);

			$pagination['total'] = $iaCoupon->foundRows();

			break;

		case 'deals':
			// get deals
			$coupons = $iaCoupon->getCoupons("`coupon_type` = 'deal'", 't1.`date_added` DESC', $pagination['limit'], $pagination['start'], true);
			$iaView->assign('coupons', $coupons);

			$pagination['total'] = $iaCoupon->foundRows();

			break;

		case 'coupon_codes':
			// get coupon codes
			$coupons = $iaCoupon->getCoupons("`coupon_type` = 'simple'", 't1.`date_added` DESC', $pagination['limit'], $pagination['start'], true);
			$iaView->assign('coupons', $coupons);

			$pagination['total'] = $iaCoupon->foundRows();

			break;

		case 'my_coupons':
			if (!iaUsers::hasIdentity())
			{
				return iaView::accessDenied();
			}

			// get member coupons
			$coupons = $iaCoupon->getCoupons("t1.`member_id` = '" . iaUsers::getIdentity()->id . "'", 't1.`date_added` DESC', 20, 0, false, true);

			$iaView->assign('coupons', $coupons);

			$pagination['total'] = $iaCoupon->foundRows();

			break;

		case 'coupons_home':
			// get category by alias
			$categoryAlias = '';
			if ($iaCore->requestPath)
			{
				$categoryAlias = implode(IA_URL_DELIMITER, $iaCore->requestPath);
			}

			$category = $iaCateg->getByAlias(iaSanitize::sql($categoryAlias));
			$iaView->assign('category', $category);

			// requested category not found
			if (!$category)
			{
				return iaView::errorPage(iaView::ERROR_NOT_FOUND);
			}
			$iaView->assign('current_category', $category);

			// increment views counter
			$iaCateg->incrementViewsCounter($category['id']);

			// get categories
			$categories	= $iaCateg->getCategories("`parent_id` = '" . $category['id'] . "' AND `status` = 'active' ");
			$iaView->assign('categories', $categories);

			// get neighbour categories & update page title
			if (-1 != $category['parent_id'])
			{
				$neighbours = $iaCateg->getCategories("`parent_id` = '" . $category['parent_id'] . "'AND `id` <> '" . $category['id'] . "' AND `status` = 'active' ");
				$iaView->assign('neighbours', $neighbours);

				$iaView->title($category['title'] . ' ' . iaLanguage::get('coupons'));

				// generate breadcrumb
				if (!empty($category['parents']) && !empty($category['level']))
				{
					if ($parents = $iaCateg->getCategories("`id` IN ({$category['parents']}) AND `parent_id` > -1"))
					{
						foreach ($parents as $i => $p)
						{
							isset($parents[++$i])
								? iaBreadcrumb::add($p['title'], $iaCateg->url('view', $p))
								: iaBreadcrumb::replaceEnd($p['title'], $iaCateg->url('view', $p));
						}
					}
				}
			}

			$sorting = $iaCoupon->getSorting($_SESSION, $_GET);
			$sortingStmt = 't1.`' . $sorting[0] . '` ' . $sorting[1];

			// get coupons
			$children = (!empty($category['child'])) ? explode(',', $category['child']) : $category['child'];
			$coupons = $iaCoupon->getByCategory($stmt, $children, $pagination['start'], $pagination['limit'], $sortingStmt);
			$iaView->assign('coupons', $coupons);

			$pagination['total'] = $iaCoupon->foundRows();

			$iaView->assign('sorting', $sorting);

			// set shop meta values
			$iaView->set('description', $category['meta_description']);
			$iaView->set('keywords', $category['meta_keywords']);

			break;

		case 'coupon_buy':
			if (empty($iaCore->requestPath[0]) || count($iaCore->requestPath) != 1)
			{
				return iaView::errorPage(iaView::ERROR_NOT_FOUND);
			}

			$coupon = $iaCoupon->getById((int)$iaCore->requestPath[0]);

			if (empty($coupon))
			{
				return iaView::errorPage(iaView::ERROR_NOT_FOUND);
			}

			$title = iaDb::printf('Coupon ":title" - #:id', $coupon);
			$coupon['member_id'] = iaUsers::getIdentity()->id;

			$iaCore->factory('transaction')->create($title, $coupon['cost'], $iaCoupon->getItemName(), $coupon);

			break;

		default:
			return iaView::errorPage(iaView::ERROR_NOT_FOUND);
	}
	$iaView->assign('pagination', $pagination);

	$iaView->display($template);
}