<?php
##################################################
##												##
## Author:       Andrey Brykin (Drunya)         ##
## Version:      1.3                            ##
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




$ACL = $Register['ACL'];
$acl_groups = $ACL->get_group_info();
$acl_rules = $ACL->getRules();
$group = (isset($_GET['group'])) ? (int)$_GET['group'] : 1;



if (isset($_POST['send'])) {
	if (!empty($acl_rules)) {
		$acl_rules_ = $acl_rules;
		foreach ($acl_rules as $mod => $rules) {
			foreach ($rules as $rule => $roles) {
				
				
				foreach ($acl_groups as $id => $params)
				if (!empty($_POST[$mod][$rule . '_' . $id])) {
					if (!in_array($id, $acl_rules_[$mod][$rule])) {
						$acl_rules_[$mod][$rule][] = $id;
					}
				} else {
					if (($offkey = array_search($id, $acl_rules_[$mod][$rule])) !== false) {
						unset($acl_rules_[$mod][$rule][$offkey]);
					}
				}
			}
		}
		$ACL->save_rules($acl_rules_);
		redirect('/admin/users_rules.php');
	}
}




$pageNav = $pageTitle;
$pageNavr = '<a href="users_groups.php">Редактор групп</a>';



$dp = $Register['DocParser'];


include_once ROOT . '/admin/template/header.php';
?>
 
 



<form action="users_rules.php" method="POST">
<div class="list">
<div class="title">Права групп</div>
<table cellspacing="0" class="grid" style="min-width:100%">



<?php foreach ($acl_rules as $mod => $_rules): ?>
	<tr>
		<th class="group"><div class="title"><?php echo __($mod); ?></div></th>
		<?php foreach ($acl_groups as $id => $gr): ?>
		<th style="width:60px;">
			<?php echo h($gr['title']); ?>
		</th>
		<?php endforeach; ?>
	</tr>
	
	
	<?php foreach ($_rules as $title => $rules): ?>
	<tr>
	
	
		<td class="left"><?php echo __($title); ?></td>
		<?php foreach ($acl_groups as $id => $gr): ?>
		<?php  $ch_id = $mod . '_' . $id . '_' . $title; ?>
		<td class="right">
			<input name="<?php echo $mod.'['.$title.'_'.$id.']' ?>" type="checkbox" value="1" <?php if ($ACL->turn(array($mod, $title), false, $id)) echo 'checked="checked"' ?> id="<?php  echo $ch_id; ?>" /><label for="<?php  echo $ch_id; ?>"></label>
		</td>
		<?php endforeach; ?>
		
		
	</tr>
	<?php endforeach; ?>
<?php endforeach; ?>
	<tr>
		<td align="center" colspan="<?php echo count($acl_groups) + 2 ?>">
			<input class="save-button" name="send" type="submit" value="Сохранить"  />
		</td>
	</tr>
</table>
</div>
</form>


<?php include_once ROOT . '/admin/template/footer.php'; ?>