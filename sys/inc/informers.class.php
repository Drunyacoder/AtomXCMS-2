<?php



/**
* Informers class for CMS Fapos
*
* @author     ©Andrey Brykin
*/
class Informers {

	/**
	* @var string
	* module for informer
	*/
	private $module;
	/**
	* @var string
	* type of materials for informer
	*/
	private $materials;
	/**
	* @var string
	* output data
	*/
	private $output;
	/**
	* @var string template
	*/
	private $template;
	/**
	* @var array
	* terms for informers
	*/
	private $allowed_values = array(
		'forum' => array(
			'themes' => array(
				'title' => 'Темы',
				'table' => 'themes',
				'order' => array(
					'time' => 'Дата создания',
					'last_post' => 'Последнее сообщение',
					'posts' => 'Кол-во сообщений',
				),
			),
			'posts' => array(
				'title' => 'Сообщения',
				'table' => 'posts',
				'order' => array(
					'time' => 'Дата создания',
					'edittime' => 'Дата редактирования',
				),
			),
		),
	);

	
	
	
	/**
	* @param int $id - informer ID
	* @retrun string output | boolean false
	* 
	* get informer params and execute builder
	* OR rerurn false if informer with this ID is not exists
	*/
	public function execute($id) {
		$id = intval($id); if ($id < 1) return false;
		$informer = DataBase::select('informers', DB_FIRST, array('cond' => array('id' => $id)));
		if (count($informer) < 1) return false;
		
		$this->template = $informer[0]['template'];
		$params = unserialize($informer[0]['options']);
		$this->buildContent($params);
		return $this->output;
	}
	
	/**
	* @param array $params - params for informer
	* @return string - output data
	* 
	* get materials for informer from database fnd build informer
	*/
	public function buildContent($params) {
		if ($this->heckAllParams($params)) {
			$table = $params['materials'];
			$sql = DataBase::select($table, DB_ALL, array('order' => $params['order'], 'limit' => $params['count']));
			if (count($sql) > 0) {
				foreach ($sql as $row) {
					$temp = $this->template;
					foreach ($row as $k => $value) {
						$temp = str_replace('{' . strtoupper($k) . '}', $value, $temp);
					}
					$this->output .= $temp;
				}
				return $this->output;
			}
		}
		return null;
	}
	
	
	
	
	/**
	* @param array $params - params for checking
	* @return boolean
	*
	* checking all params
	*/
	public function heckAllParams($params) {
		$check = $this->heckParam($params['module'], 'module');
		if ($check === true) {
			$this->module = $params['module'];
			$check = $this->heckParam($params['materials'], 'materials');
			if ($check === true) {
				$this->materials = $params['materials'];
				$check = $this->heckParam($params['order'], 'order');
				if ($check === true) {
					return true;
				}
			}
		}
		return false;
	}
	
	
	
	/**
	* @param string $param - checking variable
	* @param string $dest  - context for checking
	* @return boolean
	*
	* checking permitted modules, materials and order fields
	*/
	private function heckParam($param, $dest) {
		$permitted_dest = array('module', 'materials', 'order');
		if (!in_array($dest, $permitted_dest)) return false;

		switch ($dest) {
			case 'module':
				if (!array_key_exists($param, $this->allowed_values)) return false;
				break;
			case 'materials':
				if (empty($this->module)) return false;
				if (!array_key_exists($param, $this->allowed_values[$this->module])) return false;
				break;
			case 'order':
				if (empty($this->module) || empty($this->materials)) return false;
				if (!array_key_exists($param, $this->allowed_values[$this->module][$this->materials]['order'])) return false;
				break;
			default:
				return false;
		}
		return true;
	}
}





include_once 'sys/boot.php';
$options = array(
	'module' => 'forum',
	'materials' => 'themes',
	'order' => 'time',
	'count' => 10,
	'cols' => 2,
);






$obj = new Informers;
echo $obj->execute(1);

?>