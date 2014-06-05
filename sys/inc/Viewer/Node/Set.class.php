<?php



class Fps_Viewer_Node_Set
{

	private $left;
	private $right;


	public function __construct($left, $right)
	{
		$this->left = $left;
		$this->right = $right;
	}



    public function compile(Fps_Viewer_CompileParser $compiler)
    {
        $compiler->addIndent();
        $compiler->raw("\$this->setValue(" . $this->compileAttributesArray() . ", ");
        $this->right->compile($compiler);
        $compiler->raw(");\n");
    }

	
	private function compileAttributesArray()
    {
        $output = "array('" . $this->left->getValue() . "', ";
        $attrs = $this->left->getAttrs();
        if (count($attrs) > 0) {
            foreach ($attrs as $attr) {
                if (is_object($attr)) $attr = $attr->getValue();
                $output .= "'" . $attr . "', ";
            }
        }
        return substr($output, 0, -2) . ")";
    }
	
	public function __toString()
	{
        $out = "\n";
        $out .= '[left]:' . $this->left . "\n";
        $out .= '[right]:' . $this->right . "\n";
        return $out;
	}
}