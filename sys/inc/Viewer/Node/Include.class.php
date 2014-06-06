<?php



class Fps_Viewer_Node_Include
{

	public $path;

    private $context;

	
	public function __construct($path, $context)
	{
		$this->path = trim($path);
		$this->context = $context;
	}
	


    public function compile(Fps_Viewer_CompileParser $compiler)
    {
		$Config = $compiler->loader->config;
		$path = strtr($this->path, array('//' => '/', '\\\\' => '\\'));
		
		$compiler->write("\n");
		$compiler->write("\$this->includeFile('$path', ");

        if (is_array($this->context) && count($this->context)) {
            $compiler->raw("array(");
            foreach ($this->context as $key => $row) {
                $compiler->raw("'$row' => (isset(\$$row)) ? \$$row : ''");
                if ($key + 1 < count($this->context)) $compiler->raw(", ");
            }
            $compiler->raw(")");
        } else {
            $compiler->raw("array()");
        }


        $compiler->raw(");");
		$compiler->write("\n");
    }



	
	public function __toString()
	{
		$out = "\n";
		$out .= '[path]:' . $this->path . "\n";
		return $out;
	}
}