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


function prepareConfToSave ($conf) {
	$result = array();
	
	foreach ($conf as $mod => $rules) {
		foreach ($rules as $rule => $params) {
			$result[$mod . '.' . $rule] = $params;
		}
	}

	return $result;
}


function saveRules($rules) {
	$Register = Register::getInstance();
	$Register['ACL']->save_rules(prepareConfToSave($rules));
	
	$_SESSION['message'] = __('Saved');
	redirect('/admin/users_rules.php');
}


$pageTitle = __('Users');


$ACL = $Register['ACL'];
$acl_groups = $ACL->get_group_info();
$acl_rules = $ACL->getRules();
$group = (isset($_GET['group'])) ? (int)$_GET['group'] : 1;


// convert to nice format(simple format to view)
$bufer = array();
$special_rules_bufer = array();
foreach ($acl_rules as $key => $_rules) {
	// for group rules
	$access_params = explode('.', $key);
	if (count($access_params) == 2) {
		if (empty($bufer[$access_params[0]])) $bufer[$access_params[0]] = array();
		$bufer[$access_params[0]][$access_params[1]] = $_rules;
	
	} else if (count($access_params) == 3) {
		if (empty($bufer[$access_params[0] . '.' . $access_params[1]])) 
			$bufer[$access_params[0] . '.' . $access_params[1]] = array();
		$bufer[$access_params[0] . '.' . $access_params[1]][$access_params[2]] = $_rules;
	}
	

	// for special rules
	if (!empty($_rules['users'])) {
		foreach ($_rules['users'] as $row) {
			
			if (empty($special_rules_bufer['user_'.$row])) 
				$special_rules_bufer['user_'.$row] = array();
			
			switch (count($access_params)) {
				case 2:
					if (empty($special_rules_bufer['user_'.$row][$access_params[0]]))
						$special_rules_bufer['user_'.$row][$access_params[0]] = array();
					$special_rules_bufer['user_'.$row][$access_params[0]][] = $access_params[1];
					break;
				
				// forums
				case 3:
					if (empty($special_rules_bufer['user_'.$row][$access_params[0].'_'.$access_params[1]]))
						$special_rules_bufer['user_'.$row][$access_params[0].'_'.$access_params[1]] = array();
					$special_rules_bufer['user_'.$row][$access_params[0].'_'.$access_params[1]][] = $access_params[2];
					break;
			}
		}
	}
}


$acl_rules = $bufer;
$specialRules = $special_rules_bufer;


// get special rules and additional information
// get all forums for have posibility set rules for each
$forumModel = $Register['ModManager']->getModelInstance('forum');
$allForums = $forumModel->getCollection();

// geting users names & forums titles for view below
$usersNames = array();
$forumsTitles = array();
if (!empty($specialRules)) {

	// get users & forum IDs
	$uIds = array();
	$fIds = array();
	foreach ($specialRules as $k => $uRules) {
		$uIds[] = str_replace('user_', '', $k);
		
		if (!empty($uRules)) {
			foreach ($uRules as $rk => $rv) {
				if (false !== strpos($rk, 'forum_')) {
					$fIds[] = str_replace('forum_', '', $rk);
				}
			}
 		}
	}
	
	// get users names
	$usersModel = $Register['ModManager']->getModelInstance('users');
	$uNames = $usersModel->getCollection(
		array('id IN (' . implode(',', $uIds) . ')'), 
		array('fileds' => 'name')
	);
	
	if ($uNames) {
		foreach ($uNames as $user) {
			$usersNames[$user->getId()] = $user->getName();
		}
	}
	
	
	foreach ($specialRules as $k => $v) {
		if (!array_key_exists(str_replace('user_', '', $k), $usersNames))
			unset($specialRules[$k]);
	}
	
	
	// get forum titles
	if (!empty($fIds)) {
		$forums = $forumModel->getCollection(array('id IN (' . implode(',', $fIds) . ')'));
		
		if ($forums) {
			foreach ($forums as $forum) {
				$forumsTitles[$forum->getId()] = $forum->getTitle();
			}
		}
	}
}




// save special rules
if (isset($_POST['send']) && !empty($_GET['ac']) && $_GET['ac'] == 'special') {
	if (!empty($acl_rules)) {
		$acl_rules_ = $acl_rules;
		
		$user_id = intval($_GET['uid']);
		if (!$user_id) throw new Exception('Incorrect user ID');
		
		
		
		foreach ($acl_rules as $mod => $rules) {
			foreach ($rules as $rule => $roles) {
				
				// for POST[forum_1] . . . $_POST[forum_n]
				$post_mod = str_replace('.', '_', $mod);
				
				if (!empty($_POST[$post_mod]) && !empty($_POST[$post_mod][$rule])) {
	
					if (!in_array($user_id, $acl_rules_[$mod][$rule]['users'])) {
						$acl_rules_[$mod][$rule]['users'][] = $user_id;
					}
					

				} else {
					// check old permissions whitch has been enabled but now must be disabled
					foreach ($roles['users'] as $key => $uid) {
						
						if ($uid != $user_id) continue;
						
						if (empty($_POST[$post_mod][$rule])) {
							unset($acl_rules_[$mod][$rule]['users'][$key]);
						}
					}
				}
			}
		}

		// process forum_1, forum_2, ... forum_n
		foreach ($_POST as $mod => $rules) {
		
			if (false === strpos($mod, 'forum_')) continue;
			$mod = str_replace('_', '.', $mod);
			
			foreach ($rules as $title => $value) {
				if (empty($acl_rules_[$mod])) $acl_rules_[$mod] = array();
				if (empty($acl_rules_[$mod][$title])) 
					$acl_rules_[$mod][$title] = array('users' => array());
					

				if (!in_array($user_id, $acl_rules_[$mod][$title]['users'])) {
					$acl_rules_[$mod][$title]['users'][] = $user_id;
				}
			}
		}
		saveRules($acl_rules_);
	}


// save group rules
} else if (isset($_POST['send'])) {
	if (!empty($acl_rules)) {
		$acl_rules_ = $acl_rules;
		foreach ($acl_rules as $mod => $rules) {
			foreach ($rules as $rule => $roles) {
				
				
				foreach ($acl_groups as $id => $params)
				if (!empty($_POST[$mod][$rule . '_' . $id])) {
					if (!in_array($id, $acl_rules_[$mod][$rule]['groups'])) {
						$acl_rules_[$mod][$rule]['groups'][] = $id;
					}
				} else {
					if (array_key_exists('groups', $acl_rules_[$mod][$rule]) && 
					($offkey = array_search($id, $acl_rules_[$mod][$rule]['groups'])) !== false) {
						unset($acl_rules_[$mod][$rule]['groups'][$offkey]);
					}
				}
			}
		}
		saveRules($acl_rules_);
	}
}


// Next we needn't this

foreach ($acl_rules as $k => $rule) {
	if (false !== strpos($k, 'forum.')) unset($acl_rules[$k]);
}


$pageNav = $pageTitle;
$pageNavr = '<a href="users_groups.php">' . __('Users groups') . '</a>';



$dp = $Register['DocParser'];


include_once ROOT . '/admin/template/header.php';
?>



<?php
if(!empty($_SESSION['message'])) {
	echo '<div class="warning">'.$_SESSION['message'].'</div>';
	unset($_SESSION['message']);
}
?>



<!-- Find users for add new special rules -->
<div id="sp_rules_find_users" class="popup">
	<div class="top">
		<div class="title"><?php echo __('Find users') ?></div>
		<div onClick="closePopup('sp_rules_find_users');" class="close"></div>
	</div>
	<div class="items">
		<div class="item">
			<div class="left">
				<?php echo __('Name') ?>
				<span class="comment"><?php echo __('Begin to write that see similar users') ?></span>
			</div>
			<div class="right">
				<input id="autocomplete_inp" type="text" name="user_name" placeholder="User Name" />
			</div>
			<div class="clear"></div>
		</div>
		<div id="add_users_list"></div>
			
			

	</div>
</div>
<script>
$('#autocomplete_inp').keypress(function(e){
	var inp = $(this);
	if (inp.val().length < 2) return;
	setTimeout(function(){
		AtomX.findUsers('/admin/find_users.php?name='+inp.val(), 'add_users_list', false);
	}, 500);

});
</script>


<?php if (!empty($_GET['new_sp'])): $k = intval($_GET['new_sp']); ?>
<!-- Add new special rules -->
<div id="sp_rules_add" class="popup" style="display:block;">
		<div class="top">
			<div class="title"><?php echo __('Groups rules') ?></div>
			<div onClick="closePopup('sp_rules_add');" class="close"></div>
		</div>
		<form action="users_rules.php?ac=special&uid=<?php echo $k ?>" method="POST">
		<div class="items">
		
		
				<div class="item">
					<div class="left">
						<select onChange="AtomX.hideAll('<?php echo $k ?>_hiden_block'); AtomX.toggle(this.value);">
						<?php foreach ($acl_rules as $mod => $_rules): ?>
							<?php $hash = $k . '_' . $mod ?>
									<option value="<?php echo $hash ?>"><?php echo __($mod); ?></option>
						<?php endforeach; ?>
						</select>
					</div>
					<div class="right">
					</div>
					<div class="clear"></div>
				</div>
				
				
			<?php $display = 'block'; ?>
			<?php foreach ($acl_rules as $mod => $_rules): ?>	
				<?php $hash = $k . '_' . $mod ?>
				<div class="<?php echo $k ?>_hiden_block" style="display:<?php echo $display ?>;" id="<?php echo $hash ?>">
				<?php $display = 'none'; ?>
				
				
				<?php if ($mod === 'forum'): ?>
					<select onChange="AtomX.hideAll('<?php echo $k ?>_forum_block'); AtomX.toggle(this.value);">
					<option value="all_forums">Все форумы</option>
					<?php foreach ($allForums as $forum): ?>
						<?php $hash = $k . '_forum_' . $forum->getId() ?>
								<option value="<?php echo $hash ?>"><?php echo h($forum->getTitle()); ?></option>
					<?php endforeach; ?>
					</select>
					
					

					<div class="<?php echo $k ?>_forum_block" style="display:block;" id="all_forums">
						<?php foreach ($_rules as $title => $rules): ?>
							<div class="item narrow">
								<div class="left">
									<?php echo __($title); ?>
								</div>
								<div class="right" style="width:auto;">
								
									<?php  $ch_id = $mod . '_' . $k . '_' . $title; ?>
									<input name="<?php echo $mod . '['.$title.']' ?>" type="checkbox" value="1" 
									<?php if ($ACL->turn(
										array($mod, $title), 
										false, 
										false, 
										str_replace('user_', '', $k), 
										true
									)) echo 'checked="checked"' ?> id="<?php  echo $ch_id; ?>" /><label for="<?php  echo $ch_id; ?>"></label>
									
								</div>
								<div class="clear"></div>
							</div>
						<?php endforeach; ?>
					</div>
					
					
					<?php foreach ($allForums as $forum): ?>
						<?php $hash = $k . '_forum_' . $forum->getId() ?>
						<div class="<?php echo $k ?>_forum_block" style="display:none;" id="<?php echo $hash ?>">
							<?php foreach ($_rules as $title => $rules): ?>
								<div class="item narrow">
									<div class="left">
										<?php echo __($title); ?>
									</div>
									<div class="right" style="width:auto;">
									
										<?php  $ch_id = $mod . '_' . $forum->getId() . '_' . $k . '_' . $title; ?>
										<input name="<?php echo $mod. '_' . $forum->getId() . '['.$title.']' ?>" type="checkbox" value="1" <?php if ($ACL->turn(
											array($mod, $title, $forum->getId()), 
											false, 
											false, 
											str_replace('user_', '', $k), 
											true
										)) echo 'checked="checked"' ?> id="<?php  echo $ch_id; ?>" /><label for="<?php  echo $ch_id; ?>"></label>
										
									</div>
									<div class="clear"></div>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endforeach; ?>
					
					
				<?php else: ?>
				
					
					<?php foreach ($_rules as $title => $rules): ?>
						<div class="item narrow">
							<div class="left">
								<?php echo __($title); ?>
							</div>
							<div class="right" style="width:auto;">
							
								<?php  $ch_id = $mod . '_' . $k . '_' . $title; ?>
								<input name="<?php echo $mod.'['.$title.']' ?>" type="checkbox" value="1" 
								<?php if ($ACL->turn(
									array($mod, $title), 
									false, 
									false, 
									str_replace('user_', '', $k),
									true
								)) echo 'checked="checked"' ?> id="<?php  echo $ch_id; ?>" /><label for="<?php  echo $ch_id; ?>"></label>
							</div>
							<div class="clear"></div>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
				
				</div>
			<?php endforeach; ?>
			<div class="item submit">
				<div class="left"></div>
				<div class="right" style="float:left;">
					<input type="submit" value="<?php echo __('Save') ?>" name="send" class="save-button" />
				</div>
				<div class="clear"></div>
			</div>
		</div>
		</form>
	</div>
<?php endif; ?>






 
<!-- Special users rules -->
<script>
$('#users_selector').live('change', function(){
	$('#special_rules_container > table').hide();
	var value = $('#users_selector').val();
	
	if (value == 'add') {
		openPopup('sp_rules_find_users');
		return false;
	}
	
	$('#urules_' + value).show();
	
});
</script>
 


<?php if(!empty($specialRules)): ?>
<div id="special_rules_container"class="list">
<div class="title"><?php echo __('Individual user rights') ?></div>
<div class="add-cat-butt">
	<select id="users_selector" style="width:150px;">
		<?php foreach($specialRules as $k => $userRules): ?>
			<option value="<?php echo md5($k) ?>"><?php echo h($usersNames[str_replace('user_', '', $k)]) ?></option>
		<?php endforeach; ?>
		<option value="add"><?php echo __('Add user') ?></option>
	</select>
</div>

<?php $display = 'table' ?>
<?php foreach($specialRules as $k => $userRules): ?>


	<!-- View special rules -->
	<table id="urules_<?php echo md5($k) ?>"  cellspacing="0" class="grid" style="min-width:100%; display:<?php echo $display ?>;">
		<?php $display = 'none' ?>
		<?php foreach ($userRules as $mod => $_rules): ?>
		
			<!-- General special rules -->
			<?php if (!strpos($mod, 'forum_')): ?>
				
					<tr>
						<th class="group"><div class="title"><?php echo __($mod); ?></div></th>
					</tr>
					<?php foreach ($_rules as $title): ?>
						<tr>
							<td class="left"><?php echo __($title); ?></td>
						</tr>
					<?php endforeach; ?>
			
			
			
			<!-- Forums special rules -->
			<?php else: ?>
				<tr>
					<th class="group"><div class="title"><?php echo h($forumsTitles[str_replace('forum_', '', $mod)]); ?></div></th>
				</tr>
				<?php foreach ($_rules as $title): ?>
					<tr>
						<td class="left"><?php echo __($title); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		<?php endforeach; ?>
		<tr>
			<td align="center" colspan="<?php echo count($acl_groups) + 2 ?>">
				<input class="save-button" name="send" type="submit" value="Изменить" onClick="openPopup('sp_rules_<?php echo $k ?>');" />
			</td>
		</tr>
	</table>
<?php endforeach; ?>
</div>
<?php endif; ?>


<?php foreach($specialRules as $k => $userRules): ?>
	<!-- Edit special rules -->
	<div id="sp_rules_<?php echo $k ?>" class="popup">
		<div class="top">
			<div class="title"><?php echo __('Edit rights') ?></div>
			<div onClick="closePopup('sp_rules_<?php echo $k ?>');" class="close"></div>
		</div>
		<form action="users_rules.php?ac=special&uid=<?php echo str_replace('user_', '', $k) ?>" method="POST">
		<div class="items">
		
		
				<div class="item">
					<div class="left">
						<select onChange="AtomX.hideAll('<?php echo $k ?>_hiden_block'); AtomX.toggle(this.value);">
						<?php foreach ($acl_rules as $mod => $_rules): ?>
							<?php $hash = $k . '_' . $mod ?>
									<option value="<?php echo $hash ?>"><?php echo __($mod); ?></option>
						<?php endforeach; ?>
						</select>
					</div>
					<div class="right">
					</div>
					<div class="clear"></div>
				</div>
				
				
			<?php $display = 'block'; ?>
			<?php foreach ($acl_rules as $mod => $_rules): ?>	
				<?php $hash = $k . '_' . $mod ?>
				<div class="<?php echo $k ?>_hiden_block" style="display:<?php echo $display ?>;" id="<?php echo $hash ?>">
				<?php $display = 'none'; ?>
				
				
				<?php if ($mod === 'forum'): ?>
					<select onChange="AtomX.hideAll('<?php echo $k ?>_forum_block'); AtomX.toggle(this.value);">
					<option value="all_forums">Все форумы</option>
					<?php foreach ($allForums as $forum): ?>
						<?php $hash = $k . '_forum_' . $forum->getId() ?>
								<option value="<?php echo $hash ?>"><?php echo h($forum->getTitle()); ?></option>
					<?php endforeach; ?>
					</select>
					
					

					<div class="<?php echo $k ?>_forum_block" style="display:block;" id="all_forums">
						<?php foreach ($_rules as $title => $rules): ?>
							<div class="item narrow">
								<div class="left">
									<?php echo __($title); ?>
								</div>
								<div class="right" style="width:auto;">
								
									<?php  $ch_id = $mod . '_' . $k . '_' . $title; ?>
									<input name="<?php echo $mod . '['.$title.']' ?>" type="checkbox" value="1" 
									<?php if ($ACL->turn(
										array($mod, $title), 
										false, 
										false, 
										str_replace('user_', '', $k), 
										true
									)) echo 'checked="checked"' ?> id="<?php  echo $ch_id; ?>" /><label for="<?php  echo $ch_id; ?>"></label>
									
								</div>
								<div class="clear"></div>
							</div>
						<?php endforeach; ?>
					</div>
					
					
					<?php foreach ($allForums as $forum): ?>
						<?php $hash = $k . '_forum_' . $forum->getId() ?>
						<div class="<?php echo $k ?>_forum_block" style="display:none;" id="<?php echo $hash ?>">
							<?php foreach ($_rules as $title => $rules): ?>
								<div class="item narrow">
									<div class="left">
										<?php echo __($title); ?>
									</div>
									<div class="right" style="width:auto;">
									
										<?php  $ch_id = $mod . '_' . $forum->getId() . '_' . $k . '_' . $title; ?>
										<input name="<?php echo $mod. '_' . $forum->getId() . '['.$title.']' ?>" type="checkbox" value="1" <?php if ($ACL->turn(
											array($mod, $title, $forum->getId()), 
											false, 
											false, 
											str_replace('user_', '', $k), 
											true
										)) echo 'checked="checked"' ?> id="<?php  echo $ch_id; ?>" /><label for="<?php  echo $ch_id; ?>"></label>
										
									</div>
									<div class="clear"></div>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endforeach; ?>
					
					
				<?php else: ?>
				
					
					<?php foreach ($_rules as $title => $rules): ?>
						<div class="item narrow">
							<div class="left">
								<?php echo __($title); ?>
							</div>
							<div class="right" style="width:auto;">
							
								<?php  $ch_id = $mod . '_' . $k . '_' . $title; ?>
								<input name="<?php echo $mod.'['.$title.']' ?>" type="checkbox" value="1" 
								<?php if ($ACL->turn(
									array($mod, $title), 
									false, 
									false, 
									str_replace('user_', '', $k),
									true
								)) echo 'checked="checked"' ?> id="<?php  echo $ch_id; ?>" /><label for="<?php  echo $ch_id; ?>"></label>
							</div>
							<div class="clear"></div>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
				
				</div>
			<?php endforeach; ?>
			<div class="item submit">
				<div class="left"></div>
				<div class="right" style="float:left;">
					<input type="submit" value="<?php echo __('Save') ?>" name="send" class="save-button" />
				</div>
				<div class="clear"></div>
			</div>
		</div>
		</form>
	</div>
<?php endforeach; ?>






<!-- Groups rules -->
<form action="users_rules.php" method="POST">
<div class="list">
<div class="title"><?php echo __('Groups rules') ?></div>
<table cellspacing="0" class="grid" style="min-width:100%">


	<?php $display = '' ?>
	<?php foreach ($acl_rules as $mod => $_rules): ?>
		<?php if (strpos('forum.', $mod)) continue; ?>
		<tr>
			<th class="group"><div class="title" onClick="AtomX.toggleByClass('<?php echo $mod ?>_rules_block');"><?php echo __($mod); ?></div></th>
			<?php foreach ($acl_groups as $id => $gr): ?>
			<th style="width:60px;">
				<?php echo h($gr['title']); ?>
			</th>
			<?php endforeach; ?>
		</tr>
		
		
		<?php foreach ($_rules as $title => $rules): ?>
		<tr class="<?php echo $mod ?>_rules_block" style="display:<?php echo $display ?>;">
			
		
			<td class="left"><?php echo __($title); ?></td>
			<?php foreach ($acl_groups as $id => $gr): ?>
			<?php  $ch_id = $mod . '_' . $id . '_' . $title; ?>
			<td class="right">
				<input name="<?php echo $mod.'['.$title.'_'.$id.']' ?>" type="checkbox" value="1" <?php if ($ACL->turn(array($mod, $title), false, $id)) echo 'checked="checked"' ?> id="<?php  echo $ch_id; ?>" /><label for="<?php  echo $ch_id; ?>"></label>
			</td>
			<?php endforeach; ?>
			
			
		</tr>
		<?php endforeach; ?>
		<?php $display = 'none' ?>
	<?php endforeach; ?>
	<tr>
		<td align="center" colspan="<?php echo count($acl_groups) + 2 ?>">
			<input class="save-button" name="send" type="submit" value="<?php echo __('Save') ?>"  />
		</td>
	</tr>
</table>
</div>
</form>


<?php include_once ROOT . '/admin/template/footer.php'; ?>