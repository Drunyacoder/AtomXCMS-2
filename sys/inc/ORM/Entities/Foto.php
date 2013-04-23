<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.1                           |
| @Project:      CMS                           |
| @package       CMS Fapos                     |
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
	protected $description;
	protected $views;
	protected $date;
	protected $category_id;
	protected $category = null;
	protected $author_id;
	protected $author = null;
	protected $comments;
	protected $filename = null;

	
	
	public function save()
	{
		$params = array(
			'title' => $this->title,
			'description' => $this->description,
			'views' => intval($this->views),
			'date' => $this->date,
			'category_id' => intval($this->category_id),
			'author_id' => intval($this->author_id),
			'comments' => intval($this->comments),
			'filename' => $this->filename,
		);
		if ($this->id) $params['id'] = $this->id;
		$Register = Register::getInstance();
		$Register['DB']->save('foto', $params);
	}
	
	
	
	public function delete()
	{ 
		$Register = Register::getInstance();
		$path = ROOT . '/sys/files/foto/full/' . $this->filename;
		$path2 = ROOT . '/sys/files/foto/preview/' . $this->filename;
		if (file_exists($path)) unlink($path);
		if (file_exists($path2)) unlink($path2);
		$Register['DB']->delete('foto', array('id' => $this->id));
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
	
	

    /**
     * @param $category
     */
    public function setCategory($category)
   	{
   		$this->category = $category;
   	}



	/**
     * @return object
     */
	public function getCategory()
	{
        if (!$this->checkProperty($this->category)) {
            $Model = new FotoModel('foto');
            $this->category = $Model->getCategoryByNew($this);  // TODO (function is not exists)
        }
		return $this->category;
	}

}