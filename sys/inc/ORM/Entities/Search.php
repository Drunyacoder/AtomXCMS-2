<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.1                           |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
| @subpackege    Search Entity                 |
| @copyright     ©Andrey Brykin 2010-2013      |
| @last mod      2013/04/03                    |
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
class SearchEntity extends FpsEntity
{
	
	protected $id;
	protected $index;
	protected $entity_id;
	protected $entity_title;
	protected $entity_table;
	protected $entity_view;
	protected $module;
	protected $date = null;

	
	
	
	public function save()
	{
		$params = array(
			'index' => $this->index,
			'entity_id' => intval($this->entity_id),
			'entity_title' => $this->entity_title,
			'entity_table' => $this->entity_table,
			'date' => $this->date,
			'entity_view' => $this->entity_view,
			'module' => $this->module,
		);
		if ($this->id) $params['id'] = $this->id;
		$Register = Register::getInstance();
		return $Register['DB']->save('search_index', $params);
	}
	
	
	
	public function delete()
	{ 
		$Register = Register::getInstance();
		$Register['DB']->delete('search_index', array('id' => $this->id));
	}


}