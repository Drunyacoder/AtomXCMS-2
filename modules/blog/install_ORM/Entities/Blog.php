<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.1                           |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
| @subpackege    Blog Entity                   |
| @copyright     ©Andrey Brykin 2010-2014      |
| @last mod      2014/05/07                    |
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
class BlogEntity extends FpsEntity
{
	
	protected $id;
	protected $title;
	protected $main;
	protected $views;
	protected $date;
	protected $category_id;
	protected $category = null;
	protected $author_id;
	protected $author = null;
	protected $comments;
	protected $comments_ = null;
	protected $attaches = null;
	protected $tags;
	protected $description;
	protected $sourse;
	protected $sourse_email;
	protected $sourse_site;
	protected $commented;
	protected $available;
	protected $view_on_home;
	protected $on_home_top;
	protected $add_fields = null;
	protected $premoder;
	protected $rating;
	
	
	
	public function save()
	{
		$params = array(
			'title' => $this->title,
			'main' => $this->main,
			'views' => intval($this->views),
			'date' => $this->date,
			'category_id' => $this->category_id,
			'author_id' => $this->author_id,
			'comments' => (!empty($this->comments)) ? intval($this->comments) : 0,
			'tags' => (is_array($this->tags)) ? implode(',', $this->tags) : $this->tags,
			'description' => $this->description,
			'sourse' => $this->sourse,
			'sourse_email' => $this->sourse_email,
			'sourse_site' => $this->sourse_site,
			'commented' => (!empty($this->commented)) ? '1' : new Expr("'0'"),
			'available' => (!empty($this->available)) ? '1' : new Expr("'0'"),
			'view_on_home' => (!empty($this->view_on_home)) ? '1' : new Expr("'0'"),
			'on_home_top' => (!empty($this->on_home_top)) ? '1' : new Expr("'0'"),
			'premoder' => (!empty($this->premoder) && in_array($this->premoder, array('nochecked', 'rejected', 'confirmed'))) ? $this->premoder : 'nochecked',
			'rating' => intval($this->rating),
		);
		
		
		if ($this->id) $params['id'] = $this->id;
		$Register = Register::getInstance();
		return $Register['DB']->save('blog', $params);
	}
	
	
	
	public function delete()
	{ 
		$Register = Register::getInstance();
		$attachClass = $Register['ModManager']->getModelNameFromModule('blogAttaches');
		$commentsClass = $Register['ModManager']->getModelNameFromModule('Comments');
		$addContentClass = $Register['ModManager']->getModelNameFromModule('blogAddContent');
		
		$attachesModel = new $attachClass;
		$commentsModel = new $commentsClass;
		$addContentModel = new $addContentClass;
		
		$attachesModel->deleteByParentId($this->id);
		$commentsModel->deleteByParentId($this->id, array('module' => 'blog'));
		$addContentModel->deleteByParentId($this->id);
		

		$Register['DB']->delete('blog', array('id' => $this->id));
	}



    /**
     * @param $comments
     */
	public function setComments_($comments)
    {
        $this->comments_ = $comments;
    }



    /**
     * @return array
     */
    public function getComments_()
   	{

        $this->checkProperty('comments_');
   		return $this->comments_;
   	}



    /**
     * @param $comments
     */
	public function setAttaches($attaches)
    {
        $this->attaches = $attaches;
    }



    /**
     * @return array
     */
    public function getAttaches()
   	{

        $this->checkProperty('attaches');
   		return $this->attaches;
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
            $Model = new BlogModel('blog');
            $this->author = $Model->getAuthorByNew($this);
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

}