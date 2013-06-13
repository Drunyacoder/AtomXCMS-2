<?php
/*-----------------------------------------------\
| 												 |
|  @Author:       Andrey Brykin (Drunya)         |
|  @Email:        drunyacoder@gmail.com          |
|  @Site:         http://fapos.net               |
|  @Version:      1.5.4                          |
|  @Project:      CMS                            |
|  @package       CMS Fapos                      |
|  @subpackege    Template redactor              |
|  @copyright     ©Andrey Brykin 2010-2013       |
|  @last mod.     2013/06/13                     |
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


$pageTitle = __('Design - templates');
$pageNav = $pageTitle;
$pageNavr = '<a href="set_default_dis.php" onClick="return confirm(\'Система востановит стандартный шаблон. Вы уверены?\')">' . __('Return to default template') . '</a>&nbsp;|&nbsp;<a href="backup_dis.php" onClick="return confirm(\'Система сделает резервную копию шаблона. Вы уверены?\')">' . __('Save current state of template') . '</a>';


$allowedFiles = array(
    'news' => array(
        'addform',
        'main',
        'editform',
        'material',
        'list',
    ),
    'stat' => array(
        'addform',
        'main',
        'editform',
        'material',
        'list',
    ),
    'loads' => array(
        'addform',
        'main',
        'editform',
        'material',
        'list',
    ),
    'foto' => array(
        'addform',
        'main',
        'editform',
        'material',
        'list',
    ),
    'chat' => array(
        'addform',
        'main',
        'list',
    ),
    'search' => array(
        'search_form',
        'search_row',
    ),
    'users' => array(
        'addnewuserform',
        'main',
        'edituserform',
        'loginform',
        'baned',
        'showuserinfo',
    ),
    'forum' => array(
        'addthemeform',
        'editthemeform',
        'main',
        'replyform',
        'editpostform',
        'get_stat',
        'posts_list',
        'themes_list',
    ),
    'default' => array(
        'main',
    ),
);



$entities = array(
    'addform' 			=> __('Add form'),
    'main' 				=> __('Layout'),
    'editform' 			=> __('Edit form'),
    'material' 			=> __('Material view'),
    'list' 				=> __('List of materials'),
    'addnewuserform' 	=> __('Add form'),
    'edituserform' 		=> __('Edit form'),
    'loginform' 		=> __('Login form'),
    'baned' 			=> __('Ban page'),
    'showuserinfo' 		=> __('Profile info'),
    'style' 			=> __('Style(CSS)'),
    'addthemeform' 		=> __('Add theme form'),
    'editthemeform' 	=> __('Edit theme form'),
    'replyform' 		=> __('Reply form'),
    'editpostform' 		=> __('Edit post form'),
    'get_stat' 			=> __('Statistic'),
    'posts_list' 		=> __('Posts list'),
    'themes_list' 		=> __('Themes list'),
    'search_form' 		=> __('Search form'),
    'search_row' 		=> __('Search results'),
);

if (empty($_GET['m']) || !is_string($_GET['m'])) $_GET['m'] = 'default';
if (empty($_GET['t']) || !is_string($_GET['t'])) $_GET['t'] = 'main';
if (empty($_GET['d']) || !is_string($_GET['d'])) $_GET['d'] = 'default';



$module = trim($_GET['m']);
if (!array_key_exists($_GET['m'], $allowedFiles)) {
	$modInstaller = new FpsModuleInstaller();
	$extentionParams = $modInstaller->getTemplateParts($module);
	if (!empty($extentionParams)) {
		$allowedFiles[$module] = $extentionParams;
	}
}


$module = (array_key_exists($_GET['m'], $allowedFiles)) ? $_GET['m'] : 'default';
$filename = (in_array($_GET['t'], $allowedFiles[$module])) ? $_GET['t'] : 'main';
$type = (in_array($_GET['d'], array('css', 'default'))) ? $_GET['d'] : 'default';
if ('css' == $type) $file = 'style';



if(isset($_POST['send']) && isset($_POST['templ'])) {
	if ($type == 'css') {
		$template_file = ROOT . '/template/' . $Register['Config']::read('template') . '/css/style.css';
		if (!is_file($template_file . '.stand')) {
			copy($template_file, $template_file . '.stand');
		}
		$file = fopen($template_file, 'w+');


	} else {
		 
		$template_file = ROOT . '/template/' . $Register['Config']::read('template') . '/html/' . $module . '/' . $filename . '.html';
		
		
		
		if (!file_exists($template_file . '.stand') && file_exists($template_file)) {
			copy($template_file, $template_file . '.stand');
		}
		$file = fopen($template_file, 'w+');
	}
	
	
	if(fputs($file, $_POST['templ'])) {
		$mess = __('Template is saved');
	} else {
		$mess = __('Template is not saved');
	}
	fclose($file);
}
if (!empty($_SESSION['message'])) {
    $mess = $_SESSION['message'];
    unset($_SESSION['message']);
}





if ($_GET['d'] == 'css') {
    $path = ROOT .'/template/' . Config::read('template') . '/css/style.css';
} else {
    clearstatcache();
    $path = ROOT .'/template/' . Config::read('template') . '/html/' . $module . '/' . $filename . '.html';
    if (!file_exists($path)) {
        $path = ROOT .'/template/' . Config::read('template') . '/html/default/' . $filename . '.html';
        if (!file_exists($path)) {
            $_SESSION['message'] = __('Requested file is not found');
            redirect('/admin/design.php');
        }
    }
}
$template = file_get_contents($path);


include_once ROOT . '/admin/template/header.php';
echo '<form action="' . $_SERVER['REQUEST_URI'] . '" method="POST">';

?>

<div class="warning">
<?php
if(isset($mess) && $mess != NULL) {
	echo '<b>'.$mess.'</b>';
} else {
	echo __('Change template and save');
}
?>
</div>




<div class="white">
	<div class="pages-tree">
		<div class="title">Страницы</div>
		<div class="wrapper">
			<?php foreach ($allowedFiles as $mod => $files):
				$title = ('default' == $mod) ? __('Default') : Config::read('title', $mod);
				if (!empty($title)):
			?>

				<div class="tbn"><?php echo $title; ?></div>
					<?php foreach ($files as $file): ?>
					<div class="tba1">
					<a href="design.php?d=default&t=<?php echo $file; ?>&m=<?php echo $mod; ?>"><?php echo $entities[$file]; ?></a>
					</div>
					<?php endforeach; ?>
					<?php if ('default' == $mod): ?>
						<div class="tba1">
						<a href="design.php?d=css&t=style"><?php echo __('Style(CSS)') ?></a>
						</div>
					<?php endif; ?>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
		<div style="width:100%;">&nbsp;</div>
	</div>
	
	



	
	<div class="list pages-form">
		<div class="title">Редактор шаблонов</div>
		<div class="level1">
			<div class="items">
				<div class="setting-item">
					<div class="center">
						<textarea title="Код шаблона" style="width:99%;height:380px;" wrap="off" name="templ" id="tmpl"><?php print h($template); ?></textarea>
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
</form>



<script type="text/javascript" src="js/codemirror/codemirror.js"></script>
<script type="text/javascript" src="js/codemirror/mode/htmlmixed/htmlmixed.js"></script>
<script type="text/javascript" src="js/codemirror/mode/vbscript/vbscript.js"></script>
<script type="text/javascript" src="js/codemirror/mode/css/css.js"></script>
<!--
<script type="text/javascript" src="js/codemirror/mode/javascript/javascript.js"></script>
<script type="text/javascript" src="js/codemirror/mode/xml/xml.js"></script>
-->
<link rel="StyleSheet" type="text/css" href="js/codemirror/codemirror.css" />
<link rel="StyleSheet" type="text/css" href="js/codemirror/theme/eclipse.css" />
<script type="text/javascript">
$(document).ready(function(){
    var editor = CodeMirror.fromTextArea(document.getElementById("tmpl"), {
		theme: "eclipse", 
		mode: "<?php echo ($type === 'css') ? 'css' : 'vbscript'; ?>"
	});
	editor.setSize(933, 450);
});
</script>








<script type="text/javascript">
$('#listener').hover(function(){
    $(this).stop().animate({height:'200px', overflow:'auto'}, 400);
    $(this).css('overflow','auto');
},
function(){
    $(this).stop().animate({height:'90px', opacity:'0.3'}, 300, function(){
        $(this).css('opacity','1');
    });
    $(this).css('overflow','auto');
});
</script>




<ul class="markers">
	<h2>Глобальные метки</h2>
	<li><div class="global-marks">{{ content }}</div> - Основной контент страницы</li>
	<li><div class="global-marks">{{ title }}</div> - Заголовок страницы</li>
	<li><div class="global-marks">{{ description }}</div> - Содержание Мета-тега description</li>
	<li><div class="global-marks">{{ fps_wday }}</div> - День кратко</li>
	<li><div class="global-marks">{{ fps_date }}</div> - Дата</li>
	<li><div class="global-marks">{{ fps_time }}</div> - Время</li>
	<li><div class="global-marks">{{ headmenu }}</div> - Верхнее меню</li>
	<li><div class="global-marks">{{ fps_user_name }}</div> - Ник текущего пользователя (Для не авторизованного - Гость)</li>
	<li><div class="global-marks">{{ fps_user_group }}</div> - Группа текущего пользователя (Для не авторизованного - Гости)</li>
	<li><div class="global-marks">{{ categories }}</div> - Список категорий раздела</li>
	<li><div class="global-marks">{{ counter }}</div> - Встроенный счетчик посещаемости CMS Fapos</li>
	<li><div class="global-marks">{{ fps_year }}</div> - Год</li>
	<li><div class="global-marks">{{ powered_by }}</div> - CMS Fapos</li>
	<li><div class="global-marks">{{ comments }}</div> - Комментарии к материалу и форма добавления комментариев <b>(если предусмотренно)</b></li>
	<li><div class="global-marks">{{ personal_page_link }}</div> - URL на свою персональную страницу или на страницу регистрации, если не авторизован</li>
</ul>




<?php
if (!empty($_SESSION['info_message'])):
?>
<script type="text/javascript">showHelpWin('<?php echo h($_SESSION['info_message']) ?>', '<?php echo __('Message') ?>');</script>
<?php
	unset($_SESSION['info_message']);
endif;
?>

<?php include_once 'template/footer.php'; ?>

