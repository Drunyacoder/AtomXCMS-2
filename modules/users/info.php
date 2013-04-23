<?php



$menuInfo = array(
    'url' => 'settings.php?m=users',
    'ankor' => 'Пользователи',
	'sub' => array(
        'settings.php?m=users' => 'Настройки',
        'users_groups.php' => 'Редактор групп',
        'users_rules.php' => 'Права групп',
        'users_rating.php' => 'Ранги пользователей',
        'users_sendmail.php' => 'Массовая рассылка писем',
        'users_reg_rules.php' => 'Правила регистрации',
        'settings.php?m=users' => 'Настройки',
        'additional_fields.php?m=users' => 'Дополнительные поля',
        'users_list.php' => 'Список пользователей',
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
    'max_avatar_size' => array(
        'type' => 'text',
        'title' => 'Максимальный размер аватара',
        'description' => 'Это картинка, которая отображаеться на форуме и в профиле пользователя',
        'help' => 'Байт',
    ),
    'users_per_page' => array(
        'type' => 'text',
        'title' => 'Пользователей на одну страницу',
        'description' => 'в списке зарегистрированных пользователей',
    ),
    'max_mail_lenght' => array(
        'type' => 'text',
        'title' => 'Максимальный размер письма',
        'description' => 'которое один пользователь форума может написать другому',
    ),
    'max_count_mess' => array(
        'type' => 'text',
        'title' => 'Максимальное количество личных сообщений',
        'description' => 'в папках "Входящие" и "Исходящие"',
    ),
    'max_message_lenght' => array(
        'type' => 'text',
        'title' => 'Максимальная длина личного сообщения',
        'description' => '',
    ),
    'rating_comment_lenght' => array(
        'type' => 'text',
        'title' => 'Максимальная длина комментария к голосу',
        'description' => '',
    ),
    'warnings_by_ban' => array(
        'type' => 'text',
        'title' => 'Кол-во предупреждений для наступления бана',
        'description' => '',
    ),
    'autoban_interval' => array(
        'type' => 'text',
        'title' => 'Продолжительность бана, из-за предупреждений',
        'help' => 'Секунд',
    ),


    'Обязательные поля' => 'Обязательные поля',
    'fields_login' => array(
        'type' => 'checkbox',
        'title' => 'Имя',
        'attr' => array(
            'disabled' => 'disabled',
            'checked' => 'checked',
        ),
    ),
    'fields_email' => array(
        'type' => 'checkbox',
        'title' => 'E-mail',
        'attr' => array(
            'disabled' => 'disabled',
            'checked' => 'checked',
        ),
    ),
    'fields_password' => array(
        'type' => 'checkbox',
        'title' => 'Пароль',
        'attr' => array(
            'disabled' => 'disabled',
            'checked' => 'checked',
        ),
    ),
    'sub_keystring' => array(
        'type' => 'checkbox',
        'title' => 'Код(каптча)',
        'fields' => 'fields',
        'value' => 'keystring',
        'checked' => '1',
    ),
    'sub_icq' => array(
        'type' => 'checkbox',
        'title' => 'ICQ',
        'fields' => 'fields',
        'value' => 'icq',
        'checked' => '1',
    ),
    'sub_jabber' => array(
        'type' => 'checkbox',
        'title' => 'Jabber',
        'fields' => 'fields',
        'value' => 'jabber',
        'checked' => '1',
    ),
    'sub_pol' => array(
        'type' => 'checkbox',
        'title' => 'Пол',
        'fields' => 'fields',
        'value' => 'pol',
        'checked' => '1',
    ),
    'sub_city' => array(
        'type' => 'checkbox',
        'title' => 'Город',
        'fields' => 'fields',
        'value' => 'city',
        'checked' => '1',
    ),
    'sub_telephone' => array(
        'type' => 'checkbox',
        'title' => 'Телефон',
        'fields' => 'fields',
        'value' => 'telephone',
        'checked' => '1',
    ),
    'sub_byear' => array(
        'type' => 'checkbox',
        'title' => 'Год рождения',
        'fields' => 'fields',
        'value' => 'byear',
        'checked' => '1',
    ),
    'sub_bmonth' => array(
        'type' => 'checkbox',
        'title' => 'Месяц рождения',
        'fields' => 'fields',
        'value' => 'bmonth',
        'checked' => '1',
    ),
    'sub_bday' => array(
        'type' => 'checkbox',
        'title' => 'День рождения',
        'fields' => 'fields',
        'value' => 'bday',
        'checked' => '1',
    ),
    'sub_url' => array(
        'type' => 'checkbox',
        'title' => 'Домашняя страничка',
        'fields' => 'fields',
        'value' => 'url',
        'checked' => '1',
    ),
    'sub_about' => array(
        'type' => 'checkbox',
        'title' => 'Интересы',
        'fields' => 'fields',
        'value' => 'about',
        'checked' => '1',
    ),
    'sub_signature' => array(
        'type' => 'checkbox',
        'title' => 'Подпись',
        'fields' => 'fields',
        'value' => 'signature',
        'checked' => '1',
    ),
    'sub_timezone' => array(
        'type' => 'checkbox',
        'title' => 'Временная зон',
        'fields' => 'fields',
        'value' => 'timezone',
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