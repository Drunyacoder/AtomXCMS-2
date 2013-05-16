<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.1                           |
| @Project:      CMS                           |
| @package       CMS Fapos                     |
| @subpackege    Messages Entity               |
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
class MessagesEntity extends FpsEntity
{
	
	protected $id;
	protected $to_user;
	protected $from_user;
	protected $sendtime;
	protected $subject;
	protected $message;
	protected $id_rmv;
	protected $viewed;

	
	
	
	public function save()
	{
		$params = array(
			'to_user' => intval($this->to_user),
			'from_user' => intval($this->from_user),
			'sendtime' => $this->sendtime,
			'subject' => $this->subject,
			'message' => $this->message,
			'id_rmv' => intval($this->id_rmv),
			'viewed' => intval($this->viewed),
		);
		if ($this->id) $params['id'] = $this->id;
		$Register = Register::getInstance();
		$Register['DB']->save('messages', $params);
	}
	
	
	
	public function delete()
	{ 
		$Register = Register::getInstance();
		$Register['DB']->delete('messages', array('id' => $this->id));
	}




}