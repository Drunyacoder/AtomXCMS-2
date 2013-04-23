<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.0                           |
| @Project:      CMS                           |
| @package       CMS Fapos                     |
| @subpackege    Chat Entity                   |
| @copyright     ©Andrey Brykin 2010-2012      |
| @last mod      2012/04/27                    |
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
class ChatEntity extends FpsEntity
{
	
	protected $id;
	protected $login;
	protected $message;
	protected $data;
	protected $ip;


	
	
	public function save()
	{
		$params = array(
			'title' => $this->title,
			'login' => $this->login,
			'message' => $this->message,
			'date' => $this->date,
			'ip' => $this->ip,
		);
		if ($this->id) $params['id'] = $this->id;
	}
	
	
	
	public function delete()
	{ 
		$Register = Register::getInstance();
	}


    /**
     * @param $author
     */
    public function setAuthor($author)
   	{
   		$this->author = $author;
   	}



    /**
     * @return object
     */
	public function getAuthor()
	{
        if (!$this->checkProperty('author')) {
            $Model = new FotoModel('foto');
            $this->author = $Model->getAuthorByEntity($this); // TODO (function is not exists)
        }
		return $this->author;
	}

}