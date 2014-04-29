<?php



class Fps_Viewer_Node_Var extends Fps_Viewer_Node_Expresion
{

	private $value;

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
		if (is_array($this->filters) && count($this->filters)) {
            $this->parseFilters($compiler);
		} else {
            $value = $this->compileValue($compiler);
            $compiler->raw($value);
        }
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
                $attr = $this->attr;
				while (count($attr)) {
					$key = array_shift($attr);
					$key = $key instanceof Fps_Viewer_Node_Var 
						? $key->compileValue($compiler) 
						: "'$key'";
					$value = "\$this->getValue(" . $value . ", $key)";
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
	
	
	
	public function addAttr($key)
	{
		$this->attr[] = $key;
	}
	
	
	
	public function __toString()
	{
		$out = "\n";
		$out .= '[value]:' . $this->value . "\n";
		$out .= '[filter]:' . implode(', ', $this->filters) . "\n";
		$out .= '[attr]:' . implode(', ', (array)$this->attr) . "\n";
		return $out;
	}
}