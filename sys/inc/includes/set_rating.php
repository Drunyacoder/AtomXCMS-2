<?php
//turn access
if (!$this->ACL->turn(array($this->module, 'use_rating'), false)) 
	die(__('Some error occurred'));
	
	
$id = (!empty($id)) ? (int)$id : 0;
if ($id < 1) redirect('/' . $this->module);


if (!empty($_COOKIE[md5('rating_'.$this->module.'_'.$id)])) die(__('Some error occurred'));

$entity = $this->Model->getById($id);
if ($entity) {
	$rating = $entity->getRating();
	
	
	if ($_GET['vote'] == 1) {
		$rating--;
	} else {
		$rating++;
	}
	
	
	$entity->setRating($rating);
	$entity->save();
	setcookie(md5('rating_'.$this->module.'_'.$id), true, time()+999999, '/');
	die(__('Operation is successful'));	
}
die(__('Some error occurred'));