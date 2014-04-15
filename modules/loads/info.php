<?php


$menuInfo = array(
    'url' => 'settings.php?m=loads',
    'ankor' => 'Каталог файлов',
	'sub' => array(
        'settings.php?m=loads' => 'Настройки',
        'design.php?m=loads' => 'Дизайн',
        'category.php?mod=loads' => 'Управление категориями',
        'additional_fields.php?m=loads' => 'Дополнительные поля',
		'materials_list.php?m=loads&premoder=1' => 'Премодерация',
        'materials_list.php?m=loads' => 'Список материалов',
		'comments_list.php?m=loads&premoder=1' => 'Премодерация комментариев',
        'comments_list.php?m=loads' => 'Список комментариев',
	),
);



$settingsInfo = array(
    'title' => array(
        'type' => 'text',
        'title' => 'Заголовок',
    ),
    'description' => array(
        'type' => 'text',
        'title' => 'Описание',
    ),

    'Ограничения' => 'Ограничения',
    'min_lenght' => array(
        'type' => 'text',
        'title' => 'Минимальная длина описания',
    ),
    'max_lenght' => array(
        'type' => 'text',
        'title' => 'Максимальная длина описания',
    ),
    'announce_lenght' => array(
        'type' => 'text',
        'title' => 'Длина анонса',
    ),
    'per_page' => array(
        'type' => 'text',
        'title' => 'Материалов на странице',
    ),
    'max_file_size' => array(
        'type' => 'text',
        'title' => 'Максимальный размер файла',
        'description' => 'Байт',
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
	

    'Обязательные поля' => 'Обязательные поля',
    'fields_cat' => array(
        'type' => 'checkbox',
        'title' => 'Категория',
        'attr' => array(
            'disabled' => 'disabled',
            'checked' => 'checked',
        ),
    ),
    'fields_title' => array(
        'type' => 'checkbox',
        'title' => 'Заголовок',
        'attr' => array(
            'disabled' => 'disabled',
            'checked' => 'checked',
        ),
    ),
    'fields_main' => array(
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
        'fields' => 'fields',
        'value' => 'description',
        'checked' => '1',
    ),
    'sub_tags' => array(
        'type' => 'checkbox',
        'title' => 'Теги',
        'fields' => 'fields',
        'value' => 'tags',
        'checked' => '1',
    ),
    'sub_sourse' => array(
        'type' => 'checkbox',
        'title' => 'Источник(автор)',
        'fields' => 'fields',
        'value' => 'sourse',
        'checked' => '1',
    ),
    'sub_sourse_email' => array(
        'type' => 'checkbox',
        'title' => 'E-Mail автора',
        'fields' => 'fields',
        'value' => 'sourse_email',
        'checked' => '1',
    ),
    'sub_sourse_site' => array(
        'type' => 'checkbox',
        'title' => 'Сайт автора',
        'fields' => 'fields',
        'value' => 'sourse_site',
        'checked' => '1',
    ),
    'sub_download_url' => array(
        'type' => 'checkbox',
        'title' => 'Ссылка на файл',
        'fields' => 'fields',
        'value' => 'download_url',
        'checked' => '1',
    ),
    'sub_download_url_size' => array(
        'type' => 'checkbox',
        'title' => 'Размер удаленног файла',
        'fields' => 'fields',
        'value' => 'download_url_size',
        'checked' => '1',
    ),
    'sub_attach_file' => array(
        'type' => 'checkbox',
        'title' => 'Файл',
        'fields' => 'fields',
        'value' => 'attach_file',
        'checked' => '1',
    ),

    'Комментарии' => 'Комментарии',
    'comment_lenght' => array(
        'type' => 'text',
        'title' => 'Максимальный размер',
    ),
    'comment_per_page' => array(
        'type' => 'text',
        'title' => 'Комментариев на страницу',
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
        'checked' => '1',
        'value' => '1',
        'description' => '(Активирован/Деактивирован)',
    ),
);