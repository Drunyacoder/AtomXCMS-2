<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.1                           |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
| @subpackege    News Entity                   |
| @copyright     ©Andrey Brykin 2010-2013      |
| @last mod      2013/11/10                    |
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
class NewsEntity extends FpsEntity
{
	
	protected $id;
	protected $title;
	protected $clean_url_title;
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
        $Register = Register::getInstance();
		$params = array(
			'title' => $this->title,
			'clean_url_title' => $this->clean_url_title,
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
		return $Register['DB']->save('news', $params);
	}
	
	
	
	public function delete()
	{ 
		$Register = Register::getInstance();
		$attachClass = $Register['ModManager']->getModelNameFromModule('newsAttaches');
		$commentsClass = $Register['ModManager']->getModelNameFromModule('Comments');
		$addContentClass = $Register['ModManager']->getModelNameFromModule('newsAddContent');
		
		$attachesModel = new $attachClass;
		$commentsModel = new $commentsClass;
		$addContentModel = new $addContentClass;
		
		$attachesModel->deleteByParentId($this->id);
		$commentsModel->deleteByParentId($this->id, array('module' => 'news'));
		$addContentModel->deleteByParentId($this->id);
		

		$Register['DB']->delete('news', array('id' => $this->id));
        $Register['URL']->removeOldTmpFiles($this, 'news');
	}


    /**
     * @param string $title
     */
	public function setTitle($title)
    {
        $Register = Register::getInstance();
        if (!empty($this->title) && $this->title !== $title) {
            $Register['URL']->saveOldEntryUrl($this, 'news', $title);
        }
        $this->title = $title;
        $this->clean_url_title = $Register['URL']->getUrlByTitle($title, false);
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
     * @return array
     */
    public function getAttaches()
   	{

        $this->checkProperty('attaches');
   		return $this->attaches;
   	}
}