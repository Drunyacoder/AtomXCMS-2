<?php
//turn access
$this->ACL->turn(array($this->module, 'edit_comments'));
$id = (!empty($id)) ? (int)$id : 0;
if ($id < 1) redirect('/' . $this->module);


$errors = '';


$commModel = $this->Register['ModManager']->getModelInstance('Comments');
$comment = $commModel->getById($id);
if (!$comment) return $this->showInfoMessage(__('Comment not found'), $this->module);


$message = (!empty($_POST['message'])) ? $_POST['message'] : '';
$message = mb_substr($message, 0, $this->Register['Config']->read('comment_lenght', $this->module));
$message = trim($message);


/* cut and trim values */
if ($comment->getUser_id() > 0) {
	$name = $comment->getName();
	
	// for validation
	$_POST['login'] = $name;
} else {
	$name = mb_substr($_POST['login'], 0, 70);
	$name = trim($name);
}


if (!$comment->getUser_id()) {
	$this->Register['Validate']->disableFieldCheck('login');
}

$errors .= $this->Register['Validate']->check($this->getValidateRules());

	
/* if an error */
if (!empty( $errors )) {
	$_SESSION['editCommentForm'] = array();
	$_SESSION['editCommentForm']['error'] = '<p class="errorMsg">' . __('Some error in form') . '</p>'
		."\n".'<ul class="errorMsg">'."\n".$errors.'</ul>'."\n";
	$_SESSION['editCommentForm']['message'] = $message;
	$_SESSION['editCommentForm']['name'] = $name;
	redirect('/' . $this->module . '/edit_comment_form/' . $id );
}


//remove cache
$this->Cache->clean(CACHE_MATCHING_TAG, array('module_' . $this->module, 'record_id_' . $comment->getEntity_id()));
$this->DB->cleanSqlCache();


// Update comment
$comment->setMessage($message);
if ($name) $comment->setName($name);
$comment->save();


if ($this->Log) $this->Log->write('editing comment for ' . $this->module, $this->module . ' id(' . $comment->getEntity_id() . '), comment id(' . $id . ')');
return $this->showInfoMessage(__('Operation is successful'), '/' . $this->module . '/view/' . $comment->getEntity_id() );






