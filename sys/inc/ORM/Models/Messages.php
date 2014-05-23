<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.0                           |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
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
            'internalKey' => 'to_user',
      	),
        'fromuser' => array(
            'model' => 'Users',
            'type' => 'has_one',
            'internalKey' => 'from_user',
        ),
    );




}