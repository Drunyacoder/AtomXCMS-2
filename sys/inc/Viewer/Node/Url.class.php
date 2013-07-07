<?php



class Fps_Viewer_Node_Url
{

	private $value;
	private static $pagesModel;

	
	
	public function __construct($value)
	{
		$this->value = $value;
		if (empty(self::$pagesModel)) $this->__setPagesModel();
	}
	
	
	private function __setPagesModel()
	{
		self::$pagesModel = Register::getInstance()['ModManager']->getModelInstance('Pages');
	}


	
	public function getValue()
	{
		return $this->value;
	}
	

	
    public function compile(Fps_Viewer_CompileParser $compiler)
    {
		$url = self::$pagesModel->buildUrl($this->value);
        $compiler->write('"'.get_url('/'.$url).'"');
    }

	
	
	public function __toString()
	{
		$out = "\n";
		$out .= '[value]:' . $this->value . "\n";
		return $out;
	}
}