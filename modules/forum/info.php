<?php


$menuInfo = array(
    'url' => 'settings.php?m=forum',
    'ankor' => 'Форум',
	'sub' => array(
        'settings.php?m=forum' => 'Настройки',
        'design.php?m=forum' => 'Дизайн',
        'forum_cat.php' => 'Управление форумами',
        'forum_repair.php' => 'Пересчет сообщений',
	),
);




$settingsInfo = array(
	'title' => array(
		'type' => 'text',
		'title' => 'Заголовок',
		'description' => 'Заголовок, который подставится в блок <title></title>',
	),
	'description' => array(
		'type' => 'text',
		'title' => 'Описание',
		'description' => 'То, что подставится в мета тег description',
	),
	'not_reg_user' => array(
		'type' => 'text',
		'title' => 'Псевдоним гостя',
		'description' => '(Под этим именем будет показано сообщение (пост) <br>
 не зарегистрированного пользователя)',
	),
	
	
	'Ограничения' => 'Ограничения',
	'max_post_lenght' => array(
		'type' => 'text',
		'title' => 'Максимальная длина сообщения',
		'description' => '',
		'help' => 'Символов',
	),
	'posts_per_page' => array(
		'type' => 'text',
		'title' => 'Постов на странице',
		'description' => '',
		'help' => '',
	),
	'themes_per_page' => array(
		'type' => 'text',
		'title' => 'Тем на странице',
		'description' => '',
		'help' => '',
	),
	
	
	'Прочее' => 'Прочее',
	'active' => array(
		'type' => 'checkbox',
		'title' => 'Статус',
		'description' => '(Активирован/Деактивирован)',
		'value' => '1',
		'checked' => '1',
	),
);
