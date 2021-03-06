<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Email:        drunyacoder@gmail.com         |
| @Site:         http://atomx.net              |
| @Version:      1.0                           |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
| @Subpackege    Show filter                   |
| @Copyright     ©Andrey Brykin 2010-2014      |
| @Last mod      2014/04/27                    |
|----------------------------------------------|
|											   |
| any partial or not partial extension         |
| CMS AtomX,without the consent of the         |
| author, is illegal                           |
|----------------------------------------------|
| Любое распространение                        |
| CMS AtomX или ее частей,                     |
| без согласия автора, является не законным    |
\---------------------------------------------*/

class Fps_Viewer_Filter_Show {


	public function compile($value, Fps_Viewer_CompileParser $compiler)
	{
		if (!is_callable($value)) throw new Exception('(Filter_Show):Value for filtering must be callable.');
        $compiler->raw("'<pre>' . print_r(");
        $value($compiler);
        $compiler->raw(", true) . '<pre>'");
	}

	
	public function __toString()
	{
		$out = '[filter]:show' . "\n";
		return $out;
	}
}