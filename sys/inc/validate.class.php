<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.1.0                         |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
| @subpackege    Validate class                |
| @copyright     ©Andrey Brykin                |
| @last mod      2014/03/14                    |
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


/*
 * REGEX for titles. 
 * Allowed chars. You can change this.
 */
//define ('V_TITLE', '#^[A-ZА-Яа-яa-z0-9\s-\(\),\._\?\!\w\d\{\} ]+$#ui');
define ('V_TITLE', '#^[A-ZА-Яа-яa-z0-9ё\s\-(),._\?!\w\d\{\}\<\>:=\+&%\$\[\]\\\/"\']+$#ui');
define ('V_INT', '#^\d+$#i');
define ('V_TEXT', '#^[\wA-ZА-Яа-яa-z0-9\s\-\(\):;\[\]\+!\.,&\?/\{\}="\']*$#uim');
define ('V_MAIL', '#^[0-9a-z_\-\.]+@[0-9a-z\-\.]+\.[a-z]{2,6}$#i');
define ('V_URL', '#^((https?|ftp):\/\/)?(www.)?([0-9a-z]+(-?[0-9a-z]+)*\.)+[a-z]{2,6}\/?([-0-9a-z_]*\/?)*([-0-9A-Za-zА-Яа-я_]+\.?[-0-9a-z_]+\/?)*$#i');
define ('V_CAPTCHA', '#^[\dabcdefghijklmnopqrstuvwxyz]+$#i');
define ('V_LOGIN', '#^[- _0-9a-zА-Яа-я@]+$#ui');


class Validate {
	
	/**
	 * @var array
	 */
	private $rules;
	
	/**
	 * @var string
	 */
	private $module;
	
	/**
	 * @var array
	 */
	private $disabledFields = array();
	
	/**
	 * @var function(callable)
	 */
	private $layoutWrapper;
	
	const V_TITLE = '#^[A-ZА-Яа-яa-z0-9ё\s\-(),._\?!\w\d\{\}\<\>:=\+&%\$\[\]\\\/"\']+$#ui';
	const V_INT = '#^\d+$#i';
	const V_FLOAT = '#^\d+\.?\d*$#i';
	const V_DATETIME = '#^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$#i';
	const V_TEXT = '#^[\wA-ZА-Яа-яa-z0-9\s\-\(\):;\[\]\+!\.,&\?/\{\}="\']*$#uim';
	const V_MAIL = '#^[0-9a-z_\-\.]+@[0-9a-z\-\.]+\.[a-z]{2,6}$#i';
	const V_URL = '#^((https?|ftp):\/\/)?(www.)?([0-9a-z]+(-?[0-9a-z]+)*\.)+[a-z]{2,6}\/?([-0-9a-z_]*\/?)*([-0-9A-Za-zА-Яа-я_]+\.?[-0-9a-z_]+\/?)*$#i';
	const V_CAPTCHA = '#^[\dabcdefghijklmnopqrstuvwxyz]+$#i';
	const V_LOGIN = '#^[- _0-9a-zА-Яа-я@]+$#ui';

	
	public function __construct($layoutWrapper)
	{
		if (is_callable($layoutWrapper)) $this->layoutWrapper = $layoutWrapper;
	}
	
	
	/**
	 * @param $data string
	 * @param $filter string
	 */
	public function cha_val ($data, $filter = '#^\w*$#Uim') {
		if (!preg_match($filter, $data)) return false;
		else return true;
	}
	
	
	/**
	 * @param $data string
	 * @param $min integer
	 * @param $max integer
	 */
	public function len_val ($data, $min = 1, $max = 100) {
	
		if (mb_strlen($data) > $max) return 'Текст слишком велик';
		elseif (mb_strlen($data) < $min) return 'Текст слишком короткий';
		
		return true;
	}
	
	
	
	//find similar records from  $sourse (< array(table, field) >)
	// TODO || Delete
	public function uniq_val($data, $sourse, $type = 'low') 
	{
		$Register = Register::getInstance();
		
		if ($type == 'hight') {
			//array with russian letters
			$rus = array( "А","а","В","Е","е","К","М","Н","О","о","Р","р","С","с","Т","Х","х" );
			// array with latinic letters
			$eng = array( "A","a","B","E","e","K","M","H","O","o","P","p","C","c","T","X","x" );
			// change russian to latinic
			$eng_new_data = str_replace($rus, $eng, $data);
			// change latinic to russian
			$rus_new_data = str_replace($eng, $rus, $data);
			
			// create SQL query
			$sql = "SELECT * FROM `{$sourse['table']}`
					WHERE `{$sourse['field']}` LIKE '".$Register['DB']->escape( $data )."' OR
					`{$sourse['field']}` LIKE '".$Register['DB']->escape( $eng_new_data )."' OR
					`{$sourse['field']}` LIKE '".$Register['DB']->escape( $rus_new_data )."'";
		  
		} else { //security level not hight...
		
			$sql = "SELECT COUNT(*) 
					FROM `{$sourse['table']}`  
					WHERE `{$sourse['field']}` LIKE '" . $Register['DB']->escape($data) . "'";
					
		}
		$query = $Register['DB']->query($sql);

		return (count($query) > 0) ? false : true;
	}
	
	
	public function setRules($rules) 
	{
		$this->rules = $rules;
	}
	
	
	public function setModule($module) 
	{
		$this->module = $module;
	}
	
	
	public function disableFieldCheck($key) 
	{
        if (is_array($key)) $this->disabledFields = array_merge($this->disabledFields, $key);
		else $this->disabledFields[] = $key;
	}
	
	

	public function check($action = null, $wrap = false)
	{
		$rules = $this->prepareRules($action);

		$Register = Register::getInstance();
		$request = $_POST;
		$errors = array();
		
		
		foreach ($rules as $title_ => $params) {
			if (in_array($title_, $this->disabledFields)) continue;
			
			$fields = array();
			
			// multiple fialeds (for examole attaches)
			if (!empty($params['for'])) {
				for ($i = intval($params['for']['from']); $i <= $params['for']['to']; $i++) {
					$fields[] = $title_ . $i;
				}
				
			} else {
				$fields[] = $title_;
			}
			

			// process
			foreach ($fields as $field) {
				$title = (substr($field,0, 7) == 'files__') ? substr($field, 7) : $field;
				$publicTitle = (!empty($params['title'])) ? $params['title'] : $title_;
			

				// file or field
				if (substr($field, 0, 7) != 'files__') {
					// required
					if (!empty($params['required']) && $params['required'] === true) {
						if (empty($request[$title])) 
							$errors[] = $this->getErrorMessage('required', $params, $title);
						
						
					} else if (!empty($params['required']) && $params['required'] === 'editable') {
						$fields_settings = $Register['Config']->read('fields', $this->module);
						
						if (empty($_POST[$title]) && in_array($title, $fields_settings)) { 
							$errors[] = $this->getErrorMessage('required', $params, $title);
							continue;
						}
					}
				
				
					// max length
					if (!empty($params['max_lenght'])) {
						if (!empty($request[$title]) && mb_strlen($request[$title]) > $params['max_lenght']) 
							$errors[] = $this->getErrorMessage('max_lenght', $params, $title);
					}

					// min length
					if (!empty($params['min_lenght'])) {
						if (!empty($request[$title]) && mb_strlen($request[$title]) < $params['min_lenght']) 
							$errors[] = $this->getErrorMessage('min_lenght', $params, $title);
					}

					// compare
					if (!empty($params['compare'])) {
						if (!empty($request[$title]) && $request[$title] != @$_POST[$params['compare']]) 
							$errors[] = $this->getErrorMessage('compare', $params, $title);
					}					
					
					// pattern
					if (!empty($params['pattern'])) {
						if (!empty($request[$title]) && !$this->cha_val($request[$title], $params['pattern'])) 
							$errors[] = $this->getErrorMessage('pattern', $params, $title);
					}
					
					// user function
					if (!empty($params['function'])) {
						if (!empty($request[$title]) && is_callable($params['function'])) 
							$errors[] = $params['function']($errors);
					}
					
				
				// field is file
				} else {
					
					// required
					if (!empty($params['required']) && $params['required'] === true) {
						if (empty($_FILES[$title]) || empty($_FILES[$title]['name'])) 
							$errors[] = $this->getErrorMessage('required', $params, $title);
						
						
					} else if (!empty($params['required']) && $params['required'] === 'editable') {
						$fields_settings = $Register['Config']->read('fields', $this->module);
						
						if ((empty($_FILES[$title]) || empty($_FILES[$title]['name'])) && in_array($title, $fields_settings)) 
							$errors[] = $this->getErrorMessage('required', $params, $title);
					}
					
					
					if (@empty($_FILES[$title]['name'])) continue;
					
					
					// type
					if (!empty($params['type'])) {
						$ext = strrchr($_FILES[$title]['name'], ".");
						
						switch ($params['type']) {
							// image files
							case 'image':
								$img_extentions = array('.png','.jpg','.gif','.jpeg', '.PNG','.JPG','.GIF','.JPEG');
								
								if (($_FILES[$title]['type'] != 'image/jpeg'
								&& $_FILES[$title]['type'] != 'image/jpg'
								&& $_FILES[$title]['type'] != 'image/gif'
								&& $_FILES[$title]['type'] != 'image/png')
								|| !in_array(strtolower($ext), $img_extentions)) {
									$errors[] = $this->getErrorMessage('type', $params, $title);
								}
								break;
								
							// other files (for example loads module attaches)	
							case 'file':
								$denied_exts = array('.php', '.phtml', '.php3', '.html', '.htm', '.pl', 'js');
								if (in_array(strtolower($ext), $denied_exts)) 
									$errors[] = $this->getErrorMessage('type', $params, $title);
								break;
						}
					}
					
					// size
					if (!empty($params['max_size'])) {
						//pr($title);
						if ($_FILES[$title]['size'] > $params['max_size']) {
							$errors[] = $this->getErrorMessage('max_size', $params, $title);
						}
					}
				}
			}
		}
		
		if ($wrap === true && is_callable($this->layoutWrapper)) {
			$errors = $this->wrapErrors($errors);
		}
		
		return $errors;
	}

	
    /**
     * Merge entity with form session(viewMessage|FpsForm).
     * Geting [object|array] entity and array pattern. Fill entity from session by pattern.
     * Geting array entity and nothing pattern. Pattern = entity and fill entity from session by pattern
     *
     * @param $entity
     * @param array $pattern
     * @return array
     */
    public static function  getCurrentInputsValues($entity, $pattern = array())
    {
        if (!empty($_SESSION['viewMessage'])) {
			$session = $_SESSION['viewMessage'];
        } else if (!empty($_SESSION['FpsForm'])) {
            $session = $_SESSION['FpsForm'];
        } else if (!empty($_POST)) {
			$session = $_POST;
		}
		
		
		if (empty($pattern) && is_array($entity)) $pattern = $entity;
		foreach ($pattern as $key => $value) {
		
			if (is_object($entity)) {
				$getter = 'get' . ucfirst($key);
				$setter = 'set' . ucfirst($key);
				if (!empty($session[$key])) {
					$entity->$setter($session[$key]);
				} else if (!$entity->$getter()) {
					$entity->$setter($value);
				}
			} else if (is_array($entity)) {
				if (!empty($session[$key])) {
					$entity[$key] = $session[$key];
				} else if (!isset($entity[$key])) {
					$entity[$key] = $value;
				}
			}
		}
		return $entity;
    }
	
	
	
	/**
	 * @param $rules array
	 */
	public function getFormFields($action)
	{
		$rules = $this->prepareRules($action);
		$Register = Register::getInstance();
		$request = $_POST;
		return array_fill_keys(array_keys($rules), null);
	}
	
	
	
	public function getErrors()
	{
        $outputContent = '';

        if (!empty($_SESSION['FpsForm']['errors'])) {
            $outputContent = $this->wrapErrors($_SESSION['FpsForm']['errors']);
        }

        return $outputContent;
	}
	
	
	
	/**
	 * @param $rules array
	 * @param $additional_fields array
	 */
	public function getAndMergeFormPost($action = null, $additional_fields = array(), $correct = false, $fields_meta = false)
	{
		$rules = $this->prepareRules($action);
		$pattern = array_fill_keys(array_keys($rules), null);
		$pattern = array_merge($pattern, $additional_fields);
		$fields = $this->getCurrentInputsValues($pattern);
		
		if ($correct === true) 
			$fields = array_map(function($n){
				return trim($n);
			}, $fields);

        if ($fields_meta === true) {
            foreach ($fields as $k => $value) {
				$fields[$k] = $rules[$k];
				$fields[$k]['value'] = $value;
				
				// Dataset is array with possible values.
				// It may be a function whitch returns the array.
				if (isset($fields[$k]['dataset'])) {
					$fields[$k]['dataset'] = (is_callable($fields[$k]['dataset'])) 
						? call_user_func($fields[$k]['dataset'])
						: $fields[$k]['dataset'];
				}
            }
        }
		
		return $fields;
	}


    private function wrapErrors($errors, $preprocess = false) {
        if ($preprocess) {
            if (is_array($errors)) {
                foreach ($errors as $k => $error) {
                    $errors[$k] = $this->completeErrorMessage($error);
                }
            } else $errors = $this->completeErrorMessage($errors);
        }
        return (is_callable($this->layoutWrapper))
            ? call_user_func($this->layoutWrapper, $errors)
            : $errors;
    }

	
	private function getErrorMessage($type, $params, $title) {
		$publicTitle = (!empty($params['title'])) ? $params['title'] : $title;
		
		if (array_key_exists($type . '_error', $params)) return $this->completeErrorMessage($params[$type . '_error']);
		
		switch ($type) {
		
			case 'required':
				$message = sprintf(__('Empty field "%s"'), $title);
				break;
				
			case 'max_lenght':
				$message = sprintf(__('Very big "material"'), $publicTitle, $params['max_lenght']);
				break;
				
			case 'min_lenght':
				$message = sprintf(__('Very small "material"'), $publicTitle, $params['min_lenght']);
				break;
				
			case 'compare':
				$message = sprintf(__('Fields are differend'), $publicTitle);
				break;
				
			case 'pattern':
				$message = sprintf(__('Wrong chars in "..."'), $publicTitle);
				break;
				
			case 'type':
				$message = __('Wrong file format');
				break;
				
			case 'max_size':
				$message = sprintf(__('Very big file'), $title, round(($params['max_size'] / 1000), 1));
				break;
		}
		
		return $this->completeErrorMessage($message);
	}
	
	
	private function completeErrorMessage($message) {
		return $message;
		return "<li>$message</li>\n";
	}


	
	private function prepareRules($action)
	{
        $rules = (!empty($this->rules) && is_array($this->rules)) ? $this->rules : array();
		$rules = (array_key_exists($action, $rules))
			? $rules[$action]
			: @$rules[substr($action, 0, -5)];
		
		if (empty($rules) || count($rules) < 1) 
			throw new Exception("Rules for ".$action."(_form) not found.");
		
		return $rules;
	}
}
