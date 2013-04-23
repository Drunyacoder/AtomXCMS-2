
CREATE TABLE `chank` (
  `name` varchar(255) character set utf8 default NULL,
  `chanck` longtext character set utf8,
  `id` int(11) NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;



INSERT INTO `chank` VALUES ('footer', 'Copi by Drunya', 2);
INSERT INTO `chank` VALUES ('MENU_1', '<ul class="uMenuRoot">\r\n<li><div class="uMenuItem"><a href="/">Главная</a></div></li>\r\n<li><div class="uMenuItem"><a href="/news/">Новости</a></div></li>\r\n<li><div class="uMenuItem"><a href="/load/">Файлы</a></div></li>\r\n<li><div class="uMenuItem"><a href="/stat/">Статьи</a></div></li>\r\n<li><div class="uMenuItem"><a href="/forum/">Форум</a></div></li>\r\n</ul>', 10);

-- --------------------------------------------------------

-- 
-- Структура таблицы `forum_cat`
-- 

CREATE TABLE `forum_cat` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) collate utf8_unicode_ci default NULL,
  `previev_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- 
-- Дамп данных таблицы `forum_cat`
-- 

INSERT INTO `forum_cat` VALUES (1, 'TEST', 1);

-- --------------------------------------------------------

-- 
-- Структура таблицы `forums`
-- 

CREATE TABLE `forums` (
  `id` int(6) NOT NULL auto_increment,
  `title` varchar(100) character set utf8 default NULL,
  `description` mediumtext collate utf8_unicode_ci NOT NULL,
  `pos` smallint(6) NOT NULL default '0',
  `in_cat` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- 
-- Дамп данных таблицы `forums`
-- 

INSERT INTO `forums` VALUES (1, 'TEST', 'тестовый форум', 1, 1);

-- --------------------------------------------------------

-- 
-- Структура таблицы `htmlpage`
-- 

CREATE TABLE `htmlpage` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `template` varchar(255) collate utf8_unicode_ci NOT NULL,
  `content` longtext collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;


-- --------------------------------------------------------

-- 
-- Структура таблицы `loads`
-- 

CREATE TABLE `loads` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `announce` longtext NOT NULL,
  `main` longtext NOT NULL,
  `author_id` int(11) NOT NULL,
  `cat_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `author_name` varchar(255) NOT NULL,
  `cat_name` varchar(255) NOT NULL,
  `section_name` varchar(255) NOT NULL,
  `views` int(11) default '0',
  `downloads` int(11) default '0',
  `rate` int(11) default '0',
  `download` varchar(255) NOT NULL,
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

-- 
-- Структура таблицы `loads_sections`
-- 

CREATE TABLE `loads_sections` (
  `id` int(11) NOT NULL auto_increment,
  `id_section` int(11) default '0',
  `class` varchar(100) NOT NULL,
  `announce` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

-- 
-- Дамп данных таблицы `loads_sections`
-- 

INSERT INTO `loads_sections` VALUES (1, 0, 'section', '', 'TEST');
INSERT INTO `loads_sections` VALUES (3, 1, 'category', '', 'TEST CAT');


-- --------------------------------------------------------

-- 
-- Структура таблицы `messages`
-- 

CREATE TABLE `messages` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `to_user` int(10) unsigned NOT NULL default '0',
  `from_user` int(10) unsigned NOT NULL default '0',
  `sendtime` datetime NOT NULL default '0000-00-00 00:00:00',
  `subject` varchar(255) default NULL,
  `message` text,
  `id_rmv` int(10) unsigned NOT NULL default '0',
  `viewed` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;


-- --------------------------------------------------------

-- 
-- Структура таблицы `news`
-- 

CREATE TABLE `news` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) collate utf8_unicode_ci NOT NULL,
  `announce` text collate utf8_unicode_ci NOT NULL,
  `main` text collate utf8_unicode_ci NOT NULL,
  `views` int(11) default '0',
  `date` datetime default NULL,
  `author_name` varchar(255) character set utf8 default NULL,
  `section_id` int(11) NOT NULL,
  `cat_id` int(11) NOT NULL,
  `cat_name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `section_name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `author_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- 
-- Структура таблицы `news_sections`
-- 

CREATE TABLE `news_sections` (
  `id` int(11) NOT NULL auto_increment,
  `section_id` int(11) default '0',
  `class` varchar(100) NOT NULL,
  `announce` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

-- 
-- Дамп данных таблицы `news_sections`
-- 

INSERT INTO `news_sections` VALUES (1, 0, 'section', '', 'TEST');
INSERT INTO `news_sections` VALUES (2, 1, 'category', '', 'TEST CAT');

-- --------------------------------------------------------

-- 
-- Структура таблицы `posts`
-- 

CREATE TABLE `posts` (
  `id` int(11) NOT NULL auto_increment,
  `message` text,
  `putfile` tinytext,
  `author` tinytext character set utf8 NOT NULL,
  `id_author` int(6) unsigned NOT NULL default '0',
  `time` datetime NOT NULL default '0000-00-00 00:00:00',
  `edittime` datetime NOT NULL default '0000-00-00 00:00:00',
  `id_editor` int(6) unsigned NOT NULL default '0',
  `id_theme` int(11) NOT NULL default '0',
  `locked` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

-- 
-- Структура таблицы `snippets`
-- 

CREATE TABLE `snippets` (
  `name` varchar(255) default NULL,
  `body` longtext,
  `id` int(11) NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;


-- --------------------------------------------------------

-- 
-- Структура таблицы `stat`
-- 

CREATE TABLE `stat` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `announce` longtext NOT NULL,
  `main` longtext NOT NULL,
  `author_id` int(11) NOT NULL,
  `cat_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `author_name` varchar(255) NOT NULL,
  `cat_name` varchar(255) NOT NULL,
  `section_name` varchar(255) NOT NULL,
  `views` int(11) default '0',
  `downloads` int(11) default '0',
  `rate` int(11) default '0',
  `download` varchar(255) NOT NULL,
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;


-- 
-- Структура таблицы `stat_sections`
-- 

CREATE TABLE `stat_sections` (
  `id` int(11) NOT NULL auto_increment,
  `class` set('section','cat') default 'section',
  `section_id` int(11) default NULL,
  `title` varchar(100) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

-- 
-- Дамп данных таблицы `stat_sections`
-- 

INSERT INTO `stat_sections` VALUES (1, 'section', 0, 'TEST');
INSERT INTO `stat_sections` VALUES (3, 'cat', 1, 'TEST CAT');


-- --------------------------------------------------------

-- 
-- Структура таблицы `statistics`
-- 

CREATE TABLE `statistics` (
  `id` int(11) NOT NULL auto_increment,
  `ip` varchar(50) NOT NULL,
  `referer` varchar(255) NOT NULL,
  `date` date default NULL,
  `views` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;


-- --------------------------------------------------------

-- 
-- Структура таблицы `themes`
-- 

CREATE TABLE `themes` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(120) default NULL,
  `author` tinytext character set utf8 NOT NULL,
  `id_author` int(6) NOT NULL default '0',
  `time` datetime NOT NULL default '0000-00-00 00:00:00',
  `id_last_author` int(6) NOT NULL default '0',
  `last_author` varchar(32) default NULL,
  `last_post` datetime NOT NULL,
  `id_forum` int(2) NOT NULL default '0',
  `locked` tinyint(1) unsigned NOT NULL default '0',
  `posts` int(11) default '0',
  `views` int(11) default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

-- Структура таблицы `users`
-- 

CREATE TABLE `users` (
  `id` int(6) NOT NULL auto_increment,
  `name` varchar(32) character set utf8 NOT NULL default '',
  `passw` varchar(255) character set utf8 NOT NULL default '',
  `email` varchar(64) character set utf8 NOT NULL default '',
  `timezone` tinyint(2) NOT NULL default '0',
  `url` varchar(64) character set utf8 NOT NULL default '',
  `icq` varchar(12) character set utf8 NOT NULL default '',
  `about` tinytext character set utf8 NOT NULL,
  `signature` tinytext character set utf8 NOT NULL,
  `photo` varchar(32) character set utf8 NOT NULL default '',
  `puttime` datetime NOT NULL default '0000-00-00 00:00:00',
  `last_visit` datetime NOT NULL default '0000-00-00 00:00:00',
  `themes` mediumint(8) unsigned NOT NULL default '0',
  `posts` int(10) unsigned NOT NULL default '0',
  `status` set('1','2','3') NOT NULL default '1',
  `locked` tinyint(1) NOT NULL default '0',
  `activation` varchar(255) character set utf8 NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

-- 
-- Дамп данных таблицы `users`
-- 


-- --------------------------------------------------------

-- 
-- Структура таблицы `users_settings`
-- 

CREATE TABLE `users_settings` (
  `id` int(11) NOT NULL auto_increment,
  `type` varchar(255) NOT NULL,
  `values` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

-- 
-- Дамп данных таблицы `users_settings`
-- 

INSERT INTO `users_settings` VALUES (1, 'rating', 'a:36:{s:3:"cms";s:21:"Z:/home/localhost/www";s:11:"cookie_time";s:2:"30";s:9:"start_mod";s:0:"";s:8:"open_reg";s:1:"1";s:10:"debug_mode";s:1:"1";s:5:"forum";a:12:{s:5:"title";s:27:"Каталог файлов";s:11:"description";s:42:"Большой каталог файлов";s:15:"max_avatar_size";s:6:"100000";s:12:"not_reg_user";s:16:"Гостелло";s:15:"max_post_lenght";s:3:"500";s:18:"max_message_lenght";s:4:"3000";s:15:"max_mail_lenght";s:3:"500";s:14:"max_count_mess";s:2:"50";s:13:"post_per_page";N;s:15:"themes_per_page";s:1:"4";s:14:"posts_per_page";s:2:"15";s:14:"users_per_page";s:2:"30";}s:13:"max_file_size";s:8:"15000000";s:19:"min_password_lenght";s:1:"6";s:11:"admin_email";s:19:"drunyacoder@mail.ru";s:14:"redirect_delay";s:1:"1";s:12:"time_on_line";s:2:"10";s:4:"news";a:4:{s:5:"title";s:14:"Новости";s:15:"max_news_lenght";s:5:"15000";s:19:"max_announce_lenght";s:3:"700";s:13:"news_per_page";s:1:"3";}s:4:"stat";a:5:{s:5:"title";s:12:"Статьи";s:11:"description";s:44:"Много интересных статей";s:15:"max_post_lenght";s:5:"15000";s:13:"stat_per_page";s:1:"7";s:15:"announce_lenght";s:4:"1500";}s:7:"antisql";s:3:"yes";s:4:"load";a:8:{s:5:"title";s:27:"Каталог файлов";s:11:"description";s:54:"Каталог файлов. Все файлы тут.";s:15:"min_load_lenght";s:3:"200";s:15:"max_load_lenght";s:4:"1500";s:19:"max_announce_lenght";s:3:"300";s:14:"posts_per_page";s:1:"5";s:13:"max_file_size";s:8:"15000000";s:14:"loads_per_page";s:1:"5";}s:4:"rat0";s:21:"хуй просышь";s:5:"cond1";s:1:"2";s:4:"rat1";s:14:"никакой";s:5:"cond2";s:1:"5";s:4:"rat2";s:12:"салага";s:5:"cond3";s:1:"7";s:4:"rat3";s:13:"кое что";s:5:"cond4";s:2:"12";s:4:"rat4";s:8:"шкет";s:5:"cond5";s:2:"17";s:4:"rat5";s:19:"пойдет тип";s:5:"cond6";s:2:"22";s:4:"rat6";s:16:"зависший";s:5:"cond7";s:2:"25";s:4:"rat7";s:8:"кент";s:5:"cond8";s:2:"30";s:4:"rat8";s:10:"перец";s:5:"cond9";s:2:"35";s:4:"rat9";s:23:"крутой перец";s:6:"cond10";s:2:"40";s:5:"rat10";s:34:"круче любого перца";}');
INSERT INTO `users_settings` VALUES (2, 'reg_rules', 'Правила как правила:)');
        