<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.0                           |
| @Project:      CMS                           |
| @package       CMS Fapos                     |
| @subpackege    UsersSettings Model           |
| @copyright     ©Andrey Brykin 2010-2012      |
| @last mod      2012/05/14                    |
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
class UsersSettingsModel extends FpsModel
{
	public $Table = 'users_settings';

    protected $RelatedEntities = array(
    );



    public function getByType($type)
    {
        $Register = Register::getInstance();
        $data = $Register['DB']->select($this->Table, DB_ALL, array('cond' => array('type' => $type)));
        return $data;
    }

}