<?php
##################################################
##												##
## Author:       Andrey Brykin (Drunya)         ##
## Version:      0.7                            ##
## Project:      CMS                            ##
## package       CMS Fapos                      ##
## subpackege    Admin Panel module             ##
## copyright     ©Andrey Brykin 2010-2011       ##
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

 
$pageTitle = 'Пользователи';
$pageNav = $pageTitle;
$pageNavr = '<a href="javascript://" onClick="openPopup(\'Add_group\')">Добавить группу</a>&nbsp;|&nbsp;<a href="users_rules.php">Редактор прав</a>';




$dp = $Register['DocParser'];
$acl_groups = $Register['ACL']->get_group_info();

//create tmp array with groups and cnt users in them.
$errors = array();
$groups = array();
$popups = '';


if (!empty($acl_groups)) {
	$groups = $acl_groups;
	foreach ($acl_groups as $key => $value) {
		$groups[$key] = array();
		$groups[$key]['title'] = $value['title'];
		$groups[$key]['color'] = $value['color'];
		$groups[$key]['cnt_users'] = $FpsDB->select('users', DB_COUNT, array('cond' => array('status' => $key)));
	}
}


//move users into other group
if (!empty($_GET['ac']) && $_GET['ac'] == 'move') {
	if (isset($_POST['id']) && is_numeric($_POST['id']) && (int)$_POST['id'] !== 0) {
		$from = (int)$_POST['id'];
		if (!empty($_POST['to']) && is_numeric($_POST['to'])) {
			if (key_exists($_POST['to'], $acl_groups)) {
				$FpsDB->save('users', array('status' => $_POST['to']), array('status' => $from));
			}
		}
	}
	if(empty($errors)) redirect('/admin/users_groups.php');
	
//edit group
} else if (!empty($_GET['ac']) && $_GET['ac'] == 'edit') {
	if (isset($_POST['id']) && is_numeric($_POST['id'])) {
		$id = (int)$_POST['id'];
		if (!empty($_POST['title'])) {
			$allowed_colors = array('000000', 'EF1821', '368BEB', '959385', 'FBCA0B', '00AA2B', '9B703F', 'FAAA3C');
			if (!in_array($_POST['color'], $allowed_colors)) $errors[] = 'Не допустимый цвет';
			if (mb_strlen($_POST['title']) > 100 || mb_strlen($_POST['title']) < 2) {
				$errors[] = 'Поле "имя группы" должно быть в пределах 2-100 символов';
			} 

			if (!preg_match('#^[\w\d-_a-zа-я0-9 ]+$#ui', trim($_POST['title']))) {
				$errors[] = 'Поле "имя группы" содержит недопустимые символы';
			}
			if (empty($errors)) {
				if (key_exists($id, $acl_groups)) {
					$acl_groups[$id] = array('title' => h($_POST['title']), 'color' => h($_POST['color']));
					$ACL->save_groups($acl_groups);
				}
			}
		} else {
			$errors[] = 'Не заполненно поле "имя группы"';
		}
	}
	if(empty($errors)) redirect('/admin/users_groups.php');

//delete group
} else if (!empty($_GET['ac']) && $_GET['ac'] == 'delete') {
	if (isset($_GET['id']) && is_numeric($_GET['id']) && (int)$_GET['id'] !== 0 && (int)$_GET['id'] !== 1) {
		$id = (int)$_GET['id'];
		if ($groups[$_GET['id']]['cnt_users'] > 0) {
			$errors[] = 'Группа не пуста. Сперва перенесите пользователей';
		} else {
			unset($acl_groups[$_GET['id']]);
			$ACL->save_groups($acl_groups);
		}
	}
	if(empty($errors)) redirect('/admin/users_groups.php');

//add group	
} else if (!empty($_GET['ac']) && $_GET['ac'] == 'add') {
	if (!empty($_POST['title']) && !empty($_POST['color'])) {
		$allowed_colors = array('000000', 'EF1821', '368BEB', '959385', 'FBCA0B', '00AA2B', '9B703F', 'FAAA3C');
		if (!in_array($_POST['color'], $allowed_colors)) $errors[] = 'Не допустимый цвет';
		if (mb_strlen($_POST['title']) > 100 || mb_strlen($_POST['title']) < 2) {
			$errors[] = 'Поле "имя группы" должно быть в пределах 2-100 символов';
		}
		if (!preg_match('#^[\w\d-_a-zа-я0-9 ]+$#ui', $_POST['title'])) {
			$errors[] = 'Поле "имя группы" содержит недопустимые символы';
		}
		if (empty($errors)) {
			$acl_groups[] = array('title' => h($_POST['title']), 'color' => h($_POST['color']));
			$ACL->save_groups($acl_groups);
		}
	} else {
		$errors[] = 'Не заполненно поле "имя группы"';
	}
	if(empty($errors)) redirect('/admin/users_groups.php');



}


include_once ROOT . '/admin/template/header.php';

?>
 

<?php
if (!empty($errors)) {
	foreach ($errors as $error) {
?>
<span style="color:red;"><?php echo $error  ?></span><br />
<?php
	}
	unset($errors);
}
?>



	<div class="popup" id="Add_group">
		<div class="top">
			<div class="title">Добавление группы</div>
			<div class="close" onClick="closePopup('Add_group')"></div>
		</div>
		<div class="items">
			<form action="users_groups.php?ac=add" method="POST">
			<div class="item">
				<div class="left">
					Имя Группы:
				</div>
				<div class="right">
					<input type="text" name="title" />
				</div>
				<div class="clear"></div>
			</div>
			<div class="item">
				<div class="left">
					Цвет для группы:
				</div>
				<div class="right">
					<select name="color">
						<option style="color:#000000;" value="000000">Черный</option>
						<option style="color:#EF1821;" value="EF1821">Красный</option>
						<option style="color:#368BEB;" value="368BEB">Синий</option>
						<option style="color:#959385;" value="959385">Серый</option>
						<option style="color:#FBCA0B;" value="FBCA0B">Желтый</option>
						<option style="color:#00AA2B;" value="00AA2B">Зеленый</option>
						<option style="color:#9B703F;" value="9B703F">Коричневый</option>
						<option style="color:#FAAA3C;" value="FAAA3C">Оранж</option>
					</select>
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


	
	
	<?php if (!empty($groups)): ?>
		<?php foreach ($groups as $key => $value): ?>
			<?php if ($key !== 0): ?>
				<!-- FOR EDIT -->
				<div class="popup" id="<?php echo h($key) ?>_Edit">
					<div class="top">
						<div class="title">Редактроване группы</div>
						<div class="close" onClick="closePopup('<?php echo h($key) ?>_Edit')"></div>
					</div>
					<div class="items">
						<form action="users_groups.php?ac=edit" method="POST">
						<div class="item">
							<div class="left">
								Имя Группы:
							</div>
							<div class="right">
								<input type="hidden" name="id" value="<?php echo $key ?>" />
								<input type="text" name="title"  value="<?php echo $value['title'] ?>" />
							</div>
							<div class="clear"></div>
						</div>
						<div class="item">
							<div class="left">
								Цвет для группы:
							</div>
							<div class="right">
								<select name="color">
									<option style="color:#000000;" value="000000" <?php if($value['color'] == '000000') echo 'selected="selected"' ?>>Черный</option>
									<option style="color:#EF1821;" value="EF1821" <?php if($value['color'] == 'EF1821') echo 'selected="selected"' ?>>Красный</option>
									<option style="color:#368BEB;" value="368BEB" <?php if($value['color'] == '368BEB') echo 'selected="selected"' ?>>Синий</option>
									<option style="color:#959385;" value="959385" <?php if($value['color'] == '959385') echo 'selected="selected"' ?>>Серый</option>
									<option style="color:#FBCA0B;" value="FBCA0B" <?php if($value['color'] == 'FBCA0B') echo 'selected="selected"' ?>>Желтый</option>
									<option style="color:#00AA2B;" value="00AA2B" <?php if($value['color'] == '00AA2B') echo 'selected="selected"' ?>>Зеленый</option>
									<option style="color:#9B703F;" value="9B703F" <?php if($value['color'] == '9B703F') echo 'selected="selected"' ?>>Коричневый</option>
									<option style="color:#FAAA3C;" value="FAAA3C" <?php if($value['color'] == 'FAAA3C') echo 'selected="selected"' ?>>Оранж</option>
								</select>
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
				
				
				
				
				<!-- FOR MOVE -->
				<div class="popup" id="<?php echo h($key) ?>_Move">
					<div class="top">
						<div class="title">Перенос пользователей</div>
						<div class="close" onClick="closePopup('<?php echo h($key) ?>_Move')"></div>
					</div>
					<div class="items">
						<form action="users_groups.php?ac=move" method="POST">
						<div class="item">
							<div class="left">
								Куда перенести:
							</div>
							<div class="right">
								<input type="hidden" name="id" value="<?php echo $key ?>" />
								<?php
								$select = '<select name="to">';
								if (!empty($groups)) {
									foreach($groups as $sk => $sv) { 
										if ($sk != $key) {
											$select .= '<option value="' . $sk . '">' . h($sv['title']) . '</option>';
										}
									}
								}
								$select .= '</select>';
								?>
								<?php echo $select; ?>
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
	
			<?php endif; ?>
		<?php endforeach; ?>
	<?php endif; ?>
	
	


	<div class="list">
		<div class="title"></div>
		<table cellspacing="0" class="grid" style="min-width:100%">
		<tr>
			<th width="5%">ID</th>
			<th>Группа</th>
			<th width="10%">Пользователей</th>
			<th width="15%">Действия</th>
		</tr>

		
	<?php

	if (!empty($groups)) {
		foreach ($groups as $key => $value) {
			if ($key !== 0) {
	?>
		<tr>
			<td><?php echo h($key); ?></td>
			<td><?php echo h($value['title']); ?></td>
			<td><?php echo h($value['cnt_users']); ?></td>
			<td>
				<a title="Edit" href="javascript://" onClick="openPopup('<?php echo h($key) ?>_Edit')" class="edit"></a>
				<a title="Move" href="javascript://" onClick="openPopup('<?php echo h($key) ?>_Move')" class="move"></a>
				<?php if ($key !== 0 && $key !== 1): ?>
				<a title="Delete" href="users_groups.php?ac=delete&id=<?php echo h($key) ?>" onClick="return confirm('Are you sure?')" class="delete"></a>
				<?php endif; ?>
				
			</td>
		</tr>
		
		

		
	<?php	} else { ?>
			
		<tr>
			<td><?php echo h($key); ?></td>
			<td><?php echo h($value['title']); ?></td>
			<td> - </td>
			<td>
				-
			</td>
		</tr>
			
	<?php		
			}
		}
	} else {
	?>
		<tr>
			<td colspan="4">Нет групп</td>
		</tr>

	<?php
	}

	?>

		
		

	</table>
	</div>
	</form>


<?php 
include_once ROOT . '/admin/template/footer.php';
?>