<?php


$menuInfo = array(
    'url' => 'settings.php?m=pages',
    'ankor' => 'Страницы',
	'sub' => array(
        'settings.php?m=pages' => 'Настройки',
        'page.php' => 'Управление страницами',
	),
);



$settingsInfo = array(
    'active' => array(
        'type' => 'checkbox',
        'title' => 'Статус',
        'checked' => '1',
        'value' => '1',
        'description' => '(Активирован/Деактивирован)',
    ),
);