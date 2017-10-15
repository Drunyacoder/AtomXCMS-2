<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.7.0                         |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
| @subpackege    Users Module                  |
| @copyright     ©Andrey Brykin 2010-2014      |
| @last mod      2017/10/15                    |
|----------------------------------------------|
|											   |
| any partial or not partial extension         |
| CMS AtomX,without the consent of the         |
| author, is illegal                           |
|----------------------------------------------|
| Любое распространение                        |
| CMS AtomX или ее частей,                     |
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
        $this->addToPageMetaContext('entity_title', __('Users list'));
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
        $queryParams = array(
            'order' => $this->Model->getOrderParam(),
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
				$markers['pm'] = get_link(__('Write'), '/users/pm_send_form/' . $uid);
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
		
		if (!empty($_SESSION['user']['id'])) redirect('/');

		// View Register Form
		$markers = array();

		// Add fields
		if (is_object($this->AddFields)) {
			$_addFields = $this->AddFields->getInputs();
            foreach($_addFields as $k => $field) {
                $markers[strtolower($k)] = $field;
            }
		}
		
		
		$this->Register['Protector']->cleanCaptcha('reguser');


        // Check for preview or errors
        $data = array(
            'name' => null,
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


        $errors = $this->Register['Validate']->getErrors();
        if (isset($_SESSION['FpsForm'])) unset($_SESSION['FpsForm']);
        if (!empty($errors)) $markers['errors'] = $errors;
		else $markers['errors'] = '';
		

		list ($captcha, $captcha_text) = $this->Register['Protector']->getCaptcha('reguser');
        $markers['captcha'] = $captcha;
        $markers['captcha_text'] = $captcha_text;
        $data['name']    = $data['name'];
        $data['fpol']  	= (!empty($data['pol']) && $data['pol'] === 'f') ? ' checked="checked"' : '';
        $data['mpol']  	= (!empty($data['pol']) && $data['pol'] === 'm') ? ' checked="checked"' : '';


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


        $data['byears_selector'] = createOptionsFromParams(1970, 2008, $data['byear']);
        $data['bmonth_selector'] = createOptionsFromParams(1, 12, $data['bmonth']);
        $data['bday_selector'] = createOptionsFromParams(1, 31, $data['bday']);
        $markers['user'] = $data;

        $source = $this->render('addnewuserform.html', array('context' => $markers));
		return $this->_view($source);
	}



	/**
	 * Write into base and check data. Also work for additional fields.
	 */
	public function add()
    {
		if (!empty($_SESSION['user']['id'])) redirect('/');


		// Обрезаем переменные до длины, указанной в параметре maxlength тега input
		$fields = array('name', 'password', 'confirm', 'email', 'icq', 'jabber', 'pol', 'city', 'telephone', 
			'byear', 'bmonth', 'bday', 'url', 'about', 'signature', 'keystring');
		
		$fields_settings = (array)$this->Register['Config']->read('fields', 'users');
		$fields_settings = array_merge($fields_settings, array('email', 'login', 'password', 'confirm'));

		
		foreach ($fields as $field) {
			$$field = (isset($_POST[$field])) ? trim($_POST[$field]) : '';
		}
		
		
		if ('1' === $pol) $pol =  'm';
		else if ('2' === $pol) $pol = 'f';
		else $pol = '';

	
		// Обрезаем переменные до длины, указанной в параметре maxlength тега input
		$name    	  = mb_substr($name, 0, 30);
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


		$errors = $this->Register['Validate']->check($this->Register['action']);


		// Проверяем, заполнены ли обязательные поля
		// Additional fields checker
		if (is_object($this->AddFields)) {
            try {
                $_addFields = $this->AddFields->checkFields();
            } catch (Exception $e) {
                $errors[] = $this->AddFields->getErrors();
            }
		}

	
		// Проверяем поле "код"
		if (!empty($keystring)) {									
			if (!$this->Register['Protector']->checkCaptcha('reguser', $keystring))
				$errors[] = __('Wrong protection code');
		}
		$this->Register['Protector']->cleanCaptcha('reguser');



		$new_name = preg_replace( "#[^- _0-9a-zА-Яа-я]#i", "", $name );
		// Формируем SQL-запрос
        $res = $this->Model->getSameNics($new_name);
		if ($res) $errors[] = sprintf(__('Name already exists'), $new_name);
		
		
		/* check avatar */
		$tmp_key = rand(0, 9999999);
		if (!empty($_FILES['avatar']['name'])) {
			$path = ROOT . '/sys/tmp/images/' . $tmp_key . '.jpg';

			if (move_uploaded_file($_FILES['avatar']['tmp_name'], $path)) {
				chmod($path, 0644);
				@$sizes = resampleImage($path, $path, 100);
				if (!$sizes) {
					@unlink($path);
					$errors[] = __('Some error in avatar');
				}
			}
		}
		

		$timezone = (int)$_POST['timezone'];
		if ( $timezone < -12 or $timezone > 12 ) $timezone = 0;

		
		// Если были допущены ошибки при заполнении формы - перенаправляем посетителя на страницу регистрации
		if (!empty($errors)) {
			$_SESSION['FpsForm'] = array_merge(array('name' => null, 'email'=> null, 'timezone' => null, 'icq' => null,
                'url' => null, 'about' => null, 'signature' => null, 'pol' => $pol, 'telephone' => null, 'city' => null,
                'jabber' => null, 'byear' => null, 'bmonth' => null, 'bday' => null), $_POST);
			$_SESSION['FpsForm']['errors'] = $errors;
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
        $id = $entity->save();
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
            $entity->setPassw($password);
            $context = array(
                'activation_link' => 'http://'.$_SERVER['SERVER_NAME'] . '/users/activate/'.$code,
                'user' => $entity,
            );
			
			$subject = 'Регистрация на форуме '.$_SERVER['SERVER_NAME'];
			$mailer = new AtmMail(ROOT . '/sys/settings/email_templates/');
			$mailer->prepare('registration');
			$mailer->sendMail($email, $subject, $context);
			
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
        $markers['errors'] = $this->Register['Validate']->getErrors();
        if (isset($_SESSION['FpsForm'])) unset($_SESSION['FpsForm']);


        $markers['action'] = get_url('/users/send_new_password/');
        $source = $this->render('newpasswordform.html', array('context' => $markers));


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
		// Обрезаем переменные до длины, указанной в параметре maxlength тега input
		$name  = (!empty($_POST['username'])) ? mb_substr( $_POST['username'], 0, 30 ) : '';
		$email = (!empty($_POST['email'])) ? mb_substr( $_POST['email'], 0, 60 ) : '';
		$name  = trim( $name );
		$email = trim( $email );

        if (!empty($name)) {
            $this->Register['Validate']->disableFieldCheck('email');
        } else if (!empty($email)) {
            $this->Register['Validate']->disableFieldCheck('username');
        }
		
		$errors = $this->Register['Validate']->check($this->Register['action']);
		if (!empty($errors)) {
			$_SESSION['FpsForm'] = array();
			$_SESSION['FpsForm']['errors'] = $errors;
			redirect('/users/new_password_form/');
		}
		

		if (!empty($name)) {
			$res = $this->Model->getCollection(array('name' => $name));
		} else if (!empty($email)) {
			$res = $this->Model->getCollection(array('email' => $email));
		}
		
		if (empty($res)) {
			$_SESSION['FpsForm'] = array();
			$_SESSION['FpsForm']['errors'] = array(__('Wrong login or email'));
			redirect('/users/new_password_form/');
		}


		// Небольшой код, который читает содержимое директории activate
		// и удаляет старые файлы для активации пароля (были созданы более суток назад)
		touchDir(ROOT . '/sys/tmp/activate/');
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


		$context = array(
			'activation_link' => 'http://'.$_SERVER['SERVER_NAME'] . '/users/activate_password/'.$code,
			'new_password' => $newPassword,
			'user_name' => $name,
		);
		
		$subject = 'Активация пароля на форуме '.$_SERVER['SERVER_NAME'];
		$mailer = new AtmMail(ROOT . '/sys/settings/email_templates/');
		$mailer->prepare('new_password');
		$mailer->sendMail($user->getEmail(), $subject, $context);


		$msg = __('We send mail to your e-mail');
		$source = $this->render('infomessage.html', array('info_message' => $msg));

		/* clean DB cache */
		$this->Register['DB']->cleanSqlCache();
		if ($this->Log) $this->Log->write('send new passw', 'name(' . $name . '), mail(' . $email . ')');
		return $this->_view($source);
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


        $user = $this->Model->getById((int)$_SESSION['user']['id']);
		if (is_object($this->AddFields) && $user) {
            $user = $this->AddFields->mergeRecords(array($user), true);
            $user = $user[0];
		}
		$user->setStatistic($this->Model->getFullUserStatistic($_SESSION['user']['id']));



        // Check for preview or errors
        $data = array('email' => null, 'timezone' => null, 'icq' => null, 'jabber' => null, 'pol' => null, 'city' => null, 'telephone' => null, 'byear' => null, 'bmonth' => null, 'bday' => null, 'url' => null, 'about' => null, 'signature' => null, 'email_notification' => null);
        //$data = array_merge($data, $user);
        $data = Validate::getCurrentInputsValues($user, $data);
        $markers = array();


        $markers['errors'] = $this->Register['Validate']->getErrors();
        if (isset($_SESSION['FpsForm'])) unset($_SESSION['FpsForm']);


	
        $fpol = ($data->getPol() && $data->getPol() === 'f') ? ' checked="checked"' : '';
        $data->setFpol($fpol);
        $mpol = ($data->getPol() && $data->getPol() === 'm') ? ' checked="checked"' : '';
        $data->setMpol($mpol);
		
		
        $markers['action'] = get_url('/users/update/');
        if ($data->getPol() === 'f') $data->setPol(__('f'));
        else if ($data->getPol() === 'm') $data->setPol(__('m'));
        else $data->setPol(__('no gender'));
		


        if (file_exists(ROOT . '/sys/avatars/' . $user->getId() . '.jpg')) {
            $data->setAvatar(get_url('/sys/avatars/' . $user->getId() . '.jpg'));
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
        $markers['options'] = $options;
        $markers['servertime'] = date( "d.m.Y H:i:s" );
        $data->setByears_selector(createOptionsFromParams(1950, 2008, $data->getByear()));
        $data->setBmonth_selector(createOptionsFromParams(1, 12, $data->getBmonth()));
        $data->setBday_selector(createOptionsFromParams(1, 31, $data->getBday()));


        $unlinkfile = '';
        if (is_file(ROOT . '/sys/avatars/' . $_SESSION['user']['id'] . '.jpg')) {
            $unlinkfile = '<input type="checkbox" name="unlink" value="1" />'
                . __('Are you want delete file') . "\n";
        }
        $markers['unlinkfile'] = $unlinkfile;
        $markers['user'] = $data;
       
		
        $source = $this->render('edituserform.html', array('context' => $markers));

		
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


        $markers = array();
		
		$fields = array('email', 'icq', 'jabber', 'pol', 'city', 'telephone', 'byear', 
			'bmonth', 'bday', 'url', 'about', 'signature', 'email_notification', 'summer_time');
		$fields_settings = (array)$this->Register['Config']->read('fields', 'users');
		$fields_settings = array_merge($fields_settings, array('email'));
		
		
		foreach ($fields as $field) {
			$$field = (isset($_POST[$field])) ? trim($_POST[$field]) : '';
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
		$email_notification = intval($email_notification);
		$summer_time = intval($summer_time);


        // Если заполнено поле "Текущий пароль" - значит пользователь
        // хочет изменить его или поменять свой e-mail
        $changePassword = false;
        $changeEmail = false;


        // if new and old emails are equal, we needn't check password
        if (empty($newpassword) && $email == $_SESSION['user']['email']) {
            $this->Register['Validate']->disableFieldCheck('password');
        } else {
            // want to change password
            if (!empty($newpassword)) $changePassword = true;
            // user want to change email
            if ($email != $_SESSION['user']['email']) $changeEmail = true;
        }


        $errors = $this->Register['Validate']->check($this->Register['action']);


		// Additional fields
		if (is_object($this->AddFields)) {
            try {
                $_addFields = $this->AddFields->checkFields();
            } catch (Exception $e) {
                $errors[] = $this->AddFields->getErrors();
            }
		}
		

		$tmp_key = rand(0, 9999999);
		if (!empty($_FILES['avatar']['name'])) {
		
			touchDir(ROOT . '/sys/tmp/images/', 0777);
			$path = ROOT . '/sys/tmp/images/' . $tmp_key . '.jpg';
			
			
			if (!isset($check_image) && move_uploaded_file($_FILES['avatar']['tmp_name'], $path)) {
				chmod($path, 0644);
				@$sizes = resampleImage($path, $path, 100);
				if (!$sizes) {
					@unlink($path);
					$errors[] = __('Some error in avatar');
				}
			} else {
				$errors[] = __('Some error in avatar');
			}
		}

		$timezone = (int)$_POST['timezone'];
		if ($timezone < -12 || $timezone > 12) $timezone = 0;

		
		// if an Errors
		if (!empty($errors)) {
			$_SESSION['FpsForm'] = array_merge(array('login' => null, 'email'=> null, 'timezone' => null, 
			'icq' => null, 'url' => null, 'about' => null, 'signature' => null, 'pol' => $pol, 
			'telephone' => null, 'city' => null, 'jabber' => null, 'byear' => null, 
			'bmonth' => null, 'bday' => null, 'email_notification' => null, 'summer_time' => null), $_POST);
			$_SESSION['FpsForm']['errors'] = $errors;
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
		if (!empty($url) && mb_substr($url, 0, mb_strlen('http://')) !== 'http://') $url = 'http://' . $url;


        $user = $this->Model->getById($_SESSION['user']['id']);

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
        $user->setEmail_notification($email_notification);
        $user->setSummer_time($summer_time);
        $user->save();

		// Additional fields saving
		if (is_object($this->AddFields)) {
			$this->AddFields->save($_SESSION['user']['id'], $_addFields);
		}
		
		
		// Теперь надо обновить данные о пользователе в массиве $_SESSION['user']
		$_SESSION['user'] = array_merge($_SESSION['user'], $user->asArray());
		
		
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
        $data = array('name' => null, 'email' => null, 'timezone' => null, 'icq' => null, 'jabber' => null
        , 'pol' => null, 'city' => null, 'telephone' => null, 'byear' => null, 'bmonth' => null, 'bday' => null
        , 'url' => null, 'about' => null, 'signature' => null);
        $data = Validate::getCurrentInputsValues($user, $data);


        $markers['errors'] = $this->Register['Validate']->getErrors();
        if (isset($_SESSION['FpsForm'])) unset($_SESSION['FpsForm']);



		
        $fpol = ($data->getPol() && $data->getPol() === 'f' || $data->getPol() === '2') ? ' checked="checked"' : '';
        $data->setFpol($fpol);
        $mpol = ($data->getPol() && $data->getPol() === 'm' || $data->getPol() === '1') ? ' checked="checked"' : '';
        $data->setMpol($mpol);
		
		
        $markers['action'] = get_url('/users/update_by_admin/' . $id);
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
		
        $markers['options'] = $options;
        $markers['servertime'] = date( "d.m.Y H:i:s" );
		
		
        $data->setByears_selector(createOptionsFromParams(1950, 2008, $data->getByear()));
        $data->setBmonth_selector(createOptionsFromParams(1, 12, $data->getBmonth()));
        $data->setBday_selector(createOptionsFromParams(1, 31, $data->getBday()));


        $unlinkfile = '';
        if (is_file(ROOT . '/sys/avatars/' . $_SESSION['user']['id'] . '.jpg')) {
            $unlinkfile = '<input type="checkbox" name="unlink" value="1" />'
                . __('Are you want delete file') . "\n";
        }
        $markers['unlinkfile'] = $unlinkfile;


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


        $activation = ($user->getActivation())
            ? __('Activate') . ' <input name="activation" type="checkbox" value="1" >' : __('Active');
        $data->setActivation($activation);

		
		// Navigation Panel
		$nav = array();
		$nav['navigation'] = get_link(__('Home'), '/') . __('Separator')
			. get_link(h($this->module_title), '/users/') . __('Separator') . __('Editing');
		$this->_globalize($nav);


        $markers['user'] = $data;
        $source = $this->render('edituserformbyadmin.html', array('context' => $markers));
		
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

		
		// Получаем данные о пользователе из БД
        $user = $this->Model->getById($id);
        if (!$user) return $this->showInfoMessage(__('Can not find user'), '/users/' );
        if (is_object($this->AddFields) && $user) {
            $user = $this->AddFields->mergeRecords(array($user), true);
            $user = $user[0];
        }


		$fields = array('name', 'email', 'oldEmail', 'icq', 'jabber', 'pol', 'city', 
			'telephone', 'byear', 'bmonth', 'bday', 'url', 'about', 'signature');

		$fields_settings = (array)Config::read('fields', 'users');
		$fields_settings = array_merge($fields_settings, array('email'));


		foreach ($fields as $field) {
			$$field = (isset($_POST[$field])) ? trim($_POST[$field]) : '';
		}
		

		if ('1' === $pol) $pol =  'm';
		else if ('2' === $pol) $pol = 'f';
		else $pol = '';
		
		
		// Обрезаем лишние пробелы
		$newpassword  = (!empty($_POST['newpassword'])) ? trim($_POST['newpassword']) : '';
		$confirm      = (!empty($_POST['confirm'])) ? trim($_POST['confirm']) : '';

		
		// Обрезаем переменные до длины, указанной в параметре maxlength тега input
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


        $errors = $this->Register['Validate']->check($this->Register['action']);


		// Additional fields
		if (is_object($this->AddFields)) {
            try {
                $_addFields = $this->AddFields->checkFields();
            } catch (Exception $e) {
                $errors[] = $this->AddFields->getErrors();
            }
		}
		
		
		
		// Если заполнено поле "Текущий пароль" - значит пользователь
		// хочет изменить его или поменять свой e-mail
		$changePassword = false;
		$changeEmail = false;
			
		// want to change password
		if (!empty($newpassword)) $changePassword = true;

		// user want to change email
		if ($email != $oldEmail) $changeEmail = true;
		
		
		// if new and old emails are equal, we needn't check password
		if ($email == $oldEmail) {
			$this->Register['Validate']->disableFieldCheck('password');
		}
		
		
		$tmp_key = rand(0, 9999999);
		if (!empty($_FILES['avatar']['name'])) {
		
			touchDir(ROOT . '/sys/tmp/images/', 0777);
			$path = ROOT . '/sys/tmp/images/' . $tmp_key . '.jpg';

			
			if (!isset($check_image) && move_uploaded_file($_FILES['avatar']['tmp_name'], $path)) {
				chmod($path, 0644);
				@$sizes = resampleImage($path, $path, 100);
				if (!$sizes) {
					@unlink($path);
					$errors[] = __('Some error in avatar');
				}
			} else {
				$errors[] = __('Some error in avatar');
			}
		}


		$status = (int)$_POST['status'];
		$timezone = (int)$_POST['timezone'];
		if ( $timezone < -12 or $timezone > 12 ) $timezone = 0;


		// Errors
		if (!empty($errors)) {
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
			$_SESSION['FpsForm']['errors'] = $errors;
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
        $user->setName($name);
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
			$privateMessage = get_link(__('Send PM'), '/users/pm_send_form/' . $id);
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
		$markers['group_id'] 		= $user->getStatus();
		$markers['group'] 		    = h($status_info['title']);
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


        $user->setStatistic($this->Model->getFullUserStatistic($id));

		
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
	public function pm_send_form($id = null)
    {
		// Незарегистрированный пользователь не может отправлять личные сообщения
		if (!isset($_SESSION['user'])) $this->showInfoMessage(__('pm_send_form'), '/' . $this->module . '/' );
		//if (!isset($_SESSION['user'])) redirect('/');
		$writer_status = (!empty($_SESSION['user']['status'])) ? $_SESSION['user']['status'] : 0;


		$toUser = '';
		if (isset($id)) {
			$id = (int)$id;
			if ($id > 0) {
				$res = $this->Model->getById($id);
				if ($res) $toUser = $res->getName();
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
            $prevMessage = $this->Parser->getPreview(
                $_SESSION['viewMessage']['message'],
                array('status' => $writer_status));

			$toUser  = h($_SESSION['viewMessage']['toUser']);
			$subject = h($_SESSION['viewMessage']['subject']);
			$message = h($_SESSION['viewMessage']['message']);
			unset($_SESSION['viewMessage']);
		}


		$action = get_url('/users/pm_send');
        $errors = '';
		// Если при заполнении формы были допущены ошибки
		if (isset($_SESSION['FpsForm'])) {
			$errors = $this->Register['Validate']->getErrors();
			$toUser  = h( $_SESSION['FpsForm']['toUser'] );
			$subject = h( $_SESSION['FpsForm']['subject'] );
			$message = h( $_SESSION['FpsForm']['message'] );
			unset($_SESSION['FpsForm']);
		}


		$markers = array();
		$markers['to_user_id'] = $id;
		$markers['errors'] = $errors;
		$markers['action'] = $action;
		$markers['touser'] = $toUser;
		$markers['subject'] = $subject;
		$markers['main_text'] = $message;
		$markers['preview'] = (!empty($prevSource)) ? $prevSource : '';
		$source = $this->render('pm_send_form.html', array('context' => $markers));
		
		
		// Navigation Panel
		$nav = array();
		$nav['navigation'] = get_link(__('Home'), '/') . __('Separator')
			. get_link(h($this->module_title), '/users/') . __('Separator') . __('PM nav');
		$this->_globalize($nav);

		
		return $this->_view($source);
	}

	

	// Отправка личного сообщения (добавляется новая запись в таблицу БД TABLE_MESSAGES)
	public function pm_send()
    {
		// Незарегистрированный пользователь не может отправлять личные сообщения
		if ( !isset( $_SESSION['user'] ) ) {
			redirect('/');
		}

		// Обрезаем лишние пробелы
		$toUser  = trim($_POST['toUser']);
		$subject = (!empty($_POST['subject'])) ? trim($_POST['subject']) : '';
		$message = trim($_POST['main_text']);

		
		// Если пользователь хочет посмотреть на сообщение перед отправкой
		if (isset($_POST['viewMessage']) && !isset($_REQUEST['ajax'])) {
			$_SESSION['viewMessage']             = array();
			$_SESSION['viewMessage']['toUser']   = $toUser;
			$_SESSION['viewMessage']['subject']  = $subject;
			$_SESSION['viewMessage']['message'] = $message;
			redirect('/users/pm_send_form/' );
		}
		
		
		// Проверяем, заполнены ли обязательные поля
		$errors = $this->Register['Validate']->check($this->Register['action']);
		
		
		// Проверяем, есть ли такой пользователь
		if (!empty($toUser)) {
			$to = preg_replace( "#[^- _0-9a-zА-Яа-я]#iu", '', $toUser );
            $res = $this->Model->getCollection(
                array('name' => $toUser),
                array('limit' => 1)
            );


			if (empty($res[0]))
				$errors[] = sprintf(__('No user with this name'), $to);
			if ((count($res) && is_array($res)) && ($res[0]->getId() == $_SESSION['user']['id']) )
				$errors[] = __('You can not send message to yourself');


			//chek max count messages
			if ($res[0]) {
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


				if (!empty($cnt_to) && $cnt_to >= Config::read('max_count_mess', 'users')) {
					$errors[] = __('This user has full  messagebox');
				}
				if (!empty($cnt_from) && $cnt_from >= Config::read('max_count_mess', 'users')) {
					$errors[] = __('You have full  messagebox');
				}
			}
		}

		
		
		// Errors
		if (!empty($errors )) {
			$_SESSION['FpsForm'] = array();
			$_SESSION['FpsForm']['errors'] = $errors;
			$_SESSION['FpsForm']['toUser'] = $toUser;
			$_SESSION['FpsForm']['subject'] = $subject;
			$_SESSION['FpsForm']['message'] = $message;
            if (isset($_REQUEST['ajax'])) {
                $data = $_SESSION['FpsForm'];
                unset($_SESSION['FpsForm']);
                $this->showAjaxResponse($data);
            } else {
                redirect('/users/pm_send_form/');
            }
		}

		// Все поля заполнены правильно - "посылаем" сообщение
        $toUser = $res[0];
		$to = $toUser->getId();
		$from = $_SESSION['user']['id'];


        $data = array(
            'to_user' => $to,
            'from_user' => $from,
            'sendtime' => new Expr('NOW()'),
            'subject' => $subject,
            'message' => $message,
            'id_rmv' => 0,
            'viewed' => 0,
        );
        $className = $this->Register['ModManager']->getEntityName('Messages');
        $message = new $className($data);
        $last_id = $message->save();


        if ($last_id) {
            if (Config::read('new_pm_mail', $this->module) == 1 && $toUser->getEmail_notification()) {
                $context = array(
                    'from_user' => $_SESSION['user'],
                    'user' => $toUser,
                    'link' => get_url('/' . $this->module . '/pm_view/' . $_SESSION['user']['id']),
                );
				
				$mailer = new AtmMail(ROOT . '/sys/settings/email_templates/');
				$mailer->prepare('new_pm_message');
				$mailer->sendMail($toUser->getEmail(), __('New PM on forum'), $context);
            }
        }


		/* clean DB cache */
		$this->Register['DB']->cleanSqlCache();
		if ($this->Log) $this->Log->write('adding pm message', 'message id(' . $last_id . ')');


        if (isset($_REQUEST['ajax'])) {
			$message = $this->Model->getDialog($from, $to, array("`id` < '" . $last_id . "'"));
			$id = (!empty($message[0])) ? $message[0]->getId() : $last_id;
			$this->pm_view_update($id);
		}


		return $this->showInfoMessage(__('Message successfully send'), '/' . $this->module . '/pm_view/' . $to);
	}



	// Функция возвращает личное сообщение для просмотра пользователем
	public function pm_view($user_id = null)
    {
		if (!isset($_SESSION['user'])) redirect('/' . $this->module . '/');
        $user_id = (int)$user_id;
		if ($user_id < 1) redirect('/' . $this->module . '/pm/' );

        $collocutor = $this->Model->getById($user_id);
        if (!$collocutor) $this->showInfoMessage(__('Some error occurred'), '/' . $this->module . '/' );


		// Navigation Panel
		$nav = array();
		$nav['navigation'] = get_link(__('Home'), '/') . __('Separator')
			. get_link(h($this->module_title), '/' . $this->module . '/') . __('Separator') . __('Message');
		$this->_globalize($nav);


        $messages = $this->Model->getDialog($_SESSION['user']['id'], $user_id);
		if (!$messages) {
			redirect('/' . $this->module . '/pm/' );
		}

        $markers = array();
        $markers['response'] = get_url('/' . $this->module . '/pm_send_form/' . $user_id);

        foreach ($messages as $message) {
            if (!$message->getFromuser() || !$message->getTouser()) {
                $this->showInfoMessage(__('Some error occurred'), '/' . $this->module . '/' );
            }

            $text = $this->Textarier->parseBBCodes(
				$message->getMessage(), 
				array('status' => $message->getFromuser()->getStatus()));
            $message->setMessage($text);
            $message->setDeleteLink(get_link(__('Delete'), '/users/pm_delete/' . $message->getId(), array('onClick' => "return confirm('" . __('Are you sure') . "');")));

            // Помечаем сообщение, как прочитанное
            if ($_SESSION['user']['id'] === $message->getTo_user() && $message->getViewed() != 1) {
                $message->setViewed(1);
                $message->save();
            }
        }

        $source = $this->render('pm_view.html', array(
            'context' => $markers,
            'messages' => $messages,
            'collocutor' => $collocutor,
        ));
		
		return $this->_view($source);
	}


    public function pm_view_update($pm_id = null) {
        $this->counter = false;

        $result = array('errors' => null, 'data' => array());
        if (empty($pm_id)) $this->showAjaxResponse($result);
        if (empty($_SESSION['user'])) $this->showAjaxResponse($result);

        // don't use getById, because current user might not be message owner
		$messageModel = $this->Register['ModManager']->getModelInstance('messages');
        $message = $messageModel->getCollection(array(
			'id' => $pm_id,
			"(`to_user` = '" . $_SESSION['user']['id'] . "' OR `from_user` = '" . $_SESSION['user']['id'] . "')",
		));
        if (!$message[0]) {
            $result['errors'] = __('Message not found');
            $this->showAjaxResponse($result);
        }

		$message = $message[0];
        $last_date = $message->getSendtime();
        $owner = $_SESSION['user']['id'];
        $collocutor = ($owner == $message->getTo_user()) ? $message->getFrom_user() : $message->getTo_user();

        $newMessages = $this->Model->getDialog($owner, $collocutor, array("`sendtime` > '" . $last_date . "'"));
        if (is_array($newMessages) && count($newMessages)) {
            foreach ($newMessages as &$mes) {
                $message_text = $this->Textarier->parseBBCodes(
					$mes->getMessage(), 
					array('status' => $mes->getFromuser()->getStatus()));
                $mes_ = array(
                    'touser' => array(
                        'id' => $mes->getTouser()->getId(),
                        'name' => $mes->getTouser()->getName(),
                        'avatar' => $mes->getTouser()->getAvatar(),
                    ),
                    'fromuser' => array(
                        'id' => $mes->getFromuser()->getId(),
                        'name' => $mes->getFromuser()->getName(),
                        'avatar' => $mes->getFromuser()->getAvatar(),
                    ),
                );
                $mes_['sender'] = $mes_['fromuser'];
                $mes = array_merge($mes->asArray(), $mes_);
                $mes['message'] = $message_text;
                $mes['sendtime'] = AtmDateTime::getSimpleDate($mes['sendtime']);
            }
            $result['data'] = $newMessages;
            $this->showAjaxResponse($result);
        }

        $this->showAjaxResponse($result);
    }



	// Папка личных сообщений (входящие)
	public function pm()
    {
		if (!isset($_SESSION['user'])) redirect('/');


        // Navigation Panel
        $nav = array();
        $nav['navigation'] = get_link(__('Home'), '/') . __('Separator')
            . get_link(h($this->module_title), '/users/') . __('Separator') . __('PM nav');
        $this->_globalize($nav);


        $markers = array('error' => '');
        $messages = $this->Model->getUserDialogs($_SESSION['user']['id']);


        if (!is_array($messages) || !count($messages)) {
            $markers['messages'] = array();
            $markers['errors'] = __('This dir is empty');
            $source = $this->render('pm.html', array('context' => $markers));
            return $this->_view($source);
        }


        foreach ($messages as $message) {
            // Если сообщение еще не прочитано
            $icon = ($message->getViewed() == 0) ? 'folder_new' : 'folder';
            $message->setIcon(get_img('/template/'.getTemplateName().'/img/' . $icon . '.gif'));
            $message->setEntryLink(get_link(h($message->getSubject()), '/users/pm_view/' . $message->getId()));
            $message->setEntryUrl(get_url('/users/pm_view/' . $message->getId()));
            $message->setDeleteLink(get_link(__('Delete'), '/users/pm_delete/' . $message->getId(), array('onClick' => "return confirm('" . __('Are you sure') . "');")));
            $message->setMessage(h($message->getMessage()));
        }

        //pr($messages[0]->getTouser()->getAvatar()); die();
		$source = $this->render('pm.html', array('messages' => $messages, 'context' => $markers));
		return $this->_view($source);
	}


	/**
	 * Multi message Delete
	 */
	public function pm_delete_pack()
    {
		$this->pm_delete();
	}
	
	

	// Функция удаляет личное сообщение; ID сообщения передается методом GET
	public function pm_delete($id_msg = null)
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

		$collocutor_id = null;
		foreach ($ids as $idMsg) {
			$messages = $messagesModel->getCollection(array(
				'id' => $idMsg,
				"(`to_user` = '" . $_SESSION['user']['id'] . "' OR `from_user` = '" . $_SESSION['user']['id'] . "')"
			));
			if (count($messages) == 0) {
				continue;
			}

			
			$message = $messages[0];
			$toUser = $message->getTo_user();
			$id_rmv = $message->getId_rmv();
            if ($collocutor_id === null) {
                $collocutor_id = ($message->getTo_user() == $_SESSION['user']['id'])
                    ? $message->getFrom_user()
                    : $message->getTo_user();
            }

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


        $redirect = '/' . $this->module . '/pm';
        if (!empty($collocutor_id)) {
            $messages = $this->Model->getDialog($_SESSION['user']['id'], $collocutor_id);
            if (is_array($messages) && count($messages))
                $redirect = '/' . $this->module . '/pm_view/' . $collocutor_id;
        }

		
		/* clean DB cache */
		$this->Register['DB']->cleanSqlCache();
		if ($this->Log) $this->Log->write('delete pm message(s)', 'message(s) id(' . implode(', ', $ids) . ')');
		return $this->showInfoMessage(__('Operation is successful'), $redirect);
	}

	
	// Функция удаляет личное сообщение; ID сообщения передается методом GET
	public function pm_delete_by_user($id_msg = null)
    {
		if (!isset( $_SESSION['user'])) redirect('/');
		$messagesModel = $this->Register['ModManager']->getModelInstance('Messages');
		
		$multi_del = true;
		if (empty($_POST['ids']) 
		|| !is_array($_POST['ids'])
		|| count($_POST['ids']) < 1) $multi_del = false;

		$idMsg = (int)$id_msg;
		if ($idMsg < 1 && $multi_del === false) redirect('/');
		
		
		// We create array with ids for delete
		// There are ids of users
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

		
		foreach ($ids as $user_id) {
			$messages = $this->Model->getDialog($_SESSION['user']['id'], $user_id);
			if (!$messages) continue;
			
			foreach ($messages as $message) {
				$id_rmv = $message->getId_rmv();
			
				if ($id_rmv == 0) {
					$message->setId_rmv($_SESSION['user']['id']);
					$message->save();
				} else {
					$message->delete();
				}
			}
		}
		
		/* clean DB cache */
		$this->Register['DB']->cleanSqlCache();
		if ($this->Log) $this->Log->write('delete pm by user message(s)', 'user(s) id(' . implode(', ', $ids) . ')');
		return $this->showInfoMessage(__('Operation is successful'), '/' . $this->module . '/pm/' );
	}


	/**
	 *
	 */
	public function send_mail_form($id = null)
    {
		if (!isset($_SESSION['user'])) redirect('/');
		$id = intval($id);
		if (!$id) redirect('/');
        if ($id == $_SESSION['user']['id'])
            return $this->showInfoMessage(__('You can not send message to yourself'), '/' . $this->module);

		
		$toUser = null;
		$user = $this->Model->getById($id);
		if (!empty($user)) $toUser = $user->getName();

        $user->setStatistic($this->Model->getFullUserStatistic($id));

		$markers = array(
			'message' => '',
			'subject' => '',
			'action' => get_url('/users/send_mail/'),
			'to_user' => $toUser,
			'errors' => '',
		);
		
		
		// Если при заполнении формы были допущены ошибки
		if (isset($_SESSION['FpsForm'])) {
			$markers['errors'] = $this->Register['Validate']->getErrors();
			$markers['to_user']  = $_SESSION['FpsForm']['toUser'];
			$markers['subject'] = $_SESSION['FpsForm']['subject'];
			$markers['message'] = $_SESSION['FpsForm']['message'];
			unset($_SESSION['FpsForm']);
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
		if (!isset($_SESSION['user'])) redirect('/');
		
		// Обрезаем лишние пробелы
		$toUser  = trim( $_POST['toUser'] );
		$subject = trim( $_POST['subject'] );
		$message = trim( $_POST['message'] );

		
		$errors = $this->Register['Validate']->check($this->Register['action']);

		
		// Проверяем, есть ли такой пользователь
		if (!empty($toUser)) {
			$to = preg_replace("#[^- _0-9a-zа-яА-Я]#ui", '', $toUser);
			$user = $this->Model->getByName($to);
			if (empty($user))				
				$errors[] = sprintf(__('No user with this name'), $to);
		}
		
		// Если были допущены ошибки при заполнении формы -
		// перенаправляем посетителя для исправления ошибок
		if (!empty($errors)) {
			$_SESSION['FpsForm'] = array();
			$_SESSION['FpsForm']['errors'] = $errors;
			$_SESSION['FpsForm']['toUser']  = $toUser;
			$_SESSION['FpsForm']['subject'] = $subject;
			$_SESSION['FpsForm']['message'] = $message;
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
		


		if (isset($_SESSION['FpsForm']['errors'])) {
			$errors = $this->Register['Validate']->getErrors();
			unset($_SESSION['FpsForm']['errors']);
		}
		
		
		
		$markers = array(
			'form_key' => '',
			'action' => get_url('/users/login/'),
			'new_password' => get_link('Забыли пароль?', '/users/new_password_form/'),
			'errors' => (!empty($errors)) ? $errors : '',
		);


		if ($this->Register['Config']->read('autorization_protected_key', 'secure') === 1) {
			$_SESSION['form_key_mine'] = rand(1000, 9999);
			$form_key = rand(1000, 9999);
			$_SESSION['form_hash'] = md5($form_key . $_SESSION['form_key_mine']);
			$markers['form_key'] = '<input type="hidden" name="form_key" value="' . $form_key . '" />';
		}


        $this->addToPageMetaContext('entity_title', __('Authorization'));

		
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
		if (Config::read('autorization_protected_key', 'secure') === 1) {
			if (empty($_SESSION['form_key_mine'])
			|| empty($_POST['form_key'])		
			|| md5(substr($_POST['form_key'], 0, 10) . $_SESSION['form_key_mine']) != $_SESSION['form_hash']) {
				return $this->showInfoMessage(__('Use authorize form'), '/');
			}
		}

		$errors = $this->Register['Validate']->check($this->Register['action']);
		
		
		// Защита от перебора пароля - при каждой неудачной попытке время задержки увеличивается
		if (isset($_SESSION['FpsForm']['count']) && $_SESSION['FpsForm']['count'] > time()) {
			$errors[] = sprintf(__('You must wait'), ($_SESSION['FpsForm']['count'] - time()));
		}

		// Обрезаем лишние пробелы
		$name      = trim( $_POST['username'] );
		$password  = trim( $_POST['password'] );

			
		// Проверять существование такого пользователя есть смысл только в том
		// случае, если поля не пустые и не содержат недопустимых символов
		if (empty($errors)) {
			$user = $this->Model->getByNamePass($name, $password);
			if (empty($user)) $errors[] = __('Wrong login or pass');
		}

		
		// Если были допущены ошибки при заполнении формы
		if (!empty($errors)) {
			if (!isset($_SESSION['FpsForm']['count'])) $_SESSION['FpsForm']['count'] = 1;
			else if ($_SESSION['FpsForm']['count'] < 10) $_SESSION['FpsForm']['count']++;
  			else if ($_SESSION['FpsForm']['count'] < time()) $_SESSION['FpsForm']['count'] = time() + 10;
			else $_SESSION['FpsForm']['count'] = $_SESSION['FpsForm']['count'] + 10;
			
			$_SESSION['FpsForm']['errors'] = $errors;
			
			
			if (isset($_GET['ajax'])) {
				$data = $_SESSION['FpsForm']; 
				unset($_SESSION['FpsForm']);
				return $this->showAjaxResponse($data);
			} else {
				redirect('/users/login_form/');
			}
		}

		// Все поля заполнены правильно и такой пользователь существует - продолжаем...
		unset($_SESSION['FpsForm']);


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
		$to = '/';
		
		if (isset($_SESSION['authorize_referer'])) {
			$to = '/' . $_SESSION['authorize_referer'];
			
		} else if (!empty($_SERVER['HTTP_REFERER']) 
		&& preg_match('#^http://([^/]+)/(.+)#', $_SERVER['HTTP_REFERER'], $match)) {
			if (!empty($match[1]) && !empty($match[2]) && $match[1] == $_SERVER['SERVER_NAME']) {
				$ref_params = explode('/', $match[2]);
				if (empty($ref_params[0]) || empty($ref_params[1]) ||
				($ref_params[0] != 'users' && $ref_params[1] != 'login_form')) {
					$to = '/' . $match[2];
				}
			}
		}
		
		
		redirect($to);
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
			$user->setRating($user->getRating() + $points);
			$user->save();


			$voteEntity = $this->Register['ModManager']->getEntityName('UsersVotes');
			$voteEntity = new $voteEntity(array(
				'from_user' => $from_id,
				'to_user' => $to_id,
				'comment' => $comment,
				'points' => $points,
				'date' => new Expr('NOW()'),
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
			die($res);
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
				$markers['message'] = $this->Textarier->parseBBCodes($entity->getMessage(), $entity);

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
        $this->addToPageMetaContext('entity_title', $title);


		$navi = array(
            'add_link'      => ($this->ACL->turn(array($this->module, 'add_materials'), false))
                ? get_link(__('Add material'), $this->getModuleURL('add_form/')) : '',
            'module_url'    => get_url($this->getModuleURL()),
            'category_url'  => get_url($this->getModuleURL('comments/' . ($id ? $id : ''))),
            'category_name' => $title,
            'navigation'    => get_link(__('Home'), '/') . __('Separator')
                . get_link(h($this->module_title), $this->getModuleURL()) . __('Separator')
                . $title,
        );
		$this->_globalize($navi);


		return $this->_view('');
	}



    public function search_niks() {
        $result = array();
        if (empty($_GET['name']))
            return $this->showAjaxResponse($result);

        $name = $this->Register['DB']->escape($_GET['name']);
        $params = array("`name` LIKE '%$name%'");
        if (isset($_SESSION['user'])) {
            $authorizedName = $this->Register['DB']->escape($_SESSION['user']['name']);
            $params[] = "`name` NOT LIKE '$authorizedName'";
        }

        $result = $this->Model->getCollection($params, array('limit' => 20));
        if (is_array($result) && count($result))
            $result = array_map(function($row){
                return array(
                    'name' => h($row->getName()),
                    'id' => h($row->getId()),
                );
            }, $result);
        return $this->showAjaxResponse($result);
    }

	
	
	protected function _getValidateRules()
	{
        $Register = Register::getInstance();
		$rules = array(
			'add' => array(
				'name' => array(
					'required' => true,
					'max_lenght' => 20,
					'min_lenght' => 3,
					'pattern' => V_LOGIN,
				),
				'first_name' => array(
					'required' => 'editable',
					'max_lenght' => 20,
					'min_lenght' => 3,
					'pattern' => V_LOGIN,
				),
				'last_name' => array(
					'required' => 'editable',
					'max_lenght' => 20,
					'min_lenght' => 3,
					'pattern' => V_LOGIN,
				),
				'password' => array(
					'min_lenght' => Config::read('min_password_lenght'),
					'required' => true,
				),
				'confirm' => array(
					'compare' => 'password',
					'required' => true,
				),
				'email' => array(
					'required' => true,
					'pattern' => V_MAIL,
				),
				'keystring' => array(
					'required' => true,
					'pattern' => V_CAPTCHA,
				),
				'icq' => array(
					'required' => 'editable',
					'pattern' => V_INT,
				),
				'jabber' => array(
					'required' => 'editable',
					'pattern' => V_MAIL,
				),
				'pol' => array(
					'required' => 'editable',
				),
				'city' => array(
					'required' => 'editable',
					'pattern' => V_LOGIN,
				),
				'telephone' => array(
					'required' => 'editable',
					'pattern' => V_INT,
				),
				'byear' => array(
					'required' => 'editable',
					'pattern' => V_INT,
				),
				'bmonth' => array(
					'required' => 'editable',
					'pattern' => V_INT,
				),
				'bday' => array(
					'required' => 'editable',
					'pattern' => V_INT,
				),
				'url' => array(
					'required' => 'editable',
					'pattern' => V_URL,
				),
				'about' => array(
					'required' => 'editable',
					'pattern' => V_TEXT,
				),
				'signature' => array(
					'required' => 'editable',
					'pattern' => V_TEXT,
				),
				'email_notification' => array(
					'required' => false,
					'pattern' => V_INT,
				),
				'files__avatar' => array(
					'type' => 'image',
					'max_size' => Config::read('max_avatar_size', 'users'),
				),
			),
			'update' => array(
				'first_name' => array(
					'required' => 'editable',
					'max_lenght' => 20,
					'min_lenght' => 3,
					'pattern' => V_LOGIN,
				),
				'last_name' => array(
					'required' => 'editable',
					'max_lenght' => 20,
					'min_lenght' => 3,
					'pattern' => V_LOGIN,
				),
				'password' => array(
					'min_lenght' => Config::read('min_password_lenght'),
					'required' => true,
                    'function' => function($errors) use ($Register) {
                        if ( md5($_POST['password']) != $_SESSION['user']['passw'] )
                            return __('Wrong current pass');
                    },
				),
				'confirm' => array(
					'compare' => 'newpassword',
					'required' => false,
				),
				'newpassword' => array(
					'required' => false,
					'min_lenght' => Config::read('min_password_lenght'),
				),
				'email' => array(
					'required' => true,
					'pattern' => V_MAIL,
				),
				'icq' => array(
					'required' => 'editable',
					'pattern' => V_INT,
				),
				'jabber' => array(
					'required' => 'editable',
					'pattern' => V_MAIL,
				),
				'pol' => array(
					'required' => 'editable',
				),
				'city' => array(
					'required' => 'editable',
					'pattern' => V_LOGIN,
				),
				'telephone' => array(
					'required' => 'editable',
					'pattern' => V_INT,
				),
				'byear' => array(
					'required' => 'editable',
					'pattern' => V_INT,
				),
				'bmonth' => array(
					'required' => 'editable',
					'pattern' => V_INT,
				),
				'bday' => array(
					'required' => 'editable',
					'pattern' => V_INT,
				),
				'url' => array(
					'required' => 'editable',
					'pattern' => V_URL,
				),
				'about' => array(
					'required' => 'editable',
					'pattern' => V_TEXT,
				),
				'signature' => array(
					'required' => 'editable',
					'pattern' => V_TEXT,
				),
				'files__avatar' => array(
					'type' => 'image',
					'max_size' => Config::read('max_avatar_size', 'users'),
				),
			),
			'update_by_admin' => array(
				'name' => array(
					'required' => true,
					'max_lenght' => 20,
					'min_lenght' => 3,
					'pattern' => V_LOGIN,
				),
				'first_name' => array(
					'required' => 'editable',
					'max_lenght' => 20,
					'min_lenght' => 3,
					'pattern' => V_LOGIN,
				),
				'last_name' => array(
					'required' => 'editable',
					'max_lenght' => 20,
					'min_lenght' => 3,
					'pattern' => V_LOGIN,
				),
				'confirm' => array(
					'compare' => 'newpassword',
					'required' => false,
				),
				'newpassword' => array(
					'required' => false,
					'min_lenght' => Config::read('min_password_lenght'),
				),
				'email' => array(
					'required' => true,
					'pattern' => V_MAIL,
				),
				'icq' => array(
					'required' => 'editable',
					'pattern' => V_INT,
				),
				'jabber' => array(
					'required' => 'editable',
					'pattern' => V_MAIL,
				),
				'pol' => array(
					'required' => 'editable',
				),
				'city' => array(
					'required' => 'editable',
					'pattern' => V_LOGIN,
				),
				'telephone' => array(
					'required' => 'editable',
					'pattern' => V_INT,
				),
				'byear' => array(
					'required' => 'editable',
					'pattern' => V_INT,
				),
				'bmonth' => array(
					'required' => 'editable',
					'pattern' => V_INT,
				),
				'bday' => array(
					'required' => 'editable',
					'pattern' => V_INT,
				),
				'url' => array(
					'required' => 'editable',
					'pattern' => V_URL,
				),
				'about' => array(
					'required' => 'editable',
					'pattern' => V_TEXT,
				),
				'signature' => array(
					'required' => 'editable',
					'pattern' => V_TEXT,
				),
				'files__avatar' => array(
					'type' => 'image',
					'max_size' => Config::read('max_avatar_size', 'users'),
				),
			),
			'pm_send' => array(
				'toUser' => array(
					'required' => true,
					'max_lenght' => 20,
					'pattern' => V_LOGIN,
				),
				'subject' => array(
					'required' => false,
					'max_lenght' => 200,
				),
				'main_text' => array(
					'required' => true,
					'max_lenght' => Config::read('max_message_lenght', 'users'),
				),
			),
			'send_mail' => array(
				'toUser' => array(
					'required' => true,
					'max_lenght' => 20,
					'pattern' => V_LOGIN,
				),
				'subject' => array(
					'required' => true,
					'max_lenght' => 200,
				),
				'message' => array(
					'required' => true,
					'max_lenght' => Config::read('max_mail_lenght', 'users'),
				),
			),
			'login' => array(
				'username' => array(
					'required' => true,
					'pattern' => V_LOGIN,
				),
				'password' => array(
					'required' => true,
				),
			),
			'send_new_password' => array(
				'username' => array(
					'required' => true,
					'pattern' => V_LOGIN,
				),
				'email' => array(
					'required' => true,
					'pattern' => V_MAIL,
				),
			),
		);
		
		return $rules;
	}
}

