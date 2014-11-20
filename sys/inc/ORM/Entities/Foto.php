<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.1                           |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
| @subpackege    Foto Entity                   |
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
class FotoEntity extends FpsEntity
{
	
	protected $id;
	protected $title;
	protected $clean_url_title;
	protected $description;
	protected $views;
	protected $date;
	protected $category_id;
	protected $category = null;
	protected $author_id;
	protected $author = null;
	protected $comments;
	protected $filename = null;
	protected $rating;

	
	
	public function save()
	{
		$params = array(
			'title' => $this->title,
			'clean_url_title' => $this->clean_url_title,
			'description' => $this->description,
			'views' => intval($this->views),
			'date' => $this->date,
			'category_id' => intval($this->category_id),
			'author_id' => intval($this->author_id),
			'comments' => intval($this->comments),
			'filename' => $this->filename,
			'rating' => intval($this->rating),
		);
		if ($this->id) $params['id'] = $this->id;

        return parent::save('foto', $params);
	}
	
	
	
	public function delete()
	{ 
		$Register = Register::getInstance();
		$path = ROOT . '/sys/files/foto/' . $this->filename;
		if (file_exists($path)) unlink($path);
		$Register['DB']->delete('foto', array('id' => $this->id));
	}




    /**
     * @param string $title
     */
	public function setTitle($title)
    {
        $Register = Register::getInstance();
        if (!empty($this->title) && $this->title !== $title) {
            $Register['URL']->saveOldEntryUrl($this, 'foto', $title);
        }
        $this->title = $title;
        $this->clean_url_title = $Register['URL']->getUrlByTitle($title, false);
    }

}