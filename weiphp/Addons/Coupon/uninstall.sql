DELETE FROM `wp_attribute` WHERE `model_name`='coupon';
DELETE FROM `wp_model` WHERE `name`='coupon' ORDER BY id DESC LIMIT 1;
DROP TABLE IF EXISTS `wp_coupon`;


DELETE FROM `wp_attribute` WHERE `model_name`='coupon_shop';
DELETE FROM `wp_model` WHERE `name`='coupon_shop' ORDER BY id DESC LIMIT 1;
DROP TABLE IF EXISTS `wp_coupon_shop`;


DELETE FROM `wp_attribute` WHERE `model_name`='coupon_shop_link';
DELETE FROM `wp_model` WHERE `name`='coupon_shop_link' ORDER BY id DESC LIMIT 1;
DROP TABLE IF EXISTS `wp_coupon_shop_link`;


