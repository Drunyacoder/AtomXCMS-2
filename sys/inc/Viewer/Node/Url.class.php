<?php



class Fps_Viewer_Node_Url
{

	private $value;
	static private $pages = array();

	
	
	public function __construct($value)
	{
		$this->value = $value;
		if (empty(self::$pages)) $this->getPages();
	}
	
	
	private function getPages()
	{
		$pagesModel = Register::getInstance()['ModManager']->getModelInstance('Pages');
		self::$pages = $pagesModel->getCollection(array('`id` > 1'), array('fields' => array('id', 'url')));
	}


	
	public function getValue()
	{
		return $this->value;
	}
	
	

    public function compile(Fps_Viewer_CompileParser $compiler)
    {
		if (is_array(self::$pages) && count(self::$pages)) {
			foreach (self::$pages as $page) {
				if ($this->value == $page->getId() && $page->getUrl()) {
					$compiler->write('"'.get_url('/'.$page->getUrl()).'"');
					return;
				}
			}
		}
        $compiler->write('"'.get_url('/'.$this->value).'"');
    }

	
	
	public function __toString()
	{
		$out = "\n";
		$out .= '[value]:' . $this->value . "\n";
		return $out;
	}
}