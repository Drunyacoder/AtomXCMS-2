<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.0                           |
| @Project:      CMS                           |
| @package       CMS Fapos                     |
| @subpackege    Messages Model                |
| @copyright     ©Andrey Brykin 2010-2012      |
| @last mod      2012/05/16                    |
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
class MessagesModel extends FpsModel
{
	public $Table = 'messages';

    protected $RelatedEntities = array(
        'touser' => array(
            'model' => 'Users',
            'type' => 'has_one',
            'foreignKey' => 'to_user',
      	),
        'fromuser' => array(
            'model' => 'Users',
            'type' => 'has_one',
            'foreignKey' => 'from_user',
        ),
    );




}