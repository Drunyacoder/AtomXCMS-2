<?php
##################################################
##												##
## Author:       Andrey Brykin (Drunya)         ##
## Version:      1.2                            ##
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

// Clean snippets Cache
$cache = new Cache;
$cache->prefix = 'block';
$cache->cacheDir = ROOT . '/sys/cache/blocks/';
$cache->clean();



$pageTitle = $pageNav = 'Глобальные блоки. Сниппеты.';
$pageNavr = $pageTitle;
if (isset($_GET['a']) && $_GET['a'] == 'ed') {
    $pageNavr = 'Сниппеты &raquo; [редактирование] &raquo; <a href="snippets.php">создание</a>';
} else {
    $pageNavr = 'Сниппеты &raquo; <a href="snippets.php?a=ed">редактирование</a> &raquo; [создание]';
}



if (isset($_GET['a']) && $_GET['a'] == 'ed') {

	$id = (!empty($_GET['id'])) ? intval($_GET['id']) : '';
	if(isset($_POST['send']) && isset($_POST['text_edit'])) {
		$sql = $Register['DB']->save('snippets', array(
			'body' => $_POST['text_edit'],
			'id' => $id,
		));
		$_SESSION['mess'] = 'Сниппет успешно сохранен!';
		
		redirect('/admin/snippets.php?a=ed&id=' . $id);
	}


	if(isset($_GET['delete'])) {
		$sql = $FpsDB->query("DELETE FROM `" . $FpsDB->getFullTableName('snippets') . "` WHERE id='" . $id . "'");
		$_SESSION['mess'] = 'Сниппет успешно удален!';
		redirect('/admin/snippets.php?a=ed');
	}
	if (!empty($id)) {
		$sql = $FpsDB->select('snippets', DB_FIRST, array('cond' => array('id' => $id)));
		if(count($sql) > 0) {
			$content = h($sql[0]['body']);
			$name = h($sql[0]['name']);
		 }
	} else {
		$content = 'Выберите сниппет';
	}

    include_once ROOT . '/admin/template/header.php';
?>	


<div class="warning">
	Снипеты позволяют создать блоки php кода и подключать их в любом месте сайта, прямо в шаблонах.<br />
	Вызвать снипет из шаблона можно так <strong>{[ИМЯ СНИППЕТА]}</strong><br />
	После того, как Вы добавите метку в шаблон, она будет заменена на результат выполнения кода сниппета.<br />
	Тут приведен список, уже созданных, сниппетов. Вы можете их просматривать и редактировать.<br />
	Для то, что бы создавать и редактировать сниппеты, желательно, обладать, хотя бы, базовыми знаниями PHP
	

	<?php if (isset($_SESSION['mess'])): ?>
	<br />
	<br />
	<br />
	<b><?php echo $_SESSION['mess'] ?></b>
	<?php unset($_SESSION['mess']); 
	endif; ?>
</div>


<div class="white">
	<div class="pages-tree" style="height:550px;">
		<div class="title">Страницы</div>
		<div class="wrapper">
			<div class="tree-wrapper">
				<div id="pageTree">
				<?php
				$sql = $FpsDB->select('snippets', DB_ALL);
					foreach ($sql as $record) {
						echo '<div id="mItem48"  class="tba"><a href="snippets.php?a=ed&id='
						 . ($record['id']) . '">'
						 . h($record['name']) . '</a></div>';
					}
				?>
				</div>
			</div>
		</div>
		<div style="width:100%;">&nbsp;</div>
	</div>
	
	

	<div style="display:none;" class="ajax-wrapper" id="ajax-loader"><div class="loader"></div></div>
	<form action="<?php echo $_SERVER['REQUEST_URI']?>" method="post">


	
	<div class="list pages-form">
		<div class="title">Редактор страницы</div>
		<div class="level1">
			<div class="items">
				<div class="setting-item">
					<div class="left">
						Имя сниппета
					</div>
					<div class="right">
						<input disabled="disabled" name="my_title" type="text" style="" value="<?php echo (!empty($name)) ? $name : '';?>">
						<?php if(isset($id) && $id != null) : ?> 
							<a class="delete" href="snippets.php?a=ed&id=<?php echo $id ?>&delete=y" onClick="return confirm('Are you sure?')"></a>
						<?php endif; ?>
					</div>
					<div class="clear"></div>
				</div>
				<div class="setting-item">
					<div class="center">
						<textarea name="text_edit" style="width:99%; height:300px;"><?php echo $content;?></textarea>
					</div>
					<div class="clear"></div>
				</div>
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
	<div class="clear"></div>

</div>






	

<?php

} else {
	
	 
	if (isset($_POST['send'])) {
		if (empty($_POST['my_title']) || mb_strlen($_POST['my_text']) < 3 || empty($_POST['my_title'])) $_SESSION['mess'] = 'Заполните все поля';
		if (empty($_SESSION['mess'])) {
			$countchank = $FpsDB->select('snippets', DB_COUNT, array('cond' => array('name' => $_POST['my_title'])));
			if ($countchank == 0) {
				$sql = $FpsDB->save('snippets', array(
					'name' => $_POST['my_title'],
					'body' => $_POST['my_text'],
				));
				
				$_SESSION['mess'] = "Сниппет создан! Применяйте его так: {[" . h($_POST['my_title']) . "]}";
				redirect('/admin/snippets.php?a=ed&id=' . mysql_insert_id());
			} else {
				$_SESSION['mess'] = 'Такой сниппет уже есть! Измените имя блока.';
			}
		}
	}
    include_once ROOT . '/admin/template/header.php';
	?>


	
<div class="warning">
	Снипеты позволяют создать блоки php кода и подключать их в любом месте сайта, прямо в шаблонах.<br />
	Вызвать снипет из шаблона можно так <strong>{[ИМЯ СНИППЕТА]}</strong><br />
	После того, как Вы добавите метку в шаблон, она будет заменена на результат выполнения кода сниппета.<br />
	На странице редактирования приведен список, уже созданных, сниппетов. Вы можете их просматривать и редактировать.<br />
	Для то, что бы создавать и редактировать сниппеты, желательно, обладать, хотя бы, базовыми знаниями PHP
	

	
	<?php if (isset($_SESSION['mess'])): ?>
	<br />
	<br />
	<br />
	<b><?php echo $_SESSION['mess'] ?></b>
	<?php unset($_SESSION['mess']); 
	endif; ?>
</div>


<div class="white">
	<div class="pages-tree" style="height:550px;">
		<div class="title">Страницы</div>
		<div class="wrapper">
			<div class="tree-wrapper">
				<div id="pageTree">
				<?php
				$sql = $FpsDB->select('snippets', DB_ALL);
					foreach ($sql as $record) {
						echo '<div id="mItem48"  class="tba"><a href="snippets.php?a=ed&id='
						 . ($record['id']) . '">'
						 . h($record['name']) . '</a></div>';
					}
				?>
				</div>
			</div>
		</div>
		<div style="width:100%;">&nbsp;</div>
	</div>
	
	

	<div style="display:none;" class="ajax-wrapper" id="ajax-loader"><div class="loader"></div></div>
	<form action="snippets.php" method="post">


	
	<div class="list pages-form">
		<div class="title">Редактор страницы</div>
		<div class="level1">
			<div class="items">
				<div class="setting-item">
					<div class="left">
						Имя сниппета
					</div>
					<div class="right">
						<input name="my_title" type="text" style="" value="<?php if (!empty($_POST['my_title'])) echo h($_POST['my_title']) ?>" />
					</div>
					<div class="clear"></div>
				</div>
				<div class="setting-item">
					<div class="center">
						<textarea name="my_text" style="width:99%; height:300px;" ><?php if (!empty($_POST['my_text'])) echo h($_POST['my_text']) ?></textarea>
					</div>
					<div class="clear"></div>
				</div>
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
	<div class="clear"></div>

</div>	
	
	



<?php } ?> 

<?php
include_once 'template/footer.php';
?>