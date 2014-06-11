<?php


$menuInfo = array(
    'url' => 'settings.php?m=search',
    'ankor' => __('Search'),
	'sub' => array(
        'settings.php?m=search' => __('Settings'),
        'design.php?m=search&t=search_form' => __('Design'),
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
    'index_interval' => array(
        'type' => 'text',
        'title' => __('Refresh rate'),
        'description' => __('After a number of days to carry out re-index the site'),
    ),
    'min_lenght' => array(
        'type' => 'text',
        'title' => __('Min search query length'),
        'help' => __('Symbols'),
    ),
    'per_page' => array(
        'type' => 'text',
        'title' => __('Results per page'),
        'description' => '',
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