<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.1                           |
| @Project:      CMS                           |
| @package       CMS Fapos                     |
| @subpackege    UsersSettings Entity          |
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
class UsersSettingsEntity extends FpsEntity
{
	
	protected $id;
	protected $type;
	protected $vales;

	
	
	
	public function save()
	{
		$params = array(
			'type' => $this->type,
			'values' => $this->values,
		);
		if ($this->id) $params['id'] = $this->id;
		$Register = Register::getInstance();
		$Register['DB']->save('users_settings', $params);
	}
	
	
	
	public function delete()
	{
		$Register = Register::getInstance();
		$Register['DB']->delete('users_settings', array('id' => $this->id));
	}

}