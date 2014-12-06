<?php


$menuInfo = array(
    'url' => 'settings.php?m=loads',
    'ankor' => __('Loads'),
	'sub' => array(
        'settings.php?m=loads' => __('Settings'),
        'design.php?m=loads' => __('Design'),
        'category.php?mod=loads' => __('Categories management'),
        'additional_fields.php?m=loads' => __('Additional fields'),
		'materials_list.php?m=loads&premoder=1' => __('Premoderation'),
        'materials_list.php?m=loads' => __('List of materials'),
		'comments_list.php?m=loads&premoder=1' => __('Comments premoderation'),
        'comments_list.php?m=loads' => __('Comments list'),
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
    'min_lenght' => array(
        'type' => 'text',
        'title' => __('Min description length'),
    ),
    'max_lenght' => array(
        'type' => 'text',
        'title' => __('Max description length'),
    ),
    'announce_lenght' => array(
        'type' => 'text',
        'title' => __('Announce length'),
    ),
    'per_page' => array(
        'type' => 'text',
        'title' => __('Materials per page'),
    ),
    'max_file_size' => array(
        'type' => 'text',
        'title' => __('Max the main file size'),
		'help' => __('KB'),
		'onview' => array(
			'division' => 1000,
		),
		'onsave' => array(
			'multiply' => 1000,
		),
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
    'fields_cat' => array(
        'type' => 'checkbox',
        'title' => __('Category'),
        'attr' => array(
            'disabled' => 'disabled',
            'checked' => 'checked',
        ),
    ),
    'fields_title' => array(
        'type' => 'checkbox',
        'title' => __('Title'),
        'attr' => array(
            'disabled' => 'disabled',
            'checked' => 'checked',
        ),
    ),
    'fields_main' => array(
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
        'fields' => 'fields',
        'value' => 'description',
        'checked' => '1',
    ),
    'sub_tags' => array(
        'type' => 'checkbox',
        'title' => __('Tags'),
        'fields' => 'fields',
        'value' => 'tags',
        'checked' => '1',
    ),
    'sub_sourse' => array(
        'type' => 'checkbox',
        'title' => __('Source'),
        'fields' => 'fields',
        'value' => 'sourse',
        'checked' => '1',
    ),
    'sub_sourse_email' => array(
        'type' => 'checkbox',
        'title' => __('Author email'),
        'fields' => 'fields',
        'value' => 'sourse_email',
        'checked' => '1',
    ),
    'sub_sourse_site' => array(
        'type' => 'checkbox',
        'title' => __('Author site'),
        'fields' => 'fields',
        'value' => 'sourse_site',
        'checked' => '1',
    ),
    'sub_download_url' => array(
        'type' => 'checkbox',
        'title' => __('Link to file'),
        'fields' => 'fields',
        'value' => 'download_url',
        'checked' => '1',
    ),
    'sub_download_url_size' => array(
        'type' => 'checkbox',
        'title' => __('Remote file size'),
        'fields' => 'fields',
        'value' => 'download_url_size',
        'checked' => '1',
    ),
    'sub_attach_file' => array(
        'type' => 'checkbox',
        'title' => __('File'),
        'fields' => 'fields',
        'value' => 'attach_file',
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
        'checked' => '1',
        'value' => '1',
        'description' => __('Enable/Disable'),
    ),
);