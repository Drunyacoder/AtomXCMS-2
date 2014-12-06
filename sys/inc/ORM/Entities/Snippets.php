<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.3                           |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
| @subpackege    Snippets Entity               |
| @copyright     ©Andrey Brykin 2010-2014      |
| @last mod      2014/03/09                    |
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
class SnippetsEntity extends FpsEntity
{
	
	protected $id;
	protected $name;
	protected $body;
	
	
	public function save()
	{
		$params = array(
			'name' => $this->ips,
			'body' => $this->cookie,
		);
		
		if ($this->id) $params['id'] = $this->id;
		$Register = Register::getInstance();
		return $Register['DB']->save('snippets', $params);
	}
	
	
	
	public function delete()
	{ 
		$Register = Register::getInstance();
		$Register['DB']->delete('snippets', array('id' => $this->id));
	}


}