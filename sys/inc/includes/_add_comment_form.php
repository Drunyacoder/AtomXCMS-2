<?php
//turn access
$this->ACL->turn(array($this->module, 'add_comments'));

$id = (int)$id;



if ($id < 1) {
	$html = '';
} else { 

	$markers = array();
	$name = (!empty($_SESSION['user']['name'])) ? h($_SESSION['user']['name']) : '';
	$message = '';	
	$info = '';
	

	/* if an error */
	if (isset($_SESSION['addCommentForm'])) {
		$info = $this->render('infomessage.html', array('info_message' => $_SESSION['addCommentForm']['error']));
		$name = h($_SESSION['addCommentForm']['name']);
		$message = h($_SESSION['addCommentForm']['message']);
		unset($_SESSION['addCommentForm']);
	}


	$markers['action'] = get_url('/' . $this->module . '/add_comment/' . $id);
	
	//$kcaptcha = get_img('/sys/inc/kcaptcha/kc.php?'.session_name().'='.session_id());
	$kcaptcha = '';
	if (!$this->ACL->turn(array('other', 'no_captcha'), false)) {
		$kcaptcha = getCaptcha();
	}
	
	$markers['disabled'] = (!empty($_SESSION['user'])) ? ' disabled="disabled"' : '';
	$markers['add_comment_captcha'] = $kcaptcha;
	$markers['add_comment_name'] = $name;
	$markers['add_comment_message'] = $message;
	$html = $this->render('addcommentform.html', array('data' => $markers));
	$html = $info . $html . "\n";
}


