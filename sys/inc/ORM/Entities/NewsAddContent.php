<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.2                           |
| @Project:      CMS                           |
| @package       CMS Fapos                     |
| @subpackege    NewsAddContent Entity         |
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
class NewsAddContentEntity extends FpsEntity
{
	
	protected $id;
	protected $field_id;
	protected $entity_id;
	protected $content;

	
	
	public function save()
	{
		$params = array(
			'entity_id' => intval($this->entity_id),
			'field_id' => intval($this->field_id),
			'content' => $this->content,
		);
		if ($this->id) $params['id'] = $this->id;
		$Register = Register::getInstance();
		$Register['DB']->save('news_add_content', $params);
	}
	
	
	
	public function delete()
	{
		$Register = Register::getInstance();
		$Register['DB']->delete('news_add_content', array('id' => $this->id));
	}

}