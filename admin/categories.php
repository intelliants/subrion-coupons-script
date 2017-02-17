<?php
//##copyright##

class iaBackendController extends iaAbstractControllerModuleBackend
{
	protected $_name = 'categories';
	protected $_itemName = 'ccats';

	protected $_helperName = 'ccat';

	protected $_gridColumns = ['title', 'title_alias', 'order', 'locked', 'status'];
	protected $_gridFilters = ['status' => self::EQUAL, 'title' => self::LIKE];

	protected $_activityLog = ['item' => 'category'];

	protected $_phraseAddSuccess = 'coupon_category_added';
	protected $_phraseGridEntryDeleted = 'coupon_category_deleted';

	private $_root;


	public function __construct()
	{
		parent::__construct();

		$this->_root = $this->getHelper()->getRoot();
	}

	protected function _entryAdd(array $entryData)
	{
		$entryData['order'] = $this->_iaDb->getMaxOrder() + 1;

		return parent::_entryAdd($entryData);
	}

	protected function _modifyGridParams(&$conditions, &$values, array $params)
	{
		$conditions[] = '`parent_id` >= 0';
	}

	public function updateCounters($entryId, array $entryData, $action, $previousData = null)
	{
		$this->getHelper()->rebuildRelation();
	}

	protected function _setDefaultValues(array &$entry)
	{
		$entry = [
			'parent_id' => $this->_root['id'],
			'locked' => 0,
			'featured' => false,
			'icon' => false,
			'status' => iaCore::STATUS_ACTIVE,
			'title_alias' => ''
		];
	}

	protected function _preSaveEntry(array &$entry, array $data, $action)
	{
		parent::_preSaveEntry($entry, $data, $action);

		$entry['parent_id'] = empty($data['tree_id']) ? $this->_root['id'] : (int)$data['tree_id'];
		$entry['locked'] = (int)$data['locked'];
		$entry['status'] = $data['status'];
		$entry['title_alias'] = iaSanitize::alias(empty($data['title_alias']) ? $data['title'][$this->_iaCore->language['iso']] : $data['title_alias']);

		// add parent alias
		if ($entry['parent_id'] != $this->_root['id'])
		{
			$parent = $this->getHelper()->getById($entry['parent_id']);
			$entry['title_alias'] = $parent['title_alias'] . IA_URL_DELIMITER . $entry['title_alias'];
		}

		if ($this->getHelper()->exists($entry['title_alias'], $entry['parent_id'], $this->getEntryId()))
		{
			$this->addMessage('coupon_category_already_exists');
		}

		return !$this->getMessages();
	}

	protected function _assignValues(&$iaView, array &$entryData)
	{
		parent::_assignValues($iaView, $entryData);

		$parent = $this->_iaDb->row(['id', 'title' => 'title_' . $iaView->language, 'parents', 'child'],
			iaDb::convertIds($entryData['parent_id']));

		$iaView->assign('parent', $parent);
	}

	protected function _setPageTitle(&$iaView, array $entryData, $action)
	{
		parent::_setPageTitle($iaView, $entryData, $action);

		if (iaCore::ACTION_EDIT == $action)
		{
			$iaView->title(iaLanguage::getf('edit_coupon_category', ['name' => $entryData['title_' . $iaView->language]]));
		}
	}

	protected function _getJsonAlias(array $params)
	{
		$title = isset($params['title']) ? iaSanitize::alias($params['title']) : '';
		$category = isset($params['category']) ? (int)$params['category'] : $this->getHelper()->getRootId();
		$categoryAlias = false;
		if ($category > 0)
		{
			$categoryAlias = $this->_iaDb->one('title_alias', iaDb::convertIds($category));
		}

		$data = [
			'id' => (isset($params['id']) && (int)$params['id'] > $this->getHelper()->getRootId() ? (int)$params['id'] : '{id}'),
			'title_alias' => ($categoryAlias ? $categoryAlias . IA_URL_DELIMITER : '') . $title,
		];
		/*
		if ($iaCateg->existsAlias($data['title_alias']))
		{
			$output['exists'] = iaLanguage::get('coupon_category_already_exists');
		}
		*/
		$alias = $this->getHelper()->url('view', $data);

		return ['data' => $alias];
	}
}