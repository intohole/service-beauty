DELETE FROM `wp_attribute` WHERE `model_name`='prize_address';
DELETE FROM `wp_model` WHERE `name`='prize_address' ORDER BY id DESC LIMIT 1;
DROP TABLE IF EXISTS `wp_prize_address`;


DELETE FROM `wp_attribute` WHERE `model_name`='real_prize';
DELETE FROM `wp_model` WHERE `name`='real_prize' ORDER BY id DESC LIMIT 1;
DROP TABLE IF EXISTS `wp_real_prize`;


