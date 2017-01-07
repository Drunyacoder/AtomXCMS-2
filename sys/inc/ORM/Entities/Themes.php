<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.4                           |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
| @subpackege    Themes Entity                 |
| @copyright     Andrey Brykin 2010-2013       |
| @last mod      2016/12/26                    |
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
class ThemesEntity extends FpsEntity
{
	
	protected $id;
	protected $title;
	protected $clean_url_title;
	protected $id_author;
	protected $time;
	protected $id_last_author;
	protected $last_post;
	protected $id_forum;
	protected $locked;
	protected $posts;
	protected $views;
	protected $important;
	protected $description;
	protected $group_access;
	protected $first_top;

	
	
	
	public function save()
	{
		$params = array(
			'title' 			=> $this->title,
			'clean_url_title' 	=> $this->clean_url_title,
			'id_author' 		=> intval($this->id_author),
			'time' 				=> $this->time,
			'id_last_author' 	=> intval($this->id_last_author),
			'last_post' 		=> ($this->last_post) ? $this->last_post : $this->time,
			'id_forum' 			=> intval($this->id_forum),
			'locked' 			=> intval($this->locked),
			'posts' 			=> intval($this->posts),
			'views' 			=> intval($this->views),
			'important' 		=> (!empty($this->important)) ? '1' : new Expr("'0'"),
			'description' 		=> $this->description,
			'group_access' 		=> (is_array($this->group_access) && count($this->group_access) == 1 && $this->group_access[0] !== '') 
									? $this->group_access[0] 
									: implode('.', (array)$this->group_access),
			'first_top' 		=> (!empty($this->first_top)) ? '1' : new Expr("'0'"),
		);
		if ($this->id) $params['id'] = $this->id;
		$Register = Register::getInstance();
		return $Register['DB']->save('themes', $params);
	}
	
	
	public function getGroup_access()
	{
		$out = (is_array($this->group_access)) ? $this->group_access : explode('.', $this->group_access);
		foreach ($out as $k => $v) if ('' === $v) unset($out[$k]);
		return $out;
	}

	
	public function delete()
	{ 
		$Register = Register::getInstance();
		$Register['DB']->delete('themes', array('id' => $this->id));
        $Register['URL']->removeOldTmpFiles($this, 'forum');
	}


    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $Register = Register::getInstance();
        if (!empty($this->title) && $this->title !== $title) {
            $Register['URL']->saveOldEntryUrl($this, 'forum', $title);
        }
        $this->title = $title;
        $this->clean_url_title = $Register['URL']->getUrlByTitle($title, false);
    }
}