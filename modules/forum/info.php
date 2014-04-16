<?php


$menuInfo = array(
    'url' => 'settings.php?m=forum',
    'ankor' => __('Forum'),
	'sub' => array(
        'settings.php?m=forum' => __('Settings'),
        'design.php?m=forum' => __('Design'),
        'forum_cat.php' => __('Forums management'),
        'forum_repair.php' => __('Posts recounting'),
	),
);




$settingsInfo = array(
	'title' => array(
		'type' => 'text',
		'title' => __('Title'),
		'description' => sprintf(__('Used in the template as %s'), '{{ meta_title }} | {{ title }}'),
	),
	'description' => array(
		'type' => 'text',
		'title' => __('Description'),
		'description' => sprintf(__('Used in the template as %s'), '{{ meta_description }}'),
	),
	'not_reg_user' => array(
		'type' => 'text',
		'title' => __('Alias for guests'),
		'description' => __('this name will be shown as non authorized user nickname'),
	),


    __('Restrictions'),
	'max_post_lenght' => array(
		'type' => 'text',
		'title' => __('Max message length'),
		'description' => '',
		'help' => __('Symbols'),
	),
	'posts_per_page' => array(
		'type' => 'text',
		'title' => __('Posts per page'),
		'description' => '',
		'help' => '',
	),
	'themes_per_page' => array(
		'type' => 'text',
		'title' => __('Topics per page'),
		'description' => '',
		'help' => '',
	),

	
	__('Images'),
	'img_size_x' => array(
		'type' => 'text',
		'title' => __('Size of X axis'),
		'description' => '',
		'help' => 'Px',
	),
	'img_size_y' => array(
		'type' => 'text',
		'title' => __('Size of Y axis'),
		'description' => '',
		'help' => 'Px',
	),
	'max_attaches_size' => array(
		'type' => 'text',
		'title' => __('Max an attach file size'),
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
		'title' => __('Max quantity of an uploaded files per time'),
		'description' => '',
		'help' => __('Units'),
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
	
	
	__('Common'),
	'active' => array(
		'type' => 'checkbox',
		'title' => __('Status'),
		'description' => __('Enable/Disable'),
		'value' => '1',
		'checked' => '1',
	),
);
