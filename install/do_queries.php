<?php
##################################################
##												##
## Author:       Andrey Brykin (Drunya)         ##
## Version:      1.5                            ##
## Project:      CMS                            ##
## package       CMS Fapos                      ##
## subpackege    Install                        ##
## copyright     ©Andrey Brykin 2010-2013       ##
## last mod.     2013/04/02                     ##
##################################################


##################################################
##												##
## any partial or not partial extension         ##
## CMS Fapos,without the consent of the         ##
## author, is illegal                           ##
##################################################
## Любое распространение                        ##
## CMS Fapos или ее частей,                     ##
## без согласия автора, является не законным    ##
##################################################
@ini_set('display_errors', 0);
session_start();
define ('ROOT', dirname(dirname(__FILE__)));
if (function_exists('set_time_limit')) set_time_limit(0);
include_once '../sys/inc/config.class.php';


$errors = array();


new Config('../sys/settings/config.php');
$set = Config::read('all');


@$db = mysql_connect($set['db']['host'], $set['db']['user'], $set['db']['pass']);
if (!$db) $errors['connect'] = 'Не удалось подключиться к базе. Проверте настройки!';
if (!mysql_select_db($set['db']['name'], $db)) $errors['connect'] = 'Не удалось найти базу. Проверте имя базы!';
mysql_query("SET NAMES 'utf8'");


if (!empty($errors['connect'])) {
	
	echo $errors['connect'];
	echo '<br /> Попробуйте начать сначала.';
}

$array = array();
$array[] = "DROP TABLE IF EXISTS `{$set['db']['prefix']}forum_cat`";
$array[] = "CREATE TABLE `{$set['db']['prefix']}forum_cat` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) collate utf8_general_ci default NULL,
  `previev_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";
$array[] = "INSERT INTO `{$set['db']['prefix']}forum_cat` VALUES (1, 'TEST', 1)";
#####################################################################
$array[] = "DROP TABLE IF EXISTS `{$set['db']['prefix']}forums`";
$array[] = "CREATE TABLE `{$set['db']['prefix']}forums` (
  `id` int(6) NOT NULL auto_increment,
  `title` text character set utf8,
  `description` mediumtext character set utf8,
  `pos` smallint(6) NOT NULL default '0',
  `in_cat` int(11) default NULL,
  `last_theme_id` int(11) NOT NULL default 0,
  `themes` int(11) default '0',
  `posts` int(11) NOT NULL default '0',
  `parent_forum_id` INT(11),
  `lock_posts` INT( 11 ) DEFAULT '0' NOT NULL,
  `lock_passwd` VARCHAR( 100 ) NOT NULL default 0,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";
$array[] = "INSERT INTO `{$set['db']['prefix']}forums` (`title`, `description`, `pos`, `in_cat`) VALUES ('TEST', 'тестовый форум', 1, 1)";
#####################################################################
$array[] = "DROP TABLE IF EXISTS `{$set['db']['prefix']}forum_attaches`";
$array[] = "CREATE TABLE `{$set['db']['prefix']}forum_attaches` (
	`id` INT NOT NULL AUTO_INCREMENT ,
	`post_id` INT NOT NULL,
	`theme_id` INT NOT NULL ,
	`user_id` INT NOT NULL ,
	`attach_number` INT NOT NULL ,
	`filename` VARCHAR( 100 ) NOT NULL ,
	`size` BIGINT NOT NULL ,
	`date` DATETIME NOT NULL ,
	`is_image` ENUM( '0', '1' ) DEFAULT '0' NOT NULL ,
	PRIMARY KEY ( `id` )
) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;";
#####################################################################
$array[] = "DROP TABLE IF EXISTS `{$set['db']['prefix']}pages`";
$array[] = "CREATE TABLE `{$set['db']['prefix']}pages` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_general_ci NOT NULL,
  `template` varchar(255) default '' collate utf8_general_ci NOT NULL,
  `content` longtext collate utf8_general_ci NOT NULL,
  `url` varchar(255) default ''  NOT NULL,
  `meta_keywords` varchar(255) default ''  NOT NULL,
  `meta_description` text,
  `parent_id` int(11) default 0  NOT NULL,
  `path` varchar(255) default '1.'  NOT NULL,
  `visible` enum('1','0') default '1'  NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";
$array[] = "INSERT INTO `{$set['db']['prefix']}pages` (`id`,`name`,`path`,`content`) VALUES ('1', 'root', '.', '')";
#####################################################################
$array[] = "DROP TABLE IF EXISTS `{$set['db']['prefix']}loads`";
$array[] = "CREATE TABLE `{$set['db']['prefix']}loads` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `main` text NOT NULL,
  `author_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `views` int(11) default '0',
  `downloads` int(11) default '0',
  `rate` int(11) default '0',
  `download` varchar(255) NOT NULL,
  `download_url` VARCHAR( 255 ) NOT NULL,
  `download_url_size` bigint( 20 ) NOT NULL,
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
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
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";
#####################################################################
$array[] = "DROP TABLE IF EXISTS `{$set['db']['prefix']}comments`";
$array[] = "CREATE TABLE `{$set['db']['prefix']}comments` (
  `id` int(11) NOT NULL auto_increment,
  `entity_id` int(11) NOT NULL,
  `user_id` INT(11) DEFAULT '0' NOT NULL,
  `name` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `ip` varchar(50) NOT NULL,
  `mail` varchar(150) NOT NULL,
  `date` DATETIME NOT NULL,
  `module` varchar(10) default 'news' NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";
#####################################################################
$array[] = "DROP TABLE IF EXISTS `{$set['db']['prefix']}loads_sections`";
$array[] = "CREATE TABLE `{$set['db']['prefix']}loads_sections` (
  `id` int(11) NOT NULL auto_increment,
  `parent_id` int(11) default '0',
  `announce` varchar(255) NOT NULL default '',
  `title` varchar(255) NOT NULL,
  `view_on_home` ENUM( '0', '1' ) DEFAULT '1' NOT NULL,
  `no_access` VARCHAR( 255 ) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";
$array[] = "INSERT INTO `{$set['db']['prefix']}loads_sections` VALUES (1, 0, '', 'TEST CAT', '1', '')";
#####################################################################
$array[] = "DROP TABLE IF EXISTS `{$set['db']['prefix']}messages`";
$array[] = "CREATE TABLE `{$set['db']['prefix']}messages` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `to_user` int(10) unsigned NOT NULL default '0',
  `from_user` int(10) unsigned NOT NULL default '0',
  `sendtime` datetime NOT NULL default '0000-00-00 00:00:00',
  `subject` varchar(255) default NULL,
  `message` text,
  `id_rmv` int(10) unsigned NOT NULL default '0',
  `viewed` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";
#####################################################################
$array[] = "DROP TABLE IF EXISTS `{$set['db']['prefix']}news`";
$array[] = "CREATE TABLE `{$set['db']['prefix']}news` (
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
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";
$array[] = "INSERT INTO `{$set['db']['prefix']}news` VALUES (1, 'Моя первая новость', 'Теперь сайт установлен и вы можете приступать его настройке. По любым вопросам обращайтесь на официальный сайт Fapos.net', 0, NOW(), 1, 1, 0, '', '', '', '', '', '1', '1', '1', '0')";
#####################################################################
$array[] = "DROP TABLE IF EXISTS `{$set['db']['prefix']}news_sections`";
$array[] = "CREATE TABLE `{$set['db']['prefix']}news_sections` (
  `id` int(11) NOT NULL auto_increment,
  `parent_id` int(11) default '0',
  `announce` varchar(255) NOT NULL default '',
  `title` varchar(255) NOT NULL,
  `view_on_home` ENUM( '0', '1' ) DEFAULT '1' NOT NULL,
  `no_access` VARCHAR( 255 ) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";
$array[] = "INSERT INTO `{$set['db']['prefix']}news_sections` VALUES (1, 0, '', 'Test category', '1', '')";
#####################################################################
$array[] = "DROP TABLE IF EXISTS `{$set['db']['prefix']}posts`";
$array[] = "CREATE TABLE `{$set['db']['prefix']}posts` (
  `id` int(11) NOT NULL auto_increment,
  `message` text,
  `attaches` ENUM( '0', '1' ) DEFAULT '0',
  `id_author` int(6) unsigned NOT NULL default '0',
  `time` datetime NOT NULL default '0000-00-00 00:00:00',
  `edittime` datetime NOT NULL default '0000-00-00 00:00:00',
  `id_editor` int(6) unsigned NOT NULL default '0',
  `id_theme` int(11) NOT NULL default '0',
  `locked` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";
#####################################################################
$array[] = "DROP TABLE IF EXISTS `{$set['db']['prefix']}snippets`";
$array[] = "CREATE TABLE `{$set['db']['prefix']}snippets` (
  `name` varchar(255) default NULL,
  `body` longtext,
  `id` int(11) NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";
$array[] = "INSERT  INTO `{$set['db']['prefix']}snippets` " . file_get_contents('db2.sql');
$array[] = "INSERT  INTO `{$set['db']['prefix']}snippets` (`name`, `body`) VALUES ('last_added', '" . file_get_contents('last_added_forum.sql') . "');";
#####################################################################
$array[] = "DROP TABLE IF EXISTS `{$set['db']['prefix']}stat`";
$array[] = "CREATE TABLE `{$set['db']['prefix']}stat` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `main` longtext NOT NULL,
  `author_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `views` int(11) default '0',
  `rate` int(11) default '0',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
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
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";
#####################################################################
$array[] = "DROP TABLE IF EXISTS `{$set['db']['prefix']}stat_sections`";
$array[] = "CREATE TABLE `{$set['db']['prefix']}stat_sections` (
  `id` int(11) NOT NULL auto_increment,
  `parent_id` int(11) default '0',
  `announce` varchar(255) NOT NULL default '',
  `title` varchar(255) NOT NULL,
  `view_on_home` ENUM( '0', '1' ) DEFAULT '1' NOT NULL,
  `no_access` VARCHAR( 255 ) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";
$array[] = "INSERT INTO `{$set['db']['prefix']}stat_sections` VALUES (1, '0', '', 'Test category', '1', '')";
#####################################################################
$array[] = "DROP TABLE IF EXISTS `{$set['db']['prefix']}statistics`";
$array[] = "CREATE TABLE `{$set['db']['prefix']}statistics` (
  `id` int(11) NOT NULL auto_increment,
  `ips` int(50) default '1',
  `cookie` int(11) default '0',
  `referer` varchar(255) NOT NULL,
  `date` date default NULL,
  `views` int(11) NOT NULL,
  `yandex_bot_views` int(11) NOT NULL default '0',
  `google_bot_views` int(11) default '0',
  `other_bot_views` int(11) NOT NULL default '0',
  `other_site_visits` int(11) default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8  COLLATE=utf8_general_ci;";
#####################################################################
$array[] = "DROP TABLE IF EXISTS `{$set['db']['prefix']}themes`";
$array[] = "CREATE TABLE `{$set['db']['prefix']}themes` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(120) default NULL,
  `id_author` int(6) NOT NULL default '0',
  `time` datetime NOT NULL default '0000-00-00 00:00:00',
  `id_last_author` int(6) NOT NULL default '0',
  `last_post` datetime NOT NULL,
  `id_forum` int(2) NOT NULL default '0',
  `locked` tinyint(1) unsigned NOT NULL default '0',
  `posts` int(11) default '0',
  `views` int(11) default '0',
  `important` enum('0','1') NOT NULL default '0',
  `description` TEXT NOT NULL,
  `group_access` varchar(255) default '' NOT NULL,
  `first_top` ENUM( '0', '1' ) DEFAULT '0' NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";
#####################################################################
$array[] = "DROP TABLE IF EXISTS `{$set['db']['prefix']}users`";
$array[] = "CREATE TABLE `{$set['db']['prefix']}users` (
	`id` int(6) NOT NULL auto_increment,
	`name` varchar(32) character set utf8 NOT NULL,
	`passw` varchar(255) character set utf8 NOT NULL,
	`email` varchar(64) character set utf8 NOT NULL default '',
	`color` VARCHAR(7) character set utf8 NOT NULL default '',
	`state` VARCHAR(100) character set utf8 NOT NULL default '',
	`rating` INT DEFAULT '0' NOT NULL,
	`timezone` tinyint(2) NOT NULL default '0',
	`url` varchar(64) character set utf8 NOT NULL default '',
	`icq` varchar(12) character set utf8 NOT NULL default '',
	`pol` ENUM( 'f', 'm', '' ) DEFAULT '' NOT NULL,
	`jabber` VARCHAR( 100 ) DEFAULT '' NOT NULL,
	`city` VARCHAR( 100 ) DEFAULT '' NOT NULL,
	`telephone` BIGINT( 15 ) DEFAULT 0 NOT NULL,
	`byear` INT( 4 ) DEFAULT 0 NOT NULL,
	`bmonth` INT( 2 ) DEFAULT 0 NOT NULL,
	`bday` INT( 2 ) DEFAULT 0 NOT NULL,
	`about` tinytext character set utf8 default NULL,
	`signature` tinytext character set utf8 default NULL,
	`photo` varchar(32) character set utf8 NOT NULL default '',
	`puttime` datetime NOT NULL default '0000-00-00 00:00:00',
	`last_visit` datetime NOT NULL default '0000-00-00 00:00:00',
	`themes` mediumint(8) unsigned NOT NULL default '0',
	`posts` int(10) unsigned NOT NULL default '0',
	`status` INT(2) NOT NULL default '1',
	`locked` tinyint(1) NOT NULL default '0',
	`activation` varchar(255) character set utf8 NOT NULL default '',
	`warnings` INT DEFAULT '0' NOT NULL,
	`ban_expire` DATETIME DEFAULT 0 NOT NULL,
	PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";
##########################################################################
$array[] = "DROP TABLE IF EXISTS `{$set['db']['prefix']}users_votes`";
$array[] = "CREATE TABLE `{$set['db']['prefix']}users_votes` (
	`id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
	`from_user` INT( 11 ) NOT NULL ,
	`to_user` INT( 11 ) NOT NULL ,
	`comment` TEXT CHARACTER SET utf8 NOT NULL ,
	`date` DATETIME,
	`points` INT DEFAULT '0' NOT NULL,
	PRIMARY KEY ( `id` )
	) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci";
##########################################################################
$array[] = "DROP TABLE IF EXISTS `{$set['db']['prefix']}users_settings`";
$array[] = "CREATE TABLE `{$set['db']['prefix']}users_settings` (
  `id` int(11) NOT NULL auto_increment,
  `type` varchar(255) NOT NULL,
  `values` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";
$array[] = "INSERT INTO `{$set['db']['prefix']}users_settings` VALUES (1, 'rating', 'a:36:{s:3:\"cms\";s:21:\"Z:/home/localhost/www\";s:11:\"cookie_time\";s:2:\"30\";s:9:\"start_mod\";s:0:\"\";s:8:\"open_reg\";s:1:\"1\";s:10:\"debug_mode\";s:1:\"1\";s:5:\"forum\";a:12:{s:5:\"title\";s:27:\"Каталог файлов\";s:11:\"description\";s:42:\"Большой каталог файлов\";s:15:\"max_avatar_size\";s:6:\"100000\";s:12:\"not_reg_user\";s:16:\"Гостелло\";s:15:\"max_post_lenght\";s:3:\"500\";s:18:\"max_message_lenght\";s:4:\"3000\";s:15:\"max_mail_lenght\";s:3:\"500\";s:14:\"max_count_mess\";s:2:\"50\";s:13:\"post_per_page\";N;s:15:\"themes_per_page\";s:1:\"4\";s:14:\"posts_per_page\";s:2:\"15\";s:14:\"users_per_page\";s:2:\"30\";}s:13:\"max_file_size\";s:8:\"15000000\";s:19:\"min_password_lenght\";s:1:\"6\";s:11:\"admin_email\";s:19:\"drunyacoder@mail.ru\";s:14:\"redirect_delay\";s:1:\"1\";s:12:\"time_on_line\";s:2:\"10\";s:4:\"news\";a:4:{s:5:\"title\";s:14:\"Новости\";s:15:\"max_news_lenght\";s:5:\"15000\";s:19:\"max_announce_lenght\";s:3:\"700\";s:13:\"news_per_page\";s:1:\"3\";}s:4:\"stat\";a:5:{s:5:\"title\";s:12:\"Статьи\";s:11:\"description\";s:44:\"Много интересных статей\";s:15:\"max_post_lenght\";s:5:\"15000\";s:13:\"stat_per_page\";s:1:\"7\";s:15:\"announce_lenght\";s:4:\"1500\";}s:7:\"antisql\";s:3:\"yes\";s:4:\"load\";a:8:{s:5:\"title\";s:27:\"Каталог файлов\";s:11:\"description\";s:54:\"Каталог файлов. Все файлы тут.\";s:15:\"min_load_lenght\";s:3:\"200\";s:15:\"max_load_lenght\";s:4:\"1500\";s:19:\"max_announce_lenght\";s:3:\"300\";s:14:\"posts_per_page\";s:1:\"5\";s:13:\"max_file_size\";s:8:\"15000000\";s:14:\"loads_per_page\";s:1:\"5\";}s:4:\"rat0\";s:25:\"Заглянул сюда\";s:5:\"cond1\";s:1:\"2\";s:4:\"rat1\";s:14:\"никакой\";s:5:\"cond2\";s:1:\"5\";s:4:\"rat2\";s:12:\"салага\";s:5:\"cond3\";s:1:\"7\";s:4:\"rat3\";s:13:\"кое что\";s:5:\"cond4\";s:2:\"12\";s:4:\"rat4\";s:8:\"шкет\";s:5:\"cond5\";s:2:\"17\";s:4:\"rat5\";s:19:\"пойдет тип\";s:5:\"cond6\";s:2:\"22\";s:4:\"rat6\";s:16:\"зависший\";s:5:\"cond7\";s:2:\"25\";s:4:\"rat7\";s:8:\"кент\";s:5:\"cond8\";s:2:\"30\";s:4:\"rat8\";s:10:\"перец\";s:5:\"cond9\";s:2:\"35\";s:4:\"rat9\";s:23:\"крутой перец\";s:6:\"cond10\";s:2:\"40\";s:5:\"rat10\";s:34:\"круче любого перца\";}')";
$array[] = "INSERT INTO `{$set['db']['prefix']}users_settings` VALUES (2, 'reg_rules', 'Правила как правила:)')";
##########################################################################
$array[] = "DROP TABLE IF EXISTS `{$set['db']['prefix']}foto`";
$array[] = "CREATE TABLE `{$set['db']['prefix']}foto` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) character set utf8 NOT NULL,
  `description` TEXT character set utf8 NOT NULL,
  `filename` VARCHAR( 255 ) NOT NULL,
  `views` int(11) default '0',
  `date` datetime default NULL,
  `category_id` int(11) default NULL,
  `author_id` int(11) NOT NULL,
  `comments` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";
#############################################################################
$array[] = "DROP TABLE IF EXISTS `{$set['db']['prefix']}foto_sections`";
$array[] = "CREATE TABLE `{$set['db']['prefix']}foto_sections` (
  `id` int(11) NOT NULL auto_increment,
  `parent_id` int(11) default '0',
  `announce` varchar(255) NOT NULL default '',
  `title` varchar(255) NOT NULL,
  `view_on_home` ENUM( '0', '1' ) DEFAULT '1' NOT NULL,
  `no_access` VARCHAR( 255 ) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";
$array[] = "INSERT INTO `{$set['db']['prefix']}foto_sections` VALUES (1, 0, '', 'section', '1', '')";
#############################################################################
$array[] = "DROP TABLE IF EXISTS `{$set['db']['prefix']}search_index`";
$array[] = "CREATE TABLE `{$set['db']['prefix']}search_index` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`index` TEXT character set utf8 NOT NULL,
	`entity_id` INT(11) NOT NULL,
	`entity_table` VARCHAR(100) character set utf8 NOT NULL,
	`entity_view` VARCHAR(100) character set utf8 NOT NULL,
	`module` VARCHAR(100) character set utf8 NOT NULL,
	`date` DATETIME NOT NULL,
	PRIMARY KEY (`id`),
	FULLTEXT KEY `index` (`index`)
) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci";
#############################################################################
$array[] = "DROP TABLE IF EXISTS `{$set['db']['prefix']}news_add_fields`";
$array[] = "CREATE TABLE `{$set['db']['prefix']}news_add_fields` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`type` VARCHAR(10) NOT NULL,
	`name` VARCHAR(100) NOT NULL,
	`label` VARCHAR(255) NOT NULL,
	`size` INT(11) NOT NULL,
	`params` VARCHAR(255) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci";
#############################################################################
$array[] = "DROP TABLE IF EXISTS `{$set['db']['prefix']}news_add_content`";
$array[] = "CREATE TABLE `{$set['db']['prefix']}news_add_content` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`field_id` INT(11) NOT NULL,
	`entity_id` INT(11) NOT NULL,
	`content` TEXT NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci";
#############################################################################
$array[] = "DROP TABLE IF EXISTS `{$set['db']['prefix']}users_add_fields`";
$array[] = "CREATE TABLE `{$set['db']['prefix']}users_add_fields` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`type` VARCHAR(10) NOT NULL,
	`name` VARCHAR(100) NOT NULL,
	`label` VARCHAR(255) NOT NULL,
	`size` INT(11) NOT NULL,
	`params` VARCHAR(255) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci";
#############################################################################
$array[] = "DROP TABLE IF EXISTS `{$set['db']['prefix']}users_add_content`";
$array[] = "CREATE TABLE `{$set['db']['prefix']}users_add_content` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`field_id` INT(11) NOT NULL,
	`entity_id` INT(11) NOT NULL,
	`content` TEXT NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci";
#############################################################################
$array[] = "DROP TABLE IF EXISTS `{$set['db']['prefix']}stat_add_fields`";
$array[] = "CREATE TABLE `{$set['db']['prefix']}stat_add_fields` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`type` VARCHAR(10) NOT NULL,
	`name` VARCHAR(100) NOT NULL,
	`label` VARCHAR(255) NOT NULL,
	`size` INT(11) NOT NULL,
	`params` VARCHAR(255) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci";
#############################################################################
$array[] = "DROP TABLE IF EXISTS `{$set['db']['prefix']}stat_add_content`";
$array[] = "CREATE TABLE `{$set['db']['prefix']}stat_add_content` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`field_id` INT(11) NOT NULL,
	`entity_id` INT(11) NOT NULL,
	`content` TEXT NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci";
#############################################################################
$array[] = "DROP TABLE IF EXISTS `{$set['db']['prefix']}loads_add_fields`";
$array[] = "CREATE TABLE `{$set['db']['prefix']}loads_add_fields` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`type` VARCHAR(10) NOT NULL,
	`name` VARCHAR(100) NOT NULL,
	`label` VARCHAR(255) NOT NULL,
	`size` INT(11) NOT NULL,
	`params` VARCHAR(255) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci";
#############################################################################
$array[] = "DROP TABLE IF EXISTS `{$set['db']['prefix']}loads_add_content`";
$array[] = "CREATE TABLE `{$set['db']['prefix']}loads_add_content` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`field_id` INT(11) NOT NULL,
	`entity_id` INT(11) NOT NULL,
	`content` TEXT NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci";
#############################################################################
$array[] = "DROP TABLE IF EXISTS `{$set['db']['prefix']}users_warnings`";
$array[] = "CREATE TABLE `{$set['db']['prefix']}users_warnings` (
	`id` INT NOT NULL AUTO_INCREMENT ,
	`user_id` INT NOT NULL ,
	`admin_id` INT NOT NULL ,
	`cause` VARCHAR( 255 ) NOT NULL ,
	`date` DATETIME NOT NULL ,
	`points` INT DEFAULT '0' NOT NULL,
	PRIMARY KEY ( `id` )
) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;";
#############################################################################
$array[] = "DROP TABLE IF EXISTS `{$set['db']['prefix']}loads_attaches`";
$array[] = "CREATE TABLE `{$set['db']['prefix']}loads_attaches` (
	`id` INT NOT NULL AUTO_INCREMENT ,
	`entity_id` INT NOT NULL,
	`user_id` INT NOT NULL ,
	`attach_number` INT NOT NULL ,
	`filename` VARCHAR( 100 ) NOT NULL ,
	`size` BIGINT NOT NULL ,
	`date` DATETIME NOT NULL ,
	`is_image` ENUM( '0', '1' ) DEFAULT '0' NOT NULL ,
	PRIMARY KEY ( `id` )
) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;";
#############################################################################
$array[] = "DROP TABLE IF EXISTS `{$set['db']['prefix']}stat_attaches`";
$array[] = "CREATE TABLE `{$set['db']['prefix']}stat_attaches` (
	`id` INT NOT NULL AUTO_INCREMENT ,
	`entity_id` INT NOT NULL,
	`user_id` INT NOT NULL ,
	`attach_number` INT NOT NULL ,
	`filename` VARCHAR( 100 ) NOT NULL ,
	`size` BIGINT NOT NULL ,
	`date` DATETIME NOT NULL ,
	`is_image` ENUM( '0', '1' ) DEFAULT '0' NOT NULL ,
	PRIMARY KEY ( `id` )
) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;";
#############################################################################
$array[] = "DROP TABLE IF EXISTS `{$set['db']['prefix']}news_attaches`";
$array[] = "CREATE TABLE `{$set['db']['prefix']}news_attaches` (
	`id` INT NOT NULL AUTO_INCREMENT ,
	`entity_id` INT NOT NULL,
	`user_id` INT NOT NULL ,
	`attach_number` INT NOT NULL ,
	`filename` VARCHAR( 100 ) NOT NULL ,
	`size` BIGINT NOT NULL ,
	`date` DATETIME NOT NULL ,
	`is_image` ENUM( '0', '1' ) DEFAULT '0' NOT NULL ,
	PRIMARY KEY ( `id` )
) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;";
#############################################################################
$array[] = "DROP TABLE IF EXISTS `{$set['db']['prefix']}polls`";
$array[] = "CREATE TABLE IF NOT EXISTS `{$set['db']['prefix']}polls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `theme_id` int(11) NOT NULL,
  `variants` text NOT NULL,
  `voted_users` text NOT NULL,
  `question` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3;";
#############################################################################




$n = 0;
foreach ($array as $key => $query) {
	if (!mysql_query($query)) {
		$errors['query'] = 'При формировании базы данных произошел збой! <br /> Начните  пожалуйста заново. (' . $query . ')';
		if (@mysql_error()) $errors['query'] .= '<br /><br />' . mysql_error();
		break;
	} else {
		echo '<span style="color:#46B100">' . $n . '. ' . htmlspecialchars(mb_substr($query, 0, 70, 'UTF-8')) . ' ...</span><br />'; 
		flush();
	}
	$n++;
}
if (empty($errors['query'])) {
	if (!mysql_query("INSERT INTO `{$set['db']['prefix']}users` (`id`, `name`, `passw`, `status`, `puttime`) 
	VALUES (1, '" . $_SESSION['adm_name'] . "', '" . md5($_SESSION['adm_pass']) . "', '4', NOW())")) 
		$errors['query'] = 'При формировании базы данных произошел збой! <br /> Начните  пожалуйста заново.<br /><br />' . mysql_error();
}
if (empty($errors)) :
?>
<div style="">
<h1 class="fin-h">Все готово</h1>
<a class="fin-a" href="../">Перейти на мой сайт :)</a><br />
<span class="help">Перед использованием сайта не забудте удалить или переименовать директорию INSTALL</span>
</div>
<?php 
 
else: 
	echo '<div style="position:absolute;top:300px;left:35%;width:400px;">' . $errors['query'] . '</div>';
	//if (mysql_error()) echo mysql_error();
endif;


?>