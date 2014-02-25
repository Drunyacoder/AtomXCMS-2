<?php



class Fps_Viewer_Node_Set
{

	private $value;


	public function __construct($value, Fps_Viewer_TreesParser $parser)
	{
		$this->value = $value;
	}



    public function compile(Fps_Viewer_CompileParser $compiler)
    {
        $compiler->addIndent();
		$this->value->compile($compiler);
		$compiler->raw(";\n");
    }

	
	
	
	public function __toString()
	{
		// TODO
	}
}