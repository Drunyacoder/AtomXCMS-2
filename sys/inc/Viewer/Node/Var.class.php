<?php



class Fps_Viewer_Node_Var
{

	private $value;

	private $filters;

	private $attr;

    /**
     * if @var == 1 variable should be $this->getValue(context, value)
     * @var bool
     */
    private $def = false;

	private $tmpContext = false;

	
	
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
		$value = $this->compileValue($compiler);
		if (is_array($this->filters) && count($this->filters)) {
			foreach ($this->filters as $filter) {
				$value = $filter->compile($value);
			}
		}
        $compiler->raw($value);
    }




    private function compileValue(Fps_Viewer_CompileParser $compiler)
    {
		if ($this->def) {
			if ($this->tmpContext) {
				$value = "\$$this->tmpContext";
			} else {
				$value = "\$this->getValue(\$this->context, '$this->value')";
			}


			if (is_array($this->attr)) {
				while (count($this->attr)) {
					$value = "\$this->getValue(" . $value . ", '" . array_shift($this->attr) . "')";
				}
			}
			
			
		} else {
			$value = "\$$this->value"; 
		}
		return $value;
    }
	
	
	
	
	public function setTmpContext($tmpContext)
	{
		$this->tmpContext = $tmpContext;
	}
	
	
	
	
	public function setDef($flag)
	{
		$this->def = $flag;
	}
	
	
	
	
	public function addFilter($filter)
	{
		$this->filters[] = $filter;
	}
	
	
	
	public function addAttr($key)
	{
		$this->attr[] = $key;
	}
	
	
	
	public function __toString()
	{
		$out = "\n";
		$out .= '[value]:' . $this->value . "\n";
		$out .= '[filter]:' . $this->filter . "\n";
		$out .= '[attr]:' . $this->attr . "\n";
		return $out;
	}
}