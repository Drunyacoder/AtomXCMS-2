<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.1                           |
| @Project:      CMS                           |
| @package       CMS Fapos                     |
| @subpackege    ForumAttaches Entity          |
| @copyright     ©Andrey Brykin 2010-2013      |
| @last mod      2013/04/03                    |
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



/**
 *
 */
class ForumAttachesEntity extends FpsEntity
{
	
	protected $id;
	protected $post_id;
	protected $theme_id;
	protected $user_id;
	protected $attach_number;
	protected $filename;
	protected $size;
	protected $date;
	protected $is_image;
	
	
	
	public function save()
	{
		$params = array(
			'post_id' => intval($this->post_id),
			'theme_id ' => intval($this->theme_id),
			'user_id' => intval($this->user_id),
			'attach_number' => intval($this->attach_number),
			'filename' => $this->filename,
			'size' => intval($this->size),
			'date' => $this->date,
			'is_image' => (!empty($this->is_image)) ? '1' : new Expr("'0'"),
		);
		if ($this->id) $params['id'] = $this->id;
		$Register = Register::getInstance();
		$Register['DB']->save('forum_attaches', $params);
	}
	
	
	
	public function delete()
	{ 
		$Register = Register::getInstance();
		$Register['DB']->delete('forum_attaches', array('id' => $this->id));
	}

}