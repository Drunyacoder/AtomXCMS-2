<?php



$menuInfo = array(
    'url' => 'settings.php?m=users',
    'ankor' => __('Users'),
	'sub' => array(
        'settings.php?m=users' => __('Settings'),
        'users_groups.php' => __('Users groups'),
        'users_rules.php' => __('Groups rules'),
        'users_rating.php' => __('Users ranks'),
        'users_sendmail.php' => __('Mass mailing'),
        'users_reg_rules.php' => __('Registration rules'),
        'additional_fields.php?m=users' => __('Additional fields'),
        'users_list.php' => __('Users list'),
	),
);




$settingsInfo = array(
    'title' => array(
        'type' => 'textarea',
        'title' => __('Title'),
		'description' => sprintf(__('Used in the template as %s'), '{{ meta_title }} | {{ title }}'),
    ),
    'description' => array(
        'type' => 'textarea',
		'title' => __('Description'),
		'description' => sprintf(__('Used in the template as %s'), '{{ meta_description }}'),
    ),


    __('Restrictions'),
    'max_avatar_size' => array(
        'type' => 'text',
        'title' => __('Max avatar size'),
        'help' => __('KB'),
		'onview' => array(
			'division' => 1000,
		),
		'onsave' => array(
			'multiply' => 1000,
		),
    ),
    'users_per_page' => array(
        'type' => 'text',
        'title' => __('Users per page'),
        'description' => __('in the already registered users list'),
    ),
    'max_mail_lenght' => array(
        'type' => 'text',
        'title' => __('Max email length'),
        'description' => __('that one user can write to another user'),
    ),
    'max_count_mess' => array(
        'type' => 'text',
        'title' => __('Max quantity of PM messages'),
        'description' => __('when the limit is reached, the user can no longer send and receive messages'),
    ),
    'max_message_lenght' => array(
        'type' => 'text',
        'title' => __('Max PM length'),
        'description' => '',
    ),
    'rating_comment_lenght' => array(
        'type' => 'text',
        'title' => __('Max comment length to a vote'),
        'description' => '',
    ),
    'warnings_by_ban' => array(
        'type' => 'text',
        'title' => __('Quantity of warnings for ban'),
        'description' => '',
    ),
    'autoban_interval' => array(
        'type' => 'text',
        'title' => __('Duration of the ban by warnings level'),
        'help' => __('Seconds'),
    ),
    'use_gravatar' => array(
        'type' => 'checkbox',
        'title' => __('Enable Gravatar'),
        'checked' => '1',
        'value' => '1',
    ),


    __('Required field'),
    'fields_login' => array(
        'type' => 'checkbox',
        'title' => __('Name'),
        'attr' => array(
            'disabled' => 'disabled',
            'checked' => 'checked',
        ),
    ),
    'fields_email' => array(
        'type' => 'checkbox',
        'title' => __('Email'),
        'attr' => array(
            'disabled' => 'disabled',
            'checked' => 'checked',
        ),
    ),
    'fields_password' => array(
        'type' => 'checkbox',
        'title' => __('Password'),
        'attr' => array(
            'disabled' => 'disabled',
            'checked' => 'checked',
        ),
    ),
    'sub_keystring' => array(
        'type' => 'checkbox',
        'title' => __('Keystring (captcha)'),
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
        'title' => __('Gender'),
        'fields' => 'fields',
        'value' => 'pol',
        'checked' => '1',
    ),
    'sub_city' => array(
        'type' => 'checkbox',
        'title' => __('City'),
        'fields' => 'fields',
        'value' => 'city',
        'checked' => '1',
    ),
    'sub_telephone' => array(
        'type' => 'checkbox',
        'title' => __('Telephone'),
        'fields' => 'fields',
        'value' => 'telephone',
        'checked' => '1',
    ),
    'sub_byear' => array(
        'type' => 'checkbox',
        'title' => __('Year of birth'),
        'fields' => 'fields',
        'value' => 'byear',
        'checked' => '1',
    ),
    'sub_bmonth' => array(
        'type' => 'checkbox',
        'title' => __('Month of birth'),
        'fields' => 'fields',
        'value' => 'bmonth',
        'checked' => '1',
    ),
    'sub_bday' => array(
        'type' => 'checkbox',
        'title' => __('Day of birth'),
        'fields' => 'fields',
        'value' => 'bday',
        'checked' => '1',
    ),
    'sub_url' => array(
        'type' => 'checkbox',
        'title' => __('Home page'),
        'fields' => 'fields',
        'value' => 'url',
        'checked' => '1',
    ),
    'sub_about' => array(
        'type' => 'checkbox',
        'title' => __('Interests'),
        'fields' => 'fields',
        'value' => 'about',
        'checked' => '1',
    ),
    'sub_signature' => array(
        'type' => 'checkbox',
        'title' => __('Signature'),
        'fields' => 'fields',
        'value' => 'signature',
        'checked' => '1',
    ),
    'sub_timezone' => array(
        'type' => 'checkbox',
        'title' => __('Timezone'),
        'fields' => 'fields',
        'value' => 'timezone',
        'checked' => '1',
    ),

    __('Common'),
    'active' => array(
        'type' => 'checkbox',
        'title' => __('Status'),
        'checked' => '1',
        'value' => '1',
        'description' => __('Enable/Disable'),
    ),
);