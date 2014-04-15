<?php

$menuInfo = array(
    'url' => 'settings.php?m=news',
    'ankor' => 'Новости',
	'sub' => array(
        'settings.php?m=news' => 'Настройки',
        'design.php?m=news' => 'Дизайн',
        'category.php?mod=news' => 'Управление категориями',
        'additional_fields.php?m=news' => 'Дополнительные поля',
        'materials_list.php?m=news&premoder=1' => 'Премодерация',
        'materials_list.php?m=news' => 'Список материалов',
		'comments_list.php?m=news&premoder=1' => 'Премодерация комментариев',
        'comments_list.php?m=news' => 'Список комментариев',
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
	'max_lenght' => array(
		'type' => 'text',
		'title' => 'Максимальный размер материала',
		'description' => '',
		'help' => 'Символов',
	),
	'announce_lenght' => array(
		'type' => 'text',
		'title' => 'Размер анонса',
		'description' => '',
		'help' => 'Символов',
	),
	'per_page' => array(
		'type' => 'text',
		'title' => 'Материалов на страницу',
		'description' => '',
		'help' => 'Единиц',
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

	
	'Поля обязательные для заполнения' => 'Поля обязательные для заполнения',
	'category_field' => array(
		'type' => 'checkbox',
		'title' => 'Категория',
		'attr' => array(
			'disabled' => 'disabled',
			'checked' => 'checked',
		),
	),
	'title_field' => array(
		'type' => 'checkbox',
		'title' => 'Заголовок',
		'attr' => array(
			'disabled' => 'disabled',
			'checked' => 'checked',
		),
	),
	'main_field' => array(
		'type' => 'checkbox',
		'title' => 'Текст материала',
		'attr' => array(
			'disabled' => 'disabled',
			'checked' => 'checked',
		),
	),
	'sub_description' => array(
		'type' => 'checkbox',
		'title' => 'Краткое описание',
		'value' => 'description',
		'fields' => 'fields',
		'checked' => '1',
	),
	'sub_tags' => array(
		'type' => 'checkbox',
		'title' => 'Теги',
		'value' => 'tags',
		'fields' => 'fields',
		'checked' => '1',
	),
	'sub_sourse' => array(
		'type' => 'checkbox',
		'title' => 'Источник(автор)',
		'value' => 'sourse',
		'fields' => 'fields',
		'checked' => '1',
	),
	'sub_sourse_email' => array(
		'type' => 'checkbox',
		'title' => 'E-Mail автора',
		'value' => 'sourse_email',
		'fields' => 'fields',
		'checked' => '1',
	),
	'sub_sourse_site' => array(
		'type' => 'checkbox',
		'title' => 'Сайт автора',
		'value' => 'sourse_site',
		'fields' => 'fields',
		'checked' => '1',
	),

	
	'Комментарии' => 'Комментарии',
	'comment_lenght' => array(
		'type' => 'text',
		'title' => 'Максимальный размер',
		'help' => 'Символов',
	),
	'comment_per_page' => array(
		'type' => 'text',
		'title' => 'Комментариев на страницу',
		'help' => 'Единиц',
	),
	'comments_order' => array(
		'type' => 'checkbox',
		'title' => 'Новые сверху',
		'value' => '1',
		'checked' => '1',
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