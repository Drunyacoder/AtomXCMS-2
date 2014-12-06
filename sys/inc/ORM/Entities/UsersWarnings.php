<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.1                           |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
| @subpackege    UsersWarnings Entity          |
| @copyright     ©Andrey Brykin 2010-2013      |
| @last mod      2013/04/03                    |
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
class UsersEntity extends FpsEntity
{
	
	protected $id;
	protected $user_id;
	protected $admin_id;
	protected $cause;
	protected $date;
	protected $points;




    public function save()
    {
        $params = array(
            'user_id' => intval($this->user_id),
            'admin_id' => intval($this->admin_id),
            'cause' => $this->cause,
            'date' => $this->date,
            'points' => intval($this->points),
        );
        if ($this->id) $params['id'] = $this->id;
        $Register = Register::getInstance();
        return $Register['DB']->save('users_warnings', $params);
    }
	


}