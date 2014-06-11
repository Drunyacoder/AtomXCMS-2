<?php

$menuInfo = array(
    'url' => 'settings.php?m=foto',
    'ankor' => __('Foto'),
	'sub' => array(
        'settings.php?m=foto' => __('Settings'),
        'design.php?m=foto' => __('Design'),
        'category.php?mod=foto' => __('Categories management'),
        'materials_list.php?m=foto' => __('List of materials'),
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
	'max_file_size' => array(
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
	'per_page' => array(
		'type' => 'text',
		'title' => __('Materials per page'),
		'description' => '',
		'help' => '',
	),
	'description_lenght' => array(
		'type' => 'text',
		'title' => __('Max description length'),
		'description' => '',
		'help' => '',
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
	'file_field' => array(
		'type' => 'checkbox',
		'title' => __('File'),
		'attr' => array(
			'disabled' => 'disabled',
			'checked' => 'checked',
		),
	),
	'sub_description' => array(
		'type' => 'checkbox',
		'title' => __('Description'),
		'value' => 'description',
		'fields' => 'fields',
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



