<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.0                           |
| @Project:      CMS                           |
| @package       CMS Fapos                     |
| @subpackege    LoadsComments Model           |
| @copyright     ©Andrey Brykin 2010-2012      |
| @last mod      2012/04/29                    |
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
class LoadsCommentsModel extends FpsModel
{
	
    public $Table = 'loads_comments';
    protected $RelatedEntities = array(
        'author' => array(
            'model' => 'Users',
            'type' => 'has_one',
            'foreignKey' => 'user_id',
      	),
        'parent_entity' => array(
            'model' => 'Loads',
            'type' => 'has_one',
            'foreignKey' => 'entity_id',
        ),
    );

	
	
	public function getByEntity($entity)
	{
		$this->bindModel('Users');
		$params['entity_id'] = $entity->getId();
		$news = $this->getCollection($params);
		return $news;
	}
	
}