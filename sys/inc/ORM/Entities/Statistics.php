<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.3                           |
| @Project:      CMS                           |
| @package       CMS Fapos                     |
| @subpackege    Stat Entity                   |
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
class StatisticsEntity extends FpsEntity
{
	
	protected $id;
	protected $ips;
	protected $cookie;
	protected $referer;
	protected $date;
	protected $views;
	protected $yandex_bot_views;
	protected $google_bot_views;
	protected $other_bot_views;
	protected $other_site_visits;
	
	
	
	public function save()
	{
		$params = array(
			'ips' => $this->ips,
			'cookie' => $this->cookie,
			'referer' => $this->referer,
			'date' => $this->date,
			'views' => intval($this->views),
			'yandex_bot_views' => intval($this->yandex_bot_views),
			'google_bot_views' => intval($this->google_bot_views),
			'other_bot_views' => intval($this->other_bot_views),
			'other_site_visits' => intval($this->other_site_visits),
		);
		if ($this->id) $params['id'] = $this->id;
		$Register = Register::getInstance();
		$Register['DB']->save('statistics', $params);
	}
	
	
	
	public function delete()
	{ 
		$Register = Register::getInstance();
		$Register['DB']->delete('statistics', array('id' => $this->id));
	}


}