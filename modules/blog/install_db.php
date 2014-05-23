<?php
$db_prefix = Config::read('db.prefix');
$FpsInstallQueries = array();
$FpsInstallQueries[] = "DROP TABLE IF EXISTS `{$db_prefix}blog`";
$FpsInstallQueries[] = "CREATE TABLE `{$db_prefix}blog` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) collate utf8_general_ci NOT NULL,
  `main` text collate utf8_general_ci NOT NULL,
  `views` int(11) default '0',
  `date` datetime default NULL,
  `category_id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  `comments` int(11) NOT NULL default '0',
  `tags` VARCHAR( 255 ) NOT NULL,
  `description` TEXT NOT NULL,
  `sourse` VARCHAR( 255 ) NOT NULL,
  `sourse_email` VARCHAR( 255 ) NOT NULL,
  `sourse_site` VARCHAR( 255 ) NOT NULL,
  `commented` ENUM( '0', '1' ) DEFAULT '1' NOT NULL,
  `available` ENUM( '0', '1' ) DEFAULT '1' NOT NULL,
  `view_on_home` ENUM( '0', '1' ) DEFAULT '1' NOT NULL,
  `on_home_top` ENUM( '0', '1' ) DEFAULT '0' NOT NULL,
  `premoder` ENUM( 'nochecked', 'rejected', 'confirmed' ) NOT NULL DEFAULT 'confirmed',
  `rating` INT( 11 ) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";
$FpsInstallQueries[] = "DROP TABLE IF EXISTS `{$db_prefix}blog_categories`";
$FpsInstallQueries[] = "CREATE TABLE `{$db_prefix}blog_categories` (
  `id` int(11) NOT NULL auto_increment,
  `parent_id` int(11) default '0',
  `path` VARCHAR( 255 ) NOT NULL DEFAULT '',
  `announce` varchar(255) NOT NULL default '',
  `title` varchar(255) NOT NULL,
  `view_on_home` ENUM( '0', '1' ) DEFAULT '1' NOT NULL,
  `no_access` VARCHAR( 255 ) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";
$FpsInstallQueries[] = "INSERT INTO `{$db_prefix}blog_categories` VALUES (1, 0, '', '', 'Test category', '1', '')";

$FpsInstallQueries[] = "DROP TABLE IF EXISTS `{$db_prefix}blog_add_fields`";
$FpsInstallQueries[] = "CREATE TABLE `{$db_prefix}blog_add_fields` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`type` VARCHAR(10) NOT NULL,
	`name` VARCHAR(100) NOT NULL,
	`label` VARCHAR(255) NOT NULL,
	`size` INT(11) NOT NULL,
	`params` VARCHAR(255) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci";

$FpsInstallQueries[] = "DROP TABLE IF EXISTS `{$db_prefix}blog_add_content`";
$FpsInstallQueries[] = "CREATE TABLE `{$db_prefix}blog_add_content` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`field_id` INT(11) NOT NULL,
	`entity_id` INT(11) NOT NULL,
	`content` TEXT NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci";

$FpsInstallQueries[] = "DROP TABLE IF EXISTS `{$db_prefix}blog_attaches`";
$FpsInstallQueries[] = "CREATE TABLE `{$db_prefix}blog_attaches` (
	`id` INT NOT NULL AUTO_INCREMENT ,
	`entity_id` INT NOT NULL default '0',
	`user_id` INT NOT NULL ,
	`attach_number` INT NOT NULL default '0',
	`filename` VARCHAR( 100 ) NOT NULL ,
	`size` BIGINT NOT NULL ,
	`date` DATETIME NOT NULL ,
	`is_image` ENUM( '0', '1' ) DEFAULT '0' NOT NULL ,
	PRIMARY KEY ( `id` )
) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;";