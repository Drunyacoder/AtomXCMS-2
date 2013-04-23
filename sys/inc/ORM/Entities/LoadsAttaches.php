<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.2                           |
| @Project:      CMS                           |
| @package       CMS Fapos                     |
| @subpackege    LoadsAttaches Entity          |
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
class LoadsAttachesEntity extends FpsEntity
{
	
	protected $id;
	protected $entity_id;
	protected $user_id;
	protected $attach_number;
	protected $filename ;
	protected $size;
	protected $date;
	protected $is_image;

	
	public function save()
	{
		$params = array(
			'entity_id' => intval($this->entity_id),
			'user_id' => intval($this->user_id),
			'attach_number' => intval($this->attach_number),
			'filename' => $this->filename,
			'size' => intval($this->size),
			'date' => $this->date,
			'is_image' => (!empty($this->is_image)) ? '1' : new Expr("'0'"),
		);
		if($this->id) $params['id'] = $this->id;
		$Register = Register::getInstance();
		$Register['DB']->save('loads_attaches', $params);
	}
	
	
	
	public function delete()
	{
		$path = ROOT . '/sys/files/loads/' . $this->filename;
		if (file_exists($path)) unlink($path);
		$Register = Register::getInstance();
		$Register['DB']->delete('loads_attaches', array('id' => $this->id));
	}
}