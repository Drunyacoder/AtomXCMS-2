<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.1                           |
| @Project:      CMS                           |
| @package       CMS Fapos                     |
| @subpackege    Users Entity                  |
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
class UsersEntity extends FpsEntity
{
	
	protected $id;
	protected $name;
	protected $passw;
	protected $email;
	protected $color;
	protected $state;
	protected $rating;
	protected $timezone;
	protected $url;
	protected $icq;
	protected $about;
	protected $signature;
	protected $pol;
	protected $jabber;
	protected $city;
	protected $telephone;
	protected $byear;
	protected $bmonth;
	protected $bday;
	protected $photo;
	protected $puttime;
	protected $themes;
	protected $posts;
	protected $status;
	protected $locked;
	protected $activation;
	protected $warnings;
	protected $ban_expire;




    public function save()
    {
        $params = array(
            'name' => $this->name,
            'passw' => $this->passw,
            'email' => $this->email,
            'color' => $this->color,
            'state' => $this->state,
            'rating' => intval($this->rating),
            'timezone' => $this->timezone,
            'url' => $this->url,
            'icq' => $this->icq,
            'about' => $this->about,
            'signature' => $this->signature,
            'pol' => $this->pol,
            'jabber' => $this->jabber,
            'city' => $this->city,
            'telephone' => intval($this->telephone),
            'byear' => intval($this->byear),
            'bmonth' => intval($this->bmonth),
            'bday' => intval($this->bday),
            'photo' => $this->photo,
            'puttime' => $this->puttime,
            'themes' => intval($this->themes),
            'posts' => intval($this->posts),
            'status' => intval($this->status),
            'locked' => intval($this->locked),
            'activation' => $this->activation,
            'warnings' => intval($this->warnings),
            'ban_expire' => $this->ban_expire,
        );
        if ($this->id) $params['id'] = $this->id;
        $Register = Register::getInstance();
        $Register['DB']->save('users', $params);
    }
	


}