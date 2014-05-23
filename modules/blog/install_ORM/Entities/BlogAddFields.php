<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.2                           |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
| @subpackege    BlogAddFields Entity          |
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
class BlogAddFieldsEntity extends FpsEntity
{
	
	protected $id;
	protected $type;
	protected $name;
	protected $label;
	protected $size;
	protected $params;
	protected $content;



    /**
     * @param $content
     */
	public function setContent($content)
    {
        $this->content = $content;
    }



    /**
     * @return array
     */
    public function getContent()
   	{

        $this->checkProperty('content');
   		return $this->content;
   	}
}