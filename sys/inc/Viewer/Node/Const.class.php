<?php



class Fps_Viewer_Node_Const
{

	private $value;

	public function __construct($value)
	{
		$this->value = $value;
	}
	
	
	
	
	public function compile(Fps_Viewer_CompileParser $compiler)
	{
		$compiler->repr($this->value);
	}
	
	
	
	
	public function __toString()
	{
		$out = "\n";
		$out .= '[value]:' . $this->value . "\n";
		return $out;
	}
}