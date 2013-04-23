<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.1                           |
| @Project:      CMS                           |
| @package       CMS Fapos                     |
| @subpackege    UsersVotes Entity             |
| @copyright     ©Andrey Brykin 2010-2013      |
| @last mod      2013/04/03                    |
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
class UsersVotesEntity extends FpsEntity
{
	
	protected $id;
	protected $from_user;
	protected $to_user;
	protected $comment;
	protected $date;
	protected $points;





    public function save()
    {
        $params = array(
            'from_user' => intval($this->from_user),
            'to_user' => intval($this->to_user),
            'comment' => $this->comment,
            'date' => $this->date,
            'points' => intval($this->points),
        );
        if ($this->id) $params['id'] = $this->id;
        $Register = Register::getInstance();
        $Register['DB']->save('users_votes', $params);
    }
	

	public function delete($id)
	{
        $Register = Register::getInstance();
        $Register['DB']->delete('users_votes', array('id' => $id));
	}
}