<?php



class Fps_Viewer_Node_Print
{

	private $value;
	


	public function __construct($value)
	{
		$this->value = $value;
	}



    public function compile(Fps_Viewer_CompileParser $compiler)
    {
        $compiler->write('echo ')
			->raw($this->value->compile($compiler))
			->raw(";\n");
    }

	
	
	
	public function __toString()
	{
		return (string)$this->value;
	}
}