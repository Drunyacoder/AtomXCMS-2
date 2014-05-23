<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.1                           |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
| @subpackege    ShopCategories Entity         |
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
class ShopCategoriesEntity extends FpsEntity
{
	
	protected $id;
	protected $parent_id;
	protected $path;
	protected $title;
	protected $description;
	protected $icon_image;
	protected $discount;
	protected $no_access;
	protected $hide_not_exists;
	protected $view_on_home;

	
	public function save()
	{
		$params = array(
			'parent_id' => intval($this->parent_id),
			'path' => (string)$this->path,
			'title' => (string)$this->title,
			'description' => (string)$this->description,
			'icon_image' => (string)$this->icon_image,
			'discount' => intval($this->discount),
			'no_access' => (string)$this->no_access,
			'hide_not_exists' => (!empty($this->hide_not_exists)) ? '1' : '0',
			'view_on_home' => (!empty($this->view_on_home)) ? '1' : '0',
		);
		if ($this->id) $params['id'] = $this->id;
		$Register = Register::getInstance();
		return $Register['DB']->save('shop_categories', $params);
	}
	
	
	
	public function delete()
	{ 
		$Register = Register::getInstance();
		$Register['DB']->delete('shop_categories', array('id' => $this->id));
	}

}