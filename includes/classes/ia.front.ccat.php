<?php
//##copyright##

class iaCcat extends abstractCouponsPackageFront
{
	protected static $_table = 'coupons_categories';

	protected $_itemName = 'ccats';

	protected $_statuses = array(iaCore::STATUS_ACTIVE, iaCore::STATUS_INACTIVE);


	public function insert(array $entryData)
	{
	}

	public function title($data)
	{
		$title = '';
		if (isset($data['title']))
		{
			$title = $data['title'];
		}

		return $title;
	}

	public function url($action, array $listingData)
	{
		$patterns = array(
			'default' => ':action/:id/',
			'view' => ':alias/'
		);

		$url = iaDb::printf(
			isset($patterns[$action]) ? $patterns[$action] : $patterns['default'],
			array(
				'action' => $action,
				'alias' => isset($listingData['title_alias']) ? $listingData['title_alias'] : '',
				'id' => isset($listingData[self::COLUMN_ID]) ? $listingData[self::COLUMN_ID] : ''
			)
		);

		return $this->getInfo('url') . $url;
	}

	public function getById($id)
	{
		$categories = $this->getCategories('`id` = ' . intval($id), 0, 0, 1);

		return ($categories ? $categories[0] : false);
	}

	public function getRootId()
	{
		return $this->iaDb->one(iaDb::ID_COLUMN_SELECTION, '`parent_id` = -1', 0, 0, self::getTable());
	}

	public function all($aWhere, $fields = '*')
	{
		return $this->iaDb->all($fields, $aWhere, 0, 1, self::getTable());
	}

	public function getCategory($aWhere, $fields = '*')
	{
		return $this->iaDb->row($fields, $aWhere, self::getTable());
	}

	public function getByAlias($alias)
	{
		return $this->iaDb->row(iaDb::ALL_COLUMNS_SELECTION, "`title_alias` = '$alias'", self::getTable());
	}

	public function getCategories($where = '', $aStart = 0, $aLimit = null)
	{
		$fields = '*, `title_alias` `category_alias`, `num_all_coupons` `num` ';

		return $this->iaDb->all($fields, $where . ' ORDER BY `level` ASC, `title`', $aStart, $aLimit, self::getTable());
	}

	public function existsAlias($alias)
	{
		return $this->iaDb->exists('`title_alias` = :alias', array('alias' => $alias), 0, self::getTable());
	}

	public function getRoot()
	{
		return $this->iaDb->row(iaDb::ALL_COLUMNS_SELECTION, '`parent_id` = -1', self::getTable());
	}

	public function getAllCategories($parent, &$categories)
	{
		$id = $parent['id'];
		$cats = $this->iaDb->assoc(array('id', 'parent_id', 'title', 'level'), "`parent_id` = '{$id}'" . ' ORDER BY `title`', self::getTable());

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
}