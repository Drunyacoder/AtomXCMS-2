<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.3                           |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
| @subpackege    Comments Entity               |
| @copyright     ©Andrey Brykin 2010-2013      |
| @last mod      2013/04/25                    |
|----------------------------------------------|
|											   |
| any partial or not partial extension         |
| CMS AtomX,without the consent of the         |
| author, is illegal                           |
|----------------------------------------------|
| Любое распространение                        |
| CMS AtomX или ее частей,                     |
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
	protected $premoder;


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
			'premoder' => (!empty($this->premoder) && in_array($this->premoder, array('nochecked', 'rejected', 'confirmed'))) ? $this->premoder : 'nochecked',
		);
		
		if($this->id) $data['id'] = $this->id;
		$Register = Register::getInstance();
		return $Register['DB']->save('comments', $data);
	}
	
	
	public function delete()
	{
		$Register = Register::getInstance();
		$Register['DB']->delete('comments', array('id' => $this->id));
	}
}