DELETE FROM `wp_attribute` WHERE `model_name`='custom_menu';
DELETE FROM `wp_model` WHERE `name`='custom_menu' ORDER BY id DESC LIMIT 1;
DROP TABLE IF EXISTS `wp_custom_menu`;


