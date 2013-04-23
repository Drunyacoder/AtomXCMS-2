<?php



class Fps_Viewer_Node_Text
{

	protected $data;
	
	
	public function __construct($data)
	{
		$this->data = $data;
	}



    public function compile(Fps_Viewer_CompileParser $compiler)
    {
        $compiler->string($this->data);
    }
	
	
	
	
	public function __toString()
	{
		$out = "\n";
		$out .= (string)$this->data;
		return $out;
	}
}