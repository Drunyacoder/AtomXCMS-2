<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.0                           |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
| @subpackege    UsersWarnings Model           |
| @copyright     ©Andrey Brykin 2010-2012      |
| @last mod      2012/05/19                    |
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
class UsersWarningsModel extends FpsModel
{
	public $Table  = 'users_warnings';

    protected $RelatedEntities = array(
        'user' => array(
            'model' => 'Users',
            'type' => 'has_one',
            'internalKey' => 'user_id',
        ),
        'admin' => array(
            'model' => 'Users',
            'type' => 'has_one',
            'internalKey' => 'admin_id',
        ),
    );
	
	
	

}