<?php
/*-----------------------------------------------\
| 												 |
| @Author:       Andrey Brykin (Drunya)          |
| @Email:        drunyacoder@gmail.com           |
| @Site:         http://fapos.net                |
| @Version:      0.5                             |
| @Project:      CMS                             |
| @package       CMS Fapos                       |
| @subpackege    Additional Fields (Admin Part)  |
| @copyright     ©Andrey Brykin 2010-2013        |
\-----------------------------------------------*/

/*-----------------------------------------------\
| 												 |
|  any partial or not partial extension          |
|  CMS Fapos,without the consent of the          |
|  author, is illegal                            |
|------------------------------------------------|
|  Любое распространение                         |
|  CMS Fapos или ее частей,                      |
|  без согласия автора, является не законным     |
\-----------------------------------------------*/


include_once '../sys/boot.php';
include_once ROOT . '/admin/inc/adm_boot.php';




// Know module
$ModulesManager = new ModulesManager();
$allow_modules = $ModulesManager->getAddFieldsAllowedModules();
$modules_titles = $ModulesManager->getAddFieldsAllowedModulesTitles();




if (empty($_GET['m']) || !in_array($_GET['m'], $allow_modules)) {
	$_GET['m'] = 'news';
	$_GET['ac'] = 'index';
}
$pageTitle = $modules_titles[$_GET['m']] . ' - ' . __('Additional fields');

// Know action
if (!isset($_GET['ac'])) $_GET['ac'] = 'index';
$permis = array('add', 'del', 'index', 'edit');
if (!in_array($_GET['ac'], $permis)) $_GET['ac'] = 'index';

switch($_GET['ac']) {
	case 'del':
		$content = FpsDelete();
		break;
	case 'add':
		$content = FpsAdd();
		break;
	case 'edit':
		$content = FpsEdit();
		break;
	default:
		
}




if ($_GET['ac'] == 'index'):
	$fields = $FpsDB->select($_GET['m'] . '_add_fields', DB_ALL);
	$AddFields = new FpsAdditionalFields;
	if (count($fields) > 0)
		$inputs = $AddFields->getInputs($fields, false, $_GET['m']);




	$pageNav = $pageTitle;
	$pageNavr = '';
	//echo $head
    include_once ROOT . '/admin/template/header.php';
?>
	
	
	
	
	
	
	
	
	
	
	<div class="popup" id="addCat">
		<div class="top">
			<div class="title">Добавление поля</div>
			<div class="close" onClick="closePopup('addCat')"></div>
		</div>
		<div class="items">
			<form action="additional_fields.php?m=<?php echo $_GET['m'] ?>&ac=add" method="POST">
			<div class="item">
				<div class="left">
					<?php echo __('Type of field') ?>:
				</div>
				<div class="right">
					<select name="type">
						<option value="text">text</option>
						<option value="checkbox">checkbox</option>
						<option value="textarea">textarea</option>
					</select>
				</div>
				<div class="clear"></div>
			</div>
			<div class="item">
				<div class="left">
					<?php echo __('Visible name of field') ?>:
					<span class="comment"><?php echo __('Will be displayed in errors') ?></span>
				</div>
				<div class="right">
					<input type="text" name="label" value="" />
				</div>
				<div class="clear"></div>
			</div>
			<div class="item">
				<div class="left">
					<?php echo __('Max length') ?>:
					<span class="comment"><?php echo __('of saving data') ?></span>
				</div>
				<div class="right">
					<input type="text" name="size" value="" />
				</div>
				<div class="clear"></div>
			</div>
			<div class="item">
				<div class="left">
					<?php echo __('Params') ?>:
					<span class="comment"><?php echo __('Read more in the doc') ?></span>
				</div>
				<div class="right">
					<input type="text" name="params" value="" />
				</div>
				<div class="clear"></div>
			</div>
			<div class="item">
				<div class="left">
					<?php echo __('Required field') ?>:
				</div>
				<div class="right">
					<input type="checkbox" name="required" value="1" id="required" /><label for="required"></label>
				</div>
				<div class="clear"></div>
			</div>
			<div class="item submit">
				<div class="left"></div>
				<div class="right" style="float:left;">
					<input type="submit" value="Сохранить" name="send" class="save-button" />
				</div>
				<div class="clear"></div>
			</div>
			</form>
		</div>
	</div>
	

	
	
	
<?php if (!empty($fields)): ?>
<?php foreach($fields as $field): ?>
	<?php
		$params = (!empty($field['params'])) ? unserialize($field['params']) : array();
		$values = (!empty($params['values'])) ? $params['values'] : '-';
		$field_marker = 'add_field_' . $field['id'];
		
		$required = (!empty($params['required'])) 
		? '<span style="color:red;">' . __('Yes') . '</span>' 
		: '<span style="color:blue;">' . __('No') . '</span>';
	?>
	
	<div class="popup" id="edit_<?php echo $field['id'] ?>">
		<div class="top">
			<div class="title">Добавление поля</div>
			<div class="close" onClick="closePopup('edit_<?php echo $field['id'] ?>')"></div>
		</div>
		<div class="items">
			<form action="additional_fields.php?m=<?php echo $_GET['m'] ?>&ac=edit&id=<?php echo $field['id'] ?>" method="POST">
			<div class="item">
				<div class="left">
					<?php echo __('Type of field') ?>:
				</div>
				<div class="right">
					<select name="type">
						<option value="text"<?php if($field['type'] == 'text') echo ' selected="selected"' ?>>test</option>
						<option value="checkbox"<?php if($field['type'] == 'checkbox') echo ' selected="selected"' ?>>checkbox</option>
						<option value="textarea"<?php if($field['type'] == 'textarea') echo ' selected="selected"' ?>>textarea</option>
					</select>
				</div>
				<div class="clear"></div>
			</div>
			<div class="item">
				<div class="left">
					<?php echo __('Visible name of field') ?>:
					<span class="comment"><?php echo __('Will be displayed in errors') ?></span>
				</div>
				<div class="right">
					<input type="text" name="label" value="<?php echo h($field['label']) ?>" />
				</div>
				<div class="clear"></div>
			</div>
			<div class="item">
				<div class="left">
					<?php echo __('Max length') ?>:
					<span class="comment"><?php echo __('of saving data') ?></span>
				</div>
				<div class="right">
					<input type="text" name="size" value="<?php echo (!empty($field['size'])) ? h($field['size']) : ''; ?>" />
				</div>
				<div class="clear"></div>
			</div>
			<div class="item">
				<div class="left">
					<?php echo __('Params') ?>:
					<span class="comment"><?php echo __('Read more in the doc') ?></span>
				</div>
				<div class="right">
					<input type="text" name="params" value="<?php echo ($values != '-') ? h($values) : ''; ?>" />
				</div>
				<div class="clear"></div>
			</div>
			<div class="item">
				<div class="left">
					<?php echo __('Required field') ?>:
				</div>
				<div class="right">
					<input id="required<?php $field['id'] ?>" type="checkbox" name="required" value="1"<?php if(!empty($params['required'])) echo ' checked="checked"' ?>/><label for="required<?php $field['id'] ?>"></label>
				</div>
				<div class="clear"></div>
			</div>
			<div class="item submit">
				<div class="left"></div>
				<div class="right" style="float:left;">
					<input type="submit" value="Сохранить" name="send" class="save-button" />
				</div>
				<div class="clear"></div>
			</div>
			</form>
		</div>
	</div>
	
	

	
	
<?php endforeach; ?>
<?php endif; ?>	
	
	
	
	


	
<div class="list">
	<div class="title">Дополнительные поля</div>
	<div onclick="openPopup('addCat');" class="add-cat-butt"><div class="add"></div><?php echo __('Add') ?></div>
	<table class="grid" cellspacing="0" style="width:100%;">
		<tr>
			<th><?php echo __('Type of field') ?></th>
			<th><?php echo __('Visible name of field') ?></th>
			<th><?php echo __('Max length') ?></th>
			<th><?php echo __('Params') ?></th>
			<th><?php echo __('Required field') ?></th>
			<th><?php echo __('Marker of field') ?></th>
			<th style="width:160px;">Действия</th>
		</tr>
	
	<?php if (!empty($fields)): ?>
	<?php foreach($fields as $field): ?>
		<?php
			$params = (!empty($field['params'])) ? unserialize($field['params']) : array();
			$values = (!empty($params['values'])) ? $params['values'] : '-';
			$field_marker = 'add_field_' . $field['id'];
			
			$required = (!empty($params['required'])) 
			? '<span style="color:red;">' . __('Yes') . '</span>' 
			: '<span style="color:blue;">' . __('No') . '</span>';
		?>
		


				<tr>
					<td><?php echo h($field['type']); ?></td>
					<td><?php echo h($field['label']); ?></td>
					<td><?php echo (!empty($field['size'])) ? h($field['size']) : '-'; ?></td>
					<td><?php echo (!empty($values)) ? h($values) : ''; ?></td>
					<td><?php echo $required; ?></td>
					<td><?php echo h(strtolower($field_marker)); ?></td>
					<td>
						<a class="edit" title="Edit" href="javascript://" onClick="openPopup('edit_<?php echo $field['id'] ?>')"></a>
						<a class="delete" title="Delete" href="additional_fields.php?m=<?php echo $_GET['m'] ?>&ac=del&id=<?php echo $field['id'] ?>" onClick="return confirm('Are you sure?');"></a>
					</td>
				</tr>

	<?php endforeach; ?>
	<?php else: ?>
	<div class="fps-win"><div class="h3"><?php echo __('Additional fields not found') ?></div></div>
	<?php endif; ?>
	</table>
</div>
			
	
	
	
	
	
	<?php if (!empty($_SESSION['FpsForm']['errors'])): ?>
		<script type="text/javascript">showHelpWin('<?php echo '<ul class="error">' . $_SESSION['FpsForm']['errors'] . '</ul>'; ?>', '<?php echo __('Errors') ?>');</script>
		<?php unset($_SESSION['FpsForm']); ?>
	<?php endif; ?>
<?php endif; ?>




<?php

function FpsEdit() {
	global $FpsDB;
	
	
	if (empty($_GET['id'])) redirect('/admin/additional_fields.php?m=' . $_GET['m']);
	$id = intval($_GET['id']);
	if ($id < 1) redirect('/admin/additional_fields.php?m=' . $_GET['m']);
	
	
	if (isset($_POST['send'])) {
		$error = null;
		$allow_types = array('text', 'checkbox', 'textarea');
		
		
		//type of field
		$type = (!empty($_POST['type']) && in_array(trim($_POST['type']), $allow_types))
		? trim($_POST['type']) : 'text';
		if (empty($_POST['label'])) $error .= '<li>' . __('Empty field "visible name"') . '</li>';
		if (empty($_POST['size']) && $type != 'checkbox') $error .= '<li>' . __('Empty field "max length"') . '</li>';
		if (!empty($_POST['size']) && !is_numeric($_POST['size'])) $error .= '<li>' . __('Wrong chars in "max length"') . '</li>';
		
		
		//params
		$params = array();
		$params['values'] = (!empty($_POST['params'])) ? trim($_POST['params']) : __('Yes') . '|' . __('No');
		if (!empty($_POST['required'])) $params['required'] = 1;
		if ($type != 'checkbox') unset($params['values']);
		$params = serialize($params);
		
		
		//label
		$label = (!empty($_POST['label'])) ? trim($_POST['label']) : 'Add. field';
		
		//size
		$size = (!empty($_POST['size'])) ? intval($_POST['size']) : 70;
		
		if (!empty($error)) {
			$_SESSION['FpsForm'] = array('errors' => $error);
			redirect('/admin/additional_fields.php?m=' . $_GET['m']);
		}
		$data = array(
			'type' => $type,
			'label' => $label,
			'size' => $size,
			'params' => $params,
			'id' => $id,
		);
		$FpsDB->save($_GET['m'] . '_add_fields', $data);
		
		//clean cache
		$Cache = new Cache;
		$Cache->clean(CACHE_MATCHING_ANY_TAG, array('module_' . $_GET['m']));
		redirect('/admin/additional_fields.php?m=' . $_GET['m']);
	}
}



function FpsAdd() {
	global $FpsDB;
	
	
	if (isset($_POST['send'])) {
		$error = null;
		$allow_types = array('text', 'checkbox', 'textarea');
		
		
		//type of field
		$type = (!empty($_POST['type']) && in_array(trim($_POST['type']), $allow_types))
		? trim($_POST['type']) : 'text';
		if (empty($_POST['label'])) $error .= '<li>' . __('Empty field "visible name"') . '</li>';
		if (empty($_POST['size']) && $type != 'checkbox') $error .= '<li>' . __('Empty field "max length"') . '</li>';
		if (!empty($_POST['size']) && !is_numeric($_POST['size'])) $error .= '<li>' . __('Wrong chars in "max length"') . '</li>';
		
		
		//params
		$params = array();
		$params['values'] = (!empty($_POST['params'])) ? trim($_POST['params']) : __('Yes') . '|' . __('No');
		if (!empty($_POST['required'])) $params['required'] = 1;
		if ($type != 'checkbox') unset($params['values']);
		$params = serialize($params);
		
		
		//label
		$label = (!empty($_POST['label'])) ? trim($_POST['label']) : 'Add. field';
		
		//size
		$size = (!empty($_POST['size'])) ? intval($_POST['size']) : 70;
		
		
		if (!empty($error)) {
			$_SESSION['FpsForm'] = array('errors' => $error);
			redirect('/admin/additional_fields.php?m=' . $_GET['m']);
		}
		
		
		$data = array(
			'type' => $type,
			'label' => $label,
			'size' => $size,
			'params' => $params,
		);
		$FpsDB->save($_GET['m'] . '_add_fields', $data);
		
		//clean cache
		$Cache = new Cache;
		$Cache->clean(CACHE_MATCHING_ANY_TAG, array('module_' . $_GET['m']));
		redirect('/admin/additional_fields.php?m=' . $_GET['m']);
	}
}




function FpsDelete() {
	global $FpsDB;
	
	
	if (empty($_GET['id'])) redirect('/admin/additional_fields.php?m=' . $_GET['m']);
	$id = intval($_GET['id']);
	if ($id < 1) redirect('/admin/additional_fields.php?m=' . $_GET['m']);
	
	
	$FpsDB->query("DELETE FROM `" . $FpsDB->getFullTableName($_GET['m'] . '_add_fields') 
	. "` WHERE `id` = '" . $id . "' LIMIT 1");
	redirect('/admin/additional_fields.php?m=' . $_GET['m']);
}



include_once 'template/footer.php';
?>