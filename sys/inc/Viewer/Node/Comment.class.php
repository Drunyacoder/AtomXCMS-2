<?php



class Fps_Viewer_Node_Comment
{

	private $value;

	
	
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
        $compiler->write("/** Comment block\n")
			->write($this->value)
			->raw("\n")
			->write("*/ \n");
    }

	
	
	public function __toString()
	{
		$out = "\n";
		$out .= '[value]:' . $this->value . "\n";
		return $out;
	}
}