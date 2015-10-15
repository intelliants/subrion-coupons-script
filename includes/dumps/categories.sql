TRUNCATE TABLE `{prefix}coupons_categories`;
INSERT INTO `{prefix}coupons_categories` (`id`,`parent_id`,`title`,`title_alias`,`level`,`parents`,`child`) VALUES
(1,-1,'ROOT','',0,0,'2,3,14,4,5,6,7,8,9,10,11,12,13,15,16'),
(2,1,'Accessories','Accessories',1,'2,1','2'),
(3,1,'Business','Business',1,'3,1','3'),
(4,1,'Clothing','Clothing',1,'4,1','4'),
(5,1,'Computers','Computers',1,'5,1','5'),
(6,1,'Electronics','Electronics',1,'6,1','6'),
(7,1,'Food And Drink','Food-And-Drink',1,'7,1','7'),
(8,1,'Home','Home',1,'8,1','8'),
(9,1,'Miscellaneous','Miscellaneous',1,'9,1','9'),
(10,1,'Software','Software',1,'10,1','10'),
(11,1,'Travel','Travel',1,'11,1','11'),
(12,1,'Web Hosting','Web-Hosting',1,'12,1','12'),
(13,1,'Wedding','Wedding',1,'13,1','13'),
(14,1,'Games','Games',1,'14,1','14'),
(15,1,'Flowers','Flowers',1,'15,1','15'),
(16,1,'Insurance','Insurance',1,'16,1','16');
UPDATE `{prefix}coupons_categories` SET `status` = 'active',`order` = `id`;
TRUNCATE TABLE `{prefix}coupons_categories_flat`;
INSERT INTO `{prefix}coupons_categories_flat` (`parent_id`,`category_id`) VALUES
(1,2),(1,3),(1,4),(1,5),(1,6),(1,7),(1,8),(1,9),(1,10),(1,11),(1,12),(1,13),(1,14),(1,15),(1,16),(2,2),
(3,3),(4,4),(5,5),(6,6),(7,7),(8,8),(9,9),(10,10),(11,11),(12,12),(13,13),(14,14),(15,15),(16,16);