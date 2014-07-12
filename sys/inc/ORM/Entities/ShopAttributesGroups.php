<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.1                           |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
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
class ShopAttributesGroupsEntity extends FpsEntity
{
	
	protected $id;
	protected $title;
	
	
	public function save()
	{
		$params = array(
			'title' => $this->title ,
		);
		if ($this->id) $params['id'] = intval($this->id);
		$Register = Register::getInstance();
		return $Register['DB']->save('shop_attributes_groups', $params);
	}
	
	
	
	public function delete()
	{ 
		$Register = Register::getInstance();
		$Register['DB']->delete('shop_attributes_groups', array('id' => $this->id));
	}

}