<?php

abstract class Fps_Viewer_Template
{
	protected $context;
	

	public function __construct($context)
	{
		$this->context = $context;
	}
	
	
	public function getContext() {
		return $this->context;
	}
	
	
	public function addToContext($array) {
		$this->context = array_merge($this->context, $array);
	}
	
	
	public function includeFile($path, array $subcontext) {
        $context = array_merge($this->context, $subcontext);
		$Viewer = new Fps_Viewer_Manager(new Fps_Viewer_Loader(array('root_dir' => '')));
		echo $Viewer->view($path, $context);
	}
	
	
	protected function getValue($context, $need) 
	{
		
		if (is_array($context)) {
			if (array_key_exists($need, $context)) return $context[$need];
			//return null;
			
		} else if (is_object($context)) {
			$getter = 'get' . ucfirst(strtolower($need));
			//if (null !== $var = $context->$getter()) return $var;
            return $context->$getter();
		}
		

		//return '{{ ' . $need . ' }}';
		return '';
	}

	abstract public function display();

}