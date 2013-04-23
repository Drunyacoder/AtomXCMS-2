<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.0                           |
| @Project:      CMS                           |
| @package       CMS Fapos                     |
| @subpackege    NewsAttaches Model            |
| @copyright     ©Andrey Brykin 2010-2012      |
| @last mod      2012/03/01                    |
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
class NewsAttachesModel extends FpsModel
{
	
    public $Table = 'news_attaches';

	
	
	public function getByEntity($entity)
	{
		$params['entity_id'] = $entity->getId();
		$data = $this->getMapper()->getCollection($params);
		return $data;
	}
	

}