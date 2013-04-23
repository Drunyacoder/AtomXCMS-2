<?php



class Fps_Viewer_Node_If
{

	public $tests;
	public $else;

	
	public function __construct($tests, $else)
	{
		$this->tests = $tests;
		$this->else = $else;
	}
	


    public function compile(Fps_Viewer_CompileParser $compiler)
    {
		$compiler->write('// If block node')->raw("\n");
		
		/*
		$compiler->write('var_dump($this->getValue($this->context, \'fps_user_id\'));');
		$compiler->write('var_dump((');
		$compiler->subcompile($this->tests[0]);
		$compiler->write('));')->raw("\n");
		*/
		
		for ($i = 0; $i < count($this->tests); $i += 2) {
			if ($i > 0) {
				$compiler->outdent()->write('} else if (');
			} else {
				$compiler->write('if (');
			}
			
			//pr($this->tests);
			$compiler->subcompile($this->tests[$i])
				->raw(") {\n")
				->indent();

			foreach ($this->tests[$i + 1] as $k => $v) {
				$compiler->subcompile($v);
			}
		}
		
		
		if (!empty($this->else)) {
			$compiler->outdent()
				->write("} else {\n")
				->indent();
				foreach ($this->else as $key => $val) {
					$compiler->subcompile($val);
				}
		}
	
	
		$compiler->outdent()->write("}\n");
    }



	
	public function __toString()
	{
		$tests = '(' . "\n";
		if (is_array($this->tests)) {
			foreach ($this->tests as $key => $val) {
				$tests .= $key . ':' . $val;
			}
		}
		$tests .= "\n" . ')' . "\n";

	
		$out = "\n";
		$out .= '[tests]:' . $tests . "\n";
		$out .= '[else]:' . $this->else . "\n";
		return $out;
	}
}