<?php
//turn access
$this->ACL->turn(array($this->module, 'add_comments'));
if (!isset($_POST['login']) || !isset($_POST['message'])) {
	redirect('/' . $this->module);
}
$id = (int)$id;
if ($id < 1) redirect('/' . $this->module);


$target_new = $this->Model->getById($id);
if (!$target_new) redirect('/' . $this->module);
if (!$target_new->getCommented()) return $this->showInfoMessage(__('Comments are denied here'), '/' . $this->module . '/view/' . $id); 


/* cut and trim values */
if (!empty($_SESSION['user'])) {
	$name = $_SESSION['user']['name'];
} else {
	$name = mb_substr($_POST['login'], 0, 70);
	$name = trim($name);
}


$mail = '';
$message = mb_substr($_POST['message'], 0, $this->Register['Config']->read('comment_lenght', $this->module));
$message = trim( $message );
$ip      = (!empty($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : '';
$keystring = (isset($_POST['captcha_keystring'])) ? trim($_POST['captcha_keystring']) : '';


// Check fields
$error  = '';
$valobj = $this->Register['Validate'];
if (empty($name))                          
	$error = $error . '<li>' . __('Empty field "login"') . '</li>' . "\n";
elseif (!$valobj->cha_val($name, V_TITLE))  
	$error = $error . '<li>' . __('Wrong chars in field "login"') . '</li>' . "\n";
if (empty($message))                       
	$error = $error . '<li>' . __('Empty field "text"') . '</li>' . "\n";


// Check captcha if need exists	 
if (!$this->ACL->turn(array('other', 'no_captcha'), false)) {
	if (empty($keystring))                      
		$error = $error . '<li>' . __('Empty field "code"') . '</li>' . "\n";

	
	// Проверяем поле "код"
	if (!empty($keystring)) {
		// Проверяем поле "код" на недопустимые символы
		if (!$valobj->cha_val($keystring, V_CAPTCHA))
			$error = $error.'<li>' . __('Wrong chars in field "code"') . '</li>'."\n";									
		if (!isset($_SESSION['captcha_keystring'])) {
			if (file_exists(ROOT . '/sys/logs/captcha_keystring_' . session_id() . '-' . date("Y-m-d") . '.dat')) {
				$_SESSION['captcha_keystring'] = file_get_contents(ROOT . '/sys/logs/captcha_keystring_' . session_id() . '-' . date("Y-m-d") . '.dat');
				@_unlink(ROOT . '/sys/logs/captcha_keystring_' . session_id() . '-' . date("Y-m-d") . '.dat');
			}
		}
		if (!isset($_SESSION['captcha_keystring']) || $_SESSION['captcha_keystring'] != $keystring)
			$error = $error.'<li>' . __('Wrong protection code') . '</li>'."\n";
	}
	unset($_SESSION['captcha_keystring']);
}


/* if an errors */
if (!empty($error)) {
	$_SESSION['addCommentForm'] = array();
	$_SESSION['addCommentForm']['error'] = '<p class="errorMsg">' . __('Some error in form') . '</p>' .
		"\n" . '<ul class="errorMsg">' . "\n" . $error . '</ul>' . "\n";
	$_SESSION['addCommentForm']['name'] = $name;
	$_SESSION['addCommentForm']['message'] = $message;
	redirect('/' . $this->module . '/view/' . $id);
}


/* SPAM DEFENCE */
if (isset($_SESSION['unix_last_post']) and (time()-$_SESSION['unix_last_post'] < 10)) {
	return $this->showInfoMessage(__('Your message has been added'), '/' . $this->module . '/view/' . $id);
} else {
	$_SESSION['unix_last_post'] = time();
}


/* remove cache */
$this->Cache->clean(CACHE_MATCHING_TAG, array('module_' . $this->module, 'record_id_' . $id));
$this->DB->cleanSqlCache();


/* save data */	
$data = array(
	'entity_id'   => $id,
	'name'     => $name,
	'message'  => $message,
	'ip'       => $ip,
	'user_id'  => (!empty($_SESSION['user']['id'])) ? $_SESSION['user']['id'] : 0,
	'date'     => new Expr('NOW()'),
	'mail'     => $mail,
);



$className = $this->Register['ModManager']->getEntityName('Comments');
$entityComm = new $className($data);

if ($entityComm) {

	$entityComm->save();

	$entity = $this->Model->getById($id);
	if ($entity) {
		$entity->setComments($entity->getComments() + 1);
		$entity->save();
		

		if ($this->Log) $this->Log->write('adding comment to ' . $this->module, $this->module . ' id(' . $id . ')');
		return $this->showInfoMessage(__('Comment is added'), '/' . $this->module . '/view/' . $id);
	}
}



if ($this->Log) $this->Log->write('adding comment to ' . $this->module, $this->module . ' id(' . $id . ')');
return $this->showInfoMessage(__('Comment is added'), '/' . $this->module . '/view/' . $id );
