DROP TABLE IF EXISTS `Evaluations`;
CREATE TABLE `Evaluations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from_eid` int(11) NOT NULL,
  `to_eid` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `phase` varchar(32) NOT NULL,
  `question_0` int(1) NOT NULL DEFAULT '0',
  `question_1` int(1) NOT NULL DEFAULT '0',
  `question_2` int(1) NOT NULL DEFAULT '0',
  `question_3` int(1) NOT NULL DEFAULT '0',
  `comment` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=597 DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `Groups`;
CREATE TABLE `Groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phase` varchar(32) DEFAULT NULL,
  `number` int(11) NOT NULL,
  `eid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1160 DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `Log`;
CREATE TABLE `Log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time` varchar(32) NOT NULL,
  `account` varchar(32) NOT NULL,
  `action` varchar(64) NOT NULL,
  `details` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=252 DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `People`;
CREATE TABLE `People` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `eid` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `employeetype` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `account` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `attributes` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `fname` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `lname` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `birthday` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `country` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `college` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `majorlong` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `majorinfo` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `room` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `title` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `office` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `deptinfo` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `year` varchar(8) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `major` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `block` varchar(8) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `floor` varchar(8) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `photo_url` varchar(256) NOT NULL,
  `flag_url` varchar(256) NOT NULL,
  `flag_small_url` varchar(256) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `eid` (`eid`),
  KEY `fname` (`fname`),
  KEY `lname` (`lname`),
  KEY `college` (`college`),
  KEY `employeetype` (`employeetype`),
  KEY `birthday` (`birthday`),
  KEY `year` (`year`),
  KEY `status` (`status`),
  KEY `majorlong` (`majorlong`),
  KEY `major` (`major`),
  KEY `block` (`block`),
  KEY `floor` (`floor`)
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=latin1;
