<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.2                           |
| @Project:      CMS                           |
| @package       CMS Fapos                     |
| @subpackege    LoadsComments Entity          |
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
class LoadsCommentsEntity extends FpsEntity
{
	
	protected $id;
	protected $entity_id;
	protected $user_id;
	protected $name;
	protected $message;
	protected $ip;
	protected $mail;
	protected $date;


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
		);
		if($this->id) $data['id'] = $this->id;
		$Register = Register::getInstance();
		$Register['DB']->save('loads_comments', $data);
	}
	
	
	public function delete()
	{
		$Register = Register::getInstance();
		$Register['DB']->delete('loads_comments', array('id' => $this->id));
	}
}