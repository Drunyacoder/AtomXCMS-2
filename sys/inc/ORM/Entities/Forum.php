<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.1                           |
| @Project:      CMS                           |
| @package       CMS Fapos                     |
| @subpackege    Forum Entity                  |
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
class ForumEntity extends FpsEntity
{
	
	protected $id;
	protected $title;
	protected $description;
	protected $pos;
	protected $in_cat;
	protected $last_theme_id;
	protected $themes;
	protected $posts;
	protected $parent_forum_id;
	protected $lock_posts;
	protected $lock_passwd;

	
	
	
	public function save()
	{
		$params = array(
			'title' => $this->title,
			'description ' => $this->description ,
			'pos' => intval($this->pos),
			'in_cat' => intval($this->in_cat),
			'last_theme_id' => intval($this->last_theme_id),
			'themes' => intval($this->themes),
			'posts' => intval($this->posts),
			'parent_forum_id' => intval($this->parent_forum_id),
			'lock_posts' => intval($this->lock_posts),
			'lock_passwd' => $this->lock_passwd,
		);
		if ($this->id) $params['id'] = $this->id;
		$Register = Register::getInstance();
		return ($Register['DB']->save('forums', $params));
	}
	
	
	
	public function delete()
	{ 
		$Register = Register::getInstance();
		$Register['DB']->delete('forums', array('id' => $this->id));
	}

}