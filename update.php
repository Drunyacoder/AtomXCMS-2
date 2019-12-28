<?php
include_once '/sys/boot.php';
include '/sys/settings/acl_rules.php';



// convert section tables
/*$Register['DB']->query("RENAME TABLE `news_sections` TO `news_categories`");
$Register['DB']->query("RENAME TABLE `foto_sections` TO `foto_categories`");
$Register['DB']->query("RENAME TABLE `loads_sections` TO `loads_categories`");
$Register['DB']->query("RENAME TABLE `stat_sections` TO `stat_categories`");*/


// Add cyrillic  support
/*$Register['DB']->query("ALTER TABLE news ADD clean_url_title VARCHAR(255) DEFAULT '' NOT NULL");
$Register['DB']->query("ALTER TABLE stat ADD clean_url_title VARCHAR(255) DEFAULT '' NOT NULL");
$Register['DB']->query("ALTER TABLE loads ADD clean_url_title VARCHAR(255) DEFAULT '' NOT NULL");
$Register['DB']->query("ALTER TABLE themes ADD clean_url_title VARCHAR(255) DEFAULT '' NOT NULL");
$Register['DB']->query("ALTER TABLE foto ADD clean_url_title VARCHAR(255) DEFAULT '' NOT NULL");*/

// Update users
/*$Register['DB']->query("ALTER TABLE `users` ADD `first_name` VARCHAR(32) character set utf8 NOT NULL default ''");
$Register['DB']->query("ALTER TABLE `users` ADD `last_name` VARCHAR(32) character set utf8 NOT NULL default ''");*/
$Register['DB']->query("ALTER TABLE `users` CHANGE  `telephone`  `telephone` VARCHAR( 15 ) NOT NULL DEFAULT  '0'");


// Add indexes
/*$Register['DB']->query("ALTER TABLE `forums` ADD INDEX (`in_cat`)");
$Register['DB']->query("ALTER TABLE `loads` ADD INDEX (`category_id`, `author_id`)");
$Register['DB']->query("ALTER TABLE `foto` ADD INDEX (`category_id`, `author_id`)");
$Register['DB']->query("ALTER TABLE `news` ADD INDEX (`category_id`, `author_id`)");
$Register['DB']->query("ALTER TABLE `stat` ADD INDEX (`category_id`, `author_id`)");
$Register['DB']->query("ALTER TABLE `comments` ADD INDEX (`entity_id`, `user_id`)");
$Register['DB']->query("ALTER TABLE `messages` ADD INDEX (`to_user`, `from_user`)");
$Register['DB']->query("ALTER TABLE `posts` ADD INDEX (`id_theme`)");
$Register['DB']->query("ALTER TABLE `themes` ADD INDEX (`id_forum`)");
$Register['DB']->query("ALTER TABLE `users_votes` ADD INDEX (`from_user`, `to_user`)");
$Register['DB']->query("ALTER TABLE `loads_add_content` ADD INDEX (`field_id`, `entity_id`)");
$Register['DB']->query("ALTER TABLE `news_add_content` ADD INDEX (`field_id`, `entity_id`)");
$Register['DB']->query("ALTER TABLE `stat_add_content` ADD INDEX (`field_id`, `entity_id`)");
$Register['DB']->query("ALTER TABLE `users_add_content` ADD INDEX (`field_id`, `entity_id`)");
$Register['DB']->query("ALTER TABLE `users_warnings` ADD INDEX (`user_id`, `admin_id`)");
$Register['DB']->query("ALTER TABLE `loads_attaches` ADD INDEX (`user_id`)");
$Register['DB']->query("ALTER TABLE `news_attaches` ADD INDEX (`user_id`)");
$Register['DB']->query("ALTER TABLE `stat_attaches` ADD INDEX (`user_id`)");
$Register['DB']->query("ALTER TABLE `forum_attaches` ADD INDEX (`post_id`, `user_id`)");*/


die(__('Operation is successful'));