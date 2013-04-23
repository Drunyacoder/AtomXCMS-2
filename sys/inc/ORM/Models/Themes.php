<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.0                           |
| @Project:      CMS                           |
| @package       CMS Fapos                     |
| @subpackege    Themes Model                  |
| @copyright     ©Andrey Brykin 2010-2012      |
| @last mod      2012/05/20                    |
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
class ThemesModel extends FpsModel
{
	public $Table = 'themes';

    protected $RelatedEntities = array(
        'forum' => array(
            'model' => 'Forum',
            'type' => 'has_one',
            'foreignKey' => 'id_forum',
      	),
        'poll' => array(
            'model' => 'Polls',
            'type' => 'has_many',
            'foreignKey' => 'theme_id',
      	),
        'author' => array(
            'model' => 'Users',
            'type' => 'has_one',
            'foreignKey' => 'id_author',
        ),
        'last_author' => array(
            'model' => 'Users',
            'type' => 'has_one',
            'foreignKey' => 'id_last_author',
        ),
        'postslist' => array(
            'model' => 'Posts',
            'type' => 'has_many',
            'foreignKey' => 'id_theme',
        ),
    );


}