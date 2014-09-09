<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.0                           |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
| @subpackege    ShopAttaches Model            |
| @copyright     ©Andrey Brykin 2010-2014      |
| @last mod      2014/08/25                    |
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
class ShopAttachesModel extends FpsModel
{
	
    public $Table = 'shop_attaches';

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
		$data = $this->getCollection($params);
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
	
	
	public function deleteNotRelated()
	{
		$attaches = $this->getCollection(array(
			'or' => array(
				'entity_id IS NULL',
				'entity_id = 0',
			),
			"date < '" . date('Y-m-d H:i:s', time() - 84600) . "'"
		));
		if ($attaches) {
			foreach ($attaches as $attach) {
				$attach->delete();
			}
		}
	}
}