<?php



class Fps_Viewer_Node_Set
{

	private $value;


	public function __construct($value)
	{
		$this->value = $value;
	}



    public function compile(Fps_Viewer_CompileParser $compiler)
    {
        $compiler->write('TODO');
    }

	
	
	
	public function __toString()
	{
		// TODO
	}
}