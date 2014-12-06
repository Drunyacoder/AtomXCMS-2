<?php



class Fps_Viewer_Node_Url
{

	private $value;
	private static $callback;

	
	
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
		if (isset($compiler->loader->createPageUrlCallback) 
		&& is_callable($compiler->loader->createPageUrlCallback)) {
			$this->callback($compiler->loader->createPageUrlCallback);
		}
	
        if (is_callable(self::$callback)) {
            $url = self::$callback($this->value);
            $compiler->write('"'.$url.'"');
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
}