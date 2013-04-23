<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.1                           |
| @Project:      CMS                           |
| @package       CMS Fapos                     |
| @subpackege    Pages Entity                  |
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
class PollsEntity extends FpsEntity
{
	
	protected $id;
	protected $theme_id;
	protected $variants;
	protected $voted_users;
	protected $question;

	
	
	public function save()
	{
		$params = array(
			'theme_id' => intval($this->theme_id),
			'variants' => $this->variants,
			'voted_users' => $this->voted_users,
			'question' => $this->question,
		);
		if ($this->id) $params['id'] = $this->id;
		$Register = Register::getInstance();
		$id = $Register['DB']->save('polls', $params);
		if($id) $this->setId($id);
	}
	
	
	
	public function delete()
	{ 
		$Register = Register::getInstance();
		$Register['DB']->delete('polls', array('id' => $this->id));
	}

}