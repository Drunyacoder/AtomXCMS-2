<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.0                           |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
| @subpackege    Posts Model                   |
| @copyright     ©Andrey Brykin 2010-2012      |
| @last mod      2012/04/28                    |
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
class PostsModel extends FpsModel
{
	public $Table = 'posts';

    protected $RelatedEntities = array(
        'author' => array(
            'model' => 'Users',
            'type' => 'has_one',
            'foreignKey' => 'id_author',
      	),
        'editor' => array(
            'model' => 'Users',
            'type' => 'has_one',
            'foreignKey' => 'id_editor',
      	),
        'theme' => array(
            'model' => 'Users',
            'type' => 'has_many',
            'foreignKey' => 'id_theme',
      	),
        'attacheslist' => array(
            'model' => 'ForumAttaches',
            'type' => 'has_many',
            'foreignKey' => 'post_id',
      	),
    );

	
	
	public function deleteByTheme($theme_id)
	{
		$this->getDbDriver()->query("DELETE FROM `" . $this->getDbDriver()->getFullTableName('posts') . "` WHERE `id_theme` = '" . $theme_id . "'");
	}


    public function getFirst($where, $params)
    {
        $params['limit'] = 1;
        $post = $this->getCollection($where, $params);
        return (!empty($post[0])) ? $post[0] : false;
    }
}