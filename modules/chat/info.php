<?php

$menuInfo = array(
    'url' => 'settings.php?m=chat',
    'ankor' => __('Chat'),
	'sub' => array(
        'settings.php?m=chat' => __('Settings'),
        'design.php?m=chat' => __('Design'),
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
	
	
	__('Restrictions'),
	'max_lenght' => array(
		'type' => 'text',
		'title' => __('Max message length'),
		'description' => '',
		'help' => __('Symbols'),
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
