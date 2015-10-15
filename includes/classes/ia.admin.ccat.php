<?php
//##copyright##

class iaCcat extends abstractCouponsPackageAdmin
{
	protected static $_table = 'coupons_categories';

	protected $_itemName = 'ccats';

	protected $_moduleUrl = 'coupons/categories/';

	private $patterns = array(
		'default' => ':location_alias:title_alias/',
	);


	public function url($action, array $data)
	{
		$data['action'] = $action;
		$data['location_alias'] = (isset($data['location_alias']) ? $data['location_alias'] . IA_URL_DELIMITER : '');

		unset($data['location'], $data['title'], $data['alias']);

		if (!isset($this->patterns[$action]))
		{
			$action = 'default';
		}

		$url = trim(iaDb::printf($this->patterns[$action], $data), IA_URL_DELIMITER) . IA_URL_DELIMITER;

		return $this->getinfo('url') . $url;
	}

	/*
	 * Rebuild categories relations.
	 * Fields to be updated: parents, child, level, title_alias
	 */
	public function rebuildRelation()
	{
		$table_flat = $this->iaDb->prefix . 'coupons_categories_flat';
		$table = self::getTable(true);

		$insert_second = 'INSERT INTO ' . $table_flat . ' (`parent_id`, `category_id`) SELECT t . `parent_id`, t . `id` FROM ' . $table . ' t WHERE t . `parent_id` != -1';
		$insert_first = 'INSERT INTO ' . $table_flat . ' (`parent_id`, `category_id`) SELECT t . `id`, t . `id` FROM ' . $table . ' t WHERE t . `parent_id` != -1';
		$update_level = 'UPDATE ' . $table . ' s SET `level` = (SELECT COUNT(`category_id`)-1 FROM ' . $table_flat . ' f WHERE f . `category_id` = s . `id`) WHERE s . `parent_id` != -1;';
		$update_child = 'UPDATE ' . $table . ' s SET `child` = (SELECT GROUP_CONCAT(`category_id`) FROM ' . $table_flat . ' f WHERE f . `parent_id` = s . `id`);';
		$update_parent = 'UPDATE ' . $table . ' s SET `parents` = (SELECT GROUP_CONCAT(`parent_id`) FROM ' . $table_flat . ' f WHERE f . `category_id` = s . `id`);';

		$num = 1;
		$count = 0;

		$iaDb = &$this->iaDb;
		$iaDb->truncate($table_flat);
		$iaDb->query($insert_first);
		$iaDb->query($insert_second);

		while($num > 0 && $count < 10)
		{
			$count++;
			$num = 0;
			$sql = 'INSERT INTO ' . $table_flat . ' (`parent_id`, `category_id`) '
					. 'SELECT DISTINCT t . `id`, h' . $count . ' . `id` FROM ' . $table . ' t, ' . $table . ' h0 ';
			$where = ' WHERE h0 . `parent_id` = t . `id` ';

			for ($i = 1; $i <= $count; $i++)
			{
				$sql .= 'LEFT JOIN ' . $table . ' h' . $i . ' ON (h' . $i . '.`parent_id` = h' . ($i - 1) . '.`id`) ';
				$where .= ' AND h' . $i . '.`id` is not null';
			}

			if ($iaDb->query($sql . $where))
			{
				$num = $iaDb->getAffected();
			}
		}
		$iaDb->query($update_level);
		$iaDb->query($update_child);
		$iaDb->query($update_parent);

		$iaDb->query('UPDATE ' . $table . ' SET `order` = 1 WHERE `order` = 0');
	}

	public function exists($alias, $parentId, $id = false)
	{
		return $id
			? (bool)$this->iaDb->exists('`title_alias` = :alias AND `parent_id` = :parent AND `id` != :id', array('alias' => $alias, 'parent' => $parentId, 'id' => $id), self::getTable())
			: (bool)$this->iaDb->exists('`title_alias` = :alias AND `parent_id` = :parent', array('alias' => $alias, 'parent' => $parentId), self::getTable());
	}

	public function getRoot()
	{
		return $this->iaDb->row(iaDb::ALL_COLUMNS_SELECTION, '`parent_id` = -1', self::getTable());
	}

	public function getRootId()
	{
		return $this->iaDb->one(iaDb::ID_COLUMN_SELECTION, '`parent_id` = -1', self::getTable());
	}

	public function getSitemapEntries()
	{
		$result = array();

		$stmt = '`status` = :status AND `parent_id` != -1';
		$this->iaDb->bind($stmt, array('status' => iaCore::STATUS_ACTIVE));
		if ($rows = $this->iaDb->all(array('title_alias'), $stmt, null, null, self::getTable()))
		{
			foreach ($rows as $row)
			{
				$result[] = $this->url(null, $row);
			}
		}

		return $result;
	}
}