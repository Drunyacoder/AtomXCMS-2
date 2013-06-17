<?php
//turn access
$this->ACL->turn(array($this->module, 'delete_comments'));
$id = (!empty($id)) ? (int)$id : 0;
if ($id < 1) redirect('/' . $this->module);


$commentsModel = $this->Register['ModManager']->getModelInstance('Comments');
if ($commentsModel) {
	$comment = $commentsModel->getById($id);
	if ($comment) {
		$entityID = $comment->getEntity_id();
		$comment->delete();
		
		$entity = $this->Model->getById($entityID);
		if ($entity) {
			$entity->setComments($entity->getComments() - 1);
			$entity->save();
			


			if ($this->Log) $this->Log->write('delete comment for ' . $this->module, $this->module . ' id(' . $entityID . ')');
			return $this->showInfoMessage(__('Comment is deleted'), '/' . $this->module . '/view/' . $entityID );
		}
	}
}
return $this->showInfoMessage(__('Some error occurred'), '/' . $this->module . '/view/' . $entityID);