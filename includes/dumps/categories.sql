TRUNCATE TABLE `{prefix}coupons_categories`;
TRUNCATE TABLE `{prefix}coupons_categories_flat`;

INSERT INTO `{prefix}coupons_categories` (`id`,`parent_id`,`title_{lang}`,`title_alias`,`level`) VALUES
(1,0,'ROOT','',0),
(2,1,'Accessories','Accessories',1),
(3,1,'Business','Business',1),
(4,1,'Clothing','Clothing',1),
(5,1,'Computers','Computers',1),
(6,1,'Electronics','Electronics',1),
(7,1,'Food And Drink','Food-And-Drink',1),
(8,1,'Home','Home',1),
(9,1,'Miscellaneous','Miscellaneous',1),
(10,1,'Software','Software',1),
(11,1,'Travel','Travel',1),
(12,1,'Web Hosting','Web-Hosting',1),
(13,1,'Wedding','Wedding',1),
(14,1,'Games','Games',1),
(15,1,'Flowers','Flowers',1),
(16,1,'Insurance','Insurance',1);

UPDATE `{prefix}coupons_categories` SET `status` = 'active',`order` = `id`;

INSERT INTO `{prefix}coupons_categories_flat` VALUES
(2,2),(3,3),(4,4),(5,5),(6,6),(7,7),(8,8),(9,9),
(10,10),(11,11),(12,12),(13,13),(14,14),(15,15),(16,16);