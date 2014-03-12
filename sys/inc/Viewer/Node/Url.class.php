<?php



class Fps_Viewer_Node_Url
{

	private $value;
	private static $pagesModel;

	
	
	public function __construct($value)
	{
		$this->value = $value;
	}


	
	public function getValue()
	{
		return $this->value;
	}
	

	
    public function compile(Fps_Viewer_CompileParser $compiler)
    {
		if (isset($compiler->loader->pagesModel) && is_object($compiler->loader->pagesModel)) {
			$this->__setPagesModel($compiler->loader->pagesModel);
		}
	
        if (is_object(self::$pagesModel) && is_callable(array(self::$pagesModel, 'buildUrl'))) {
            $url = self::$pagesModel->buildUrl($this->value);
            $compiler->write('"'.get_url('/'.$url).'"');
        } else {
            $compiler->write($this->value);
        }
    }

	
	
	public function __toString()
	{
		$out = "\n";
		$out .= '[value]:' . $this->value . "\n";
		return $out;
	}
	
	
	
	private function __setPagesModel($model)
	{
		self::$pagesModel = $model;
	}
}