<?php

$menuInfo = array(
    'url' => 'settings.php?m=chat',
    'ankor' => 'Чат',
	'sub' => array(
        'settings.php?m=chat' => 'Настройки',
        'design.php?m=chat' => 'Дизайн',
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
	
	
	'Ограничения' => 'Ограничения',
	'max_lenght' => array(
		'type' => 'text',
		'title' => 'Максимальная длина сообщения',
		'description' => '',
		'help' => 'Символов',
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
