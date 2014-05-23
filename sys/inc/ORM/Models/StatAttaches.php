<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.0                           |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
| @subpackege    StatAttaches Model            |
| @copyright     ©Andrey Brykin 2010-2012      |
| @last mod      2012/04/25                    |
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
class StatAttachesModel extends FpsModel
{
	
    public $Table = 'stat_attaches';

    protected $RelatedEntities = array(
        'user' => array(
            'model' => 'Users',
            'type' => 'has_one',
            'internalKey' => 'user_id',
        ),
    );
	
	
	public function getByEntity($entity)
	{
		$params['entity_id'] = $entity->getId();
		$data = $this->getMapper()->getCollection($params);
		return $data;
	}
	
	public function getUserOveralFilesSize($user_id)
	{
		$ovaral_size = $this->getDbDriver()->select($this->Table, DB_ALL, array(
			'cond' => array(
				'user_id' => $user_id, 
			),
			'fields' => array(
				"SUM(size) as size",
			),
		));
		return (!empty($ovaral_size[0]) && !empty($ovaral_size[0]['size'])) 
			? $ovaral_size[0]['size']
			: 0;
	}
}