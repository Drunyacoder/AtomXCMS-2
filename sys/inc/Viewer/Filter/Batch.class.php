<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Email:        drunyacoder@gmail.com         |
| @Site:         http://atomx.net              |
| @Version:      1.0                           |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
| @Subpackege    Batch filter                  |
| @Copyright     ©Andrey Brykin 2010-2014      |
| @Last mod      2014/04/29                    |
|----------------------------------------------|
|											   |
| any partial or not partial extension         |
| CMS Fapos,without the consent of the         |
| author, is illegal                           |
|----------------------------------------------|
| Любое распространение                        |
| CMS Fapos или ее частей,                     |
| без согласия автора, является не законным    |
\---------------------------------------------*/

class Fps_Viewer_Filter_Batch {

    private $params = array();

	public function compile($value, Fps_Viewer_CompileParser $compiler)
	{
        if (empty($this->params[0])) throw new Exception('First parameter is not exists in "Batch" filter.');
        if (empty($this->params[1])) throw new Exception('Second parameter is not exists in "Batch" filter.');
        if (!is_callable($value)) throw new Exception('(Filter_Batch):Value for filtering must be callable.');


        $compiler->raw('array_map(function($n, $size = ');
        $this->params[0]->compile($compiler);
        $compiler->indent()->raw(', $def = ');
        $this->params[1]->compile($compiler);
        $compiler->indent()->raw(') {' . "\n");
        $compiler->indent()->write('return array_pad($n, $size, $def);' . "\n");
        $compiler->outdent()->write('}, array_chunk(');
        $value($compiler);
        $compiler->raw(', ');
        $this->params[0]->compile($compiler);
        $compiler->raw('))');
	}
	

    public function addParam($param)
    {
        $this->params[] = $param;
    }


	public function __toString()
	{
		$out = '[filter]:batch' . "\n";
        $out .= '[params]:' . implode("<br>\n", $this->params) . "\n";
		return $out;
	}
}