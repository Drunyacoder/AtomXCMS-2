<?php

$menuInfo = array(
    'url' => 'settings.php?m=blog',
    'ankor' => __('Blog'),
	'sub' => array(
        'settings.php?m=blog' => __('Settings'),
        'design.php?m=blog' => __('Design'),
        'category.php?mod=blog' => __('Categories management'),
        'additional_fields.php?m=blog' => __('Additional fields'),
        'materials_list.php?m=blog&premoder=1' => __('Premoderation'),
        'materials_list.php?m=blog' => __('List of materials'),
		'comments_list.php?m=blog&premoder=1' => __('Comments premoderation'),
        'comments_list.php?m=blog' => __('Comments list'),
        'blog/blogs_list/?m=blog' => __('Blogs list'),
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
	'announce_lenght' => array(
		'type' => 'text',
		'title' => __('Announce length'),
		'description' => '',
		'help' => __('Symbols'),
	),
	'per_page' => array(
		'type' => 'text',
		'title' => __('Materials per page'),
		'description' => '',
	),
    'main_user' => array(
        'type' => 'checkbox',
        'title' => __('Main user ID'),
        'description' => __('If this option is defined, only this user\'s articles will be displayed'),
        'value' => '1',
        'checked' => '1',
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

	
	__('Required field'),
	'category_field' => array(
		'type' => 'checkbox',
		'title' => __('Category'),
		'attr' => array(
			'disabled' => 'disabled',
			'checked' => 'checked',
		),
	),
	'title_field' => array(
		'type' => 'checkbox',
		'title' => __('Title'),
		'attr' => array(
			'disabled' => 'disabled',
			'checked' => 'checked',
		),
	),
	'main_field' => array(
		'type' => 'checkbox',
		'title' => __('Text of material'),
		'attr' => array(
			'disabled' => 'disabled',
			'checked' => 'checked',
		),
	),
	'sub_description' => array(
		'type' => 'checkbox',
		'title' => __('Short description'),
		'value' => 'description',
		'fields' => 'fields',
		'checked' => '1',
	),
	'sub_tags' => array(
		'type' => 'checkbox',
		'title' => __('Tags'),
		'value' => 'tags',
		'fields' => 'fields',
		'checked' => '1',
	),
	'sub_sourse' => array(
		'type' => 'checkbox',
		'title' => __('Source'),
		'value' => 'sourse',
		'fields' => 'fields',
		'checked' => '1',
	),
	'sub_sourse_email' => array(
		'type' => 'checkbox',
		'title' => __('Author email'),
		'value' => 'sourse_email',
		'fields' => 'fields',
		'checked' => '1',
	),
	'sub_sourse_site' => array(
		'type' => 'checkbox',
		'title' => __('Author site'),
		'value' => 'sourse_site',
		'fields' => 'fields',
		'checked' => '1',
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
    'addform',
    'editform',
);