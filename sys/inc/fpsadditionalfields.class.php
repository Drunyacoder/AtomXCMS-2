<?php
/*-----------------------------------------------\
| 												 |
|  @Author:       Andrey Brykin (Drunya)         |
|  @Version:      0.5                            |
|  @Project:      CMS                            |
|  @package       CMS Fapos                      |
|  @subpackege    Additional Fields              |
|  @copyright     ©Andrey Brykin 2010-2013       |
|  @last mod.     2013/02/22                     |
\-----------------------------------------------*/

/*-----------------------------------------------\
| 												 |
|  any partial or not partial extension          |
|  CMS Fapos,without the consent of the          |
|  author, is illegal                            |
|------------------------------------------------|
|  Любое распространение                         |
|  CMS Fapos или ее частей,                      |
|  без согласия автора, является не законным     |
\-----------------------------------------------*/


/**
 * Begin work when additional fields mode is ON
 *
 * @author     Andrey Brykin
 * @version    0.2
 * @url        http://fapos.net
 */
class FpsAdditionalFields {
 
	
	public $module;
	
	
	public function __construct() {
	}
	


    /**
     * Merge result of SQL query without additional fields with
   	 * additional fields.
     *
     * @param array $records
     * @param bool $inputs
     * @param string $module
     * @return mixed
     */
	public function mergeRecords($records, $inputs = false, $module = null)
    {
		$Register = Register::getInstance();


        if (empty($module)) $module = $this->module;
		$ids = array();
		foreach ($records as $record) 
			$ids[] = $record->getId();
		$ids = implode(', ', $ids);
        $where = array('entity_id IN (' . $ids . ')');


        $FieldsModelName = $Register['ModManager']->getModelInstance($module . 'AddFields');
        $ContentModelName = $Register['ModManager']->getModelInstance($module . 'AddContent');
   
		
        //$Model->bindModel('content');
        $addFields = $FieldsModelName->getCollection(array());
        $addContents = $ContentModelName->getCollection(array($where));
		


        if (!empty($addFields) && is_array($addFields)) {
            foreach ($records as $k => $entity) {
                $output = array();
                foreach ($addFields as $addField) {
				
				
					$fieldContent = array();
					if (!empty($addContents)) {
						foreach($addContents as $addCon) {
						
							// Get current field contents
							if ($addCon->getField_id() == $addField->getId()
							&& $entity->getId() === $addCon->getEntity_id()) {
								$viewData = $addCon->getContent();
								break;
							}
						}
					}
					$addField->setContent($fieldContent);
					
				

                    $field = 'add_field_' . $addField->getId();
                    $f_params = $addField->getParams();
                    if (!empty($f_params)) $f_params = unserialize($f_params);




                    if ($inputs === true) {
                        if (isset($_SESSION['viewMessage'][$field]))
                            $viewData = h($_SESSION['viewMessage'][$field]);
                        if (isset($_SESSION['FpsForm'][$field]))
                            $viewData = h($_SESSION['FpsForm'][$field]);
                    }


                    switch ($addField->getType()) {
                        case 'textarea':// TEXTAREA
                            if ($inputs === true) {
                                $viewData = '<textarea name="' . $field . '">'
                                . $viewData . '</textarea>';
                            }
                            break;


                        case 'text':// TEXT
                            if ($inputs === true) {
                              $viewData = '<input type="text" name="' . $field . '"'
                              . ' value="' . $viewData . '" />';
                            }
                            break;


							
                        case 'checkbox':// CHECKBOX
                            $ans = (!empty($f_params['values'])) ? explode('|', $f_params['values']) : array();
                            $yes = (!empty($ans[0])) ? h($ans[0]) : '';
                            $no = (!empty($ans[1])) ? h($ans[1]) : '';
                            
							
                            if ($inputs === true) {
                                if (empty($viewData))
                                    $viewData = '<input type="checkbox" name="' . $field . '" value="1" />';
                                else
                                    $viewData = '<input type="checkbox" name="' . $field . '"'
                                    . 'value="1" checked="checked" />';
                            
							
							} else {
								$viewData = (!empty($viewData)) ? $yes : $no;
							}
                            break;


                        default:
                            if ($inputs === true) {
                              $viewData = '<input type="text" name="' . $field . '"'
                              . ' value="' . $viewData . '" />';
                            }
                            break;
                    }


                    $output[$field] = $viewData;
                }
                $entity->setAdd_fields($output);
            }
      	}
		return $records;
	}


	
	/**
	 *
	 */
	public function getInputs($records = array(), $setValues = true, $module = null)
    {
		if (empty($module)) $module = $this->module;
		$Register = Register::getInstance();
        $ModelName = $Register['ModManager']->getModelName(ucfirst($module) . 'AddFields');
        $Model = new $ModelName();

        $Model->bindModel(ucfirst($module) . 'AddContent');
		if (!empty($records)) $addFields = $records;
		else $addFields = $Model->getCollection();
		

		$_addFields = array();
		$output = array();
		
		
		if (!empty($addFields)) {
			foreach($addFields as $key => $field) {
				
				$id = (is_object($field)) ? $field->getId() : $field['id'];
				$type = (is_object($field)) ? $field->getType() : $field['type'];
				
				$_addFields[$key] = 'add_field_' . $id;
				$output[$_addFields[$key]] = '';
				$value = '';
				
				if ($setValues) {
					if (!empty($_SESSION['viewMessage'][$_addFields[$key]])) 
						$value = h($_SESSION['viewMessage'][$_addFields[$key]]);
					if (!empty($_SESSION['FpsForm'][$_addFields[$key]])) 
						$value = h($_SESSION['FpsForm'][$_addFields[$key]]);
				}

				switch ($type) {
					case 'text':
						$output[$_addFields[$key]] = '<input type="text" value="' . $value 
						. '" name="' . $_addFields[$key] . '" />';
						break;
					case 'textarea':
						$output[$_addFields[$key]] = '<textarea name="' . $_addFields[$key] . '">' 
						. $value . '</textarea>';
						break;
					case 'checkbox':
						$output[$_addFields[$key]] = '<input type="checkbox" value="1" name="' 
						. $_addFields[$key] . '" ';
						$output[$_addFields[$key]] .= (!empty($value)) ? 'checked="checked" />' : ' />';
						break;
				}
			}
		}
		return $output;
	}

	
	
	/**
	 *
	 */
	public function checkFields($module = null) {
		if (empty($module)) $module = $this->module;
		$Register = Register::getInstance();
        $ModelName = $Register['ModManager']->getModelName(ucfirst($module) . 'AddFields');
        $Model = new $ModelName();
		
        $Model->bindModel(ucfirst($module) . 'AddContent');
        $addFields = $Model->getCollection();
		
		
		$_addFields = array();
		$error = null;
		
		
		if (!empty($addFields)) {
			foreach($addFields as $key => $field) {
				$params = $field->getParams();
				$params = (!empty($params)) 
				? unserialize($params) : array();
				$field_name = 'add_field_' . $field->getId();
				$value = '';
				$array = array('field_id' => $field->getId());
				
				
				
				
				switch ($field->getType()) {
					case 'textarea':
					case 'text':
						if (!empty($params['required']) && empty($_POST[$field_name])) 
							$error = $error . '<li>Поле "' . $field->getLabel() . '" не заполнено</li>';
						if (!empty($_POST[$field_name]) && mb_strlen($_POST[$field_name]) > (int)$field->getSize()) 
							$error = $error . '<li>Поле "' . $field->getLabel() . '" превышает ' 
							. $field->getSize() . ' символов</li>';
						$array['content'] = (!empty($_POST[$field_name])) ? $_POST[$field_name] : '';
						break;
					case 'checkbox':
						if (!empty($_POST[$field_name])) 
							$array['content'] = 1;
						else
							$array['content'] = 0;
						break;
				}
				$_addFields[] = $array;
			}
		}
		
		return (!empty($error)) ? $error : $_addFields;
	}
	
	
	
	/**
	 * For saving additional fields
	 */
	public function save($rec_id, $fields, $module = null) {
		if (empty($module)) $module = $this->module;
		$Register = Register::getInstance();
	
		if (empty($fields)) return true;
		foreach ($fields as $field) {

			$where = array(
				'entity_id' => $rec_id,
				'field_id' => $field['field_id'],
			);
			$classNameM = ucfirst($module) . 'AddContentModel';
			$classNameE = ucfirst($module) . 'AddContentEntity';
			$fieldsModel = new $classNameM;
			$check = $fieldsModel->getCollection($where, array('limit' => 1));
			
			
			$data = array(
				'entity_id' => $rec_id,
				'field_id' => $field['field_id'],
				'content' => $field['content'],
			);
			if ($check) $data['id'] = $check[0]->getId();
			$entity = new $classNameE($data);
			$entity->save();
		}
	}
	
	
	
	
}
