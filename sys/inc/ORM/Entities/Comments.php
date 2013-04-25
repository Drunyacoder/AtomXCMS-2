<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.3                           |
| @Project:      CMS                           |
| @package       CMS Fapos                     |
| @subpackege    Comments Entity               |
| @copyright     ©Andrey Brykin 2010-2013      |
| @last mod      2013/04/25                    |
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
class CommentsEntity extends FpsEntity
{
	
	protected $id;
	protected $entity_id;
	protected $user_id;
	protected $name;
	protected $message;
	protected $ip;
	protected $mail;
	protected $date;
	protected $module;


	public function save()
	{
		$data = array(
			'entity_id' => intval($this->entity_id),
			'user_id' => intval($this->user_id),
			'name' => $this->name,
			'message' => $this->message,
			'ip' => $this->ip,
			'mail' => $this->mail,
			'date' => $this->date,
			'module' => $this->module,
		);
		if($this->id) $data['id'] = $this->id;
		$Register = Register::getInstance();
		$Register['DB']->save('comments', $data);
	}
	
	
	public function delete()
	{
		$Register = Register::getInstance();
		$Register['DB']->delete('comments', array('id' => $this->id));
	}
}