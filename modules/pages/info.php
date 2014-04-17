<?php


$menuInfo = array(
    'url' => 'settings.php?m=pages',
    'ankor' => __('Pages'),
	'sub' => array(
        'settings.php?m=pages' => __('Settings'),
        'page.php' => __('Pages management'),
	),
);



$settingsInfo = array(
    'active' => array(
        'type' => 'checkbox',
        'title' => __('Status'),
        'checked' => '1',
        'value' => '1',
        'description' => __('Enable/Disable'),
    ),
);