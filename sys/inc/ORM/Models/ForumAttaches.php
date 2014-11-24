<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.0                           |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
| @subpackege    ForumAttaches Model           |
| @copyright     ©Andrey Brykin 2010-2012      |
| @last mod      2012/05/20                    |
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
class ForumAttachesModel extends FpsModel
{
	public $Table = 'forum_attaches';

    protected $RelatedEntities = array(
        'post' => array(
            'model' => 'Posts',
            'type' => 'has_one',
            'internalKey' => 'post_id',
      	),
        'theme' => array(
            'model' => 'Themes',
            'type' => 'has_one',
            'internalKey' => 'theme_id',
        ),
        'user' => array(
            'model' => 'Users',
            'type' => 'has_one',
            'internalKey' => 'user_id',
        ),
    );

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