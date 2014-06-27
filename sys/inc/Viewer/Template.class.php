<?php

abstract class Fps_Viewer_Template
{
	protected $context;



    abstract public function display();


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
		return '';
	}


    public function setValue($variable_params, $value, $context = null)
    {
        if ($context === null) $context = &$this->context;

        $param = array_shift($variable_params);
        $getter = 'get' . ucfirst($param);
        $setter = 'set' . ucfirst($param);

        if (is_array($context) &&
            !array_key_exists($param, $context) &&
            count($variable_params) > 0) {
            return false;
        }
        if (count($variable_params) === 0) {
            if ($param === '[]') {
                if (is_array($context))
                    $context[] = $value;
            } else {
                if (is_object($context)) {
                    $context->{$setter}($value);
                } else {
                    $context[$param] = $value;
                }
            }
        } else {
            if (is_object($context)) {
                $context->{$setter}($this->setValue($variable_params, $value, $context->{$getter}()));
            } else if (is_array($context)) {
                $context[$param] = $this->setValue($variable_params, $value, $context[$param]);
            }
        }

        return $context;
    }
}