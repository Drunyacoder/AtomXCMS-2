<?php
/*-----------------------------------------------\
| 												 |
|  Author:       Andrey Brykin (Drunya)          |
|  Version:      1.2.3                           |
|  Project:      CMS                             |
|  package       CMS Fapos                       |
|  subpackege    Admin Panel module              |
|  copyright     ©Andrey Brykin 2010-2011        |
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

 
$pageTitle = 'Пользователи';
 
 
if ( !isset( $_GET['ac'] ) ) $_GET['ac'] = 'index';
$actions = array( 'index',
					'ank',
					'del',
					'save');
					
if ( !in_array( $_GET['ac'], $actions ) ) $_GET['ac'] = 'index';

switch ( $_GET['ac'] )
{
	case 'index':  // главная страница 
		$content = index($pageTitle);
		break;
	case 'ank':        
		$content = editAnk($pageTitle);
		break;
	case 'save':         
		$content = saveAnk();
		break;
	default:
		$content = index($pageTitle);
}


$pageNav = $pageTitle;
$pageNavl = '<a href="users_list.php">Список пользователей</a>';





$dp = $Register['DocParser'];


include_once ROOT . '/admin/template/header.php';
echo $content;
include_once ROOT . '/admin/template/footer.php';
 	

	
function index(&$page_title) {
    $Register = Register::getInstance();
    $FpsDB = $Register['DB'];
   	$ACL = $Register['ACL'];
	$page_title = 'Список пользователей';
	$order = '';
	$limit = 30;
	$content = '';

	if (!empty($_GET['cond'])) {
		$permision_cond = array('name', 'email', 'status',  'posts', 'themes', 'puttime');
		if (!in_array($_GET['cond'], $permision_cond)) $_GET['cond'] = 'puttime';
		
		$order = (!empty($_GET['value']) && $_GET['value'] == '1') ? ' DESC' : ' ASC';
		$order = 'ORDER BY ' . $_GET['cond'] . $order;
	}

	if (!empty($_POST['search'])) $str_search = "WHERE `name` LIKE '%{$_POST['search']}%'";
	else $str_search = '';
	$count = $FpsDB->query("SELECT COUNT(*) as cnt FROM `" . $FpsDB->getFullTableName('users') . "` {$str_search} {$order}");
	$total = (!empty($count[0]['cnt'])) ? $count[0]['cnt'] : 0;
    list($pages, $page) = pagination($total, $limit, '/admin/users_list.php?ac=index');
	$start = ($page - 1) * $limit;

	
	$sql = "SELECT * FROM `" . $FpsDB->getFullTableName('users') . "` {$str_search} {$order} LIMIT {$start}, {$limit}";
	$query = $FpsDB->query($sql);
	
	$nick = (!empty($_GET['cond']) && $_GET['cond'] == 'name') ? '<a href="?cond=name&value=0">Ник</a>' : '<a href="?cond=name&value=1">Ник</a>';
	$email = (!empty($_GET['cond']) && $_GET['cond'] == 'email') ? '<a href="?cond=email&value=0">Почта</a>' : '<a href="?cond=email&value=1">Почта</a>';
	$puttime = (!empty($_GET['cond']) && $_GET['cond'] == 'puttime') ? '<a href="?cond=puttime&value=0">Дата регистрации</a>' : '<a href="?cond=puttime&value=1">Дата регистрации</a>';
	$status = (!empty($_GET['cond']) && $_GET['cond'] == 'status') ? '<a href="?cond=status&value=0">Статус</a>' : '<a href="?cond=status&value=1">Статус</a>';
	$themes = (!empty($_GET['cond']) && $_GET['cond'] == 'themes') ? '<a href="?cond=themes&value=0">Тем</a>' : '<a href="?cond=themes&value=1">Тем</a>';
	$posts = (!empty($_GET['cond']) && $_GET['cond'] == 'posts') ? '<a href="?cond=posts&value=0">Постов</a>' : '<a href="?cond=posts&value=1">Постов</a>';
	
	$pages = '<div class="pages">' . $pages . '</div>';
	$content .= "<div class=\"list\">
			<div class=\"title\">{$pages}</div>
			<table cellspacing=\"0\" class=\"grid\"><th width=\"20%\">{$nick}</th>
			<th width=\"25%\">{$email}</th>
			<th width=\"20%\">{$puttime}</th>
			<th width=\"15%\">{$status}</th>
			<th width=\"9%\">{$themes}</th>
			<th width=\"9%\">{$posts}</th>
			<th width=\"20px\" colspan=\"2\">Действие</th>";
	

	
	foreach ($query as $result) {
		$status_info = $ACL->get_user_group($result['status']);
		$status = $status_info['title'];
		$color = (!empty($status_info['color'])) ? $status_info['color'] : '';
		$content .= "<tr><td><a href='users_list.php?ac=ank&id={$result['id']}'>{$result['name']}</a></td>
						<td>{$result['email']}</td>
						<td>{$result['puttime']}</td>
						<td><span style=\"color:#{$color}\">{$status}</span></td>
						<td>{$result['themes']}</td>
						<td>{$result['posts']}</td>
						<td colspan=\"2\"><a class=\"edit\" href='users_list.php?ac=ank&id={$result['id']}'></a>
						</td>";
	}
	$content .= '</table></div>';
	
	$content .= '<form method="POST" action="users_list.php?ac=index"><table class="metatb"><tr><td>
				<input type="text" name="search" />
				<input type="submit" name="send" value="Поиск" />
				</td></tr></table></form>';
	
	return $content;
  
}

//*****************************************************************************************************************
//*****************************************************************************************************************

function editAnk(&$page_title) {
    $Register = Register::getInstance();
    $FpsDB = $Register['DB'];
   	$ACL = $Register['ACL'];
	if (!is_numeric($_GET['id'])) redirect('/admin/users_list.php');
	
	$page_title = 'Редактирование данных пользователя';
	$content = '';
	$statuses = $ACL->get_group_info();
	$query = $FpsDB->select('users', DB_FIRST, array('cond' => array('id' => $_GET['id'])));
	
	if (empty($query)) return '<span style="color:red;">Запись не найдена</span>';

	foreach ($query[0] as $key => $value) {
		$$key = (!empty($_SESSION['edit_ank'][$key])) ? $_SESSION['edit_ank'][$key] : $value;
	}
	
	
	$telephone = (empty($telephone)) ? '' : intval($telephone);
	$mpol = (!empty($query[0]['pol']) && $query[0]['pol'] === 'm') ? 'checked="checked"' : '';
	$fpol = (!empty($query[0]['pol']) && $query[0]['pol'] === 'f') ? 'checked="checked"' : '';
	
	
	if (!empty($_SESSION['edit_ank']['errors'])) {
		$content .= '<script type="text/javascript">showHelpWin(\'' . $_SESSION['edit_ank']['errors'] . '\', \'Ошибки\');</script>';
	}
	unset($_SESSION['edit_ank']);
	$_SESSION['adm_form_key'] = md5(rand() . rand());
	
	
	
	
	
	$content .= '<form action="users_list.php?ac=save&id=' . $_GET['id'] . '" method="POST"><div class="list">
			<div class="title">Редактирование анкеты (' . h($name) . ')</div>
			<div class="level1">
				<div class="items">
					<div class="setting-item">
						<div class="left">
							Имя
						</div>
						<div class="right">
							<input type="hidden" value="' . $_SESSION['adm_form_key'] . '" name="adm_form_key" />
							<input type="text" name="login" value="' . h($name) .'" />
						</div>
						<div class="clear"></div>
					</div>
					<div class="setting-item">
						<div class="left">
							Ранг
						</div>
						<div class="right">
							<input type="text" name="state" value="' . h($state) .'" />
						</div>
						<div class="clear"></div>
					</div>
					<div class="setting-item">
						<div class="left">
							Пароль
						</div>
						<div class="right">
							<input type="text" name="passw" value="" />
						</div>
						<div class="clear"></div>
					</div>
					<div class="setting-item">
						<div class="left">
							Почта
						</div>
						<div class="right">
							<input type="text" name="email" value="' . h($email) .'" />
						</div>
						<div class="clear"></div>
					</div>
					<div class="setting-item">
						<div class="left">
							Сайт
						</div>
						<div class="right">
							<input type="text" name="url" value="' . h($url) .'" />
						</div>
						<div class="clear"></div>
					</div>
					<div class="setting-item">
						<div class="left">
							ICQ
						</div>
						<div class="right">
							<input type="text" name="icq" value="' . h($icq) .'" />
						</div>
						<div class="clear"></div>
					</div>
					<div class="setting-item">
						<div class="left">
							Jabber
						</div>
						<div class="right">
							<input type="text" name="jabber" value="' . h($jabber) .'" />
						</div>
						<div class="clear"></div>
					</div>
					<div class="setting-item">
						<div class="left">
							City
						</div>
						<div class="right">
							<input type="text" name="city" value="' . h($city) .'" />
						</div>
						<div class="clear"></div>
					</div>
					<div class="setting-item">
						<div class="left">
							Telephone
						</div>
						<div class="right">
							<input type="text" name="telephone" value="' . h($telephone) .'" />
						</div>
						<div class="clear"></div>
					</div>
					<div class="setting-item">
						<div class="left">
							Пол
						</div>
						<div class="right">
							<input type="radio" name="pol" value="m" '.$mpol.' id="polm" /><label for="polm">М</label><br /><br />
							<input type="radio" name="pol" value="f" '.$fpol.' id="polj" /><label for="polj">Ж</label>
						</div>
						<div class="clear"></div>
					</div>
					<div class="setting-item">
						<div class="left">
							Дата рождения
						</div>
						<div class="right">
							<select style="width:80px;" name="byear">'
							. createOptionsFromParams(1970, 2008, $byear) . 
							'</select>
							<select style="width:50px;" name="bmonth">'
							. createOptionsFromParams(1, 12, $bmonth) . 
							'</select>
							<select style="width:50px;" name="bday">'
							. createOptionsFromParams(1, 31, $bday) . 
							'</select>
						</div>
						<div class="clear"></div>
					</div>
					<div class="setting-item">
						<div class="left">
							О себе
						</div>
						<div class="right">
							<textarea name="about" style="height:100px;">' . h($about) .'</textarea>
						</div>
						<div class="clear"></div>
					</div>
					<div class="setting-item">
						<div class="left">
							Подпись
						</div>
						<div class="right">
							<textarea  style="height:100px;" name="signature" />' . h($signature) .'</textarea>
						</div>
						<div class="clear"></div>
					</div>
					<div class="setting-item">
						<div class="left">
							Бан
						</div>
						<div class="right">
							<select name="locked">';
						
							if ($locked == 0) {
								$content .= '<option value="1">Заблокирован</option>
											<option value="0" selected="selected">Разблокирован</option>';
							} else {
								$content .= '<option value="1" selected="selected">Заблокирован</option>
											<option value="0">Разблокирован</option>';	
							}
							
							$content .= '</select>
						</div>
						<div class="clear"></div>
					</div>
					<div class="setting-item">
						<div class="left">
							Статус
						</div>
						<div class="right">
							<select name="status">';
							
							foreach ($statuses as $key => $value) {
								if ($key == 0) continue;
								$content .= ($status == $key) ? '<option value="' . $key . '" selected="selected">' . $value['title'] . '</option>' : 
															'<option value="' . $key . '">' . $value['title'] . '</option>';
							}
							$activation = (!empty($activation)) 
							? '<input id="activation" name="activation" type="checkbox" value="1" ><label for="activation">Активировать</label>' : '<span style="color:blue;">Активирован</span>';
							$content .=	 '</select>
						</div>
						<div class="clear"></div>
					</div>
					<div class="setting-item">
						<div class="left">
							Активация
						</div>
						<div class="right">
							' . $activation . '
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
		</div></form>';

	return $content;
}


//*****************************************************************************************************************
//*****************************************************************************************************************

function saveAnk() {
    $Register = Register::getInstance();
    $FpsDB = $Register['DB'];
   	$ACL = $Register['ACL'];
    $v_obj = $Register['Validate'];

	if (empty($_GET['id']) || !is_numeric($_GET['id'])) redirect('/admin/users_list.php');
	if (empty($_SESSION['adm_form_key']) 
	|| empty($_POST['adm_form_key'])
	|| $_SESSION['adm_form_key'] != $_POST['adm_form_key']) redirect('/admin/users_list.php');
	
	$check_user = $FpsDB->select('users', DB_FIRST, array('cond' => array('id' => (int)$_GET['id'])));
	if (count($check_user) < 1) {
		$_SESSION['info_message'] = 'Не найден пользователь с таким ID';
		redirect('/admin/users_list.php');
	}
	
	
	//validate class object for validate data
	$errors = '';
	$content = '';
	
	//deleting spaces
	$_POST = array_merge(array('login', 'state', 'passw', 'email', 'url', 'icq', 'jabber', 'city', 'telephone', 'pol', 'byear', 'bmonth', 'bday', 'about', 'signature', 'locked', 'status'), $_POST);
	foreach ($_POST as $key => $value) {
		$$key = trim($value);
	}
	
	if (isset($pol) && ($pol == '1' || $pol == 'm')) $pol = 'm';
	else if (!isset($pol) || $pol === '') $pol = '';
	else $pol = 'f';
	
	$byear = (isset($byear)) ? intval($byear) : '';
	$byear = (!empty($byear) && ($byear >= 1970 && $byear <= 2008)) ? $byear : 0;
	$bmonth = (isset($bmonth)) ? intval($bmonth) : '';
	$bmonth = (!empty($bmonth) && ($bmonth >= 1 && $bmonth <= 12)) ? $bmonth : 0;
	$bday = (isset($bday)) ? intval($bday) : '';
	$bday = (!empty($bday) && ($bday >= 1 && $bday <= 31)) ? $bday : 0;

	//check data for wrong chars
	if ($v_obj->cha_val($login, V_TITLE) !== true) 
		$errors .= '<li>Имя содержит недопустимые символы</li>';
	//if (!empty($state) && $v_obj->cha_val($state, V_TEXT) !== true) 
	//	$errors .= '<li>Ранг содержит недопустимые символы</li>';
	if (!empty($email) && $v_obj->cha_val($email, V_MAIL) !== true) 
		$errors .= '<li>некоректный Email</li>';
	if (!empty($url) && $v_obj->cha_val($url, V_URL) !== true) 
		$errors .= '<li>Не коректный URL сайта</li>';
	if (!empty($icq) && $v_obj->cha_val($icq, V_INT) !== true) 
		$errors .= '<li>ICQ должно содержать только цифры</li>';
	if (!empty($about) && $v_obj->cha_val($about, V_TEXT) !== true) 
		$errors .= '<li>Поле "о себе" содержит недопустимые символы</li>';
	if (!empty($signature) && $v_obj->cha_val($signature, V_TEXT) !== true) 
		$errors .= '<li>Подпись содержит недопустимые символы</li>';

	//check data for max/min lenght
	if ($v_obj->len_val($login, 3, 15) !== true) 
		$errors .= '<li>Имя слишком короткое/длинное. Должно быть в диапазоне 3-15 символов</li>';
	if ($v_obj->len_val($url, 0) !== true) 
		$errors .= '<li>Слишком длинный URL</li>';
	if ($v_obj->len_val($icq, 0, 10) !== true) 
		$errors .= '<li>ICQ не должно превышать 10 символов </li>';
	if ($v_obj->len_val($about, 0, 300) !== true) 
		$errors .= '<li>Поле "о себе" не должно превышать 300 символов</li>';
	if ($v_obj->len_val($signature, 0) !== true) 
		$errors .= '<li>Подпись не должна превышать 100 символов</li>';
	if (!empty($passw) && $v_obj->len_val($passw, 6, 32) !== true) 
		$errors .= '<li>Пароль слишком длинный/короткий. Он должен содержать 6-32 символа</li>';
	

	if ($locked != 1 && $locked != 0) $locked = 0;
	$status = (int)$status;	
	if ($status < 1) $status = 1;
	
	if (!empty($check_user[0]['name']) && $check_user[0]['name'] !== $login) {
		if ($v_obj->uniq_val($login, array('table' => 'users', 'field' => 'name'), 'hight') != true) 
			$errors .= '<li>Это имя уже занято :(</li>';
	}
	
	if (!empty($errors)) {
		$_SESSION['edit_ank'] = array();
		$_SESSION['edit_ank']['errors'] = '<ul class="uz">' . $errors . '</ul>';
		foreach ($_POST as $key => $value) {
			$_SESSION['edit_ank'][$key] = trim($value);
		}
		redirect('/admin/users_list.php?ac=ank&id=' . $_GET['id']);
	}
	
	$data = array(
		'id' 		=> $_GET['id'],
		'name' 		=> $login,
		'state' 	=> $state,
		'email'	    => $email,
		'url' 		=> $url,
		'icq' 		=> $icq,
		'jabber' 	=> $jabber,
		'city' 		=> $city,
		'telephone' => $telephone,
		'pol' 		=> $pol,
		'byear' 	=> $byear,
		'bmonth' 	=> $bmonth,
		'bday' 		=> $bday,
		'about' 	=> $about,
		'signature' => $signature,
		'locked'	=> $locked,
		'status' 	=> $status,
	);
	if (!empty($passw)) $data['passw'] = md5($passw);
	if (isset($_POST['activation'])) $data['activation'] = '';
	$FpsDB->save('users', $data);
			
	redirect('/admin/users_list.php?ac=ank&id=' . $_GET['id']);
}



?>

<?php
if (!empty($_SESSION['info_message'])):
?>
<script type="text/javascript">showHelpWin('<?php echo h($_SESSION['info_message']) ?>', 'Сообщение');</script>
<?php
	unset($_SESSION['info_message']);
endif;
?>