# MySQL-Front 5.1  (Build 4.13)

/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE */;
/*!40101 SET SQL_MODE='' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES */;
/*!40103 SET SQL_NOTES='ON' */;


# Host: 192.168.1.220:3308    Database: test
# ------------------------------------------------------
# Server version 5.6.39-log

#
# Source for table ke_tj
#

DROP TABLE IF EXISTS `ke_tj`;
CREATE TABLE `ke_tj` (
  `Id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `date` int(11) unsigned DEFAULT NULL COMMENT '添加时间',
  `hid` varchar(80) DEFAULT NULL COMMENT '房源ID',
  `title` varchar(50) DEFAULT NULL COMMENT '标题',
  `price` int(11) unsigned DEFAULT NULL COMMENT '单价',
  `amount` int(11) unsigned DEFAULT NULL COMMENT '总价-万',
  `acreage` float(10,2) unsigned DEFAULT NULL COMMENT '面积',
  `link` varchar(255) DEFAULT NULL COMMENT '链接',
  `focus` varchar(255) DEFAULT NULL COMMENT '关注人数',
  `tosee` varchar(20) DEFAULT NULL COMMENT '带看次数',
  `publish` varchar(50) DEFAULT NULL COMMENT '发布时间',
  `room` varchar(50) DEFAULT NULL COMMENT '厅室',
  `direction` varchar(50) DEFAULT NULL COMMENT '朝向',
  `district` varchar(20) DEFAULT NULL COMMENT '行政区',
  `street` varchar(100) DEFAULT NULL COMMENT '街道',
  `community` varchar(50) DEFAULT NULL COMMENT '小区',
  `label` varchar(40) DEFAULT NULL COMMENT '楼层',
  `lift` varchar(3) DEFAULT NULL COMMENT '是否有电梯',
  `decoration` varchar(20) DEFAULT NULL COMMENT '装修情况',
  `household` varchar(20) DEFAULT NULL COMMENT '梯户比例',
  `heating` varchar(20) DEFAULT NULL COMMENT '供暖方式',
  `property` varchar(20) DEFAULT NULL COMMENT '产权年限',
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
