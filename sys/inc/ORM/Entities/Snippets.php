<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.2                           |
| @Project:      CMS                           |
| @package       CMS Fapos                     |
| @subpackege    Snippets Entity               |
| @copyright     ©Andrey Brykin 2010-2014      |
| @last mod      2014/02/21                    |
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
class SnippetsEntity extends FpsEntity
{
	
	protected $id;
	protected $name;
	protected $body;
	
	
	
	public function save()
	{
		$params = array(
			'name' => $this->name,
			'body' => $this->body,
		);
		if ($this->id) $params['id'] = $this->id;
		$Register = Register::getInstance();
		return $Register['DB']->save('snippets', $params);
	}
	
	
	
	public function delete()
	{
		$Register['DB']->delete('snippets', array('id' => $this->id));
	}
}