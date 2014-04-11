<?php



class Fps_Viewer_Node_Text
{

	protected $data;
	
	private $filters = array();
	
	public function __construct($data)
	{
		$this->data = $data;
	}



    public function compile(Fps_Viewer_CompileParser $compiler)
    {
		if (is_array($this->filters) && count($this->filters)) {
			foreach ($this->filters as $filter) {
				$objContext = $this;
				$value = $filter->compile(function($compiler) use ($objContext) {
					$compiler->string($objContext->data);
				}, $compiler);
			}
		} else {
			$compiler->string($this->data);
		}
    }
	
	
	
	public function addFilter($filter)
	{
		$this->filters[] = $filter;
	}
	
	
	
	public function __toString()
	{
		$out = "\n";
		$out .= (string)$this->data;
		return $out;
	}
}