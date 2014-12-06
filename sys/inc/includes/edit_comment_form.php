<?php
//turn access
$this->ACL->turn(array($this->module, 'edit_comments'));
$id = (!empty($id)) ? (int)$id : 0;
if ($id < 1) redirect('/' . $this->module);



$commModel = $this->Register['ModManager']->getModelInstance('Comments');
$comment = $commModel->getById($id);
if (!$comment) return $this->showInfoMessage(__('Comment not found'), '/' . $this->module . '/');


// Categories tree
$this->Model->bindModel('category');
$entity = $this->Model->getById($comment->getEntity_id());
if ($entity && $entity->getCategory_id()) {
	$this->categories = $this->_getCatsTree($entity->getCategory_id());
} else {
	$this->categories = $this->_getCatsTree();
}

Plugins::intercept('view_category', $entity->getCategory());

$markers = array();
$markers['disabled'] = ($comment->getUser_id()) ? ' disabled="disabled"' : '';


// Если при заполнении формы были допущены ошибки
if (isset($_SESSION['FpsForm'])) {
    $errors   = $_SESSION['FpsForm']['errors'];
	$message  = $_SESSION['FpsForm']['message'];
	$name     = $_SESSION['FpsForm']['name'];
	unset($_SESSION['FpsForm']);
} else {
	$errors  = '';
	$message = $comment->getMessage();
	$name    = $comment->getName();
}


$markers['action'] = get_url('/' . $this->module . '/update_comment/' . $id);
$markers['errors'] = $errors;
$markers['name'] = h($name);
$markers['message'] = h($message);
$source = $this->render('editcommentform.html', array(
	'form' => $markers,
	'comment' => $comment,
));
$this->comments = '';

return $this->_view($source);
