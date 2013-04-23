<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.0                           |
| @Project:      CMS                           |
| @package       CMS Fapos                     |
| @subpackege    ForumAttaches Model           |
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
class ForumAttachesModel extends FpsModel
{
	public $Table = 'forum_attaches';

    protected $RelatedEntities = array(
        'post' => array(
            'model' => 'Posts',
            'type' => 'has_one',
            'foreignKey' => 'post_id',
      	),
        'theme' => array(
            'model' => 'Themes',
            'type' => 'has_one',
            'foreignKey' => 'theme_id',
        ),
        'user' => array(
            'model' => 'Users',
            'type' => 'has_one',
            'foreignKey' => 'user_id',
        ),
    );


}