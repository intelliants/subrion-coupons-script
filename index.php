<?php
//##copyright##

$iaCoupon = $iaCore->factoryModule('coupon', IA_CURRENT_MODULE);

// process ajax actions
if (iaView::REQUEST_JSON == $iaView->getRequestType())
{
	if (!in_array($_GET['trigger'], ['up', 'down']))
	{
		return iaView::errorPage(iaView::ERROR_INTERNAL);
	}

	if ($iaCoupon->incrementThumbsCounter((int)$_GET['id'], $_GET['trigger']))
	{
		$output = [
			'message' => iaLanguage::get('thumbs_vote_accepted'),
			'error' => false,
			'rating' => (int)$iaCoupon->getThumbsNum((int)$_GET['id'])
		];
	}
	else
	{
		$output = ['message' => iaLanguage::get('thumbs_already_voted'), 'error' => true];
	}

	$iaView->assign($output);
}

if (iaView::REQUEST_HTML == $iaView->getRequestType())
{
	$iaCateg = $iaCore->factoryModule('ccat', IA_CURRENT_MODULE);

	iaLanguage::set('no_my_coupons', str_replace('{%URL%}', IA_MODULE_URL . 'coupons/add/', iaLanguage::get('no_my_coupons')));

	$pagination = [
		'total' => 0,
		'limit' => $iaCore->get('coupons_per_page', 2),
		'start' => 0,
		'url' => IA_SELF . '?page={page}'
	];

	$page = isset($_GET['page']) && is_numeric($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
	$pagination['start'] = ($page - 1) * $pagination['limit'];

	$template = iaView::DEFAULT_ACTION;

	$stmt = $iaCore->get('show_expired_coupons') ? '' : 't1.`expire_date` >= NOW() OR t1.`expire_date` = 0';

	switch ($iaView->name())
	{
		case 'new_coupons':
			// get new coupons
			$coupons = $iaCoupon->get($stmt, 't1.`date_added` DESC', $pagination['limit'], $pagination['start'], true);
			$iaView->assign('coupons', $coupons);

			$pagination['total'] = $iaCoupon->foundRows();

			break;

		case 'popular_coupons':
			// get popular coupons
			$coupons = $iaCoupon->get($stmt, 't1.`views_num` DESC', $pagination['limit'], $pagination['start'], true);
			$iaView->assign('coupons', $coupons);

			$pagination['total'] = $iaCoupon->foundRows();

			break;

		case 'printable_coupons':
		case 'deals':
		case 'coupon_codes':
			$typesMap = ['printable_coupons' => 'printable', 'deals' => 'deal', 'coupon_codes' => 'simple'];

			// get coupons
			$coupons = $iaCoupon->get(iaDb::convertIds($typesMap[$iaView->name()], 'coupon_type'), 't1.`date_added` DESC', $pagination['limit'], $pagination['start'], true);
			$iaView->assign('coupons', $coupons);

			$pagination['total'] = $iaCoupon->foundRows();

			break;

		case 'my_coupons':
			if (!iaUsers::hasIdentity())
			{
				return iaView::accessDenied();
			}

			// get member coupons
			$coupons = $iaCoupon->get("t1.`member_id` = '" . iaUsers::getIdentity()->id . "'", 't1.`date_added` DESC', 20, 0, false, true);

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

			$category = $iaCateg->getCategory(iaDb::convertIds($categoryAlias, 'title_alias'));
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
			$categories	= $iaCateg->getCategories("`_pid` = '" . $category['id'] . "' AND `status` = 'active' ");
			$iaView->assign('categories', $categories);

			// get neighbour categories & update page title
			if ($category[iaCcat::COL_PARENT_ID])
			{
				$neighbours = $iaCateg->getCategories("`_pid` = '" . $category[iaCcat::COL_PARENT_ID] . "'AND `id` <> '" . $category['id'] . "' AND `status` = 'active' ");
				$iaView->assign('neighbours', $neighbours);

				$iaView->title($category['title'] . ' ' . iaLanguage::get('coupons'));

				// generate breadcrumb
				if (!empty($category['parents']) && !empty($category['level']))
				{
					if ($parents = $iaCateg->getCategories("`id` IN ({$category['parents']}) AND `_pid` > 0"))
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
			$children = (!empty($category['child']) && $iaCore->get('coupons_show_children')) ? explode(',', $category['child']) : $category['id'];

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

			if (empty($coupon) || iaCore::STATUS_ACTIVE != $coupon['status'])
			{
				return iaView::errorPage(iaView::ERROR_NOT_FOUND);
			}

			$title = iaDb::printf('Coupon ":title"', $coupon);
			$coupon['member_id'] = iaUsers::getIdentity()->id;

			$iaCore->factory('transaction')->create($title, $coupon['cost'], $iaCoupon->getItemName(), $coupon, $iaCoupon->url('view', $coupon));

			break;

		default:
			return iaView::errorPage(iaView::ERROR_NOT_FOUND);
	}
	$iaView->assign('pagination', $pagination);

	$iaPage = $iaCore->factory('page', iaCore::FRONT);
	if ($iaAcl->isAccessible('coupon_add', iaCore::ACTION_ADD))
	{
		$pageActions[] = [
			'icon' => 'plus-square',
			'title' => iaLanguage::get('page_title_coupon_add'),
			'url' => $iaPage->getUrlByName('coupon_add'),
			'classes' => 'btn-success'
		];
		$iaView->set('actions', $pageActions);
	}

	$iaView->display($template);
}