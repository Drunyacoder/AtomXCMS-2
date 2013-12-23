<?php
@ini_set('display_errors', 0);


//set chmod
function setChMod($path, $mode = 0766, $recursive = true) {
	clearstatcache();
	$flag = true;
	if (file_exists($path) && is_dir($path) && $recursive === true) {
		$child = glob($path . '/*');

		if (!empty($child)) {
			//recursive
			foreach ($child as $row) {
				if ($row != '.' && $row != '..') {
					if (!setChMod($row)) {
						$flag = false;
					}
				}
			}
		}
		if (!@chmod($path, $mode) || $flag === false) return false;
		return true;
	
	} else if (file_exists($path)) {
		if (@chmod($path, $mode)) return true;
		return false;
	}
}


function checkWriteablePerms($path, $recursive = true) {
	if (file_exists($path) && is_dir($path) && $recursive === true) {
		$child = glob($path . '/*');

		if (!empty($child)) {
			//recursive
			foreach ($child as $row) {
				if ($row != '.' && $row != '..') {
					if (!checkWriteablePerms($row)) {
						$flag = false;
					}
				}
			}
		}
		if (!@is_writeable($path) || $flag === false) return false;
		return true;
	} else {
		if (@is_writeable($path)) return true;
		return false;
	}
}

$out = '';
$flag = true;
if (!setChMod('../sys/cache/') && !checkWriteablePerms('../sys/cache/')) {
	$out .= '<span style="color:#FF0000">/sys/cache/</span> - Права выставлены не верно<br />';
	$flag = false;
}
if (!setChMod('../sys/avatars/') && !checkWriteablePerms('../sys/avatars/')) {
	$out .= '<span style="color:#FF0000">/sys/avatars/</span> - Права выставлены не верно<br />';
	$flag = false;
}
if (!setChMod('../sys/files/') && !checkWriteablePerms('../sys/files/')) {
	$out .= '<span style="color:#FF0000">/sys/files/</span> - Права выставлены не верно<br />';
	$flag = false;
}
if (!setChMod('../sys/logs/') && !checkWriteablePerms('../sys/logs/')) {
	$out .= '<span style="color:#FF0000">/sys/logs/</span> - Права выставлены не верно<br />';
	$flag = false;
}
if (!setChMod('../sys/tmp/') && !checkWriteablePerms('../sys/tmp/')) {
	$out .= '<span style="color:#FF0000">/sys/tmp/</span> - Права выставлены не верно<br />';
	$flag = false;
}
if (!setChMod('../sys/settings/') && !checkWriteablePerms('../sys/settings/')) {
	$out .= '<span style="color:#FF0000">/sys/settings/</span> - Права выставлены не верно<br />';
	$flag = false;
}
if (!setChMod('../template/') && !checkWriteablePerms('../template/')) {
	$out .= '<span style="color:#FF0000">/template/</span> - Права выставлены не верно<br />';
	$flag = false;
}
if (!setChMod('../sitemap.xml') && !checkWriteablePerms('../sitemap.xml')) {
	$out .= '<span style="color:#FF0000">/sitemap.xml</span> - Права выставлены не верно<br />';
	$flag = false;
}
if (!setChMod('../sys/plugins/') && !checkWriteablePerms('../sys/plugins/')) {
	$out .= '<span style="color:#FF0000">/sys/plugins/</span> - Права выставлены не верно<br />';
	$flag = false;
}

echo $out;
if ($flag === false) {
	echo '<span style="color:#E90E0E">Не удалось выставить права на все необходимые папки и файлы! Сделайте это в ручную.</span><br />';
} else {
	echo '<span style="color:#46B100">Права на все необходимые файлы установлены верно!</span><br />';
}


?>