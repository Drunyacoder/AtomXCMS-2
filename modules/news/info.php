<?php

$menuInfo = array(
    'url' => 'settings.php?m=news',
    'ankor' => 'Новости',
	'sub' => array(
        'settings.php?m=news' => 'Настройки',
        'design.php?m=news' => 'Дизайн',
        'category.php?mod=news' => 'Управление категориями',
        'additional_fields.php?m=news' => 'Дополнительные поля',
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
		'help' => 'Кбайт',
		'onview' => array(
			'division' => 1000,
		),
		'onsave' => array(
			'multiply' => 1000,
		),
	),
	'max_attaches' => array(
		'type' => 'text',
		'title' => 'Максимальное кол-во',
		'description' => '',
		'help' => 'Единиц',
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