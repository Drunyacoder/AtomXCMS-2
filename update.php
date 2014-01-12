<?php
include_once '/sys/boot.php';
include '/sys/settings/acl_rules.php';




// convert ACL rules config
$res = array();
foreach ($acl_rules as $mod => $rules) {
	foreach ($rules as $rule => $params) {
		$res[$mod . '.' . $rule] = array(
			'groups' => $params,
			'users' => array(
			
			),
		);
	}	
}
if ($fopen = fopen(ROOT . '/acl_rules.php', 'w')) {
	fputs($fopen, '<?php ' . "\n" . '$acl_rules = ' . var_export($res, true) . "\n" . '?>');
	fclose($fopen);
	return true;
} else {
	return false;
}


// convert section tables
$Register['DB']->query("ALTER TABLE `loads_sections` ADD `path` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `parent_id`");
$Register['DB']->query("ALTER TABLE `foto_sections` ADD `path` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `parent_id`");
$Register['DB']->query("ALTER TABLE `news_sections` ADD `path` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `parent_id`");
$Register['DB']->query("ALTER TABLE `stat_sections` ADD `path` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `parent_id`");


$mods = array('foto', 'loads', 'stat', 'news');
foreach ($mods as $mod) {
	$model = $Register['ModManager']->getModelInstance($mod . 'Sections');
	$categories = $model->getCollection();
	$categories = getTreeNode($categories);
	$categories = setPath($categories);
	
	foreach ($categories as $category) $category->save();
}


function setPath($cats, $out = array(), $path = '') {
	foreach ($cats as $k => $cat) {
		$out[$cat['category']->getId()] = $cat['category'];
		
		
		if ($cat['category']->getParent_id()) {
			$new_path = $path . $cat['category']->getParent_id() . '.';
			$out[$cat['category']->getId()]->setPath($new_path);
		} else {
			$new_path = $path;
		}
		
		if (!empty($cat['subcategories'])) $out = setPath($cat['subcategories'], $out, $new_path);
	}
	
	return $out;
}

function getTreeNode($array, $id = false) {
	$out = array();
	foreach ($array as $key => $val) {
		if ($id === false && !$val->getParent_id()) {
			$out[$val->getid()] = array(
				'category' => $val,
				'subcategories' => getTreeNode($array, $val->getId()),
			);
			unset($array[$key]);
		} else {
		
			if ($val->getParent_id() == $id) {
				$out[$val->getid()] = array(
					'category' => $val,
					'subcategories' => getTreeNode($array, $val->getid()),
				);
				unset($array[$key]);
			}
		}
	}
	return $out;
}