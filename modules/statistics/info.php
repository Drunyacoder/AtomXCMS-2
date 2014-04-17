<?php

$menuInfo = array(
    'url' => 'settings.php?m=statistics',
    'ankor' => __('Statistics'),
	'sub' => array(
        'settings.php?m=statistics' => __('Settings'),
        'statistic.php' => __('View statistics'),
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