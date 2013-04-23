<?php



class Fps_Viewer_Node_Var
{

	private $value;
	
	//private $filter = 'e';
	private $filter;
	
	//private $attr = array('foo1', 'foo2');
	private $attr;
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
        $compiler->raw($this->compileValue($compiler));
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
	
	
	
	
	public function setFilter($filter)
	{
		$this->setFilter = $setFilter;
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