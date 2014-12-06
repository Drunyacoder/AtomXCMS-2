<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.0                           |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
| @subpackege    Comments Model                |
| @copyright     ©Andrey Brykin 2010-2013      |
| @last mod      2013/04/25                    |
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
class CommentsModel extends FpsModel
{
	
    public $Table = 'comments';
	
    protected $RelatedEntities = array(
        'author' => array(
            'model' => 'Users',
            'type' => 'has_one',
            'internalKey' => 'user_id',
      	),
        'parent_entity' => array(
            'model' => 'this.module',
            'type' => 'has_one',
            'internalKey' => 'entity_id',
        ),
    );

    protected $orderParams = array(
        'allowed' => array('user_id', 'date', 'premoder'),
        'default' => 'date',
    );

	
	
	public function getByEntity($entity)
	{
		$this->bindModel('Users');
		$params['entity_id'] = $entity->getId();
		$news = $this->getCollection($params);
		return $news;
	}
	
}