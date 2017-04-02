CREATE TABLE IF NOT EXISTS `wp_payment_order` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
`from`  varchar(50) NOT NULL  COMMENT '回调地址',
`orderName`  varchar(255) NULL  COMMENT '订单名称',
`single_orderid`  varchar(100) NOT NULL  COMMENT '订单号',
`price`  decimal(10,2) NULL  COMMENT '价格',
`token`  varchar(100) NOT NULL  COMMENT 'Token',
`wecha_id`  varchar(200) NOT NULL  COMMENT 'OpenID',
`paytype`  varchar(30) NOT NULL  COMMENT '支付方式',
`showwxpaytitle`  tinyint(2) NOT NULL  DEFAULT 0 COMMENT '是否显示标题',
`status`  tinyint(2) NOT NULL  DEFAULT 0 COMMENT '支付状态',
`aim_id`  int(10) NULL  COMMENT 'aim_id',
`uid`  int(10) NULL  COMMENT '用户uid',
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci CHECKSUM=0 ROW_FORMAT=DYNAMIC DELAY_KEY_WRITE=0;
INSERT INTO `wp_model` (`name`,`title`,`extend`,`relation`,`need_pk`,`field_sort`,`field_group`,`attribute_list`,`template_list`,`template_add`,`template_edit`,`list_grid`,`list_row`,`search_key`,`search_list`,`create_time`,`update_time`,`status`,`engine_type`,`addon`) VALUES ('payment_order','订单支付记录','0','','1','["from","orderName","single_orderid","price","token","wecha_id","paytype","showwxpaytitle","status"]','1:基础','','','','','','20','','','1420596259','1423534012','1','MyISAM','Payment');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`model_name`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('from','回调地址','varchar(50) NOT NULL','string','','','1','','0','payment_order','0','1','1420596347','1420596347','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`model_name`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('orderName','订单名称','varchar(255) NULL','string','','','1','','0','payment_order','0','1','1439976366','1420596373','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`model_name`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('single_orderid','订单号','varchar(100) NOT NULL','string','','','1','','0','payment_order','0','1','1420596415','1420596415','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`model_name`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('price','价格','decimal(10,2) NULL','num','','','1','','0','payment_order','0','1','1439812508','1420596472','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`model_name`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('token','Token','varchar(100) NOT NULL','string','','','1','','0','payment_order','0','1','1420596492','1420596492','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`model_name`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('wecha_id','OpenID','varchar(200) NOT NULL','string','','','1','','0','payment_order','0','1','1420596530','1420596530','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`model_name`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('paytype','支付方式','varchar(30) NOT NULL','string','','','1','','0','payment_order','0','1','1420596929','1420596929','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`model_name`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('showwxpaytitle','是否显示标题','tinyint(2) NOT NULL','bool','0','','1','0:不显示\r\n1:显示','0','payment_order','0','1','1420596980','1420596980','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`model_name`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('status','支付状态','tinyint(2) NOT NULL','bool','0','','1','0:未支付\r\n1:已支付\r\n2:支付失败','0','payment_order','0','1','1420597026','1420597026','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`model_name`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('aim_id','aim_id','int(10) NULL','num','','','0','','0','payment_order','0','1','1445253482','1445253482','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`model_name`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('uid','用户uid','int(10) NULL','num','','','0','','0','payment_order','0','1','1445255505','1445255505','','3','','regex','','3','function');
UPDATE `wp_attribute` a, wp_model m SET a.model_id = m.id WHERE a.model_name=m.`name`;


CREATE TABLE IF NOT EXISTS `wp_payment_set` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
`wxmchid`  varchar(255) NULL  COMMENT '微信支付商户号',
`shop_id`  int(10) NULL  DEFAULT 0 COMMENT '商店ID',
`quick_merid`  varchar(255) NULL  COMMENT '银联在线merid',
`quick_merabbr`  varchar(255) NULL  COMMENT '商户名称',
`wxpartnerid`  varchar(255) NULL  COMMENT '微信partnerid',
`wxpartnerkey`  varchar(255) NULL  COMMENT '微信partnerkey',
`partnerid`  varchar(255) NULL  COMMENT '财付通标识',
`key`  varchar(255) NULL  COMMENT 'KEY',
`ctime`  int(10) NULL  COMMENT '创建时间',
`quick_security_key`  varchar(255) NULL  COMMENT '银联在线Key',
`wappartnerkey`  varchar(255) NULL  COMMENT 'WAP财付通Key',
`wappartnerid`  varchar(255) NULL  COMMENT '财付通标识WAP',
`partnerkey`  varchar(255) NULL  COMMENT '财付通Key',
`pid`  varchar(255) NULL  COMMENT 'PID',
`zfbname`  varchar(255) NULL  COMMENT '帐号',
`wxappsecret`  varchar(255) NULL  COMMENT 'AppSecret',
`wxpaysignkey`  varchar(255) NULL  COMMENT '支付密钥',
`wxappid`  varchar(255) NULL  COMMENT 'AppID',
`token`  varchar(255) NULL  COMMENT 'token',
`wx_cert_pem`  int(10) UNSIGNED NULL  COMMENT '上传证书',
`wx_key_pem`  int(10) UNSIGNED NULL  COMMENT '上传密匙',
`shop_pay_score`  int(10) NULL  DEFAULT 0 COMMENT '支付返积分',
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci CHECKSUM=0 ROW_FORMAT=DYNAMIC DELAY_KEY_WRITE=0;
INSERT INTO `wp_model` (`name`,`title`,`extend`,`relation`,`need_pk`,`field_sort`,`field_group`,`attribute_list`,`template_list`,`template_add`,`template_edit`,`list_grid`,`list_row`,`search_key`,`search_list`,`create_time`,`update_time`,`status`,`engine_type`,`addon`) VALUES ('payment_set','支付配置','0','','1','["wxappid","wxappsecret","wxpaysignkey","zfbname","pid","key","partnerid","partnerkey","wappartnerid","wappartnerkey","quick_security_key","quick_merid","quick_merabbr","wxmchid"]','1:基础','','','','','','10','','','1406958084','1439364636','1','MyISAM','Payment');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`model_name`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('wxmchid','微信支付商户号','varchar(255) NULL','string','','','1','','0','payment_set','1','1','1439364696','1436437067','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`model_name`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('shop_id','商店ID','int(10) NULL','num','0','','0','','0','payment_set','0','1','1436437020','1436437003','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`model_name`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('quick_merid','银联在线merid','varchar(255) NULL','string','','','1','','0','payment_set','0','1','1436436949','1436436949','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`model_name`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('quick_merabbr','商户名称','varchar(255) NULL','string','','','1','','0','payment_set','0','1','1436436970','1436436970','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`model_name`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('wxpartnerid','微信partnerid','varchar(255) NULL','string','','','0','','0','payment_set','0','1','1436437196','1436436910','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`model_name`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('wxpartnerkey','微信partnerkey','varchar(255) NULL','string','','','0','','0','payment_set','0','1','1436437236','1436436888','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`model_name`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('partnerid','财付通标识','varchar(255) NULL','string','','','1','','0','payment_set','0','1','1436436798','1436436798','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`model_name`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('key','KEY','varchar(255) NULL','string','','','1','','0','payment_set','0','1','1436436771','1436436771','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`model_name`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('ctime','创建时间','int(10) NULL','datetime','','','0','','0','payment_set','0','1','1436436498','1436436498','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`model_name`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('quick_security_key','银联在线Key','varchar(255) NULL','string','','','1','','0','payment_set','0','1','1436436931','1436436931','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`model_name`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('wappartnerkey','WAP财付通Key','varchar(255) NULL','string','','','1','','0','payment_set','0','1','1436436863','1436436863','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`model_name`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('wappartnerid','财付通标识WAP','varchar(255) NULL','string','','','1','','0','payment_set','0','1','1436436834','1436436834','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`model_name`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('partnerkey','财付通Key','varchar(255) NULL','string','','','1','','0','payment_set','0','1','1436436816','1436436816','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`model_name`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('pid','PID','varchar(255) NULL','string','','','1','','0','payment_set','0','1','1436436707','1436436707','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`model_name`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('zfbname','帐号','varchar(255) NULL','string','','','1','','0','payment_set','0','1','1436436653','1436436653','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`model_name`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('wxappsecret','AppSecret','varchar(255) NULL','string','','微信支付中的公众号应用密钥','1','','0','payment_set','1','1','1439364612','1436436618','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`model_name`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('wxpaysignkey','支付密钥','varchar(255) NULL','string','','PartnerKey','1','','0','payment_set','1','1','1439364810','1436436569','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`model_name`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('wxappid','AppID','varchar(255) NULL','string','','微信支付中的公众号应用ID','1','','0','payment_set','1','1','1439364573','1436436534','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`model_name`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('token','token','varchar(255) NULL','string','','','0','','0','payment_set','0','1','1436436415','1436436415','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`model_name`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('wx_cert_pem','上传证书','int(10) UNSIGNED NULL','file','','apiclient_cert.pem','1','','0','payment_set','0','1','1439804529','1439550487','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`model_name`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('wx_key_pem','上传密匙','int(10) UNSIGNED NULL','file','','apiclient_key.pem','1','','0','payment_set','0','1','1439804544','1439804014','','3','','regex','','3','function');
INSERT INTO `wp_attribute` (`name`,`title`,`field`,`type`,`value`,`remark`,`is_show`,`extra`,`model_id`,`model_name`,`is_must`,`status`,`update_time`,`create_time`,`validate_rule`,`validate_time`,`error_info`,`validate_type`,`auto_rule`,`auto_time`,`auto_type`) VALUES ('shop_pay_score','支付返积分','int(10) NULL','num','0','不设置则默认为采用该支付方式不送积分','1','','0','payment_set','0','1','1443065789','1443064056','','3','','regex','','3','function');
UPDATE `wp_attribute` a, wp_model m SET a.model_id = m.id WHERE a.model_name=m.`name`;


