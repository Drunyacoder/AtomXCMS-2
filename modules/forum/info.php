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

	
	'Изображения' => 'Изображения',
	'img_size_x' => array(
		'type' => 'text',
		'title' => 'Размер по оси Х',
		'description' => '',
		'help' => 'Пикселей(Число)',
	),
	'img_size_y' => array(
		'type' => 'text',
		'title' => 'Размер по оси Y',
		'description' => '',
		'help' => 'Пикселей(Число)',
	),
	'max_attaches_size' => array(
		'type' => 'text',
		'title' => 'Максимальный "вес"',
		'description' => '',
		'help' => __('KB'),
		'onview' => array(
			'division' => 1000,
		),
		'onsave' => array(
			'multiply' => 1000,
		),
	),
	'max_attaches' => array(
		'type' => 'text',
		'title' => 'Максимальное кол-во файлов загружаемых за раз',
		'description' => '',
		'help' => 'Единиц',
	),
	'max_all_attaches_size' => array(
		'type' => 'text',
		'title' => __('Max size of all user files'),
		'description' => '',
		'help' => __('MB'),
		'onview' => array(
			'division' => 1000000,
		),
		'onsave' => array(
			'multiply' => 1000000,
		),
	),
	'max_guest_attaches_size' => array(
		'type' => 'text',
		'title' => __('Max total size of files which guests can upload'),
		'description' => __('Used total size of all files which were uploaded by guests'),
		'help' => __('MB'),
		'onview' => array(
			'division' => 1000000,
		),
		'onsave' => array(
			'multiply' => 1000000,
		),
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
