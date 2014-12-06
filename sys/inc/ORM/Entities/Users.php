<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.2                           |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
| @subpackege    Users Entity                  |
| @copyright     ©Andrey Brykin 2010-2014      |
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
	protected $name;
	protected $first_name;
	protected $last_name;
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
	protected $email_notification;
	protected $summer_time;




    public function save()
    {
        $params = array(
            'name' => $this->name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'passw' => $this->passw,
            'email' => $this->email,
            'color' => $this->color ? $this->color : '',
            'state' => $this->state ? $this->state : '',
            'rating' => intval($this->rating),
            'timezone' => intval($this->timezone),
            'url' => (string)$this->url,
            'icq' => (string)$this->icq,
            'about' => (string)$this->about,
            'signature' => (string)$this->signature,
            'pol' => (string)$this->pol,
            'jabber' => (string)$this->jabber,
            'city' => (string)$this->city,
            'telephone' => intval($this->telephone),
            'byear' => intval($this->byear),
            'bmonth' => intval($this->bmonth),
            'bday' => intval($this->bday),
            'photo' => (string)$this->photo,
            'puttime' => $this->puttime,
            'themes' => intval($this->themes),
            'posts' => intval($this->posts),
            'status' => intval($this->status),
            'locked' => intval($this->locked),
            'activation' => $this->activation,
            'warnings' => intval($this->warnings),
            'ban_expire' => $this->ban_expire ? $this->ban_expire : '0000-00-00 00:00:00',
            'email_notification' => intval($this->email_notification),
            'summer_time' => intval($this->summer_time),
        );
		
        if ($this->id) $params['id'] = $this->id;
		
        return parent::save('users', $params);
    }
	

	public function getAvatar() {
        $template = getTemplateName();
		if (file_exists(ROOT . '/sys/avatars/' . $this->getId() . '.jpg')) {
			$avatar = get_url('/sys/avatars/' . $this->getId() . '.jpg');
		} else {
			$avatar = get_url('/template/' . $template . '/img/noavatar.png');
		}
		return $avatar;
	}
}