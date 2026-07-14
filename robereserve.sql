CREATE DATABASE IF NOT EXISTS robereserve;
USE robereserve;
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` TEXT,
  `product_name` TEXT,
  `product_type` TEXT,
  `description` TEXT,
  `price` TEXT,
  `image_path` TEXT,
  `quantity` TEXT
);
INSERT INTO `products` (`id`,`product_name`,`product_type`,`description`,`price`,`image_path`,`quantity`) VALUES ('1','Diploma Robe','robe','Diploma robe rental','15.00','images/diploma_robe.jpg','10');
INSERT INTO `products` (`id`,`product_name`,`product_type`,`description`,`price`,`image_path`,`quantity`) VALUES ('2','Degree Robe','robe','Degree robe rental','25.00','images/degree_robe.png','10');
INSERT INTO `products` (`id`,`product_name`,`product_type`,`description`,`price`,`image_path`,`quantity`) VALUES ('3','Master Robe','robe','Master robe rental','35.00','images/master_robe.png','10');
INSERT INTO `products` (`id`,`product_name`,`product_type`,`description`,`price`,`image_path`,`quantity`) VALUES ('4','PhD Robe','robe','PhD robe rental','40.00','images/phd_robe.jpg','10');
INSERT INTO `products` (`id`,`product_name`,`product_type`,`description`,`price`,`image_path`,`quantity`) VALUES ('5','Hood','hood','Program-specific hood','10.00','images/hood.jpg','10');
INSERT INTO `products` (`id`,`product_name`,`product_type`,`description`,`price`,`image_path`,`quantity`) VALUES ('6','Mortar Board','cap','Mortar board cap','10.00','images/mortarboard.jpg','10');
INSERT INTO `products` (`id`,`product_name`,`product_type`,`description`,`price`,`image_path`,`quantity`) VALUES ('7','Bonnet','cap','Bonnet cap','15.00','images/bonnet.jpg','10');
INSERT INTO `products` (`id`,`product_name`,`product_type`,`description`,`price`,`image_path`,`quantity`) VALUES ('8','Diploma Set','package','One set (robe, hood, mortar board)','30.00','images/diploma_set.jpg','10');
INSERT INTO `products` (`id`,`product_name`,`product_type`,`description`,`price`,`image_path`,`quantity`) VALUES ('9','Degree Set','package','One set (robe, hood, mortar board)','40.00','images/degree_set.jpg','10');
INSERT INTO `products` (`id`,`product_name`,`product_type`,`description`,`price`,`image_path`,`quantity`) VALUES ('10','Master Set','package','One set (robe, hood, bonnet)','50.00','images/master_set.jpg','10');
INSERT INTO `products` (`id`,`product_name`,`product_type`,`description`,`price`,`image_path`,`quantity`) VALUES ('11','PhD Set','package','One set (robe, hood, bonnet)','60.00','images/phd_set.png','10');
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` TEXT,
  `full_name` TEXT,
  `email` TEXT,
  `password` TEXT,
  `role` TEXT
);
INSERT INTO `users` (`id`,`full_name`,`email`,`password`,`role`) VALUES ('1','Admin UiTM','admin@uitm.my','admin123','admin');
INSERT INTO `users` (`id`,`full_name`,`email`,`password`,`role`) VALUES ('2','hanis','hanisrahman47@gmail.com','hanis196','user');
INSERT INTO `users` (`id`,`full_name`,`email`,`password`,`role`) VALUES ('3','HANIS SOFEA','hanisrahman45@gmail.com','hanis123','user');
INSERT INTO `users` (`id`,`full_name`,`email`,`password`,`role`) VALUES ('4','Admin2','admin2@uitm.my','admin234','admin');
DROP TABLE IF EXISTS `reservations`;
CREATE TABLE `reservations` (
  `id` TEXT,
  `user_id` TEXT,
  `robe_size` TEXT,
  `robe_type` TEXT,
  `graduation_cap` TEXT,
  `hood_code` TEXT,
  `collection_date` TEXT,
  `total_price` TEXT,
  `status` TEXT,
  `payment_status` TEXT,
  `payment_ref` TEXT
);
INSERT INTO `reservations` (`id`,`user_id`,`robe_size`,`robe_type`,`graduation_cap`,`hood_code`,`collection_date`,`total_price`,`status`,`payment_status`,`payment_ref`) VALUES ('1','3','S','Degree','Mortar Board','CS240','2025-05-24','45.00','Pending','Paid','1234');
INSERT INTO `reservations` (`id`,`user_id`,`robe_size`,`robe_type`,`graduation_cap`,`hood_code`,`collection_date`,`total_price`,`status`,`payment_status`,`payment_ref`) VALUES ('2','3','XS','Diploma','Mortar Board','','2025-05-24','35.00','Pending','Paid','134');