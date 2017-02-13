<?php
//##copyright##

interface iaCouponsPackage
{
	const MODULE_NAME = 'coupons';

	const COLUMN_ID = 'id';

	const STATUS_AVAILABLE = 'available';
	const STATUS_SUSPENDED = 'suspended';
	const STATUS_USED = 'used';
}

abstract class abstractCouponsPackageAdmin extends abstractModuleAdmin implements iaCouponsPackage
{
	protected $_moduleName = 'coupons';
}

abstract class abstractCouponsPackageFront extends abstractModuleFront implements iaCouponsPackage
{
	protected $_moduleName = 'coupons';
}