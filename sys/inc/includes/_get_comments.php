<?php
$id = (int)$entity->getId();
if (empty($id) || $id < 1) $html = true;

$commentsModel = $this->Register['ModManager']->getModelInstance('Comments');


if (empty($html) && $commentsModel) {
	$commentsModel->bindModel('Users');
	
	
	/* pages nav */
	$this->_globalize(array('comments_pagination' => ''));
	
	
	$order_way = ($this->Register['Config']->read('comments_order', $this->module)) ? 'DESC' : 'ASC';
	$params = array('order' => 'date ' . $order_way,);
	$comments = $commentsModel->getCollection(array('entity_id' => $id), $params);
	if ($comments) {
		foreach ($comments as $comment) {
			$markers = array();
			
			
			// COMMENT ADMIN BAR 
			$ip = ($comment->getIp()) ? $comment->getIp() : 'Unknown';
			$moder_panel = '';
			$adm = false;
			if ($this->ACL->turn(array($this->module, 'edit_comments'), false)) {
				$moder_panel .= get_link('', 
				'/' . $this->module . '/edit_comment_form/' . $comment->getId(), array('class' => 'fps-edit')) . '&nbsp;';
				$adm = true;
			}
			
			if ($this->ACL->turn(array($this->module, 'delete_comments'), false)) {
				$moder_panel .= get_link('', 
				'/' . $this->module . '/delete_comment/' . $comment->getId(), array(
					'class' => 'fps-delete',
					'onClick' => "return confirm('" . __('Are you sure') . "')",
				)) . '&nbsp;';
				$adm = true;
			}
			
			if ($adm) {
				$moder_panel = '<a target="_blank" href="https://apps.db.ripe.net/search/query.html?searchtext=' 
							. h($ip) . '" class="fps-ip" title="IP: ' . h($ip) . '"></a>' . $moder_panel;
			}
			
			
			$img = array(
				'alt' => 'User avatar',
				'title' => h($comment->getName()),
				'class' => 'ava',
			);
			if ($comment->getUser_id() && file_exists(ROOT . '/sys/avatars/' . $comment->getUser_id() . '.jpg')) {
				$markers['avatar'] = get_img('/sys/avatars/' . $comment->getUser_id() . '.jpg', $img);
			} else {
				$markers['avatar'] = get_img('/sys/img/noavatar.png', $img);
			}
			
			
			if ($comment->getUser_id()) {
				$markers['name_a'] = get_link(h($comment->getName()), getProfileUrl((int)$comment->getUser_id()));
				$markers['user_url'] = get_url(getProfileUrl((int)$comment->getUser_id()));
				$markers['avatar'] = get_link($markers['avatar'], $markers['user_url']);
			} else {
				$markers['name_a'] = h($comment->getName());
			}

			
			$markers['moder_panel'] = $moder_panel;
			$markers['message'] = $this->Textarier->print_page($comment->getMessage());
			$comment->setAdd_markers($markers);
		}
	}
	$html = $this->render('viewcomment.html', array('commentsr' => $comments));

	
} else {
	$html = '';
}

