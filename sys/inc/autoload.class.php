<?php



class Autoload
{



    public static function load($class)
    {
		if ('Fps_Viewer' !== substr($class, 0, strlen('Fps_Viewer'))) {
			//$class = strtolower($class);
		}
		
		
		// ORM elements must located in /sys/inc/orm/ directory or in module directory
		// Freedom for users and developers ;)
		if ('Entity' === mb_substr($class, -6)) {
			$_class = str_replace('Entity', '', $class);
			if (file_exists(ROOT . '/sys/inc/ORM/Entities/' . $_class . '.php')) {
				include_once ROOT . '/sys/inc/ORM/Entities/' . $_class . '.php';
				return true;
			} else if (file_exists(ROOT . '/modules/' . $_class . '/entity.php')) {
				include_once ROOT . '/modules/' . $_class . '/entity.php';
				return true;
			}
		} else if ('Model' === mb_substr($class, -5)) {
			$_class = str_replace('Model', '', $class);
			if (file_exists(ROOT . '/sys/inc/ORM/Models/' . $_class . '.php')) {
				include_once ROOT . '/sys/inc/ORM/Models/' . $_class . '.php';
				return true;
			} else if (file_exists(ROOT . '/modules/' . $_class . '/model.php')) {
				include_once ROOT . '/modules/' . $_class . '/model.php';
				return true;
			}
		}
		
		
		// Other classes must located in /sys/[inc | fnc]/ 
        if (file_exists(ROOT . '/sys/inc/' . strtolower($class) . '.class.php')) {
            include_once ROOT . '/sys/inc/' . strtolower($class) . '.class.php';
            return true;
        }  else if (file_exists(ROOT . '/sys/fnc/' . strtolower($class) . '.class.php')){
            include_once ROOT . '/sys/fnc/' . strtolower($class) . '.class.php';
            return true;
        }
		
		
		// Include FpsViewer files
		if ('Fps_Viewer' === substr($class, 0, strlen('Fps_Viewer'))) {
			$file = ROOT . '/sys/inc/' . strtr($class, array('_' => '/', 'Fps_' => '')) . '.class.php';
			if (file_exists($file)) {
				include_once $file;
				return true;
			}
		}


        $files = glob(ROOT . '/sys/fnc/*.php');
        if (count($files)) {
            foreach ($files as $file) {
                include_once $file;
                if (class_exists($class)) {
                    break;
                }
            }
        }
    }



    public static function loadFuncs()
    {
        $files = glob(ROOT . '/sys/fnc/*.php');
        if (count($files)) {
            foreach ($files as $file) {
                include_once $file;
            }
        }
    }
}







