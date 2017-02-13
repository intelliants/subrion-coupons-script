<?php
//##copyright##

class iaCcat extends abstractCouponsPackageFront
{
	protected static $_table = 'coupons_categories';

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
				'id' => isset($listingData[self::COLUMN_ID]) ? $listingData[self::COLUMN_ID] : ''
			]
		);

		return $this->getInfo('url') . $url;
	}

	public function getById($id)
	{
		$categories = $this->getCategories(iaDb::convertIds($id));

		return $categories ? $categories[0] : [];
	}

	public function getCategory($where = '', $start = 0, $limit = null)
	{
		$categories = $this->getCategories($where, $start, $limit);

		return $categories ? $categories[0] : [];
	}

	public function getCategories($where = '', $start = 0, $limit = null)
	{
		$fields = '*, `title_alias` `category_alias`, `num_all_coupons` `num` ';
		$categories = $this->iaDb->all($fields, $where . ' ORDER BY `level` ASC, `title`', $start, $limit, self::getTable());

		return $this->_processValues($categories);
	}

	public function existsAlias($alias)
	{
		return $this->iaDb->exists('`title_alias` = :alias', ['alias' => $alias], 0, self::getTable());
	}

	public function getRoot()
	{
		return $this->iaDb->row(iaDb::ALL_COLUMNS_SELECTION, '`parent_id` = -1', self::getTable());
	}

	public function getAllCategories($parent, &$categories)
	{
		$id = $parent['id'];
		$cats = $this->iaDb->assoc(['id', 'parent_id', 'title', 'level'], "`parent_id` = '{$id}'" . ' ORDER BY `title`', self::getTable());

		if ($cats)
		{
			foreach ($cats as $id => $category)
			{
				$category['id'] = $id;
				$categories[$id] = $category;

				$this->getAllCategories($category, $categories);
			}
		}
	}

	protected function _processValues(array &$rows)
	{
		foreach ($rows as &$row)
		{
			if (!is_array($row))
			{
				break;
			}

			!$row['icon'] || $row['icon'] = unserialize($row['icon']);
		}

		return $rows;
	}
}