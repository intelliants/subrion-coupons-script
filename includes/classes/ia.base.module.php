<?php
//##copyright##

interface iaCouponsModule
{
	const MODULE_NAME = 'coupons';

	const COLUMN_ID = 'id';

	const STATUS_AVAILABLE = 'available';
	const STATUS_SUSPENDED = 'suspended';
	const STATUS_USED = 'used';
}

abstract class abstractCouponsModuleAdmin extends abstractModuleAdmin implements iaCouponsModule
{
	protected $_moduleName = 'coupons';
}

abstract class abstractCouponsModuleFront extends abstractModuleFront implements iaCouponsModule
{
	protected $_moduleName = 'coupons';
}