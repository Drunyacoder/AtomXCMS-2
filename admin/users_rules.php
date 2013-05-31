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
<div class="title"></div>
<table cellspacing="0" class="grid" style="min-width:100%">
<tr>
	<td>Действие</td>
	<?php foreach ($acl_groups as $id => $gr): ?>
	<td style="width:60px;">
		<?php echo h($gr['title']); ?>
	</td>
	<?php endforeach; ?>
</tr>


<?php foreach ($acl_rules as $mod => $_rules): ?>
	<tr><td colspan="<?php echo count($acl_groups) + 2 ?>" class="group"> - <?php echo getAddModTitle($mod); ?></td></tr>
	<?php foreach ($_rules as $title => $rules): ?>
	<tr>
	
	
		<td class="left"><?php echo getAddTitle($title); ?></td>
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
<?php
function getAddModTitle($title) {
	$add_titles = array(
		'users' => 'Пользователи',						
		'loads' => 'Файлы',						
		'stat' => 'Статьи',						
		'foto' => 'Каталог фото',						
		'forum' => 'Форум',						
		'panel' => 'Админка',						
		'chat' => 'Чат',						
		'other' => 'Разное',						
		'news' => 'Новости',						
		'bbcodes' => 'BB коды в подписи',						
	);

	return (isset($add_titles[$title])) ? h($add_titles[$title]) : h($title);
}
function getAddTitle($title) {
	$add_titles = array(
		'view_list' => 'Смотреть список материалов',						
		'view_materials' => 'Просматривать материалы', 						
		'add_materials' => 'Добавлять материалы', 						
		'edit_mine_materials' => 'Редактировать свои материалы', 						
		'edit_materials' => 'Редактировать все материалы', 						
		'delete_mine_materials' => 'Удалять свои материалы',						
		'delete_materials' => 'Удалять все материалы', 						
		'up_materials' => 'Поднимать материалы в списке', 						
		'on_home' => 'Выводить материалы на главную', 						
		'view_comments' => 'Просматривать комментарии', 						
		'add_comments' => 'Добавлять комментарии', 						
		'edit_comments' => 'Редактировать комментарии', 						
		'delete_comments' => 'Удалять комментарии', 						
		'hide_material' => 'Скрывать материалы', 						
		'record_comments_management' => 'Разрешать/запрещать комментирование', 						

		'view_forums_list' => 'Просматривать список форумов', 						
		'view_forums' => 'Просматривать форумы', 						
		'view_themes' => 'Просматривать темы', 						
		'add_themes' => 'Добавлять темы', 						
		'edit_themes' => 'Редактирова темы', 						
		'edit_mine_themes' => 'Редактировать свои темы', 						
		'delete_themes' => 'Удалять темы', 						
		'delete_mine_themes' => 'Удалять свои темы', 						
		'close_themes' => 'Закрывать темы', 						
		'important_themes' => 'Ставить флаг "Важно"', 						
		'add_posts' => 'Добавлять посты', 						
		'edit_posts' => 'Редактировать посты', 						
		'edit_mine_posts' => 'Редактировать свои посты', 						
		'delete_posts' => 'Удалять посты', 						
		'delete_mine_posts' => 'Удалять свои посты', 						
		'add_forums' => 'Добавлять форумы', 						
		'edit_forums' => 'Редактировать форумы', 						
		'delete_forums' => 'Удалять форумы', 						
		'replace_forums' => 'Перемещать форумы', 
							
		'view_users' => 'Просматривать анкеты', 						
		'edit_users' => 'Редактировать анкеты', 						
		'edit_mine' => 'Редактировать свой профиль', 						
		'ban_users' => 'Банить пользователей', 						
		'set_rating' => 'Менять рейтинг/голосовать', 						
		'delete_rating_comments' => 'Удалять голоса/рейтинг', 						
		'bb_s' => 'Зачеркнутый текст', 						
		'bb_u' => 'Подчеркивание', 						
		'bb_b' => 'Жирный текст', 						
		'bb_i' => 'Наклонный текст', 						
		'bb_img' => 'Картинки', 						
		'bb_url' => 'Ссылки', 						
		'html' => 'Поддержка HTML', 						
		'users_warnings' => 'Выдавать предупреждения', 						
		'delete_warnings' => 'Удалять предупреждения', 						

		'entry' => 'Вход в панель управления', 						

		'can_see_hidden' => 'Могут видеть скрытые материалы', 						
		'no_captcha' => 'Не выводить каптчу', 						

	);

	return (isset($add_titles[$title])) ? h($add_titles[$title]) : h($title);
}
