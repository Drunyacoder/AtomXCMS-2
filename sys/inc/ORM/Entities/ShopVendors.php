<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.1                           |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
| @subpackege    ShopVendors Entity            |
| @copyright     ©Andrey Brykin 2010-2014      |
| @last mod      2014/05/15                    |
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
class ShopVendorsEntity extends FpsEntity
{
	
	protected $id;
	protected $title;
	protected $description;
	protected $logo_image;
	protected $discount;
	protected $hide_not_exists;
	protected $view_on_home;

	
	public function save()
	{
		$params = array(
			'title' => $this->title,
			'description' => $this->description,
			'logo_image' => $this->logo_image,
			'discount' => intval($this->discount),
			'hide_not_exists' => (!empty($this->hide_not_exists)) ? '1' : '0',
			'view_on_home' => (!empty($this->view_on_home)) ? '1' : '0',
		);
		if ($this->id) $params['id'] = $this->id;
		$Register = Register::getInstance();
		return $Register['DB']->save('shop_vendors', $params);
	}
	
	
	
	public function delete()
	{ 
		$Register = Register::getInstance();
		$Register['DB']->delete('shop_vendors', array('id' => $this->id));
	}

}