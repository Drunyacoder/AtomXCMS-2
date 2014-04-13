<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Email:        drunyacoder@gmail.com         |
| @Site:         http://atomx.net              |
| @Version:      1.0                           |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
| @Subpackege    Bbcode filter                 |
| @Copyright     ©Andrey Brykin 2010-2014      |
| @Last mod      2014/04/14                    |
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

class Fps_Viewer_Filter_Bbcode {


	public function compile($value, Fps_Viewer_CompileParser $compiler)
	{
		if (is_callable($value)) {
			$compiler->raw('Register::getInstance()->PrintText->print_page(');
			$value($compiler);
			$compiler->raw(')');
			return true;
		}
		return "Register::getInstance()->PrintText->print_page($value)";
	}
	
	
	public function __toString()
	{
		$out = '[filter]:bbcode' . "\n";
		return $out;
	}
}