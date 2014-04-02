<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.6.0                         |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
| @subpackege    Chat Module                   |
| @copyright     ©Andrey Brykin 2010-2013      |
| @last mod      2013/11/01                    |
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



class ChatModule extends Module {
	/**
	* @template  layout for module
	*/
	public $template = 'chat';
	/**
	* @module_title  title of module
	*/
	public $module_title = 'Чат';
	/**
	* @module module indentifier
	*/
	public $module = 'chat';

	

	
	/**
	* default action ( show add form and iframe for messages )
	*
	* @return view content
	*/
	public function index() {
		$content = '';
		if ($this->ACL->turn(array('chat', 'add_materials'), false)) {
			$content = $this->add_form();
		} else {
			$content = __('Permission denied');
		}
		return $this->_view($content);
	}
	
	
	
	/**
	*  show messages list 
	*
	* @return view content
	*/
	public function view_messages() {
		$content = '';
		//$this->template = '';
		$chatDataPath = ROOT . '/sys/tmp/chat/messages.dat';
		
		
		if (file_exists($chatDataPath)) {
			$data = unserialize(file_get_contents($chatDataPath));
			if (!empty($data)) {
				$data = array_reverse($data);
				
				
				foreach ($data as $key => &$record) {
				
					$record['message'] = $this->Register['PrintText']->smile(h($record['message']));
				
					/* view ip adres if admin */
					if ($this->ACL->turn(array('chat', 'delete_materials'), false)) {
						$record['ip'] = '<a target="_blank" href="https://apps.db.ripe.net/search/query.html?searchtext=' 
							. h($record['ip']) . '" class="fps-ip" title="IP: ' . h($record['ip']) . '"></a>';
					} else {
						$record['ip'] = '';
					}
				}
				
				$content = $this->render('list.html', array('messages' => $data));
			}
		}
		
		header('Refresh: 10; url=' . get_url('/chat/view_messages/'));
		echo $content;
	}
	

	
	/**
	* add message form
	*
	* @return  none
	*/
	public function add() {
		$Parser = $this->Register['DocParser'];
		$ACL = $this->Register['ACL'];
	
		if (!$ACL->turn(array('chat', 'add_materials'), false)) {
			return;
		}
		if (!isset($_POST['message'])) {
			die(__('Needed fields are empty'));
		}
		
		/* cut and trim values */
		$name    = (!empty($_SESSION['user'])) ? h($_SESSION['user']['name']) : __('Guest');
		$message = mb_substr( $_POST['message'], 0, $this->Register['Config']->read('max_lenght', 'chat'));
		$name    = trim( $name );
		$message = trim( $message );
		$ip      = (!empty($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : '';
		$keystring = (isset($_POST['keystring'])) ? trim($_POST['keystring']) : '';
		
		
		// Check fields
		$errors = '';
		
		
		$valobj = $this->Register['Validate'];
		if (!empty($name) && !$valobj->cha_val($name, V_TITLE))  
			$errors .= '<li>' . __('Wrong chars in field "login"') . '</li>' . "\n";
			
			
			
		// Check captcha if need exists	 
		if (!$ACL->turn(array('other', 'no_captcha'), false)) {

				
			// Проверяем поле "код"
			if (!empty($keystring)) {				
				if (!$this->Register['Protector']->checkCaptcha('chatsend', $keystring))
					$errors .= '<li>' . __('Wrong protection code') . '</li>' . "\n";
			}
			$this->Register['Protector']->cleanCaptcha('chatsend');
			
			
		} else {
			$this->Register['Validate']->disableFieldCheck('keystring');
		}
		
		$errors .= $this->Register['Validate']->check($this->getValidateRules());
		
		
		/* remember name */
		$_SESSION['chat_name'] = $name;
		
		/* if an errors */
		if (!empty($errors)) {
			$_SESSION['addForm']            = array();
			$_SESSION['addForm']['error']   = '<p class="errorMsg">' . __('Some error in form') . '</p>' . 
				"\n" . '<ul class="errorMsg">' . "\n" . $errors . '</ul>' . "\n";
			$_SESSION['addForm']['name']    = $name;
			$_SESSION['addForm']['message'] = $message;
			die($_SESSION['addForm']['error']);
		}
		
		/* create dir for chat tmp file if not exists */
		if (!file_exists(ROOT . '/sys/tmp/chat/')) mkdir(ROOT . '/sys/tmp/chat/', 0777, true);
		/* get data */
		if (file_exists(ROOT . '/sys/tmp/chat/messages.dat')) {
			$data = unserialize(file_get_contents(ROOT . '/sys/tmp/chat/messages.dat'));
		} else {
			$data = array();
		}
		
		
		/* cut data (no more 50 messages */
		while (count($data) > 50) {
			array_shift($data);
		}
		$data[] = array(
			'name' => $name,
			'message' => $message,
			'ip' => $ip,
			'date' => date("Y-m-d h:i"),
		);
		
		
		/* save messages */
		$file = fopen(ROOT . '/sys/tmp/chat/messages.dat', 'w+');
		fwrite($file, serialize($data));
		fclose($file);
		die('ok');
		
	}
	
	
	
	/**
	* view add message form
	*
	* @return (str)  add form
	*/
	public static function add_form() {
        $Register = Register::getInstance();


		$Parser = $Register['DocParser'];
		$Parser->templateDir = 'chat';
		$ACL = $Register['ACL'];
		if (!$ACL->turn(array('chat', 'add_materials'), false)) 
			return __('Dont have permission to write post');
	

		$markers = array();

		/* if an errors */
		if (isset($_SESSION['addForm'])) {
			$message  = $_SESSION['addForm']['message'];
			$name     = $_SESSION['addForm']['name'];
			unset( $_SESSION['addForm'] );
		} else if (isset($_SESSION['chat_name'])) {
			$message = '';
			$name    = (!empty($_SESSION['chat_name'])) ? $_SESSION['chat_name'] : '';
		} else {
			$message = '';
			$name = (!empty($_SESSION['user']['name'])) ? $_SESSION['user']['name'] : '';
		}
		

		if (!$ACL->turn(array('other', 'no_captcha'), false)) {
			$Register = Register::getInstance();
			list ($captcha, $captcha_text) = $Register['Protector']->getCaptcha('chatsend');
			$markers['captcha'] = $captcha;
			$markers['captcha_text'] = $captcha_text;
		}
		$markers['action'] = get_url('/chat/add/');
		$markers['login'] = h($name);
		$markers['message'] = h($message);

		
		
		$View = new Fps_Viewer_Manager(new Fps_Viewer_Loader());
		$View->setLayout('chat');
		$source = $View->view('addform.html', array('data' => $markers));


		return $source;
	}
	
	
	
	public function getValidateRules() 
	{
		$rules = array(
			'add' => array(
				'message' => array(
					'required' => true,
				),
				'keystring' => array(
					'required' => true,
					'pattern' => V_CAPTCHA,
				),
			),
		);
		return array($this->module => $rules);
	}
}

