<?php

$menuInfo = array(
    'url' => 'settings.php?m=shop',
    'ankor' => __('Shop'),
	'sub' => array(
        'settings.php?m=shop' => __('Settings'),
        'design.php?m=shop' => __('Design'),
        'shop/catalog' => __('Catalog management'),
        'shop/attributes_groups' => __('Attributes groups management'),
        'shop/categories' => __('Categories management'),
        'shop/orders' => __('Orders management'),
        'shop/vendors' => __('Vendors management'),
        'shop/delivery' => __('Delivery management'),
        'shop/statistics' => __('Statistics'),
		'comments_list.php?m=shop&premoder=1' => __('Comments premoderation'),
        'comments_list.php?m=shop' => __('Comments list'),
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
	'max_lenght' => array(
		'type' => 'text',
		'title' => __('Max material length'),
		'description' => '',
		'help' => __('Symbols'),
	),
	'per_page' => array(
		'type' => 'text',
		'title' => __('Materials per page'),
		'description' => '',
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

	
	__('Comments'),
	'comment_lenght' => array(
		'type' => 'text',
        'title' => __('Max length'),
        'help' => __('Symbols'),
	),
	'comment_per_page' => array(
		'type' => 'text',
		'title' => __('Comments per page'),
	),
	'comments_order' => array(
		'type' => 'checkbox',
		'title' => __('New on the top'),
		'value' => '1',
		'checked' => '1',
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


$allowedTemplateFiles = array(
    'main',
    'list',
    'material',
    'order_form',
    'filters',
    'complete_order',
    'input',
);
