<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.5.5                         |
| @Project:      CMS                           |
| @package       CMS Fapos                     |
| @subpackege    Users Module                  |
| @copyright     ©Andrey Brykin 2010-2013      |
| @last mod      2013/02/22                    |
|----------------------------------------------|
|											   |
| any partial or not partial extension         |
| CMS Fapos,without the consent of the         |
| author, is illegal                           |
|----------------------------------------------|
| Любое распространение                        |
| CMS Fapos или ее частей,                     |
| без согласия автора, является не законным    |
\---------------------------------------------*/





Class UsersModule extends Module {
	
	
	/**
	 * @template  layout for module
	 * @var string
	 */
	public $template = 'users';
	
	/**
	 * @module_title  title of module
	 * @var string
	 */
	public $module_title = 'Пользователи';
	
	/**
	 * @module module indentifier
	 * @var string
	 */
	public $module = 'users';
	



	// Функция возвращает html списка пользователей форума
	public function index()
    {
        //turn access
        $this->ACL->turn(array('users', 'view_list'));
		$this->page_title .= ' - ' . __('Users list');
        // Выбираем из БД количество пользователей - это нужно для
        // построения постраничной навигации
        $total = $this->Model->getTotal(array());
        list($pages, $page) = pagination($total, $this->Register['Config']->read('users_per_page', 'users'), '/users/');


        // Navigation Panel
        $nav = array();
        $nav['navigation'] = get_link(__('Home'), '/') . __('Separator')
            . get_link(h($this->module_title), '/users/') . __('Separator') . __('Users list');
        $nav['pagination'] = $pages;
        $nav['meta'] = __('All users') . $total;
        $this->_globalize($nav);


        if (!$total) return $this->_view(__('No users'));


        //order by
        $order = getOrderParam(__CLASS__);
        $queryParams = array(
            'order' => mysql_real_escape_string($order),
            'page' => $page,
            'limit' => $this->Register['Config']->read('users_per_page', 'users')
        );
        $records = $this->Model->getCollection(array(), $queryParams);

        if (is_object($this->AddFields) && count($records) > 0) {
            $records = $this->AddFields->mergeRecords($records);
        }
	

		foreach ($records as $user) {
			$markers = array();
            $uid = $user->getId();
			

			$markers['user_name'] = get_link(h($user->getName()), getProfileUrl($uid));
			
			$markers['moder_panel'] = '';
			if ($this->ACL->turn(array('users', 'edit_users'), false)) {
				$markers['adm_panel'] = get_link('',
				'/users/edit_form_by_admin/' . $uid, 
				array('class' => 'fps-edit'));
			}
			
			
			$status = $this->ACL->get_user_group($user->getStatus());
			$markers['status'] = h($status['title']);

			if (isset($_SESSION['user'])) {
				$markers['pm'] = get_link(__('Write'), '/users/send_msg_form/' . $uid);
			} else {
				$markers['pm'] = __('You are not authorized');
			}
			if ($user->getUrl())
				$markers['url'] = get_link(h($user->getUrl()), h($user->getUrl()), array('target' => '_blank'));
			else
                $markers['url'] = '&nbsp;';

				
			if ($user->getPol() === 'f') $markers['pol'] = __('f');
			else if ($user->getPol() === 'm') $markers['pol'] = __('m');
			else $markers['pol'] = __('no gender');

			
			if ($user->getByear() && $user->getBmonth() && $user->getBday()) {
                $markers['age'] = getAge($user->getByear(), $user->getBmonth(), $user->getBday());
			} else {
                $markers['age'] = '';
			}
			
			$user->setAdd_markers($markers);
		}
		

		$source = $this->render('list.html', array('entities' => $records));
		return $this->_view($source);
	}



	
	/**
	 * @param string $key
	 * if exists, user say "YES" and ready to register
	 */
	public function add_form($key = null)
    {
		if (!empty($_SESSION['user']['id'])) redirect('/');
	
		// Registration denied
		if (!$this->Register['Config']->read('open_reg')) {
			return $this->showInfoMessage(__('Registration denied'), '/');
		}
		
	
		// View rules
		if (empty($key)) {
            $usClassName = $this->Register['ModManager']->getModelName('UsersSettings');
            $usModel = new $usClassName;
            $rules = $usModel->getByType('reg_rules');
			$markers = array();
			$markers['rules'] = $rules[0]['values'];
			$markers['reg_url'] = get_url('/users/add_form/yes');
			$content = $this->render('viewrules.html', array('context' => $markers));
			$this->_view($content);
			die();
		}


		// View Register Form
		$markers = array();

		// Add fields
		if (is_object($this->AddFields)) {
			$_addFields = $this->AddFields->getInputs();
            foreach($_addFields as $k => $field) {
                $markers[strtolower($k)] = $field;
            }
		}
		
		
		if ( isset( $_SESSION['captcha_keystring'] ) ) unset( $_SESSION['captcha_keystring'] );


        // Check for preview or errors
        $data = array(
            'login' => null,
            'email' => null,
            'timezone' => null,
            'icq' => null,
            'jabber' => null,
            'pol' => null,
            'city' => null,
            'telephone' => null,
            'byear' => null,
            'bmonth' => null,
            'bday' => null,
            'url' => null,
            'about' => null,
            'signature' => null
        );
        $data = Validate::getCurrentInputsValues($data);


        $errors = $this->Parser->getErrors();
        if (isset($_SESSION['FpsForm'])) unset($_SESSION['FpsForm']);
        if (!empty($errors)) $markers['error'] = $errors;
		else $markers['error'] = '';
		

        $markers['captcha'] = get_url('/sys/inc/kcaptcha/kc.php?'.session_name().'='.session_id());
        $markers['name']    = $data['login'];
        $markers['fpol']  	= (!empty($data['pol']) && $data['pol'] === 'f') ? ' checked="checked"' : '';
        $markers['mpol']  	= (!empty($data['pol']) && $data['pol'] === 'm') ? ' checked="checked"' : '';



        $markers['keystring'] = '';
        $options = '';
        for ( $i = -12; $i <= 12; $i++ ) {
            if ( $i < 1 )
                $value = $i . __('Hours');
            else
                $value = '+' . $i . __('Hours');
            if ( $i == $data['timezone'] )
                $options = $options . '<option value="'.$i.'" selected>'.$value.'</option>'."\n";
            else
                $options = $options . '<option value="'.$i.'">'.$value.'</option>'."\n";
        }
        $markers['options']  = $options;
        $markers['servertime']  = date( "d.m.Y H:i:s" );
        $markers['action']  = get_url('/users/add/');


        $markers['byears_selector'] = createOptionsFromParams(1970, 2008, $data['byear']);
        $markers['bmonth_selector'] = createOptionsFromParams(1, 12, $data['bmonth']);
        $markers['bday_selector'] = createOptionsFromParams(1, 31, $data['bday']);
        $markers = array_merge($data, $markers);


        $source = $this->render('addnewuserform.html', array('context' => $markers));
		return $this->_view($source);
	}





	/**
	 * Write into base and check data. Also work for additional fields.
	 */
	public function add()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', '1');
		if (!empty($_SESSION['user']['id'])) redirect('/');
	
		// Если не переданы данные формы - значит функция была вызвана по ошибке
		if ( !isset($_POST['login']) or
			!isset($_POST['password']) or
			!isset($_POST['confirm']) or
			!isset($_POST['email']) or
			!isset($_POST['keystring'])
		) {
			redirect('/users/add_form/yes');
		}
        $error = '';


		// Обрезаем переменные до длины, указанной в параметре maxlength тега input
		$fields = array(
			'login', 
			'password', 
			'confirm', 
			'email', 
			'icq', 
			'jabber', 
			'pol', 
			'city', 
			'telephone', 
			'byear', 
			'bmonth', 
			'bday', 
			'url', 
			'about', 
			'signature', 
			'keystring'
		);
		
		$fields_settings = (array)$this->Register['Config']->read('fields', 'users');
		$fields_settings = array_merge($fields_settings, array('email', 'login', 'password', 'confirm'));

		
		foreach ($fields as $field) {
			if (empty($_POST[$field]) && in_array($field, $fields_settings)) {
				$error = $error.'<li>' . __('Empty field "'.$field.'"') . '</li>'."\n";
				$$field = null;
				
			} else {
				$$field = (isset($_POST[$field])) ? trim($_POST[$field]) : '';
			}
		}
		
		
		if ('1' === $pol) $pol =  'm';
		else if ('2' === $pol) $pol = 'f';
		else $pol = '';

		
	
		// Обрезаем переменные до длины, указанной в параметре maxlength тега input
		$name    	  = mb_substr($login, 0, 30);
		$password     = mb_substr($password, 0, 30);
		$confirm      = mb_substr($confirm, 0, 30);
		$email        = mb_substr($email, 0, 60);
		$icq          = mb_substr($icq, 0, 12);
		$jabber    	  = mb_substr($jabber, 0, 100);
		$city	      = mb_substr($city, 0, 50);
		$telephone    = (!empty($telephone)) ? number_format(mb_substr($telephone, 0, 20), 0, '', '') : '';
		$byear	      = intval(mb_substr($byear, 0, 4));
		$bmonth	      = intval(mb_substr($bmonth, 0, 2));
		$bday	      = intval(mb_substr($bday, 0, 2));
		$url          = mb_substr($url, 0, 60);
		$about        = mb_substr($about, 0, 1000);
		$signature    = mb_substr($signature, 0, 500);



		// Проверяем, заполнены ли обязательные поля
		// Additional fields checker
		if (is_object($this->AddFields)) {
			$_addFields = $this->AddFields->checkFields();
			if (is_string($_addFields)) $error .= $_addFields; 
		}
		
		
		$valobj = $this->Register['Validate'];
		
		/*
		if ( empty( $name ) )               		
			$error = $error.'<li>' . __('Empty field "login"') . '</li>'."\n";
		if ( empty( $password ) )                 
			$error = $error.'<li>' . __('Empty field "password"') . '</li>'."\n";
		if ( empty( $confirm ) )                  	
			$error = $error.'<li>' . __('Empty field "confirm"') . '</li>'."\n";
		if ( empty( $email ) )                    	
			$error = $error.'<li>' . __('Empty field "email"') . '</li>'."\n";
		if ( empty( $keystring ) ) 					
			$error = $error.'<li>' . __('Empty field "code"') . '</li>'."\n";
		*/
		
		// check login
		if (!empty($name) and mb_strlen($name) < 3 || mb_strlen($name) > 20)
			$error = $error.'<li>' . __('Wrong "name" lenght') . '</li>'."\n";
			
		// Проверяем, не слишком ли короткий пароль
		if (!empty($password) and mb_strlen($password) < $this->Register['Config']->read('min_password_lenght'))
			$error = $error.'<li>' . sprintf(__('Very short pass'), $this->Register['Config']->read('min_password_lenght')) . '</li>'."\n";
		// Проверяем, совпадают ли пароли
		if (!empty($password) and !empty($confirm) and $password != $confirm)
			$error = $error.'<li>' . __('Passwords are different') . '</li>'."\n";
	
	// Проверяем поле "код"
		if (!empty($keystring)) {
			// Проверяем поле "код" на недопустимые символы
			if (!$valobj->cha_val($keystring, V_CAPTCHA))
				$error = $error.'<li>' . __('Wrong chars in field "code"') . '</li>'."\n";
													
			if (!isset($_SESSION['captcha_keystring'])) {
				if (file_exists(ROOT . '/sys/logs/captcha_keystring_' . session_id() . '-' . date("Y-m-d") . '.dat')) {
					$_SESSION['captcha_keystring'] = file_get_contents(ROOT . '/sys/logs/captcha_keystring_'
					. session_id() . '-' . date("Y-m-d") . '.dat');
				}
			}
			if (!isset($_SESSION['captcha_keystring']) || $_SESSION['captcha_keystring'] != $keystring)
				$error = $error.'<li>' . __('Wrong protection code') . '</li>'."\n";
		}
		unset($_SESSION['captcha_keystring']);

		
		// Проверяем поля формы на недопустимые символы
		if (!empty($name) and !$valobj->cha_val($name, V_LOGIN))          
			$error = $error.'<li>' . __('Wrong chars in field "login"') . '</li>'."\n";
		if (!empty($password) and !$valobj->cha_val($password, V_LOGIN))  
			$error = $error.'<li>' . __('Wrong chars in field "password"') . '</li>'."\n";
		if (!empty($confirm) and !$valobj->cha_val($confirm, V_LOGIN))   
			$error = $error.'<li>' . __('Wrong chars in field "confirm"') . '</li>'."\n";
		if (!empty($icq) and !$valobj->cha_val($icq, V_INT))              
			$error = $error.'<li>' . __('Wrong chars in field "ICQ"') . '</li>'."\n";
		if (!empty($about) and !$valobj->cha_val($about, V_TEXT))         
			$error = $error.'<li>' . __('Wrong chars in field "interes"') . '</li>'."\n";
		if (!empty($signature) and !$valobj->cha_val($signature, V_TEXT)) 
			$error = $error.'<li>' . __('Wrong chars in field "gignature"') . '</li>'."\n";
		// Проверяем корректность e-mail
		if (!empty($email) and !$valobj->cha_val($email, V_MAIL))
			$error = $error.'<li>' . __('Wrong chars in filed "e-mail"') . '</li>'."\n";
		// Проверяем корректность URL домашней странички
		if (!empty($url) and !$valobj->cha_val($url, V_URL))
			$error = $error.'<li>' . __('Wrong chars in filed "URL"') . '</li>'."\n";
		if (!empty($jabber) && !$valobj->cha_val($jabber, V_MAIL))
			$error = $error.'<li>' . __('Wrong chars in field "jabber"') . '</li>'."\n";
		if (!empty($city) && !$valobj->cha_val($city, V_LOGIN))
			$error = $error.'<li>' . __('Wrong chars in field "city"') . '</li>'."\n";
		if (!empty($telephone) && !$valobj->cha_val($telephone, V_INT))
			$error = $error.'<li>' . __('Wrong chars in field "telephone"') . '</li>'."\n";
		if (!empty($byear) && !$valobj->cha_val($byear, V_INT))
			$error = $error.'<li>' . __('Wrong chars in field "byear"') . '</li>'."\n";
		if (!empty($bmonth) && !$valobj->cha_val($bmonth, V_INT))
			$error = $error.'<li>' . __('Wrong chars in field "bmonth"') . '</li>'."\n";
		if (!empty($bday) && !$valobj->cha_val($bday, V_INT))
			$error = $error.'<li>' . __('Wrong chars in field "bday"') . '</li>'."\n";
			


		$new_name = preg_replace( "#[^- _0-9a-zА-Яа-я]#i", "", $name );
		// Формируем SQL-запрос
        $res = $this->Model->getSameNics($new_name);


		if (count($res) > 0) $error = $error.'<li>' . sprintf(__('Name already exists'), $new_name) . '</li>'."\n";
		
		/* check avatar */
		$tmp_key = rand(0, 9999999);
		if (!empty($_FILES['avatar']['name'])) {
			$path = ROOT . '/sys/tmp/images/' . $tmp_key . '.jpg';
			$ext = strrchr( $_FILES['avatar']['name'], "." );
			$extensions = array( ".jpg", ".gif", ".bmp", ".png", '.JPG', ".GIF", ".BMP", ".PNG");
			if (!in_array(strtolower($ext), $extensions)) {
				$error = $error.'<li>' . __('Wrong avatar') . '</li>'."\n";
				$check_image = true;
			}
			if ($_FILES['avatar']['size'] > $this->Register['Config']->read('max_avatar_size', 'users')) {
				$error = $error.'<li>'. sprintf(__('Avatar is very big')
				, ($this->Register['Config']->read('max_avatar_size', 'users') / 1024)) .'</li>'."\n";
				$check_image = true;
			}
			if (!isset($check_image) && move_uploaded_file($_FILES['avatar']['tmp_name'], $path)) {
				chmod($path, 0644);
				@$sizes = resampleImage($path, $path, 100);
				if (!$sizes) {
					@unlink($path);
					$error = $error.'<li>' . __('Some error in avatar') . '</li>'."\n";
				}
			} else {
				$error = $error.'<li>' . __('Some error in avatar') . '</li>'."\n";
			}
		}

		$timezone = (int)$_POST['timezone'];
		if ( $timezone < -12 or $timezone > 12 ) $timezone = 0;

		// Если были допущены ошибки при заполнении формы - перенаправляем посетителя на страницу регистрации
		if (!empty($error)) {
			$_SESSION['FpsForm'] = array_merge(array('login' => null, 'email'=> null, 'timezone' => null, 'icq' => null, 'url' => null, 'about' => null, 'signature' => null, 'pol' => $pol, 'telephone' => null, 'city' => null, 'jabber' => null, 'byear' => null, 'bmonth' => null, 'bday' => null), $_POST);
			$_SESSION['FpsForm']['error'] 	= '<p class="errorMsg">' . __('Some error in form') . '</p>'.
			"\n".'<ul class="errorMsg">'."\n".$error.'</ul>'."\n";
			redirect('/users/add_form/yes');
		}

		if (!empty($url) and substr($url, 0, 7) != 'http://') $url = 'http://' . $url;

		// Уникальный код для активации учетной записи
		$email_activate = $this->Register['Config']->read('email_activate');
		$code = (!empty($email_activate)) ? md5(uniqid(rand(), true)) : '';
		// Все поля заполнены правильно - продолжаем регистрацию
		$data = array(
			'name'  	=> $name,
			'passw' 	=> md5( $password ),
			'email' 	=> $email,
			'timezone' 	=> $timezone,
			'url' 		=> $url,
			'icq' 		=> $icq,
			'jabber' 	=> $jabber,
			'city' 		=> $city,
			'telephone' => $telephone,
			'pol'		=> $pol,
			'byear'		=> $byear,
			'bmonth'	=> $bmonth,
			'bday'		=> $bday,
			'about' 	=> $about,
			'signature' => $signature,
			'photo' 	=> '',
			'puttime' 	=> new Expr('NOW()'),
			'last_visit' => new Expr('NOW()'),
			'themes' 	=> 0,
			'status' 	=> 1,
			'activation' => $code
		);

        $entity = new UsersEntity($data);
        $entity->save();
		$id = mysql_insert_id();
		// Additional fields saver
		if (is_object($this->AddFields)) {
			$this->AddFields->save($id, $_addFields);
		}
		
		
		if (file_exists(ROOT . '/sys/tmp/images/' . $tmp_key . '.jpg')) {
			if (copy(ROOT . '/sys/tmp/images/' . $tmp_key . '.jpg', ROOT . '/sys/avatars/' . $id . '.jpg')) {
				chmod(ROOT . '/sys/avatars/' . $id . '.jpg', 0644 );
			}
			unlink(ROOT . '/sys/tmp/images/' . $tmp_key . '.jpg');
		}
		
		
		/* clean DB cache */
		$this->DB->cleanSqlCache();
		cleanAllUsersCount();
		
		
		// Activate by Email
		if (!empty($email_activate)) {
			// Посылаем письмо пользователю с просьбой активировать учетную запись
			$headers = "From: ".$_SERVER['SERVER_NAME']." <" . $this->Register['Config']->read('admin_email') . ">\n";
			$headers = $headers."Content-type: text/html; charset=\"utf-8\"\n";
			$headers = $headers."Return-path: <" . Config::read('admin_email') . ">\n";
			$message = '<p>Добро пожаловать на форум '.$_SERVER['SERVER_NAME'].'!</p>'."\n";
			$message = $message.'<p>Пожалуйста сохраните это сообщение. Параметры вашей учётной записи таковы:</p>'."\n";
			$message = $message.'<p>Логин: '.$name.'<br/>Пароль: '.$password.'</p>'."\n";
			$message = $message.'<p>Для активации вашей учетной записи перейдите по ссылке:</p>'."\n";
			$link = 'http://'.$_SERVER['SERVER_NAME'] . '/users/activate/'.$code;
			$message = $message.'<p><a href="'.$link.'">Активировать учетную запись</a></p>'."\n";
			$message = $message.'<p>Не забывайте свой пароль: он хранится в нашей базе в зашифрованном
					 виде, и мы не сможем вам его выслать. Если вы всё же забудете пароль, то сможете
					 запросить новый, который придётся активировать таким же образом, как и вашу
					 учётную запись.</p>'."\n";
			$message = $message.'<p>Спасибо за то, что зарегистрировались на нашем форуме.</p>'."\n";
			$subject = 'Регистрация на форуме '.$_SERVER['SERVER_NAME'];
			mail( $email, $subject, $message, $headers );
			
			if ($this->Log) $this->Log->write('adding user', 'user id(' . $id . ')');
			$msg = 'На Ваш e-mail выслано письмо с просьбой подтвердить регистрацию.
				  Чтобы завершить регистрацию и активировать учетную запись, зайдите
				  по адресу, указанному в письме.';
			
		} else { // Activate without Email
			$msg = __('Registration complete');
		}
		$source = $this->render('infomessage.html', array('info_message' => $msg));
		return $this->_view($source);
	}




	// Активация учетной записи нового пользователя
	public function activate($code = null)
    {
		// Если не передан параметр $code - значит функция вызвана по ошибке
		if (empty($code) || mb_strlen($code) !== 32) {
			redirect('/');
		}

		// Т.к. код зашифрован с помощью md5, то он представляет собой
		// 32-значное шестнадцатеричное число
		$code = substr( $code, 0, 32 );
		$code = preg_replace( "#[^0-9a-f]#i", '', $code );
		/* clean DB cache */
		$this->Register['DB']->cleanSqlCache();
		$res = $this->Model->getCollection(array('activation' => $code), array('limit' => 1));

		if (count($res) > 0 ) {
			$id = $res[0]->getId();
            $res[0]->setActivation('');
            $res[0]->setLast_visit(new Expr('NOW()'));
            $res[0]->save();
			if ($this->Log) $this->Log->write('activate user', 'user id(' . $id . ')');
			return $this->showInfoMessage(__('Account activated'), '/users/login_form/' );
		}
		if ($this->Log) $this->Log->write('wrong activate user', 'activate code(' . $code . ')');
		return $this->showInfoMessage(__('Wrong activation code'), '/');
	}




	/**
	 * Return form to request new password
	 *
	 */
    public function new_password_form()
    {
        $markers = array();
        if ( isset( $_SESSION['newPasswordForm']['error'] ) ) {
            $context = array(
                'info_message' =>  $_SESSION['newPasswordForm']['error'],
            );
            $markers['error'] = $this->render('infomessage.html', $context);
            unset( $_SESSION['newPasswordForm']['error'] );
        }


        $markers['action'] = get_url('/users/send_new_password/');
        $source = $this->render('newpasswordform.html', $markers);


        // Navigation PAnel
        $nav = array();
        $nav['navigation'] = get_link(__('Home'), '/') . __('Separator')
            . get_link(h($this->module_title), '/users/') . __('Separator') . __('Password repair');
        $this->_globalize($nav);


        return $this->_view($source);
    }





	// Функция высылает на e-mail пользователя новый пароль
	public function send_new_password()
    {

		// Если не переданы методом POST логин и e-mail - перенаправляем пользователя
		if ( !isset( $_POST['username'] ) || !isset( $_POST['email'])) {
			redirect('/');
		}

		// Обрезаем переменные до длины, указанной в параметре maxlength тега input
		$name  = mb_substr( $_POST['username'], 0, 30 );
		$email = mb_substr( $_POST['email'], 0, 60 );
		$name  = trim( $name );
		$email = trim( $email );

		// Проверяем, заполнены ли обязательные поля
		$error = '';
		$valobj = $this->Register['Validate'];
		if (empty($name))  	
			$error = $error.'<li>' . __('Empty field "login"') . '</li>'."\n";
		if (empty($email)) 	
			$error = $error.'<li>' . __('Empty field "email"') . '</li>'."\n";

		// Проверяем поля формы на недопустимые символы
		if (!empty($name) and !$valobj->cha_val($name, V_LOGIN) )
			$error = $error.'<li>' . __('Wrong chars in field "login"') . '</li>'."\n";
		// Проверяем корректность e-mail
		if ( !empty( $email ) and !$valobj->cha_val($email, V_MAIL))
			$error = $error.'<li>' . __('Wrong chars in filed "e-mail"') . '</li>'."\n";
		// Проверять существование такого пользователя есть смысл только в том
		// случае, если поля не пустые и не содержат недопустимых символов
		if ( empty( $error ) ) {
			touchDir(ROOT . '/sys/tmp/activate/');
		
			$res = $this->Model->getCollection(array('name' => $name, 'email' => $email));
			// Если пользователь с таким логином и e-mail существует
			if (count($res) > 0 && empty($error)) {
				// Небольшой код, который читает содержимое директории activate
				// и удаляет старые файлы для активации пароля (были созданы более суток назад)
				if ($dir = opendir( ROOT . '/sys/tmp/activate')) {
					$tmp = 24*60*60;
					while (false !== ($file = readdir($dir))) {
						if (is_file($file))
							if ((time() - filemtime($file)) > $tmp) unlink($file);
					}
					closedir($dir);
				}


				// Как происходит процедура восстановления пароля? Пользователь ввел свой логин
				// и e-mail, мы проверяем существование такого пользователя в таблице БД. Потом
				// генерируем с помощью функции getNewPassword() новый пароль, создаем файл с именем
				// md5( $newPassword ) в директории activate. Файл содержит ID пользователя.
				// В качестве кода активации выступает хэш пароля - md5( $newPassword ).
				// Когда пользователь перейдет по ссылке в письме для активации своего нового пароля,
				// мы проверяем наличие в директории activatePassword файла с именем кода активации,
				// и если он существует, активируем новый пароль.
				$user = $res[0];
                $id = $user->getId();
				$newPassword = $this->_getNewPassword();
				$code = md5($newPassword);
				// file_put_contents(ROOT . '/sys/tmp/activate/'.$code, $id );
				$fp = fopen( ROOT . '/sys/tmp/activate/' . $code, "w" );
				fwrite($fp, $id);
				fclose($fp);


				// Посылаем письмо пользователю с просьбой активировать пароль
				$headers = "From: " . $_SERVER['SERVER_NAME'] . " <" . $this->Register['Config']->read('admin_email') . ">\n";
				$headers = $headers."Content-type: text/html; charset=\"utf-8\"\n";
				$headers = $headers."Return-path: <" . $this->Register['Config']->read('admin_email') . ">\n";
				$message = '<p>Добрый день, '.$name.'!</p>'."\n";
				$message = $message.'<p>Вы получили это письмо потому, что вы (либо кто-то, выдающий себя
						 за вас) попросили выслать новый пароль к вашей учётной записи на форуме '.
						 $_SERVER['SERVER_NAME'].'. Если вы не просили выслать пароль, то не обращайте
						 внимания на это письмо, если же подобные письма будут продолжать приходить,
						 обратитесь к администратору форума</p>'."\n";
				$message = $message.'<p>Прежде чем использовать новый пароль, вы должны его активировать.
						 Для этого перейдите по ссылке:</p>'."\n";
				$link = 'http://'.$_SERVER['SERVER_NAME'] . '/users/activate_password/'.$code;
				$message = $message.'<p><a href="'.$link.'">Активировать пароль</a></p>'."\n";
				$message = $message.'<p>В случае успешной активации вы сможете входить в систему, используя
						 следующий пароль: '.$newPassword.'</p>'."\n";
				$message = $message.'<p>Вы сможете сменить этот пароль на странице редактирования профиля.
						 Если у вас возникнут какие-то трудности, обратитесь к администратору форума.</p>'."\n";
				$subject = 'Активация пароля на форуме '.$_SERVER['SERVER_NAME'];
				mail( $email, $subject, $message, $headers );

				$msg = __('We send mail to your e-mail');
                $source = $this->render('infomessage.html', array('info_message' => $msg));


                if ($this->Log) $this->Log->write('send new passw', 'name(' . $name . '), mail(' . $email . ')');
				return $this->_view($source);
			} else {
				$error = $error.'<li>' . __('Wrong login or email') . '</li>'."\n";
			}
		}
		
		
		/* clean DB cache */
		$this->Register['DB']->cleanSqlCache();
		if ($this->Log) $this->Log->write('wrong send new passw', 'name(' . $name . '), mail(' . $email . ')');
		// Если были допущены ошибки при заполнении формы - перенаправляем посетителя
		if (!empty($error)) {
			$_SESSION['newPasswordForm'] = array();
			$_SESSION['newPasswordForm']['error'] = '<p class="errorMsg">' . __('Some error in form') . '</p>'."\n"
                . '<ul class="errorMsg">' . "\n" . $error . '</ul>' . "\n";
			redirect('/users/new_password_form/');
		}

	}




	// Активация нового пароля
	public function activate_password($code = null)
    {
		if (!isset($code)) redirect('/');

		// Т.к. код активации создан с помощью md5, то он
		// представляет собой 32-значное шестнадцатеричное число
		$code = mb_substr( $code, 0, 32 );
		$code = preg_replace( "#[^0-9a-f]#i", '', $code );
		
		if (empty($code)) redirect('/');
		
		$f_path =  ROOT . '/sys/tmp/activate/' . $code;
		if (is_file($f_path) and  ((time() - filemtime($f_path)) < 24*60*60)) {
			$file = file($f_path);
			unlink($f_path);
			$id_user = (int)trim($file[0]);
            $user = $this->Model->getById($id_user);
            $user->setPassw($code);
            $user->save();
			$message = __('New pass is ready');
			if ($this->Log) $this->Log->write('activate new passw', 'user id(' . $id_user . ')');
		} else {
			$message = __('Error when activate new pass');
			if ($this->Log) $this->Log->write('wrong activate new passw', 'code(' . $code . ')');
		}
		
		$markers = array('info_message' => $message);
		$html = $this->render('infomessage.html', $markers);
		return $this->_view($html);
	}





	// Функция возвращает случайно сгенерированный пароль
	private function _getNewPassword()
    {
		$length = rand( 10, 30 );
		$password = '';
		for( $i = 0; $i < $length; $i++ ) {
			$range = rand(1, 3);
			switch( $range ) {
				case 1: $password = $password.chr( rand(48, 57) );  break;
				case 2: $password = $password.chr( rand(65, 90) );  break;
				case 3: $password = $password.chr( rand(97, 122) ); break;
			}
		}
		return $password;
	}




	// Функция возвращает html формы для редактирования данных о пользователе
	public function edit_form()
    {
		if (!isset($_SESSION['user'])) redirect('/');

		
		//turn access
		$this->ACL->turn(array('users', 'edit_mine'));


        $anket = $this->Model->getById((int)$_SESSION['user']['id']);
		if (is_object($this->AddFields) && $anket) {
			$anket = $this->AddFields->mergeRecords(array($anket), true);
            $anket = $anket[0];
		}


        // Check for preview or errors
        $data = array('email' => null, 'timezone' => null, 'icq' => null, 'jabber' => null, 'pol' => null, 'city' => null, 'telephone' => null, 'byear' => null, 'bmonth' => null, 'bday' => null, 'url' => null, 'about' => null, 'signature' => null);
        //$data = array_merge($data, $anket);
        $data = Validate::getCurrentInputsValues($anket, $data);

        $errors = $this->Parser->getErrors();
        if (isset($_SESSION['FpsForm'])) unset($_SESSION['FpsForm']);
        if (!empty($errors)) $data->setError($errors);


	
        $fpol = ($data->getPol() && $data->getPol() === 'f') ? ' checked="checked"' : '';
        $data->setFpol($fpol);
        $mpol = ($data->getPol() && $data->getPol() === 'm') ? ' checked="checked"' : '';
        $data->setMpol($mpol);
		
		
        $data->setAction(get_url('/users/update/'));
        if ($data->getPol() === 'f') $data->setPol(__('f'));
        else if ($data->getPol() === 'm') $data->setPol(__('m'));
        else $data->setPol(__('no gender'));
		


        if (file_exists(ROOT . '/sys/avatars/' . $anket->getId() . '.jpg')) {
            $data->setAvatar(get_url('/sys/avatars/' . $anket->getId() . '.jpg'));
        } else {
            $data->setAvatar(get_url('/sys/img/noavatar.png'));
        }


        $options = '';
        for ( $i = -12; $i <= 12; $i++ ) {
            if ( $i < 1 )
                $value = $i.' часов';
            else
                $value = '+'.$i.' часов';
            if (isset($_SESSION['user']['timezone']) && $i == $_SESSION['user']['timezone'] )
                $options = $options . '<option value="'.$i.'" selected>'.$value.'</option>'."\n";
            else
                $options = $options . '<option value="'.$i.'">'.$value.'</option>'."\n";
        }
        $data->setOptions($options);
        $data->setServertime(date( "d.m.Y H:i:s" ));
        $data->setByears_selector(createOptionsFromParams(1950, 2008, $data->getByear()));
        $data->setBmonth_selector(createOptionsFromParams(1, 12, $data->getBmonth()));
        $data->setBday_selector(createOptionsFromParams(1, 31, $data->getBday()));


        $unlinkfile = '';
        if (is_file(ROOT . '/sys/avatars/' . $_SESSION['user']['id'] . '.jpg')) {
            $unlinkfile = '<input type="checkbox" name="unlink" value="1" />'
                . __('Are you want delete file') . "\n";
        }
        $data->setUnlinkfile($unlinkfile);
       

        $source = $this->render('edituserform.html', array('context' => $data));

		
		// Navigation Panel
		$navi = array();
		$navi['navigation'] = get_link(__('Home'), '/') . __('Separator')
			. get_link(h($this->module_title), '/users/') . __('Separator') . __('Editing');
		$this->_globalize($navi);
		
		return $this->_view($source);
	}




	/**
	 * Update record into Data Base
	 */
	public function update()
    {
		if ( !isset( $_SESSION['user'] ) ) redirect('/');

		//turn access
		$this->ACL->turn(array('users', 'edit_mine'));

		// Если не переданы данные формы - функция вызвана по ошибке
		if ( !isset( $_POST['password'] ) or
			!isset( $_POST['newpassword'] ) or
			!isset( $_POST['confirm'] ) or
			!isset( $_POST['email'] ) or
			!isset( $_POST['timezone'] )
		) {
			redirect('/');
		}
		
		
		$error = '';
        $markers = array();
		
		
		$fields = array(
			'email', 
			'icq', 
			'jabber', 
			'pol', 
			'city', 
			'telephone', 
			'byear', 
			'bmonth', 
			'bday', 
			'url', 
			'about', 
			'signature'
		);
		
		$fields_settings = (array)$this->Register['Config']->read('fields', 'users');
		$fields_settings = array_merge($fields_settings, array('email'));
		
		
		foreach ($fields as $field) {
			if (empty($_POST[$field]) && in_array($field, $fields_settings)) {
				$error = $error.'<li>' . __('Empty field "'.$field.'"') . '</li>'."\n";
				$$field = null;
				
			} else {
				$$field = (isset($_POST[$field])) ? trim($_POST[$field]) : '';
			}
		}
		
		
		
		if ('1' === $pol) $pol =  'm';
		else if ('2' === $pol) $pol = 'f';
		else $pol = '';
		

		
		// Обрезаем лишние пробелы
		$password     = (!empty($_POST['password'])) ? trim($_POST['password']) : '';
		$newpassword  = (!empty($_POST['newpassword'])) ? trim($_POST['newpassword']) : '';
		$confirm      = (!empty($_POST['confirm'])) ? trim($_POST['confirm']) : '';

		
		// Обрезаем переменные до длины, указанной в параметре maxlength тега input
		$password     = mb_substr($password, 0, 30);
		$newpassword  = mb_substr($newpassword, 0, 30);
		$confirm      = mb_substr($confirm, 0, 30);
		$email        = mb_substr($email, 0, 60);
		$icq          = mb_substr($icq, 0, 12);
		$jabber    	  = mb_substr($jabber, 0, 100);
		$city	      = mb_substr($city, 0, 50);
		$telephone    = number_format(mb_substr((int)$telephone, 0, 20), 0, '', '');
		$byear	      = intval(mb_substr($byear, 0, 4));
		$bmonth	      = intval(mb_substr($bmonth, 0, 2));
		$bday	      = intval(mb_substr($bday, 0, 2));
		$url          = mb_substr($url, 0, 60);
		$about        = mb_substr($about, 0, 1000);
		$signature    = mb_substr($signature, 0, 500);


		// Additional fields
		if (is_object($this->AddFields)) {
			$_addFields = $this->AddFields->checkFields();
			if (is_string($_addFields)) $markers['error'] = $_addFields;
		}
		
		$valobj = $this->Register['Validate'];
		// Если заполнено поле "Текущий пароль" - значит пользователь
		// хочет изменить его или поменять свой e-mail
		$changePassword = false;
		$changeEmail = false;
		if (!empty($password)) {
			if ( md5($password) != $_SESSION['user']['passw'] ) 
				$error = $error.'<li>' . __('Wrong current pass') . '</li>'."\n";
			// Надо выяснить, что хочет сделать пользователь:
			// поменять свой e-mail, изменить пароль или и то и другое
			if (!empty($newpassword)) { // хочет изменить пароль
				$changePassword = true;
				if (empty($confirm)) 	
					$error = $error.'<li>' . __('Empty field "confirm"') . '</li>'."\n";
				if (strlen($newpassword) < $this->Register['Config']->read('min_password_lenght'))
					$error = $error.'<li>'. sprintf(__('Very short pass'), Config::read('min_password_lenght')) . '</li>'."\n";
				if (!empty($confirm) and $newpassword != $confirm)
					$error = $error.'<li>' . __('Passwords are different') . '</li>'."\n";
				if (!$valobj->cha_val($newpassword, V_LOGIN))
					$error = $error.'<li>' . __('Wrong chars in field "password"') . '</li>'."\n";
				if (!empty($confirm) and !$valobj->cha_val($confirm, V_LOGIN))
					$error = $error.'<li>' . __('Wrong chars in field "confirm"') . '</li>'."\n";
			}
			if ($email != $_SESSION['user']['email']) { // хочет изменить e-mail
				$changeEmail = true;
				if (!empty($email) and !$valobj->cha_val($email, V_MAIL))
					$error = $error.'<li>' . __('Wrong chars in filed "e-mail"') . '</li>'."\n";
			}
		}
		if (!empty($icq) and !$valobj->cha_val($icq, V_INT))
			$error = $error.'<li>' . __('Wrong chars in field "ICQ"') . '</li>'."\n";
		if (!empty($about) and !$valobj->cha_val($about, V_TEXT))
			$error = $error.'<li>' . __('Wrong chars in field "interes"') . '</li>'."\n";
		if (!empty($signature) and !$valobj->cha_val($signature, V_TEXT))
			$error = $error.'<li>' . __('Wrong chars in field "gignature"') . '</li>'."\n";
		if (!empty($url) and !$valobj->cha_val($url, V_URL))
			$error = $error.'<li>' . __('Wrong chars in filed "URL"') . '</li>'."\n";
		if (!empty($jabber) && !$valobj->cha_val($jabber, V_MAIL))
			$error = $error.'<li>' . __('Wrong chars in field "jabber"') . '</li>'."\n";
		if (!empty($city) && !$valobj->cha_val($city, V_LOGIN))
			$error = $error.'<li>' . __('Wrong chars in field "city"') . '</li>'."\n";
		if (!empty($telephone) && !$valobj->cha_val($telephone, V_INT))
			$error = $error.'<li>' . __('Wrong chars in field "telephone"') . '</li>'."\n";
		if (!empty($byear) && !$valobj->cha_val($byear, V_INT))
			$error = $error.'<li>' . __('Wrong chars in field "byear"') . '</li>'."\n";
		if (!empty($bmonth) && !$valobj->cha_val($bmonth, V_INT))
			$error = $error.'<li>' . __('Wrong chars in field "bmonth"') . '</li>'."\n";
		if (!empty($bday) && !$valobj->cha_val($bday, V_INT))
			$error = $error.'<li>' . __('Wrong chars in field "bday"') . '</li>'."\n";
		
		
		$tmp_key = rand(0, 9999999);
		if (!empty($_FILES['avatar']['name'])) {
			touchDir(ROOT . '/sys/tmp/images/', 0777);
		
			$path = ROOT . '/sys/tmp/images/' . $tmp_key . '.jpg';
			$ext = strrchr( $_FILES['avatar']['name'], "." );
			$extensions = array( ".jpg", ".gif", ".bmp", ".png", '.JPG', ".GIF", ".BMP", ".PNG");
			if (!in_array($ext, $extensions)) {
				$error = $error.'<li>' . __('Wrong avatar') . '</li>'."\n";
				$check_image = true;
			}
			if ($_FILES['avatar']['size'] > $this->Register['Config']->read('max_avatar_size', 'users')) {
				$error = $error.'<li>'. sprintf(__('Avatar is very big'), Config::read('max_avatar_size', 'users')).'</li>'."\n";
				$check_image = true;
			}
			if (!isset($check_image) && move_uploaded_file($_FILES['avatar']['tmp_name'], $path)) {
				chmod($path, 0644);
				@$sizes = resampleImage($path, $path, 100);
				if (!$sizes) {
					@unlink($path);
					$error = $error.'<li>' . __('Some error in avatar') . '</li>'."\n";
				}
			} else {
				$error = $error.'<li>' . __('Some error in avatar') . '</li>'."\n";
			}
		}

		$timezone = (int)$_POST['timezone'];
		if ($timezone < -12 || $timezone > 12) $timezone = 0;

		
		// if an Errors
		if (!empty($error)) {
			$_SESSION['FpsForm'] = array_merge(array('login' => null, 'email'=> null, 'timezone' => null, 'icq' => null, 'url' => null, 'about' => null, 'signature' => null, 'pol' => $pol, 'telephone' => null, 'city' => null, 'jabber' => null, 'byear' => null, 'bmonth' => null, 'bday' => null), $_POST);
			$_SESSION['FpsForm']['error']     = '<p class="errorMsg">' . __('Some error in form') . '</p>'.
			"\n".'<ul class="errorMsg">'."\n".$error.'</ul>'."\n";
			redirect('/users/edit_form/');
		}

		// Если выставлен флажок "Удалить загруженный ранее файл"
		if (isset( $_POST['unlink']) and is_file(ROOT . '/sys/avatars/' . $_SESSION['user']['id'] . '.jpg')) {
			unlink(ROOT . '/sys/avatars/' . $_SESSION['user']['id'] . '.jpg');
		}
		/* copy and delete tmp image */
		if (file_exists(ROOT . '/sys/tmp/images/' . $tmp_key . '.jpg')) {
			if (copy(ROOT . '/sys/tmp/images/' . $tmp_key . '.jpg', ROOT . '/sys/avatars/' . $_SESSION['user']['id'] . '.jpg')) {
				chmod( ROOT . '/sys/avatars/' . $_SESSION['user']['id'] . '.jpg', 0644 );
			}
			unlink(ROOT . '/sys/tmp/images/' . $tmp_key . '.jpg');
		}

		// Все поля заполнены правильно - записываем изменения в БД
		if (mb_substr($url, 0, mb_strlen('http://')) !== 'http://') $url = 'http://' . $url;


        $user = $this->Model->getById($_SESSION['user']['id']);

		$user_data = array();
		if ( $changePassword ) {
			$user->setPassw(md5($newpassword));
			$_SESSION['user']['passw'] = md5( $newpassword );
		}
		if ( $changeEmail ) {
			$user->setEmail($email);
			$_SESSION['user']['email'] = $email;
		}
        $user->setTimezone($timezone);
        $user->setUrl($url);
        $user->setIcq($icq);
        $user->setJabber($jabber);
        $user->setCity($city);
        $user->setTelephone($telephone);
        $user->setPol($pol);
        $user->setByear($byear);
        $user->setBmonth($bmonth);
        $user->setBday($bday);
        $user->setAbout($about);
        $user->setSignature($signature);
        $user->save();

		// Additional fields saving
		if (is_object($this->AddFields)) {
			$this->AddFields->save($_SESSION['user']['id'], $_addFields);
		}
		
		
		// Теперь надо обновить данные о пользователе в массиве $_SESSION['user']
		if ( $changePassword ) $_SESSION['user']['passw'] = md5( $newpassword );
		if ( $changeEmail ) $_SESSION['user']['email'] = $email;
		//$_SESSION['user'] = array_merge($_SESSION['user'], $user_data);
		
		
		// ... и в массиве $_COOKIE
		if ( isset( $_COOKIE['autologin'] ) ) {
			$path   = "/";
			setcookie( 'autologin', 'yes', time() + 3600 * 24 * Config::read('cookie_time'), $path );
			setcookie( 'userid', $_SESSION['user']['id'], time() + 3600 * 24 * Config::read('cookie_time'), $path );
			setcookie( 'password', $_SESSION['user']['passw'], time() + 3600 * 24 * Config::read('cookie_time'), $path );
		}
		if ($this->Log) $this->Log->write('editing user', 'user id(' . $_SESSION['user']['id'] . ')');
		return $this->showInfoMessage(__('Your profile has been changed'), getProfileUrl($_SESSION['user']['id']));
	}




	/**
	 * Edit form by admin
	 */
	public function edit_form_by_admin($id = null)
    {
		//turn access
		$this->ACL->turn(array('users', 'edit_users'));
		$id = (int)$id;
		if ( $id < 1 ) redirect('/users/');
		if (!isset($_SESSION['user'])) redirect('/');

		$statusArray = $this->Register['ACL']->get_group_info();
		if (!empty($statusArray)) unset($statusArray[0]);
		$markers = array();


		// Получаем данные о пользователе из БД
        $user = $this->Model->getById($id);
        if (count($user) == 0) return $this->showInfoMessage(__('Can not find user'), '/users/' );
        if (is_object($this->AddFields) && count($user) > 0) {
            $user = $this->AddFields->mergeRecords(array($user), true);
            $user = $user[0];
        }


        // Check for preview or errors
        $data = array('login' => null, 'email' => null, 'timezone' => null, 'icq' => null, 'jabber' => null
        , 'pol' => null, 'city' => null, 'telephone' => null, 'byear' => null, 'bmonth' => null, 'bday' => null
        , 'url' => null, 'about' => null, 'signature' => null);
        $data = Validate::getCurrentInputsValues($user, $data);
        $name = $data->getName();
        //pr($data); die();

        $errors = $this->Parser->getErrors();
        if (isset($_SESSION['FpsForm'])) unset($_SESSION['FpsForm']);
        if (!empty($errors)) $data->setError($errors);



		
        $fpol = ($data->getPol() && $data->getPol() === 'f' || $data->getPol() === '2') ? ' checked="checked"' : '';
        $data->setFpol($fpol);
        $mpol = ($data->getPol() && $data->getPol() === 'm' || $data->getPol() === '1') ? ' checked="checked"' : '';
        $data->setMpol($mpol);
		
		
        $data->setAction(get_url('/users/update_by_admin/' . $id));
        if ($data->getPol() === 'f') $data->setPol(__('f'));
        else if ($data->getPol() === 'm') $data->setPol(__('m'));
        else $data->setPol(__('no gender'));
		


        if (file_exists(ROOT . '/sys/avatars/' . $data->getId() . '.jpg')) {
            $data->setAvatar(get_url('/sys/avatars/' . $data->getId() . '.jpg'));
        } else {
            $data->setAvatar(get_url('/sys/img/noavatar.png'));
        }

		
        $options = '';
        for ($i = -12; $i <= 12; $i++) {
			
			if ($i < 1)
                $value = $i . __('Hours');
            else
                $value = '+' . $i .  __('Hours');
				
				
            if (($data->getTimezone() && $i == $data->getTimezone()) || ($i == 0 && !$data->getTimezone()))
                $options = $options . '<option value="' . $i . '" selected>' . $value . '</option>' . "\n";
            else
                $options = $options . '<option value="' . $i . '">' . $value . '</option>' . "\n";
        }
		
        $data->setOptions($options);
        $data->setServertime(date( "d.m.Y H:i:s" ));
		
		
        $data->setByears_selector(createOptionsFromParams(1950, 2008, $data->getByear()));
        $data->setBmonth_selector(createOptionsFromParams(1, 12, $data->getBmonth()));
        $data->setBday_selector(createOptionsFromParams(1, 31, $data->getBday()));


        $unlinkfile = '';
        if (is_file(ROOT . '/sys/avatars/' . $_SESSION['user']['id'] . '.jpg')) {
            $unlinkfile = '<input type="checkbox" name="unlink" value="1" />'
                . __('Are you want delete file') . "\n";
        }
        $data->setUnlinkfile($unlinkfile);


        $userStatus = '<select name="status">'."\n";
        foreach( $statusArray as $key => $value ) {
            if ($key == $data->getStatus())
                $userStatus = $userStatus . '<option value="' . $key . '" selected>' . $value['title'] . '</option>'."\n";
            else
                $userStatus = $userStatus . '<option value="' . $key . '">' . $value['title'] . '</option>'."\n";
        }
        $userStatus = $userStatus . '</select>' . "\n";
        $data->setStatus($userStatus);
        $data->setOldemail(h($user->getEmail()));
        $data->setLogin($name);


        $activation = ($user->getActivation())
            ? __('Activate') . ' <input name="activation" type="checkbox" value="1" >' : __('Active');
        $data->setActivation($activation);

		
		// Navigation Panel
		$nav = array();
		$nav['navigation'] = get_link(__('Home'), '/') . __('Separator')
			. get_link(h($this->module_title), '/users/') . __('Separator') . __('Editing');
		$this->_globalize($nav);

		
        $source = $this->render('edituserformbyadmin.html', array('context' => $data));
		
		return $this->_view($source);
	}




	// Функция обновляет данные пользователя (только для администратора форума)
	public function update_by_admin($id = null)
    {
		//turn access
		$this->ACL->turn(array('users', 'edit_users'));
		$id = (int)$id;
		// ID зарегистрированного пользователя не может быть меньше
		// единицы - значит функция вызвана по ошибке
		if ($id < 1) redirect('/users/' );
		// Если профиль пытается редактировать не зарегистрированный
		// пользователь - функция вызвана по ошибке
		if (!isset($_SESSION['user'])) redirect( '/');


		
		// Если не переданы данные формы - функция вызвана по ошибке
		if (!isset( $_POST['status'] ) or
			!isset( $_POST['email'] ) or
			!isset( $_POST['oldEmail'] ) or
			!isset( $_POST['newpassword'] ) or
			!isset( $_POST['confirm'] )
		) {
			redirect('/');
		}

		
		// Получаем данные о пользователе из БД
        $user = $this->Model->getById($id);
        if (!$user) return $this->showInfoMessage(__('Can not find user'), '/users/' );
        if (is_object($this->AddFields) && $user) {
            $user = $this->AddFields->mergeRecords(array($user), true);
            $user = $user[0];
        }



		$error = '';
		$fields = array(
			'email', 
			'oldEmail', 
			'icq', 
			'jabber', 
			'pol', 
			'city', 
			'telephone', 
			'byear', 
			'bmonth',
			'bday', 
			'url', 
			'about', 
			'signature'
		);
		
		$fields_settings = (array)$this->Register['Config']->read('fields', 'users');
		$fields_settings = array_merge($fields_settings, array('email'));
		
		foreach ($fields as $field) {
			if (empty($_POST[$field]) && in_array($field, $fields_settings)) {
				$error = $error.'<li>' . __('Empty field "'.$field.'"') . '</li>'."\n";
				$$field = null;
			} else {
				$$field = (isset($_POST[$field])) ? trim($_POST[$field]) : '';
			}
		}
		
		
		if ('1' === $pol) $pol =  'm';
		else if ('2' === $pol) $pol = 'f';
		else $pol = '';
		

		
		// Обрезаем лишние пробелы
		$password     = (!empty($_POST['password'])) ? trim($_POST['password']) : '';
		$newpassword  = (!empty($_POST['newpassword'])) ? trim($_POST['newpassword']) : '';
		$confirm      = (!empty($_POST['confirm'])) ? trim($_POST['confirm']) : '';

		
		// Обрезаем переменные до длины, указанной в параметре maxlength тега input
		$password     = mb_substr($password, 0, 30);
		$newpassword  = mb_substr($newpassword, 0, 30);
		$confirm      = mb_substr($confirm, 0, 30);
		$email        = mb_substr($email, 0, 60);
		$oldEmail     = mb_substr($user->getEmail(), 0, 60);
		$icq          = mb_substr($icq, 0, 12);
		$jabber    	  = mb_substr($jabber, 0, 100);
		$city	      = mb_substr($city, 0, 50);
		$telephone    = number_format(mb_substr((int)$telephone, 0, 20), 0, '', '');
		$byear	      = intval(mb_substr($byear, 0, 4));
		$bmonth	      = intval(mb_substr($bmonth, 0, 2));
		$bday	      = intval(mb_substr($bday, 0, 2));
		$url          = mb_substr($url, 0, 60);
		$about        = mb_substr($about, 0, 1000);
		$signature    = mb_substr($signature, 0, 500);
		


		// Additional fields
		if (is_object($this->AddFields)) {
			$_addFields = $this->AddFields->checkFields();
			if (is_string($_addFields)) $error .= $_addFields; 
		}
		
		$valobj = $this->Register['Validate'];
		// Надо выяснить, что хочет сделать администратор:
		// поменять e-mail, изменить пароль или и то и другое
		$changePassword = false;
		$changeEmail = false;

		if ( !empty( $newpassword ) ) { // хочет изменить пароль
			$changePassword = true;
			if ( empty( $confirm ) )    
				$error = $error.'<li>' . __('Empty field "confirm"') . '</li>'."\n";
			if (strlen($newpassword) < $this->Register['Config']->read('min_password_lenght'))
				$error = $error.'<li>' . sprintf(__('Very short pass'), $this->Register['Config']->read('min_password_lenght')).'</li>'."\n";
			if (!empty($confirm) and $newpassword != $confirm)
				$error = $error.'<li>' . __('Passwords are different') . '</li>'."\n";
			if ( !$valobj->cha_val($newpassword, V_LOGIN))
				$error = $error.'<li>' . __('Wrong chars in field "password"') . '</li>'."\n";
			if (!empty($confirm) and !$valobj->cha_val($confirm, V_LOGIN))
				$error = $error.'<li>' . __('Wrong chars in field "confirm"') . '</li>'."\n";
		}
		if (!empty($email) && $email != $oldEmail) { // хочет изменить e-mail
			$changeEmail = true;
			if (empty($email)) 		 	
				$error = $error.'<li>' . __('Empty field "email"') . '</li>'."\n";
			if ( !empty( $email ) and !$valobj->cha_val($email, V_MAIL))
				$error = $error.'<li>' . __('Wrong chars in filed "e-mail"') . '</li>'."\n";
		}

		
		// Проверяем поля формы на недопустимые символы
		if (!empty($icq) and !$valobj->cha_val($icq, V_INT))
			$error = $error.'<li>' . __('Wrong chars in field "ICQ"') . '</li>'."\n";
		if (!empty($about) and !$valobj->cha_val($about, V_TEXT))
			$error = $error.'<li>' . __('Wrong chars in field "interes"') . '</li>'."\n";
		if (!empty($signature) and !$valobj->cha_val($signature, V_TEXT))
			$error = $error.'<li>' . __('Wrong chars in field "gignature"') . '</li>'."\n";
		if (!empty($url) and !$valobj->cha_val($url, V_URL))
			$error = $error.'<li>' . __('Wrong chars in filed "URL"') . '</li>'."\n";
		if (!empty($jabber) && !$valobj->cha_val($jabber, V_MAIL))
			$error = $error.'<li>' . __('Wrong chars in field "jabber"') . '</li>'."\n";
		if (!empty($city) && !$valobj->cha_val($city, V_LOGIN))
			$error = $error.'<li>' . __('Wrong chars in field "city"') . '</li>'."\n";
		if (!empty($telephone) && !$valobj->cha_val($telephone, V_INT))
			$error = $error.'<li>' . __('Wrong chars in field "telephone"') . '</li>'."\n";
		if (!empty($byear) && !$valobj->cha_val($byear, V_INT))
			$error = $error.'<li>' . __('Wrong chars in field "byear"') . '</li>'."\n";
		if (!empty($bmonth) && !$valobj->cha_val($bmonth, V_INT))
			$error = $error.'<li>' . __('Wrong chars in field "bmonth"') . '</li>'."\n";
		if (!empty($bday) && !$valobj->cha_val($bday, V_INT))
			$error = $error.'<li>' . __('Wrong chars in field "bday"') . '</li>'."\n";
		
		
		$tmp_key = rand(0, 9999999);
		if (!empty($_FILES['avatar']['name'])) {
			touchDir(ROOT . '/sys/tmp/images/', 0777);
		
			$path = ROOT . '/sys/tmp/images/' . $tmp_key . '.jpg';
			$ext = strrchr( $_FILES['avatar']['name'], "." );
			$extensions = array( ".jpg", ".gif", ".bmp", ".png", '.JPG', ".GIF", ".BMP", ".PNG");
			if (!in_array(strtolower($ext), $extensions)) {
				$error = $error.'<li>' . __('Wrong avatar') . '</li>'."\n";
				$check_image = true;
			}
			if ($_FILES['avatar']['size'] > $this->Register['Config']->read('max_avatar_size', 'users')) {
				$error = $error.'<li>'. sprintf(__('Avatar is very big'), $this->Register['Config']->read('max_avatar_size', 'users')).'</li>'."\n";
				$check_image = true;
			}
			if (!isset($check_image) && move_uploaded_file($_FILES['avatar']['tmp_name'], $path)) {
				chmod($path, 0644);
				@$sizes = resampleImage($path, $path, 100);
				if (!$sizes) {
					@unlink($path);
					$error = $error.'<li>' . __('Some error in avatar') . '</li>'."\n";
				}
			} else {
				$error = $error.'<li>' . __('Some error in avatar') . '</li>'."\n";
			}
		}


		$status = (int)$_POST['status'];
		$timezone = (int)$_POST['timezone'];
		if ( $timezone < -12 or $timezone > 12 ) $timezone = 0;

		// Errors
		if (!empty($error)) {
			$_SESSION['FpsForm'] = array_merge(
                array(
                    'name' => null,
                    'status' => null,
                    'email' => null,
                    'timezone' => null,
                    'icq' => null,
                    'url' => null,
                    'about' => null,
                    'signature' => null,
                    'pol' => $pol,
                    'telephone' => null,
                    'city' => null,
                    'jabber' => null,
                    'byear' => null,
                    'bmonth' => null,
                    'bday' => null),
                $_POST
            );
			$_SESSION['FpsForm']['error'] = '<p class="errorMsg">' . __('Some error in form') . '</p>'.
			"\n".'<ul class="errorMsg">'."\n".$error.'</ul>'."\n";
			redirect('/users/edit_form_by_admin/' . $id );
		}

		// Если выставлен флажок "Удалить загруженный ранее файл"
		if (isset($_POST['unlink']) and is_file(ROOT . '/sys/avatars/' . $id . '.jpg')) {
			unlink(ROOT . '/sys/avatars/' . $id . '.jpg');
		}
		if (file_exists(ROOT . '/sys/tmp/images/' . $tmp_key . '.jpg')) {
			if (copy(ROOT . '/sys/tmp/images/' . $tmp_key . '.jpg', ROOT . '/sys/avatars/' . $id . '.jpg')) {
				chmod(ROOT . '/sys/avatars/' . $id . '.jpg', 0644 );
			}
			unlink(ROOT . '/sys/tmp/images/' . $tmp_key . '.jpg');
		}



		// Все поля заполнены правильно - записываем изменения в БД
		if ( $changePassword ) {
			$user->setPassw(md5($newpassword));
		}
		if ( $changeEmail ) {
			$user->setEmail($email);
		}
		if (isset($_POST['activation'])) {
			$user->setActivation('');
		}
        $user->setStatus($status);
        $user->setTimezone($timezone);
        $user->setUrl($url);
        $user->setIcq($icq);
        $user->setJabber($jabber);
        $user->setCity($city);
        $user->setTelephone($telephone);
        $user->setPol($pol);
        $user->setByear($byear);
        $user->setBmonth($bmonth);
        $user->setBday($bday);
        $user->setAbout($about);
        $user->setSignature($signature);
        $user->save();


		// Additional fields saving
		if (is_object($this->AddFields)) {
			$this->AddFields->save($id, $_addFields);
		}
		
		if ($this->Log) $this->Log->write('editing user by adm', 'user id(' . $id . ') adm id(' . $_SESSION['user']['id'] . ')');
		return $this->showInfoMessage(__('Operation is successful'), getProfileUrl($id));
	}




	// Функция возврашает информацию о пользователе; ID пользователя передается методом GET
	public function info($id = null)
    {
		//turn access
		$this->ACL->turn(array('users', 'view_users'));
		$id = (int)$id;
		if ( $id < 1 ) redirect('/users/');
		
		
		$user = $this->Model->getById($id);
		if (count($user) == 0) return $this->showInfoMessage(__('Can not find user'), '/users/' );
		if (is_object($this->AddFields) && count($user) > 0) {
			$user = $this->AddFields->mergeRecords(array($user));
            $user = $user[0];
		}

		
		if (isset($_SESSION['user'])) {
			$email = get_link(__('Send mail'), '/users/send_mail_form/' . $id);
			$privateMessage = get_link(__('Send PM'), '/users/send_msg_form/' . $id);
		} else {
			$email = __('Only registered users');
			$privateMessage = __('Only registered users');
		}
		


        $postsModel = $this->Register['ModManager']->getModelName('Posts');
        $postsModel = new $postsModel();
        $posts = $postsModel->getCollection(array('id_author' => $id), array('limit' => 1, 'order' => 'time DESC'));
		if (!empty($posts[0]) && count($posts[0]) > 0) {
			$lastPost = $posts[0]->getTime();
		} else {
			$lastPost = '';
		}
		
		$status_info = $this->ACL->get_user_group($user->getStatus());
		

		$markers = array();
		$markers['user_id'] 		= intval($user->getId());
		$markers['regdate'] 		= h($user->getPuttime());
		$markers['status'] 		    = h($status_info['title']);
		$markers['lastvisit']   	= h($user->getLast_visit());
        $markers['lastpost'] 		= h($lastPost);
        $markers['totalposts'] 	    = h($user->getPosts());
        $markers['email'] 		    = $email;
        $markers['telephone'] 	    = ($user->getTelephone()) ? h($user->getTelephone()) : '';

		
		if ($user->getPol() === 'f') $markers['pol'] = __('f');
		else if ($user->getPol() === 'm') $markers['pol'] = __('m');
		else $markers['pol'] = __('no gender');
		
		$markers['fpol'] = ($user->getPol() && ($user->getPol() === 'f' || $user->getPol() === '0')) ? ' checked="checked"' : '';
		$markers['mpol'] = ($user->getPol() && $user->getPol() !== 'f') ? ' checked="checked"' : '';
		if (!$user->getPol() || $user->getPol() === '') {
			$markers['fpol'] = '';
			$markers['mpol'] ='';
		}


		$markers['byear'] 	= ($user->getByear()) ? intval($user->getByear()) : '';
		$markers['bmonth'] 	= ($user->getBmonth()) ? intval($user->getBmonth()) : '';
		$markers['bday'] 	= ($user->getBday()) ? intval($user->getBday()) : '';
		if ($user->getByear() && $user->getBmonth() && $user->getBday()) {
			$markers['age'] = getAge($user->getByear(), $user->getBmonth(), $user->getBday());
		} else {
			$markers['age'] = '';
		}


		$markers['privatemessage'] = $privateMessage;

		
		// Аватар
		if (file_exists(ROOT . '/sys/avatars/' . $user->getId() . '.jpg')) {
			$markers['avatar'] = get_url('/sys/avatars/' . $user->getId() . '.jpg');
		} else {
			$markers['avatar'] = get_url('/sys/img/noavatar.png');
		}
		
		
		// Edit profile link {EDIT_PROFILE_LINK}
		$markers['edit_profile_link'] = '';
		if ($this->ACL->turn(array('users', 'edit_mine'), false) 
		&& (!empty($_SESSION['user']['id']) && $user->getId() === $_SESSION['user']['id'])) {
			$markers['edit_profile_link'] = get_link(__('Edit profile'), '/users/edit_form/');
		} else if ($this->ACL->turn(array('users', 'edit_users'), false)) {
			$markers['edit_profile_link'] = get_link(__('Edit profile'), '/users/edit_form_by_admin/' . $user->getId());
		}
		
		
		// Navigation Panel
		$nav = array();
		$nav['navigation'] = get_link(__('Home'), '/') . __('Separator')
			. get_link(h($this->module_title), '/users/') . __('Separator') . __('Profile');
		$this->_globalize($nav);


        foreach($markers as $k => $v) {
            $setter = 'set' . ucfirst($k);
            $user->$setter($v);
        }
		$source = $this->render('showuserinfo.html', array('user' => $user));
		return $this->_view($source);
	}




	// Функция возвращает html формы для отправки личного сообщения
	public function send_msg_form($id = null)
    {
		// Незарегистрированный пользователь не может отправлять личные сообщения
		if (!isset($_SESSION['user'])) redirect('/');
		$writer_status = (!empty($_SESSION['user']['status'])) ? $_SESSION['user']['status'] : 0;


		$menu = $this->_getMessagesMenu();

		$toUser = '';
		if (isset($id)) {
			$id = (int)$id;
			if ($id > 0) {
				$res = $this->Model->getById($id);
				if ($res) {
					if (count($res) > 0) $toUser = $res->getName();
				}
			}
		}
		
		
		$subject = '';
		if (!empty($_SESSION['response_pm'])) {
			if (preg_match('#^Re(\((\d+)\))?: #i', $_SESSION['response_pm'], $match)) {
				if (!empty($match[2]) && is_numeric($match[2])) {
					$subject = h('Re(' . ((int)$match[2] + 1) . '): ' . mb_substr($_SESSION['response_pm'], 6));
				} else {
					$subject = h('Re(2): ' . mb_substr($_SESSION['response_pm'], 4));
				}
			} else {
				$subject = h('Re: ' . $_SESSION['response_pm']);
			}
			unset($_SESSION['response_pm']);
		}
		$message = ''; // TODO
		
	
		if (isset($_SESSION['viewMessage']) && !empty($_SESSION['viewMessage']['message'])) {
			$prevMessage = $this->Textarier->print_page($_SESSION['viewMessage']['message'], $writer_status);
            $prevSource = $this->render('previewmessage.html', array('message' => $prevMessage));
			$toUser  = h($_SESSION['viewMessage']['toUser']);
			$subject = h($_SESSION['viewMessage']['subject']);
			$message = h($_SESSION['viewMessage']['message']);
			unset($_SESSION['viewMessage']);
		}

		$action = get_url('/users/send_message');
        $error = '';
		// Если при заполнении формы были допущены ошибки
		if (isset($_SESSION['sendMessageForm'])) {
			$error = $this->render('infomessage.html', array('info_message' => $_SESSION['sendMessageForm']['error']));
			$toUser  = h( $_SESSION['sendMessageForm']['toUser'] );
			$subject = h( $_SESSION['sendMessageForm']['subject'] );
			$message = h( $_SESSION['sendMessageForm']['message'] );
			unset($_SESSION['sendMessageForm']);
		}


		$markers = array();
		$markers['error'] = $error;
		$markers['action'] = $action;
		$markers['touser'] = $toUser;
		$markers['subject'] = $subject;
		$markers['main_text'] = $message;
		$markers['preview'] = (!empty($prevSource)) ? $prevSource : '';
		$source = $this->render('sendmessageform.html', array('context' => $markers));
		
		
		// Navigation Panel
		$nav = array();
		$nav['navigation'] = get_link(__('Home'), '/') . __('Separator')
			. get_link(h($this->module_title), '/users/') . __('Separator') . __('PM nav');
		$this->_globalize($nav);

		
		return $this->_view($source);
	}




	// Отправка личного сообщения (добавляется новая запись в таблицу БД TABLE_MESSAGES)
	public function send_message()
    {
		// Незарегистрированный пользователь не может отправлять личные сообщения
		if ( !isset( $_SESSION['user'] ) ) {
			redirect('/');
		}
		// Если не переданы данные формы - функция вызвана по ошибке
		if ( !isset( $_POST['toUser'] ) or
		   !isset( $_POST['subject'] ) or
		   !isset( $_POST['mainText'] ) )
		{
			redirect('/');
		}

		$msgLen = mb_strlen($_POST['mainText']);

		// Обрезаем переменные до длины, указанной в параметре maxlength тега input
		$toUser  = mb_substr($_POST['toUser'], 0, 30);
		$subject = mb_substr($_POST['subject'], 0, 60);
		$message = mb_substr($_POST['mainText'], 0, $this->Register['Config']->read('max_message_lenght', 'users') );
		// Обрезаем лишние пробелы
		$toUser  = trim($toUser);
		$subject = trim($subject);
		$message = trim($message);

		// Если пользователь хочет посмотреть на сообщение перед отправкой
		if (isset($_POST['viewMessage'])) {
			$_SESSION['viewMessage']             = array();
			$_SESSION['viewMessage']['toUser']   = $toUser;
			$_SESSION['viewMessage']['subject']  = $subject;
			$_SESSION['viewMessage']['message'] = $message;
			redirect('/users/send_msg_form/' );
		}
		
		// Проверяем, заполнены ли обязательные поля
		$error = '';
		$valobj = $this->Register['Validate'];
		if (empty($toUser)) 		
			$error = $error.'<li>' . __('Empty field "for"') . '</li>'."\n";
		if (empty($subject))  		
			$error = $error.'<li>' . __('Empty field "message title"') . '</li>'."\n";
		if (empty($message))     	
			$error = $error.'<li>' . __('Empty field "text"') . '</li>'."\n";
		if ($msgLen > $this->Register['Config']->read('max_message_lenght', 'users') )
			$error = $error.'<li>' . sprintf(__('Very big message'), $this->Register['Config']->read('max_message_lenght', 'users')) . '</li>'."\n";
			
			
		// Проверяем поля формы на недопустимые символы
		if (!empty($toUser) && !$valobj->cha_val($toUser, V_LOGIN))
			$error = $error.'<li>' . __('Wrong chars in field "to"') . '</li>'."\n";
		if (!empty($subject) && !$valobj->cha_val($subject, V_TEXT))
			$error = $error.'<li>' . __('Wrong chars in field "subject"') . '</li>'."\n";
			
			
		// Проверяем, есть ли такой пользователь
		if (!empty($toUser)) {
			$to = preg_replace( "#[^- _0-9a-zА-Яа-я]#iu", '', $toUser );
            $res = $this->Model->getCollection(
                array(
                    'name' => $toUser
                ),
                array(
                    'limit' => 1
                )
            );


			if (empty($res))
				$error = $error.'<li>' . sprintf(__('No user with this name'), $to) . '</li>'."\n";
			if ((count($res) && is_array($res) ) && ($res[0]->getId() == $_SESSION['user']['id']) )
				$error = $error.'<li>' . __('You can not send message to yourself') . '</li>'."\n";


			//chek max count messages
			if ($res[0]->getId()) {
				$id_to = (int)$res[0]->getId();
				$id_from = (int)$_SESSION['user']['id'];


                $className = $this->Register['ModManager']->getModelName('Messages');
                $model = new $className;
                $cnt_to = $model->getTotal(array(
                     'cond' => array(
                         "(`to_user` = '{$id_to}' OR `from_user` = '{$id_to}') AND `id_rmv` != '{$id_to}'"
                     )
                ));
                $cnt_from = $model->getTotal(array(
                    'cond' => array(
                        "(`to_user` = '{$id_from}' OR `from_user` = '{$id_from}') AND `id_rmv` != '{$id_from}'"
                    )
                ));


				if (!empty($cnt_to) && $cnt_to >= $this->Register['Config']->read('max_count_mess', 'users')) {
					$error = $error.'<li>' . __('This user has full  messagebox') . '</li>'."\n";
				}
				if (!empty($cnt_from) && $cnt_from >= $this->Register['Config']->read('max_count_mess', 'users')) {
					$error = $error.'<li>' . __('You have full  messagebox') . '</li>'."\n";
				}
			}
		}

		
		
		// Errors
		if (!empty($error )) {
			$_SESSION['sendMessageForm'] = array();
			$_SESSION['sendMessageForm']['error'] = '<p class="errorMsg">' . __('Some error in form') . '</p>'.
			"\n".'<ul class="errorMsg">'."\n".$error.'</ul>'."\n";
			$_SESSION['sendMessageForm']['toUser'] = $toUser;
			$_SESSION['sendMessageForm']['subject'] = $subject;
			$_SESSION['sendMessageForm']['message'] = $message;
			redirect('/users/send_msg_form/' );
		}

		// Все поля заполнены правильно - "посылаем" сообщение
        $res = $res[0];
		$to = $res->getId();
		$from = $_SESSION['user']['id'];


        $data = array(
            'to_user' => $to,
            'from_user' => $from,
            'sendtime' => 'NOW()',
            'subject' => $subject,
            'message' => $message,
            'id_rmv' => 0,
            'viewed' => 0,
        );
        $className = $this->Register['ModManager']->getEntityName('Messages');
        $message = new $className($data);
        $message->save();

		/* clean DB cache */
		$this->Register['DB']->cleanSqlCache();
		if ($this->Log) $this->Log->write('adding pm message', 'message id(' . mysql_insert_id() . ')');
		return $this->showInfoMessage(__('Message successfully send'), '/users/out_msg_box/' );
	}





	// Функция возвращает личное сообщение для просмотра пользователем
	public function get_message($id_msg = null)
    {
		if (!isset($_SESSION['user'])) redirect('/users/');
		$idMsg = (int)$id_msg;
		if ($idMsg < 1) redirect('/users/in_msg_box/' );

		
		// Navigation Panel
		$nav = array();
		$nav['navigation'] = get_link(__('Home'), '/') . __('Separator')
			. get_link(h($this->module_title), '/users/') . __('Separator') . __('Message');
		$this->_globalize($nav);
		
		
		// Получаем из БД информацию о сообщении.
		// В этом запросе дополнительное условие нужно для того, чтобы
		// пользователь не смог просмотреть чужое сообщение, просто указав
		// ID сообщения в адресной строке браузера
        $message = $this->Model->getMessage($idMsg);
		if (!$message) {
			redirect('/users/in_msg_box/' );
		}
		
		if (!$message->getFromuser() || !$message->getTouser()) {
			$this->showInfoMessage(__('Some error occurred'), '/' . $this->module . '/' );
		}
		
		
		// Далее мы должны выяснить, запрашивается входящее или исходящее
		// сообщение? Это нужно, чтобы правильно указать "Отправитель"
		// или "Получатель" и вывести заголовок страницы: "Входящие"
		// или "Исходящие"
        $markers = array();
		if ($message->getTo_user() == $_SESSION['user']['id']) {
			$markers['redirect'] = get_url('/users/in_msg_box/');
			$inBox = true;
		} else {
            $markers['redirect'] = get_url('/users/out_msg_box/');
			$inBox = false;
		}
		
		// Формируем заголовок страницы
		if ($inBox)  // Папка "Входящие"
			$markers['h1'] = __('PM in');
		else           // Папка "Исходящие"
            $markers['h1'] = __('PM on');
		$markers['menu'] = $this->_getMessagesMenu();

		
		if ( $inBox ) {
			$markers['in_on'] = __('From');
			$markers['in_on_user'] = $message->getFromuser()->getName();
			$markers['in_on_user_id'] = $message->getFrom_user();
		} else {
			$markers['in_on'] = __('To');
			$markers['in_on_user'] = $message->getTouser()->getName();
			$markers['in_on_user_id'] = $message->getTo_Suser();
		}

		
		if ($inBox)
			$markers['in_on_message'] = __('Sended');
		else
			$markers['in_on_message'] = __('Getting');
			

		$text = $this->Textarier->print_page($message->getMessage(), $message->getFromuser()->getStatus());
		$_SESSION['response_pm'] = $message->getSubject();
		

		$markers['response'] = get_url('/users/send_msg_form/' . $markers['in_on_user_id']);

		
		// Помечаем сообщение, как прочитанное
		if ($inBox and $message->getViewed() != 1) {
            $message->setViewed($message->getViewed());
            $message->save();
		}
        $message->setMessage($text);
        $source = $this->render('vievpmmessage.html', array(
            'context' => $markers,
            'message' => $message,
        ));
		
		return $this->_view($source);
	}




	// Папка личных сообщений (входящие)
	public function in_msg_box()
    {
		if (!isset($_SESSION['user'])) redirect('/');


        // Navigation Panel
        $nav = array();
		$nav['messages_menu'] = $this->_getMessagesMenu();
        $nav['navigation'] = get_link(__('Home'), '/') . __('Separator')
            . get_link(h($this->module_title), '/users/') . __('Separator') . __('PM nav');
        $this->_globalize($nav);


        $markers = array('error' => '');
        $messages = $this->Model->getInputMessages();

        if (!is_array($messages) || !count($messages)) {
            $markers['messages'] = array();
            $markers['error'] = __('This dir is empty');
            $source = $this->render('vievinpm.html', array('context' => $markers));
            return $this->_view($source);
        }



        foreach ($messages as $message) {
            // Если сообщение еще не прочитано
            $icon = ($message->getViewed() == 0) ? 'folder_new' : 'folder';
            $message->setIcon(get_img('/template/'.Config::read('template').'/img/' . $icon . '.gif'));
            $message->setTheme(get_link(h($message->getSubject()), '/users/get_message/' . $message->getId()));
            $message->setDelete(get_link(__('Delete'), '/users/delete_message/' . $message->getId(), array('onClick' => "return confirm('" . __('Are you sure') . "')")));
        }


		$source = $this->render('vievinpm.html', array('messages' => $messages, 'context' => $markers));
		return $this->_view($source);
	}




	// Папка личных сообщений (исходящие)
	public function out_msg_box()
    {
		if (!isset($_SESSION['user'])) redirect('/');


        // Navigation Panel
        $nav = array();
		$nav['messages_menu'] = $this->_getMessagesMenu();
        $nav['navigation'] = get_link(__('Home'), '/') . __('Separator')
            . get_link(h($this->module_title), '/users/') . __('Separator') . __('PM nav');
        $this->_globalize($nav);


        $markers = array('error' => '');
        $messages = $this->Model->getOutputMessages();
        if (!is_array($messages) || !count($messages)) {
            $markers['messages'] = array();
            $markers['error'] = __('This dir is empty');
            $source = $this->render('vievonpm.html', array('context' => $markers));
            return $this->_view($source);
        }



        foreach ($messages as $message) {
            // Если сообщение еще не прочитано
            $icon = ($message->getViewed() == 0) ? 'folder_new' : 'folder';
            $message->setIcon(get_img('/template/'.Config::read('template').'/img/' . $icon . '.gif'));
            $message->setTheme(get_link(h($message->getSubject()), '/users/get_message/' . $message->getId()));
            $message->setDelete(get_link(__('Delete'), '/users/delete_message/' . $message->getId(), array('onClick' => "return confirm('" . __('Are you sure') . "')")));
        }


		$source = $this->render('vievonpm.html', array('messages' => $messages, 'context' => $markers));
		return $this->_view($source);
	}



	/**
	 * Multi message Delete
	 */
	public function delete_message_pack()
    {
		$this->delete_message();
	}
	

	// Функция удаляет личное сообщение; ID сообщения передается методом GET
	public function delete_message($id_msg = null)
    {
		if (!isset( $_SESSION['user'])) redirect('/');
		$messagesModel = $this->Register['ModManager']->getModelName('Messages');
		$messagesModel = new $messagesModel;
		
		$multi_del = true;
		if (empty($_POST['ids']) 
		|| !is_array($_POST['ids'])
		|| count($_POST['ids']) < 1) $multi_del = false;

		$idMsg = (int)$id_msg;
		if ($idMsg < 1 && $multi_del === false) redirect('/');
		
		
		// We create array with ids for delete
		$ids = array();
		if ($multi_del === false) {
			$ids[] = $idMsg;
		} else {
			foreach ($_POST['ids'] as $id) {
				$id = intval($id);
				if ($id < 1) continue;
				$ids[] = $id;
			}
		}
		if (count($ids) < 1) redirect('/');

		
		foreach ($ids as $idMsg) {
			// Далее мы должны выяснить, удаляется входящее или исходящее
			// сообщение. Это нужно, чтобы сделать редирект на нужный ящик.
			// В этом запросе дополнительное условие нужно для того, чтобы
			// пользователь не смог удалить чужое сообщение, просто указав
			// ID сообщения в адресной строке браузера
			$messages = $messagesModel->getCollection(array(
				'id' => $idMsg,
				"(`to_user` = '" . $_SESSION['user']['id'] . "' OR `from_user` = '" . $_SESSION['user']['id'] . "')"
			));
			if (count($res) == 0) {
				continue;
			}

			
			$message = $messages[0];
			$toUser = $message->getTo_user();
			$id_rmv = $message->getId_rmv();
			if ( $toUser == $_SESSION['user']['id'] )
				$redirect = get_url('/users/in_msg_box/');
			else
				$redirect = get_url('/users/out_msg_box/');
			// id_rmv - это поле указывает на то, что это сообщение уже удалил
			// один из пользователей. Т.е. сначала id_rmv=0, после того, как
			// сообщение удалил один из пользователей, id_rmv=id_user. И только после
			// того, как сообщение удалит второй пользователь, мы можем удалить
			// запись в таблице БД 
			if ($id_rmv == 0) {
				$message->setId_rmv($_SESSION['user']['id']);
				$message->save();
			} else {
				$message->delete();
			}
		}
		
		/* clean DB cache */
		$this->Register['DB']->cleanSqlCache();
		if ($this->Log) $this->Log->write('delete pm message(s)', 'message(s) id(' . implode(', ', $ids) . ')');
		return $this->showInfoMessage(__('Operation is successful'), $redirect );
	}




	// Функция возвращает меню для раздела "Личные сообщения"
	private function _getMessagesMenu()
    {

		$html = get_img('/sys/img/msg_inbox.png', array('alt' => __('In box'), 'title' => __('In box')))
			. get_link(__('In box'), '/users/in_msg_box/');
		$html .= get_img('/sys/img/msg_outbox.png', array('alt' => __('On box'), 'title' => __('On box')))
			. get_link(__('On box'), '/users/out_msg_box/');
		$html .= get_img('/sys/img/msg_newpost.png', array('alt' => __('Write PM'), 'title' => __('Write PM')))
			. get_link(__('Write PM'), '/users/send_msg_form/');

		return $html;
	}




	/**
	 *
	 */
	public function send_mail_form($id = null)
    {
		if (!isset($_SESSION['user'])) redirect('/');
		$id = intval($id);
		if (!$id) redirect('/');
		
		$toUser = null;
		
		$user = $this->Model->getById($id);
		if (!empty($user)) $toUser = $user->getName();


		$markers = array(
			'message' => '',
			'subject' => '',
			'action' => get_url('/users/send_mail/'),
			'to_user' => $toUser,
			'error' => '',
		);
		
		
		// Если при заполнении формы были допущены ошибки
		if (isset($_SESSION['sendMailForm'])) {
			$markers['error'] = $this->render('infomessage.html', array(
				'info_message' => $_SESSION['sendMailForm']['error']
			));
			$markers['to_user']  = $_SESSION['sendMailForm']['toUser'];
			$markers['subject'] = $_SESSION['sendMailForm']['subject'];
			$markers['message'] = $_SESSION['sendMailForm']['message'];
			unset($_SESSION['sendMailForm']);
		}

		
		$source = $this->render('sendmailform.html', array(
			'context' => $markers,
			'user' => $user,
		));
		return $this->_view($source);
	}

	


	// Отправка письма пользователю сайта
	public function send_mail()
    {
		if (!isset($_POST['toUser']) ||
		   !isset($_POST['subject']) ||
		   !isset($_POST['message']))
		{
			redirect('/');
		}
		if (!isset($_SESSION['user'])) redirect('/');
		

		// Обрезаем переменные до длины, указанной в параметре maxlength тега input
		$toUser  = mb_substr( $_POST['toUser'], 0, 30 );
		$subject = mb_substr( $_POST['subject'], 0, 60 );
		$message = mb_substr( $_POST['message'], 0, $this->Register['Config']->read('max_mail_lenght', 'users'));
		// Обрезаем лишние пробелы
		$toUser  = trim( $toUser );
		$subject = trim( $subject );
		$message = trim( $message );

		
		// Проверяем, заполнены ли обязательные поля
		$error = '';
		$valobj = $this->Register['Validate'];
		if ( empty( $toUser ) ) 				
			$error = $error.'<li>' . __('Empty field "for"') . '</li>'."\n";
		if ( empty( $subject ) ) 				
			$error = $error.'<li>' . __('Empty field "message title"') . '</li>'."\n";
		if ( empty( $message ) ) 				
			$error = $error.'<li>' . __('Empty field "text"') . '</li>'."\n";
		// Проверяем поля формы на недопустимые символы
		if (!empty($toUser) && !$valobj->cha_val($toUser, V_LOGIN))
			$error = $error.'<li>' . __('Wrong chars in field "to"') . '</li>'."\n";
		if (!empty($subject) and !$valobj->cha_val($subject, V_TEXT))
			$error = $error.'<li>' . __('Wrong chars in field "subject"') . '</li>'."\n";
			
			
		// Проверяем, есть ли такой пользователь
		if (!empty($toUser)) {
			$to = preg_replace("#[^- _0-9a-zа-яА-Я]#ui", '', $toUser);
			$user = $this->Model->getByName($to);
			if (empty($user))				
				$error = $error.'<li>' . sprintf(__('No user with this name'), $to) . '</li>'."\n";
		}
		
		// Если были допущены ошибки при заполнении формы -
		// перенаправляем посетителя для исправления ошибок
		if (!empty($error)) {
			$_SESSION['sendMailForm'] = array();
			$_SESSION['sendMailForm']['error'] = '<p class="errorMsg">' . __('Some error in form') . '</p>'.
			"\n".'<ul class="errorMsg">'."\n".$error.'</ul>'."\n";
			$_SESSION['sendMailForm']['toUser']  = $toUser;
			$_SESSION['sendMailForm']['subject'] = $subject;
			$_SESSION['sendMailForm']['message'] = $message;
			redirect('/users/send_mail_form/' . $user->getId());
		}
		
		$toUser = $user;
		$fromUser = $_SESSION['user']['name'];

		
		$message = 'ОТ: '.$fromUser."\n".'ТЕМА: '.$subject."\n\n".$message;
		
		/* clean DB cache */
		$this->DB->cleanSqlCache();
		// формируем заголовки письма
		$headers = "From: ".$_SERVER['SERVER_NAME']." <" . $this->Register['Config']->read('admin_email') . ">\n";
		$headers = $headers."Content-type: text/html; charset=\"utf-8\"\n";
		$headers = $headers."Return-path: <" . $this->Register['Config']->read('admin_email') . ">\n";
		$subject = 'Письмо с форума '.$_SERVER['SERVER_NAME'].' от '.$fromUser;
		if (mail($toUser->getEmail(), $subject, $message, $headers))
			return $this->showInfoMessage(__('Operation is successful'), '/');
		else
			return $this->showInfoMessage(__('Some error occurred'), '/');
	}




	// Функция возвращает html формы для авторизации на форуме
	public function login_form()
    {
		// For return to previos page(referer)
		if (!empty($_SERVER['HTTP_REFERER']) 
		&& preg_match('#^http://([^/]+)/(.+)#', $_SERVER['HTTP_REFERER'], $match)) {
			if (!empty($match[1]) && !empty($match[2]) && $match[1] == $_SERVER['SERVER_NAME']) {
				$ref_params = explode('/', $match[2]);
				if (empty($ref_params[0]) || empty($ref_params[1]) ||
				($ref_params[0] != 'users' && $ref_params[1] != 'login_form')) {
					$_SESSION['authorize_referer'] = $match[2];
				}
			}
		}
		


		if (isset($_SESSION['loginForm']['error'])) {
			$error = $this->render('infomessage.html', array(
				'info_message' => $_SESSION['loginForm']['error']
			));
			unset($_SESSION['loginForm']['error']);
		}
		
		
		
		$markers = array(
			'form_key' => '',
			'action' => get_url('/users/login/'),
			'new_password' => get_link('Забыли пароль?', '/users/new_password_form/'),
			'error' => (!empty($error)) ? $error : '',
		);
		if ($this->Register['Config']->read('autorization_protected_key', 'secure') === 1) {
			$_SESSION['form_key_mine'] = rand(1000, 9999);
			$form_key = rand(1000, 9999);
			$_SESSION['form_hash'] = md5($form_key . $_SESSION['form_key_mine']);
			$markers['form_key'] = '<input type="hidden" name="form_key" value="' . $form_key . '" />';
		}


		
		// Navigation Panel
		$nav = array();
		$nav['navigation'] = get_link(__('Home'), '/') . __('Separator') 
			. get_link(h($this->module_title), '/users/') . __('Separator') . __('Authorize');
		$this->_globalize($nav);
		
		
		$source = $this->render('loginform.html', array(
			'context' => $markers,
		));
		return $this->_view($source);
	}




	// Вход на форум - обработчик формы авторизации
	public function login()
    {
		// Если не переданы данные формы - значит функция была вызвана по ошибке
		if (!isset($_POST['username']) or !isset($_POST['password'])) redirect('/');
		$error = '';
		
		
		if ($this->Register['Config']->read('autorization_protected_key', 'secure') === 1) {
			if (empty($_SESSION['form_key_mine'])
			|| empty($_POST['form_key'])		
			|| md5(substr($_POST['form_key'], 0, 10) . $_SESSION['form_key_mine']) != $_SESSION['form_hash']) {
				$this->showInfoMessage(__('Use authorize form'), '/');
			}
		}
		
		
		// Защита от перебора пароля - при каждой неудачной попытке время задержки увеличивается
		if (isset($_SESSION['loginForm']['count']) && $_SESSION['loginForm']['count'] > time()) {
			$error = '<li>' . sprintf(__('You must wait'), ($_SESSION['loginForm']['count'] - time())) . '</li>';
		}


		// Обрезаем переменные до длины, указанной в параметре maxlength тега input
		$name      = mb_substr( $_POST['username'], 0, 30 );
		$password  = mb_substr( $_POST['password'], 0, 30 );
		// Обрезаем лишние пробелы
		$name      = trim( $name );
		$password  = trim( $password );

		
		// Проверяем, заполнены ли обязательные поля
		$valobj = $this->Register['Validate'];
		if (empty($name))         		
			$error = $error.'<li>' . __('Empty field "login"') . '</li>'."\n";
		if (empty($password)) 			
			$error = $error.'<li>' . __('Empty field "password"') . '</li>'."\n";

			
		// Проверяем поля формы на недопустимые символы
		if (!empty($name) && !$valobj->cha_val($name, V_LOGIN))
			$error = $error.'<li>' . __('Wrong chars in field "login"') . '</li>'."\n";
		if (!empty($password) && !$valobj->cha_val($password, V_LOGIN))
			$error = $error.'<li>' . __('Wrong chars in field "password"') . '</li>'."\n";

			
		// Проверять существование такого пользователя есть смысл только в том
		// случае, если поля не пустые и не содержат недопустимых символов
		if (empty($error)) {
			$user = $this->Model->getByNamePass($name, $password);
			if (empty($user)) $error = $error.'<li>' . __('Wrong login or pass') . '</li>'."\n";
		}

		
		// Если были допущены ошибки при заполнении формы
		if (!empty($error)) {
			if (!isset($_SESSION['loginForm']['count'])) $_SESSION['loginForm']['count'] = 1;
			else if ($_SESSION['loginForm']['count'] < 10) $_SESSION['loginForm']['count']++;
  			else if ($_SESSION['loginForm']['count'] < time()) $_SESSION['loginForm']['count'] = time() + 10;
			else $_SESSION['loginForm']['count'] = $_SESSION['loginForm']['count'] + 10;
			
			$_SESSION['loginForm']['error'] = '<p class="errorMsg">' . __('Some error in form') . '</p>'.
			"\n".'<ul class="errorMsg">'."\n".$error.'</ul>'."\n";
			redirect('/users/login_form/');
		}

		// Все поля заполнены правильно и такой пользователь существует - продолжаем...
		unset($_SESSION['loginForm']);


		if ($user->getActivation()) return $this->showInfoMessage(__('Your account not activated'), '/');

		// Если пользователь заблокирован
		if ($user->getLocked()) return redirect('/users/baned/');
		$_SESSION['user'] = $user->asArray();

		// Функция getNewThemes() помещает в массив $_SESSION['newThemes'] ID тем,
		// в которых были новые сообщения со времени последнего посещения пользователя
		$this->Register['UserAuth']->getNewThemes();

		// Выставляем cookie, если пользователь хочет входить на форум автоматически
		if ( isset ( $_POST['autologin'] ) ) {
			$path = '/';
			setcookie('autologin', 'yes', time() + 3600 * 24 * $this->Register['Config']->read('cookie_time'), $path);
			setcookie('userid', $_SESSION['user']['id'], time() + 3600 * 24 * $this->Register['Config']->read('cookie_time'), $path);
			setcookie('password', $_SESSION['user']['passw'], time() + 3600 * 24 * $this->Register['Config']->read('cookie_time'), $path);
		}
		
		
		// Authorization complete. Redirect
		if (isset($_SESSION['authorize_referer'])) {
			redirect('/' . $_SESSION['authorize_referer']);
		} else if (!empty($_SERVER['HTTP_REFERER']) 
		&& preg_match('#^http://([^/]+)/(.+)#', $_SERVER['HTTP_REFERER'], $match)) {
			if (!empty($match[1]) && !empty($match[2]) && $match[1] == $_SERVER['SERVER_NAME']) {
				$ref_params = explode('/', $match[2]);
				if (empty($ref_params[0]) || empty($ref_params[1]) ||
				($ref_params[0] != 'users' && $ref_params[1] != 'login_form')) {
					redirect('/' . $match[2]);
				}
			}
		}
		redirect('/');
	}




	// Выход из системы
	public function logout()
    {
		if (isset($_SESSION['user'])) unset($_SESSION['user']);
		if (isset($_SESSION)) unset($_SESSION);

		$path = '/';
		if ( isset( $_COOKIE['autologin'] ) ) setcookie( 'autologin', '', time() - 1, $path );
		if ( isset( $_COOKIE['userid'] ) ) setcookie( 'userid', '', time() - 1, $path );
		if ( isset( $_COOKIE['password'] ) ) setcookie( 'password', '', time() - 1, $path );
		redirect( '/');
	}

	
	
	/**
	* @param int $id - user id
	* 
	* baned user
	*/
	public function onban($id)
    {
		//turn access
		$this->ACL->turn(array('users', 'ban_users'));
		$id = (int)$id;
		if ($id < 1) {
			redirect('/');
		}
		$user = $this->Model->getById($id);
		if (!empty($user)) {
			$user->setLocked(1);
			$user->save();
		}
	}
	
	
	
	/**
	* @param int $id - user id
	* 
	* baned user
	*/
	public function offban($id)
    {
		//turn access
		$this->ACL->turn(array('users', 'ban_users'));
		$id = (int)$id;
		if ($id < 1) {
			redirect('/');
		}
		$user = $this->Model->getById($id);
		if (!empty($user)) {
			$user->setLocked(0);
			$user->save();
		}
	}

	
	
	/**
	 * Change users rating
	 * This action take request from AJAX(recomented).
	 *
	 * @param int $to_id
	 * @param int $points
	 */
	public function rating($to_id = null, $points = null)
    {
		// Fps counter OFF
		$this->counter = false;
		$this->cached = false;
		

		// Check rules
		if (!isset($_SESSION['user'])) die(__('Permission denied'));
		if (!$this->ACL->turn(array('users', 'set_rating'), false)) die(__('Permission denied'));
		$from_id = intval($_SESSION['user']['id']);
		$to_id = intval($to_id);
		if ($to_id < 1) die(__('Can not find user'));
		if ($from_id == $to_id) die(__('No voting for yourself'));
		
		if ($points === null && !empty($_POST['points'])) $points = $_POST['points'];
		$points = intval($points);
		if ($points > 1) $points = 1;
		if ($points < -1) $points = -1;
		
		
		// Check user exists
		$user = $this->Model->getById($to_id);
		if (empty($user)) die(__('Can not find user'));
		
		
		// Comment
		$comment = '';
		if (isset($_POST['comment'])) {
			$comment = trim($_POST['comment']);
			if (mb_strlen($comment) > $this->Register['Config']->read('rating_comment_lenght', 'users')) 
				die(sprintf(__('Very long comment', $this->Register['Config']->read('rating_comment_lenght', 'users'))));
			$comment = substr($comment, 0, $this->Register['Config']->read('rating_comment_lenght', 'users'));
		}
		
		

		$votesModel = $this->Register['ModManager']->getModelName('UsersVotes');
		$votesModel = new $votesModel;
		$last_vote = $votesModel->getCollection(array(
			'to_user' => $to_id
		), array(
			'order' => 'date DESC',
			'limit' => 1
		));
		
		
		
		if (empty($last_vote) || ($last_vote[0]->getFrom_user() != $from_id)) {
			$user->setRating($user->getRating() + 1);
			$user->save();

			$voteEntity = $this->Register['ModManager']->getEntityName('UsersVotes');
			$voteEntity = new $voteEntity(array(
				'from_user' => $from_id,
				'to_user' => $to_id,
				'comment' => $comment,
				'points' => $points,
				'date' => 'NOW()',
			));
			$voteEntity->save();
			die('ok');
		}
		die(__('Some error occurred'));
	}
	
	
	/**
	 * View rating story
	 *
	 * @param int $user_id
	 */
	public function votes_story($user_id)
    {
		$this->counter = false;
		$this->cached = false;
		// Without wrapper we can use this for ajax requests
		$this->wrap = (!isset($_GET['wrapper'])) ? false : true;
		
		$user_id = intval($user_id);
		if ($user_id < 1) redirect('/');
		
		
		// Check user exists
		$to_user = $this->Model->getById($user_id);
		if (empty($to_user)) redirect('/');

		
		$votesModel = $this->Register['ModManager']->getModelInstance('UsersVotes');
		$votesModel->bindModel('touser');
		$votesModel->bindModel('fromuser');
		$messages = $votesModel->getCollection(array('to_user' => $user_id), array('order' => '`date` DESC'));
		if (!is_array($messages) || count($messages) < 1) {
			return $this->_view(__('No votes for user'));
		}
		
		
		
		foreach ($messages as $message) {
			// Admin buttons
			$message->setModer_panel('');
			if ($this->ACL->turn(array('users', 'delete_rating_comments'), false)) {
				$message->setModer_panel(get_link('', 'javascript://',
					array(
						'onclick' => "deleteUserVote('" . $message->getId() . "'); return false;", 
						'class' => 'fps-delete',
					)
				));
			}
		}
		

		$source = $this->render('rating_tb.html', array(
			'to_user' => $to_user,
			'messages' => $messages,
		));
		return $this->_view($source);
	}
	
	
	/**
	 * Delete users votes
	 *
	 * @param int - vote ID
	 */
	public function delete_vote($voteID)
    {
		$this->counter = false;
		$this->cached = false;
		$voteID = intval($voteID);
		if ($voteID < 1) die('fail');
		
		
		if ($this->ACL->turn(array('users', 'delete_rating_comments'), false)) {
			$votesModel = $this->Register['ModManager']->getModelName('UsersVotes');
			$votesModel = new $votesModel;
			$vote = $votesModel->getById($voteID);

	
			if (!empty($vote)) {
				$user = $this->Model->getById($vote->getTo_user());
				$action = $vote->getAction();
				$vote->delete();
			
				$user->setRating($user->getRating() - (int)$action);
				$user->save();
				die('ok');
			}
		}
		die('fail');
	}
	
	
	
	/**
	* page for baned users
	*/
	public function baned()
    {
		$source = $this->render('baned.html', array());
		$this->_view($source);
	}
	
	
	
	/**
	* Creane warnings for bad users
	*/
	public function add_warning($uid = null)
    {
		if (!$this->ACL->turn(array('users', 'users_warnings'), false)) die(__('Permission denied'));
		$this->counter = false;
		$this->cached = false;
		
		$uid = intval($uid);
		if (empty($uid) && !empty($_POST['uid'])) $uid = intval($_POST['uid']);
		if (empty($uid)) die(__('Some error occurred'));
		
		
		$intruder = $this->Model->getById($uid);
		if (empty($intruder)) die(__('Can not find user'));

		
		// Action and cause
		$points = (!empty($_POST['points'])) ? intval($_POST['points']) : 1;
		if ((int)$points != 1 && (int)$points != -1) $points = 1;
		$cause = (!empty($_POST['cause'])) ? trim($_POST['cause']) : '';
		
		// Interval
		if (!empty($_POST['permanently'])) $timestamp = time() + 99999999;
		else if (!empty($_POST['mult']) && !empty($_POST['cnt'])) {
			switch (trim($_POST['mult'])) {
				case 'h':
					$timestamp = intval($_POST['cnt']) * 3600;
					break;
				case 'd':
					$timestamp = intval($_POST['cnt']) * 86400;
					break;
				case 'w':
					$timestamp = intval($_POST['cnt']) * 604800;
					break;
				case 'm':
					$timestamp = intval($_POST['cnt']) * 2419200;
					break;
				default:
					$timestamp = intval($_POST['cnt']) * 29030400;
					break;
			}
		}
		
		
		if (!empty($timestamp)) {
			$interval = date("Y-m-d H:i:s", time() + $timestamp);
			$ban = 1;
		} else {
			$interval = '0000-00-00 00:00:00';
			$ban = 0; 
		}
		
		
		$adm_id = (!empty($_SESSION['user']['id'])) ? intval($_SESSION['user']['id']) : 0;
		if ($adm_id < 1) die(__('Permission denied'));
		if ($adm_id == $uid) die(__('Some error occurred'));
		
		if (!$ban) {
			$max_warnings = $this->Render['Config']->read('warnings_by_ban', 'users');
			if ($intruder->getWarnings() > 0 && $intruder->getWarnings() + $points >= $max_warnings) {
				$ban = 1;
				$interval = $this->Register['Config']->read('autoban_interval', 'users');
				$interval = time() + intval($interval);
				$interval = date("Y-m-d H:i:s", $interval);
				
				$clean_warnings = true;
			}
		}
		
		
		$intruder->setBan_expire($interval);
		$intruder->setLocked($ban);

		

		if (!empty($clean_warnings)) { 
			$intruder->setWarnings(0);
			$votesModelName = $this->Register['ModManager']->getModelName('UsersVotes');
			$votesModel = new $votesModelName;
			$votesModel->deleteUserWarnings($uid);

			
		} else {
			$intruder->setWarnings($intruder->getWarnings() + $points);
			$votesEntityName = $this->Register['ModManager']->getEntityName('UsersVotes');
			$votesEntity = new $votesEntityName(array(
				'user_id' => $uid,
				'admin_id' => $adm_id,
				'points' => $points,
				'date' => 'NOW()',
				'cause' => $cause,
			));
			$votesEntity->save();
		}
		$intruder->save();

		
		
		if (!empty($_POST['noticepm'])) {
			$messEntityName = $this->Register['ModManager']->getEntityName('Messages');
			$messEntity = new $messEntityName(array(
				'to_user' => $uid,
				'from_user' => $adm_id,
				'subject' => __('You have new warnings'),
				'message' => __('Warnings cause').$cause,
				'sendtime' => 'NOW()',
				'id_rmv' => $adm_id,
			));
			$messEntity->save();
		}
		
		die('ok');
	}
	
	
	/**
	 * View warnings story
	 *
	 * @param int $uid
	 */
	public function warnings_story($uid)
    {
		$this->counter = false;
		$this->cached = false;
		// Without wrapper we can use this for ajax requests
		$this->wrap = (!isset($_GET['wrapper'])) ? false : true;
		
		$uid = intval($uid);
		if ($uid < 1) {
			if ($this->wrap) redirect('/');
			else die(__('Some error occurred'));
		}
		
		
		// Check user exists
		$to_user = $this->Model->getById($uid);
		if (empty($to_user) < 1) {
			if ($this->wrap) redirect('/');
			else die(__('Can not find user'));
		}
		
	
		$warnModelName = $this->Register['ModManager']->getModelName('UsersWarnings');
		$warModel = new $warnModelName;
		$warModel->bindModel('Users');
		$warnings = $warModel->getColection(array(
			'user_id' => $uid
		), array(
			'order' => 'date DESC'
		));
		if (empty($warnings)) {
			return $this->_view(__('No warnings for user'));
		}
		
		
		
		$max_warnings_by_ban = $this->Register['Config']->read('warnings_by_ban', 'users');
		$user_procent_warnings = (100 / $max_warnings_by_ban) * $to_user->getWarnings();
		foreach ($warnings as $warning) {
			$panel = get_link('', 'javascript://', 
				array(
					'onclick' => "deleteUserWarning('" . $warning->getId() . "'); return false;", 
					'class' => 'fps-delete',
				)
			);
			$warning->setModerPanel($panel);
		}
		
		
		$source = $this->render('rating_tb.html', array(
			'to_user' => $to_user,
			'warnings' => $warnings,
		));
		return $this->_view($source);
	}
	
	
	/**
	 * Delete users warnings
	 *
	 * @param int - warning ID
	 */
	public function delete_warning($wID)
    {
		$this->counter = false;
		$this->cached = false;
		$wID = intval($wID);
		if ($wID < 1) die('fail');
		
		
		if ($this->ACL->turn(array('users', 'delete_warnings'), false)) {
			$warnModelName = $this->Register['ModManager']->getModelName('UsersWarnings');
			$warModel = new $warnModelName;
			$warning = $warModel->getById($wID);
	
	
			if (!empty($warning)) {
				$user_warnings = $this->Model->getById($warning->getUser_id());
				$warning->delete();
				
				$ban = 1;
				if (!empty($user_warnings)) {
					if ($user_warnings->getWarnings() < $this->Register['Config']->read('warnings_by_ban', 'users')) {
						$ban = 0;
					}
				}
				
				
				$user_warnings->setLocked($ban);
				$user_warnings->setWarnings($user_warnings->getWarnings() - $warning->getPoints());
				$user_warnings->save();
				
				die('ok');
			}
		}
		die('fail');
	}
	
	
	/**
	 * Check users PM (AJAX)
	 *
	 * @param int - $uid
	 */
	public function get_count_new_pm($uid = null)
    {
		$this->counter = false;
		$this->cached = false;
		$uid = intval($uid);
		if ($uid < 1) die();
		
		$res = $this->Model->getNewPmMessages($uid);
		if ($res) {
			die($res['cnt']);
		}
			
		die();
	}
	
	
	/**
	 * Show comments by user.
	 */
	public function comments($id = null) 
	{
		/* COMMENT BLOCK */
		$total = $this->Model->getCountComments($id);
		$per_page = 25;

		/* pages nav */
		list($pages, $page) = pagination($total, $per_page, $this->getModuleURL('comments/' . ($id ? $id : '')));
		$this->_globalize(array('comments_pagination' => $pages));

		$offset = ($page - 1) * $per_page;

		$comments = $this->Model->getComments($id, $offset, $per_page);
		if ($comments && is_array($comments)) {
			foreach ($comments as $index => $entity) {

				$module = $entity->getModule();
				$markers = array();

				
				// COMMENT ADMIN BAR
				$ip = ($entity->getIp()) ? $entity->getIp() : 'Unknown';
				$moder_panel = '';
				$adm = false;
				if ($this->ACL->turn(array($module, 'edit_comments'), false)) {
					$moder_panel .= get_link('',
					'/' . $module . '/edit_comment_form/' . $entity->getId(), array('class' => 'fps-edit')) . '&nbsp;';
					$adm = true;
				}

				if ($this->ACL->turn(array($module, 'delete_comments'), false)) {
					$moder_panel .= get_link('',
					'/' . $module . '/delete_comment/' . $entity->getId(), array('class' => 'fps-delete', 'onClick' => "return confirm('" . __('Are you sure') . "')")) . '&nbsp;';
					$adm = true;
				}

				if ($adm) {
					$moder_panel = '<a target="_blank" href="https://apps.db.ripe.net/search/query.html?searchtext=' 
							. h($ip) . '" class="fps-ip" title="IP: ' . h($ip) . '"></a>' . $moder_panel;
				}


				$img = array(
					'alt' => 'User avatar',
					'title' => h($entity->getName()),
					'class' => 'ava',
				);
				
				
				
				// Аватар
				if (file_exists(ROOT . '/sys/avatars/' . $entity->getUser_id() . '.jpg')) 
					$url = get_url('/sys/avatars/' . $entity->getUser_id() . '.jpg');
				else 
					$url = get_url('/sys/img/noavatar.png');
					
				$markers['avatar'] = '<img class="ava" src="' . $url . '" alt="User avatar" />';

				

				if ($entity->getUser_id()) {
					$markers['name_a'] = get_link(h($entity->getName()), getProfileUrl((int)$entity->getUser_id()));
					$markers['user_url'] = get_url(getProfileUrl((int)$entity->getUser_id()));
					$markers['avatar'] = get_link($markers['avatar'], $markers['user_url']);
				} else {
					$markers['name_a'] = h($entity->getName());
				}
				$markers['name'] = h($entity->getName());


				$markers['moder_panel'] = $moder_panel;
				$markers['message'] = $this->Textarier->print_page($entity->getMessage());

				if ($entity->getEditdate()!='0000-00-00 00:00:00') {
					$markers['editdate'] = 'Комментарий был изменён '.$entity->getEditdate();
				} else {
					$markers['editdate'] = '';
				}

				$entity->setEntry_url(get_url('/' . $module . '/view/' . $entity->getEntity_id()));
				$entity->setAdd_markers($markers);
				
				$comments[$index] = $entity;
			}
		}
		$this->comments = $this->render('viewcomment.html', array('commentsr' => $comments));

		$title = __('All comments');
		if ($id && intval($id) > 0) {
			$user = $this->Model->getById(intval($id));
			if ($user)
				$title = __('User comments') . ' "' . h($user->getName()) . '"';
		}
		$this->page_title = $title . ' - ' . $this->page_title;

		$navi = array();
		$navi['add_link'] = ($this->ACL->turn(array($this->module, 'add_materials'), false)) ? get_link(__('Add material'), $this->getModuleURL('add_form/')) : '';
		$navi['module_url'] = get_url($this->getModuleURL());
		$navi['category_url'] = get_url($this->getModuleURL('comments/' . ($id ? $id : '')));
		$navi['category_name'] = $title;
		$navi['navigation'] = get_link(__('Home'), '/') . __('Separator')
		. get_link(h($this->module_title), $this->getModuleURL()) . __('Separator') . $title;
		$this->_globalize($navi);

		return $this->_view('');
	}
}

