<?php
##################################################
##												##
## @Author:       Andrey Brykin (Drunya)        ##
## @Version:      1.6.1                         ##
## @Project:      CMS                           ##
## @package       CMS Fapos                     ##
## @subpackege    Admin Panel module            ##
## @copyright     ©Andrey Brykin 2010-2013      ##
## @last mod.     2013/04/24                    ##
##################################################


##################################################
##												##
## any partial or not partial extension         ##
## CMS Fapos,without the consent of the         ##
## author, is illegal                           ##
##################################################
## Любое распространение                        ##
## CMS Fapos или ее частей,                     ##
## без согласия автора, является не законным    ##
##################################################


include_once '../sys/boot.php';
include_once ROOT . '/admin/inc/adm_boot.php';
$pageTitle = 'Системные настройки';
$Register = Register::getInstance();
$config = $Register['Config']->read('all');





// Prepare templates selct list
$sourse = glob(ROOT . '/template/*', GLOB_ONLYDIR);
if (!empty($sourse) && is_array($sourse)) {
	$templates = array();
	foreach ($sourse as $dir) {
		if (preg_match('#.*/(\w+)$#', $dir, $match)) {
			$templates[] = $match[1];
		}
	}
}

$templateSelect = array();
if (!empty($templates)) {
	foreach ($templates as $value) {
		$templateSelect[$value] = ucfirst($value);
	}
}



// Prepare fonts select list
$fonts = glob(ROOT . '/sys/fonts/*.ttf');
$fontSelect = array();
if (!empty($fonts)) {
	sort($fonts);
	foreach ($fonts as $value) {
		$pos = strrpos($value, "/");
		if ($pos >= 0) {
			$value = substr($value, $pos + 1);
		}
		$fontSelect[$value] = $value;
	}
}



// Prepare smiles select list
$smiles = glob(ROOT . '/sys/img/smiles/*/info.php');
sort($smiles);
$smilesSelect = array();
if (!empty($smiles)) {
	foreach ($smiles as $value) {
		if (is_file($value)) {
			include_once $value;
			$path = dirname($value);
			$pos = strrpos($path, "/");
			if ($pos >= 0) {
				$value = substr($path, $pos + 1);
			}
			if (isset($smilesInfo) && isset($smilesInfo['name'])) {
				$smilesSelect[$value] = $smilesInfo['name'];
			};
		}
	}
} else {
	$smilesSelect['fapos'] = 'Fapos';
}



/**
 * For show template preview
 * 
 * @param string $template
 * @return string
 */
function getImgPath($template) {
	$path = ROOT . '/template/' . $template . '/screenshot.png';
	if (file_exists($path)) {
		return get_url('/template/' . $template . '/screenshot.png');
	}
	return get_url('/sys/img/noimage.jpg');
}



// properties for system settings and settings that not linked to module
include_once ROOT . '/sys/settings/conf_properties.php';




// Get current module(group of settings)
if (empty($_GET['m']) || !is_string($_GET['m'])) $_GET['m'] = 'sys';
$module = trim($_GET['m']);
if (in_array($module, $sysMods)) {
	$settingsInfo = $settingsInfo[$module];
	
	switch($module) {
		case 'common':
			$pageTitle = __('RSS settings');
			break;
		case 'hlu':
			$pageTitle = __('HLU settings');
			break;
		case 'sitemap':
			$pageTitle = __('Sitemap settings');
			break;
		case 'secure':
			$pageTitle = __('Security settings');
			break;
		case 'watermark':
			$pageTitle = __('Watermark settings');
			break;
		case 'autotags':
			$pageTitle = __('Auto tags settings');
			break;
		case 'links':
			$pageTitle = __('Links settings');
			break;
	}


} else {
	$pathToModInfo = ROOT . '/modules/' . $module . '/info.php';
	if (file_exists($pathToModInfo)) {
		include ($pathToModInfo);
		$pageTitle = (isset($menuInfo['ankor']) ? $menuInfo['ankor'] . ' - Настройки' : $pageTitle);
	} else {
		$_SESSION['mess'] = "Модуль \"{$module}\" не найден!";
		$module = 'sys';
		$settingsInfo = $settingsInfo[$module];
	}
}





// Save settings

if (isset($_POST['send'])) {
	$tmpSet = (in_array($module, $noSub)) ? $config : $config[$module];
	
	
	foreach ($settingsInfo as $fname => $params) {
		if (!empty($params['attr']) && !empty($params['attr']['disabled'])) continue;
	
		unset($value);
		
		
		// Save nested elements
		if (!empty($params['fields'])) {
			if (false !== strpos($fname, 'sub_')) $fname = mb_substr($fname, mb_strlen('sub_'));
			if (!empty($_POST[$params['fields']][$fname])) {
				$tmpSet[$params['fields']][$fname] = $_POST[$params['fields']][$fname];
			} else {
				if (isset($tmpSet[$params['fields']]) && is_array($tmpSet[$params['fields']])) {
					if (array_key_exists($fname, $tmpSet[$params['fields']])) 
						unset($tmpSet[$params['fields']][$fname]);
				}
			}
			continue;
		} 
		

		if (isset($_POST[$fname]) || isset($_FILES[$fname])) {
			
			if (!empty($params['onsave']['func'])
			&& function_exists((string)$params['onsave']['func'])) {
				call_user_func((string)$params['onsave']['func'], $tmpSet);
				continue;
			}
			
			
			$value = trim((string)$_POST[$fname]);
		
		
		
			if (!empty($params['onsave'])) {
				if (!empty($params['onsave']['multiply'])) {
					$value = round($value * $params['onsave']['multiply']);
				}
			}
		}	
			
		if (empty($value)) $value = '';
		if (!empty($params['type']) && 'checkbox' === $params['type']) {
			$tmpSet[$fname] = (!empty($value)) ? 1 : 0;
		} else {
			$tmpSet[$fname] = $value;
		}
		

	}
	
	if (!in_array($module, $noSub)) {
		$_tmpSet = $config;
		$_tmpSet[$module] = $tmpSet;
		$tmpSet = $_tmpSet;
	}
	

	//save settings
	Config::write($tmpSet);
	//clean cache
	$Cache = new Cache;
	$Cache->clean(CACHE_MATCHING_ANY_TAG, array('module_' . $module));
	redirect('/admin/settings.php?m=' . $module);
}





// Build form for settings editor
$_config = (in_array($module, $noSub)) ? $config : $config[$module];
$output = '';
if (count($settingsInfo)) {
	foreach ($settingsInfo as $fname => $params) {
		if (is_string($params)) {
			//$output .= '<tr class="small"><td class="group" colspan="3">' . h($params) . '</td></tr>';
			continue;
		}
		
		
		$defParams = array(
			'type' => 'text',
			'title' => '',
			'description' => '',
			'value' => '',
			'help' => '',
			'options' => array(
			),
			'attr' => array(),
		);
		$params = array_merge($defParams, $params);
		
		$currValue = (!empty($_config[$fname])) ? $_config[$fname] : false;
		if (!empty($params['onview'])) {
			if (!empty($params['onview']['division'])) {
				$currValue = round($currValue / $params['onview']['division']);
			}
		}
	
	
		$attr = '';
		if (!empty($params['attr']) && count($params['attr'])) {
			foreach ($params['attr'] as $attrk => $attrv) {
				$attr .= ' ' . h($attrk) . '="' . h($attrv) . '"';
			}
		}
	
	
	
		switch ($params['type']) {
			case 'text':
				$output_ = '<input type="text" name="' . h($fname) . '" value="' . $currValue . '"' . $attr . ' />';
				break;
				
			case 'checkbox':
			
				$id = md5(rand(0, 99999) + rand(0, 99999));
			
				$state = (!empty($params['checked']) && 
				$currValue == $params['checked']) 
				? ' checked="checked" ' : '';
				
				
				if (!empty($params['fields'])) {
					if (false !== strpos($fname, 'sub_')) $fname = mb_substr($fname, mb_strlen('sub_'));
					$subParams = (!empty($_config[$params['fields']])) ? $_config[$params['fields']] : array();
					if (count($subParams) && in_array($fname, $subParams))
						$state = ' checked="checked"';
						
					$fname = $params['fields']. '[' . $fname . ']';
				}
				
				
				$output_ = '<input id="' . $id . '" type="checkbox" name="' . h($fname) 
				. '" value="' . $params['value'] . '" ' . $state . '' . $attr . ' /><label for="' . $id . '"></label>';
				break;
				
			case 'select':
				$options = '';
				if (count($params['options'])) {
					foreach ($params['options'] as $value => $visName) {
						$options_ = '';
						$state = ($_config[$fname] == $value) ? ' selected="selected"' : '';
						
						$attr = '';
						if (!empty($params['options_attr'])) {
							foreach ($params['options_attr'] as $k => $v) {
								$attr .= ' ' . $k . '="' . $v . '"';
							}
						}
						
						$options_ .= '<option ' . $state . $attr . ' value="' 
						. h($value) . '">' . h($visName) . '</option>';
						$options .= sprintf($options_, getImgPath($value));
					}
				}
				
				$output_ = '<select name="' . h($fname) . '">' . $options . '</select>';
				break;
				
			case 'file':
				$output_ = '<input type="file"  name="' . h($fname) . '"' . $attr . ' />';
				break;
		}
		
		

		$output .= '<div class="setting-item">
			<div class="left">
				' . h($params['title']) . '
				<span class="comment">' . h($params['description']) . '</span>
			</div>
			<div class="right">'
			. $output_;
		


		// If we have function by create sufix after input field
		if (!empty($params['input_sufix_func'])
		&& function_exists((string)$params['input_sufix_func'])) {
			$output .= call_user_func((string)$params['input_sufix_func']
			, $config);
		}
		if (!empty($params['input_sufix'])) {
			$output .= $params['input_sufix'];
		}
		

		// Help note
		if (!empty($params['help'])) $output .= '&nbsp;<span class="comment2">' . h($params['help']) . '</span>';
		$output .= '</div><div class="clear"></div></div>';
	}
}



$pageNav = $pageTitle;
$pageNavr = '';
include_once ROOT . '/admin/template/header.php';
?>

<form method="POST" action="settings.php?m=<?php echo $module; ?>" enctype="multipart/form-data">
<div class="list">
	<div class="title"><?php echo $pageNav; ?></div>
	<div class="level1">
		<div class="head">
			<div class="title settings">Ключ</div>
			<div class="title-r">Значение</div>
			<div class="clear"></div>
		</div>
		<div class="items">
			<?php echo $output; ?>
			<div class="setting-item">
				<div class="left">
				</div>
				<div class="right">
					<input class="save-button" type="submit" name="send" value="Сохранить" />
				</div>
				<div class="clear"></div>
			</div>
		</div>
	</div>
</div>
</form>



<?php /*echo '<form method="POST" action="settings.php?m=' . $module . '" enctype="multipart/form-data">*/ ?>



<?php include_once 'template/footer.php'; ?>
