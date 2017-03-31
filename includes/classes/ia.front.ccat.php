<?php
//##copyright##

class iaCcat extends iaAbstractFrontHelperCategoryHybrid
{
	protected static $_table = 'coupons_categories';

    protected $_moduleName = 'coupons';

	protected $_itemName = 'ccats';


	public function url($action, array $listingData)
	{
		$patterns = [
			'default' => ':action/:id/',
			'view' => ':alias/'
		];

		$url = iaDb::printf(
			isset($patterns[$action]) ? $patterns[$action] : $patterns['default'],
			[
				'action' => $action,
				'alias' => isset($listingData['title_alias']) ? $listingData['title_alias'] : '',
				'id' => isset($listingData['id']) ? $listingData['id'] : ''
			]
		);

		return $this->getInfo('url') . $url;
	}

	public function getById($id, $decorate = true)
	{
		$rows = $this->getCategories(iaDb::convertIds($id));

		$decorate && $this->_processValues($rows);

		return $rows ? $rows[0] : $rows;
	}

	public function getCategory($where = '', $start = 0, $limit = null)
	{
		$categories = $this->getCategories($where, $start, $limit);

		return $categories ? $categories[0] : [];
	}

	public function getCategories($where = '', $start = 0, $limit = null)
	{
		$rows = $this->iaDb->all('*, `title_alias` `category_alias`, `num_all_coupons` `num` ', $where
			. ' ORDER BY `level` ASC, `title_' . $this->iaView->language . '`', $start, $limit, self::getTable());

		$this->_processValues($rows);

		return $rows;
	}

	public function existsAlias($alias)
	{
		return $this->iaDb->exists('`title_alias` = :alias', ['alias' => $alias], 0, self::getTable());
	}

	public function getAllCategories($parentId, &$categories)
	{
		$cats = $this->iaDb->assoc(['id', self::COL_PARENT_ID, 'title' => 'title_' . $this->iaView->language,
            self::COL_LEVEL], iaDb::convertIds($parentId, self::COL_PARENT_ID) . ' ORDER BY `title`', self::getTable());

		if ($cats)
		{
			foreach ($cats as $id => $category)
			{
				$categories[$id] = $category;
				$this->getAllCategories($id, $categories);
			}
		}
	}
}