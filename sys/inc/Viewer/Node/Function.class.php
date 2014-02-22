<?php



class Fps_Viewer_Node_Function
{

	private $func;
	private $param;


	public function __construct($func, $param)
	{
		$this->func = $func;
		$this->param = $param;
	}
	
	
	
	
	public function compile(Fps_Viewer_CompileParser $compiler)
	{
		$compiler->raw("$this->func(");
		$compiler->raw($this->param->compile($compiler));
		$compiler->raw(")");
	}
}