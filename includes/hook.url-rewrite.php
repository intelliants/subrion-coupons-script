<?php
//##copyright##

if ($iaView->url)
{
	$package = $iaCore->getModules('coupons');
	if ($package['name'] == $iaCore->get('default_package') && ($iaView->url[0] . IA_URL_DELIMITER == $package['url']))
	{
		array_shift($iaView->url);
	}

	$alias = implode(IA_URL_DELIMITER, $iaView->url);
	if ($alias && $iaDb->exists('`title_alias` = :alias AND `status` = :status', ['alias' => $alias, 'status' => iaCore::STATUS_ACTIVE], 'coupons_categories'))
	{
		$pageName = 'coupons_home';
		if ($pageUrl = $iaDb->one_bind('alias', '`name` = :page AND `status` = :status', ['page' => $pageName, 'status' => iaCore::STATUS_ACTIVE], 'pages'))
		{
			$pageUrl = array_shift(explode(IA_URL_DELIMITER, trim($pageUrl, IA_URL_DELIMITER)));
			$isHomepage = ($pageName == $iaCore->get('home_page'));

			$iaView->name($isHomepage ? $pageName : $pageUrl);
			$iaCore->requestPath = $iaView->url;
		}
	}
}