<?php


$menuInfo = array(
    'url' => 'settings.php?m=search',
    'ankor' => 'Поиск',
	'sub' => array(
        'settings.php?m=search' => 'Настройки',
        'design.php?m=search&t=search_form' => 'Дизайн',
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
    'index_interval' => array(
        'type' => 'text',
        'title' => 'Частота обновления',
        'description' => 'Через какое кол-во дней проводить переиндексацию сайта',
    ),
    'min_lenght' => array(
        'type' => 'text',
        'title' => 'Минимальная длина запроса',
        'description' => 'Поиск будет вестись только
                по словам отвечающим этому требованию',
        'help' => 'Символов',
    ),
    'per_page' => array(
        'type' => 'text',
        'title' => 'Результатов на страницу',
        'description' => '',
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