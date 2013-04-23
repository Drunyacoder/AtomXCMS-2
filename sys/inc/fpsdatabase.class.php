<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.0                           |
| @Project:      CMS                           |
| @package       CMS Fapos                     |
| @subpackege    Data Base library             |
| @copyright     ©Andrey Brykin 2010-2012      |
| @last mod.     2012/05/04                    |
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


define ('DB_ALL', 'DB_ALL');
define ('DB_FIRST', 'DB_FIRST');
define ('DB_COUNT', 'DB_COUNT');


/**
 * @version       1.0.0
 * @author        Andrey Brykin
 * @url           http://fapos.net
 */
class FpsDataBase {

	/**
	 * Alias for SQL query
	 *
	 * @var string
	 */
	public $alias = 'AS ';
	/**
	 * The starting character that this DataSource uses for quoted identifiers.
	 *
	 * @var string
	 */
	public $startQuote = '`';

	/**
	 * The ending character that this DataSource uses for quoted identifiers.
	 *
	 * @var string
	 */
	public $endQuote = '`';
	
	/**
	 * @var string
	 */
	private $DB_TYPE = 'DB_FIRST'; 
	
	/**
	 * @var mixed
	 */
	static public $instance = false;
	
	
	
	
	public function __construct()
    {
        $dblocation = Config::read('host', 'db');
        $dbuser = Config::read('user', 'db');
        $dbpasswd = Config::read('pass', 'db');
        $dbname = Config::read('name', 'db');


        //Подключение к базе данных
        $dbcnx = mysql_connect($dblocation,$dbuser,$dbpasswd) or die(mysql_error());

        if (!$dbcnx) {
             echo("<p>В настоящий момент сервер базы данных не доступен, поэтому корректное отображение страницы невозможно.</p>");
             exit();
        }

        if (!mysql_select_db($dbname, $dbcnx)) {
             echo( "<p>В настоящий момент база данных не доступна, поэтому корректное отображение страницы невозможно.</p>" );
             exit();
         }
         mysql_query("SET NAMES 'utf8'");
         mysql_set_charset('UTF-8');
	}

	/**
	* for SELECT querys...
	*
	* @param string $table
	* @param string $type
	* @param array $params
	* access public
	*/
	public function select ($table, $type, $params = array()) {
		if (in_array($type, array('DB_FIRST', 'DB_ALL', 'DB_COUNT'))) $this->DB_TYPE = $type;
		$params = array_merge(array(
			'cond' => array(), 
			'limit' => null, 
			'page' => null, 
			'fields' => null, 
			'order' => null, 
			'group' => null, 
			'alias' => null, 
			'joins' => array()), $params);

		if (!is_numeric($params['page']) || intval($params['page']) < 1) {
			$params['page'] = 1;
		}
		if ($params['page'] > 1 && !empty($params['limit'])) {
			$params['offset'] = ($params['page'] - 1) * $params['limit'];
		} else {
			$params['offset'] = 0;
		}
		
		
		$query = $this->__buildQuery($params, $table);
		// trying cache querys 
		if (Config::read('cache_querys') == 1) {
			if ($this->turnSqlCache($query)) {
				return $this->getSqlCache($query);
			}
		}
		
		
		$start = getMicroTime();
		$data = mysql_query($query);
		$took = getMicroTime() - $start;
		// querys list 
		$redirect = true;
		if (Config::read('debug_mode') == 1) {
			$_SESSION['db_querys'][] = $query . ' &nbsp; [ ' . $took . ' ]';
			$redirect = false;
		}
		if (!$data) {
			showErrorMessage('Произошла ошибка при запросе к базе данных!', 
			mysql_error() . '<br /><br />' . $query, $redirect, '/');
			die();
		}
		// compact results 
		if ($data) {
			if ($type == 'DB_COUNT') { 		//if type is COUNT
				$_result = mysql_result($data, 0);
			} else { 					//if type not COUNT
				$_result = array();
				while ($result = mysql_fetch_assoc($data)) {
					$_result[] = $result;
				}
			}
		}
		// write cache 
		if (Config::read('cache_querys') == 1) {
			$this->writeSqlCache($query, $_result);
		}
		return $_result;
	}

	
	/**
	* for SAVE or UPDATE querys...
	*
	* @param string $table  database table
	* @param array $values Data for save
	* @param array $params [$cond (array), $limit (int), $page(int), $fields(array), $order(str), group(str)]
	*/
	public function save($table, $values, $params = array(), $cache = false) {
        $Register = Register::getInstance();
		if ($cache) $this->cleanSqlCache();
		$query = array('alias' => null, 'table' => null, 'cond' => null, 'fields' => null);
		$query['table'] = $this->getFullTableName($table);
		
		//if we have id of record
		if ((array_key_exists('id', $values) && !empty($values['id'])) || !empty($params)) {
			if (!empty($values['id'])) {
				$conditions = array('id' => $values['id']);
				unset($values['id']);
			} else {
				$conditions = $params;
			}
			$query['conditions'] = $this->__conditions($conditions, true, true);
			$fields = array_keys($values);
			$values = array_values($values);
			$count = count($values);
			
			for ($i = 0; $i < $count; $i++) {
				if (is_int($fields[$i]) && !empty($values[$i]) && is_string($values[$i])) {
					$valueInsert[] = $values[$i];
					continue;
				}
				$valueInsert[] = $this->__name($fields[$i]) . ' = ' . $this->__value($values[$i]);
			}
			$query['fields'] = implode(', ', $valueInsert);
			$query = $this->__renderQuery('update', $query);
		
		
		// if not $id or $params
		} else {
			unset($values['id']);
			$fields = array_keys($values);
			$values = array_values($values);
			$count = count($values);
			
			for ($i = 0; $i < $count; $i++) {
				$valueInsert[] = $this->__value($values[$i]);
			}
			for ($i = 0; $i < $count; $i++) {
				$fieldInsert[] = $this->__name($fields[$i]);
			}
			$query['fields'] = implode(', ', $fieldInsert);
			$query['values'] = implode(', ', $valueInsert);
			$query = $this->__renderQuery('insert', $query);
			
			if ($Register['Config']->read('debug_mode') == 1) $_SESSION['db_querys'][] = $query;
			
			mysql_query($query);
			
			return mysql_insert_id();
		}
		if ($Register['Config']->read('debug_mode') == 1) $_SESSION['db_querys'][] = $query;
		
		return mysql_query($query);
	}
	
	
	
	public function query($data, $cached = false) {
		if (empty($data)) die('argunent for query must not be NULL ');
		/* trying cache querys */
		if (Config::read('cache_querys') == 1 && $cached) {
			if ($this->turnSqlCache($data)) {
				return $this->getSqlCache($data);
			}
		}

		$result = '';

		
		$start = getMicroTime();
		$sql = mysql_query($data);
		$took = getMicroTime() - $start;
		if (Config::read('debug_mode') == 1) $_SESSION['db_querys'][] = $data . ' &nbsp; [ ' . $took . ' ]';
		
		if ($sql !== true) {
			if (!empty($sql)) {
				while ( $record = mysql_fetch_assoc($sql)) {
					$result[] = $record;
				}
			}
		}
		/* write cache */
		if (Config::read('cache_querys') == 1 && $cached && !empty($result)) {
			$this->writeSqlCache($data, $result);
		}
		return (!empty($result)) ? $result : array();
	}
	
	
	
	public function delete($table, $params)
	{
		$cond = array();
		foreach ($params as $field => $value) {
			if (is_int($field)) {
				$cond[] = $value;
			} else {
				$cond[] = "`{$field}` = '{$value}'";
			}
		}
		$cond = implode(' AND ', $cond);
		
		$query = $this->__renderQuery('delete', array(
			'conditions' => $cond,
			'table' => $table,
		));
		
		
		$start = getMicroTime();
		mysql_query($query);
		$took = getMicroTime() - $start;
		if (Config::read('debug_mode') == 1) $_SESSION['db_querys'][] = $query . ' &nbsp; [ ' . $took . ' ]';
	}
	
	
	
	/**
	 * @param array $params Params for query
	 *
	 * Prepare part of SQL query with JOIN uses params
	 */
	private function __buildJoin($params) {
		$params = array_merge(array(
			'table' => null, 
			'alias' => null, 
			'type' => null, 
			'cond' => array()), $params);
		$params['cond'] = $this->__conditions($params['cond'], true, false);
		if (!empty($params['alias'])) {
			$params['alias'] = $this->alias . $this->__name($params['alias']);
		}
		$params['table'] = $this->getFullTableName($params['table']);
		return $this->__renderJoin($params);
	}
	
	
	
	/**
	 * Renders a final SQL JOIN statement
	 *
	 * @param array $data
	 * @return string
	 */
	private function __renderJoin($params) {
		extract($params);
		return trim("{$type} JOIN {$table} {$alias} ON ({$cond})");
	}
	
	
	
	/**
	 * @param array $params Params for query
	 * @param string $table
	 *
	 * Prepare SQL query uses params and table
	 */
	private function __buildQuery($params, $table) {
		if (!empty($params['joins'])) {
			$count = count($params['joins']);
			for ($i = 0; $i < $count; $i++) {
				if (is_array($params['joins'][$i])) {
					$params['joins'][$i] = $this->__buildJoin($params['joins'][$i]);
				}
			}
		}
		return $this->__renderQuery('select', array(
			'conditions' => $this->__conditions($params['cond'], true, true),
			'fields' => $this->__fields($params['fields']),
			'table' => $this->__name($this->getFullTableName($table)),
			'alias' => (!empty($params['alias'])) ? $this->alias . $this->__name($params['alias']) : '',
			'order' => $this->__order($params['order']),
			'limit' => $this->__limit($params['limit'], $params['offset']),
			'joins' => implode(' ', $params['joins']),
			'group' => $this->__group($params['group'])
		));
	}
	
	
	
	
	
	/**
	 * Renders a final SQL statement by putting together the component parts in the correct order
	 *
	 * @param string $type type of query being run.  e.g select, create, update, delete, schema, alter.
	 * @param array $data Array of data to insert into the query.
	 * @return string Rendered SQL expression to be run.
	 */
	private function __renderQuery($type, $data) {
		extract($data);
		$aliases = null;

		switch (strtolower($type)) {
			case 'select':
				return "SELECT {$fields} FROM {$table} {$alias} {$joins} {$conditions} {$group} {$order} {$limit}";
			break;
			case 'insert':
				return "INSERT INTO {$table} ({$fields}) VALUES ({$values})";
			break;
			case 'update':
				if (!empty($alias)) {
					$aliases = "{$this->alias}{$alias} {$joins} ";
				}
				return "UPDATE {$table} {$aliases}SET {$fields} {$conditions}";
			break;
			case 'delete':
				if (!empty($alias)) {
					//$aliases = "{$this->alias}{$alias} {$joins} ";
				}
				return "DELETE FROM {$table} WHERE {$conditions}";
			break;
			case 'schema':
				foreach (array('columns', 'indexes', 'tableParameters') as $var) {
					if (is_array(${$var})) {
						${$var} = "\t" . join(",\n\t", array_filter(${$var}));
					} else {
						${$var} = '';
					}
				}
				if (trim($indexes) != '') {
					$columns .= ',';
				}
				return "CREATE TABLE {$table} (\n{$columns}{$indexes}){$tableParameters};";
			break;
			case 'alter':
			break;
		}
	}
	
	
	

	/**
	 * Parse fields and return string SQL fragment
	 *
	 * @param mixed $fields
	 */ 
	private function __fields($fields, $quote = false) {
		if (empty($fields)) $out = '*';
		if (is_array($fields)) {
			if ($quote === true) {
				foreach ($fields as $key => $field) {
					$fields[$key] = $this->__name($field);
				}
			} 
			$out = implode(', ', $fields);
		}
		if ($this->DB_TYPE === 'DB_COUNT') {
			$out = 'COUNT(' . $out . ')';
		}
		return $out;
	}
	
	
	
	/**
	 * Return part of SQL string with ORDER fragment
	 *
	 * @param string $order
	 */
	private function __order($order) {
		if (empty($order)) return null;
		return ' ORDER BY ' . $order;
	}
	
	
	/**
	 * Return part of SQL string with LIMIT fragment
	 *
	 * @param int $limit
	 */
	private function __limit($limit, $offset = null) {
		if ($limit && $this->DB_TYPE != 'DB_FIRST') {
			$rt = '';
			if (!strpos(strtolower($limit), 'limit') || strpos(strtolower($limit), 'limit') === 0) {
				$rt = ' LIMIT';
			}

			if ($offset) {
				$rt .= ' ' . $offset . ',';
			}

			$rt .= ' ' . $limit;
			return $rt;
		} else if ($this->DB_TYPE == 'DB_FIRST') {
			return ' LIMIT 1';
		}
		return null;
	}
	
	
	/**
	 * Return part of SQL string with GROUP fragment
	 *
	 * @param string $group
	 */
	private function __group($group) {
		if (empty($group)) return null;
		return ' GROUP BY ' . $group;
	}
	
	
	
	/**
	 * @param array $conditions conditions for query
	 *
	 * Parse and Prepare conditions
	 */
	private function __renderConditions($conditions) {
		
	}
	
	
	
	
	/**
	 * @param mixed $conditions Array or string of conditions, or any value.
	 * @param boolean $quoteValues If true, values should be quoted
	 * @param boolean $where If true, "WHERE " will be prepended to the return value
	 * @return string SQL fragment
	 */
	private function __conditions($conditions, $quoteValues = true, $where = true) {
		$output = $clause = '';

		if ($where) {
			$clause = ' WHERE ';
		}
		if (is_array($conditions) && !empty($conditions)) {
			$output = $this->__conditionKeysToString($conditions, $quoteValues);

			if (empty($output)) {
				return null;
			}
			return $clause . implode(' AND ', $output);
		}

		if (empty($conditions) || trim($conditions) == '') {
			return null;
		}
		$clauses = '/^WHERE\\x20|^GROUP\\x20BY\\x20|^HAVING\\x20|^ORDER\\x20BY\\x20/i';

		if (preg_match($clauses, $conditions, $match)) {
			$clause = '';
		}
		if (trim($conditions) == '') {
			$conditions = ' 1 = 1';
		} else {
			$conditions = $this->__quoteFields($conditions);
		}
		return $conditions; 
	}

	
	
	/**	
	 * @param array $conditions Array or string of conditions
	 * @param boolean $quoteValues If true, values should be quoted
	 * @return string SQL fragment
	 */
	private function __conditionKeysToString($conditions, $quoteValues = true) {
		$out = array();
		$data = $columnType = null;
		$bool = array('and', 'or', 'not', 'and not', 'or not', 'xor', '||', '&&');
		
		foreach ($conditions as $key => $value) {
			$join = ' AND ';
			$not = null;

			if (is_numeric($key) && empty($value)) {
				continue;
			} elseif (is_numeric($key) && is_string($value)) {
				$out[] = $not . $this->__quoteFields($value);
			} elseif ((is_numeric($key) && is_array($value)) || in_array(strtolower(trim($key)), $bool)) {
				if (in_array(strtolower(trim($key)), $bool)) {
					$join = ' ' . strtoupper($key) . ' ';
				} else {
					$key = $join;
				}
				$value = $this->__conditionKeysToString($value, $quoteValues);

				if (strpos($join, 'NOT') !== false) {
					if (strtoupper(trim($key)) == 'NOT') {
						$key = 'AND ' . trim($key);
					}
					$not = 'NOT ';
				}

				if (empty($value[1])) {
					if ($not) {
						$out[] = $not . '(' . $value[0] . ')';
					} else {
						$out[] = $value[0] ;
					}
				} else {
					$out[] = '(' . $not . '(' . implode(') ' . strtoupper($key) . ' (', $value) . '))';
				}

			} else {
				if (is_array($value) && !empty($value) && empty($valueInsert)) {
					$keys = array_keys($value);
					if ($keys === array_values($keys)) {
						$count = count($value);
						if ($count === 1) {
							$data = $this->__quoteFields($key) . ' = (';
						} else {
							$data = $this->__quoteFields($key) . ' IN (';
						}
						if ($quoteValues) {
							$data .= implode(', ', $this->__value($value, $columnType));
						}
						$data .= ')';
					} else {
						$ret = $this->__conditionKeysToString($value, $quoteValues);
						if (count($ret) > 1) {
							$data = '(' . implode(') AND (', $ret) . ')';
						} elseif (isset($ret[0])) {
							$data = $ret[0];
						}
					}
				} elseif (is_numeric($key) && !empty($value)) {
					$data = $this->__quoteFields($value);
				} else {
					$data = $this->__parseKey(trim($key), $value);
				}

				if ($data != null) {
					$out[] = $data;
					$data = null;
				}
			}
		}
		return $out;
	}

	
	
	/**
	 * @param string $key
	 * @param mixed $value
	 * @return string 
	 */
	private function __parseKey($key, $value) {
		$value = $this->__value($value);
		$key = $this->__name($key);
		return  $key . ' = ' . $value;
	}
	
	
	
	/**
	 * Quote and escape values for SQL query
	 *
	 * @param mixed $value
	 */
	private function __value($value) {
		if (empty($value) && is_int($value)) return '0';
		if (empty($value)) return "''";
		if (is_array($value) && !empty($value)) {
			foreach ($value as $k => $v) {
				$value[$k] = $this->__value($v);
			}
		} else {
			if ($value instanceof Expr) {
				return (string)$value;
			}
			return '\'' . mysql_real_escape_string($value) . '\'';
		}
		return $value;
	}
	
	
	
	/**
	 * Quotes fields
	 *
	 * @param string $conditions
	 * @return string or false if no match
	 */
	private function __quoteFields($conditions) {
		$start = $end  = null;
		$original = $conditions;
		
		if (!empty($this->startQuote)) {
			$start = preg_quote($this->startQuote);
		}
		if (!empty($this->endQuote)) {
			$end = preg_quote($this->endQuote);
		}

		$conditions = str_replace(array($start, $end), '', $conditions);
		$conditions = preg_replace_callback('/(?:[\'\"][^\'\"\\\]*(?:\\\.[^\'\"\\\]*)*[\'\"])|([a-z0-9_' 
					  . $start . $end . ']*\\.[a-z0-9_' . $start . $end . ']*)/i', 
					  array(&$this, '__quoteMatchedField'), $conditions);

		if ($conditions !== null) {
			return $conditions;
		}
		return $original;
	}
	

	
	/**
	 * Auxiliary function to quote matches `Model.fields` from a preg_replace_callback call
	 *
	 * @param string matched string
	 * @return string quoted strig
	 */
	private function __quoteMatchedField($match) {
		if (is_numeric($match[0])) {
			return $match[0];
		}
		return $this->__name($match[0]);
	}
	
	
	
	
	/**	
	 * @param mixed $data Either a string with a column to quote. An array of columns to quote
	 * @return string SQL field
	 */
	private function __name($data) {
		if ($data === '*') {
			return '*';
		}
		if (is_array($data)) {
			foreach ($data as $i => $dataItem) {
				$data[$i] = $this->__name($dataItem);
			}
			return $data;
		}

		$data = trim($data);
		if (preg_match('/^[\w-]+(\.[\w-]+)*$/', $data)) { // string, string.string
			if (strpos($data, '.') !== false) { // string
				$items = explode('.', $data);
				return $this->startQuote . implode($this->endQuote . '.' . $this->startQuote, $items) 
				. $this->endQuote;
			}
			return $this->startQuote . $data . $this->endQuote;
		}
		if (preg_match('/^[\w-]+\.\*$/', $data)) { // string.*
			return $this->startQuote . str_replace('.*', $this->endQuote . '.*', $data);
		}
		if (preg_match('/^([\w-]+)\((.*)\)$/', $data, $matches)) { // Functions
			return $matches[1] . '(' . $this->name($matches[2]) . ')';
		}
		if (preg_match('/^([\w-]+(\.[\w-]+|\(.*\))*)\s+' . preg_quote($this->alias) 
		. '\s*([\w-]+)$/', $data, $matches)) {
			return preg_replace('/\s{2,}/', ' ', $this->name($matches[1]) . ' ' 
			. $this->alias . ' ' . $this->name($matches[3]));
		}
		if (preg_match('/^[\w-_\s]*[\w-_]+/', $data)) {
			return $this->startQuote . $data . $this->endQuote;
		}
		return $data;
	}
	
	
	
	
	/**
	* @param string $table - table name without prefix
	* @return string table name with prefix
	*/
	public function getFullTableName($table) {
		$prefix = Config::read('prefix', 'db');
		return $prefix . $table;
	}
	
	
	/**
	* check cache 
	*
	* @sql (string)         query
	* @return (boolean)     false 
	*/
	public function turnSqlCache($sql) {
		/* uniq filename */
		$cache_file_name = md5(md5($sql) . md5($sql . 'salt')) . '.dat';
		/* check file */
		if (!file_exists(ROOT . '/sys/cache/sql/' . $cache_file_name)){
			return false;
		}
		if (!is_readable(ROOT . '/sys/cache/sql/' . $cache_file_name)) {
			return false;
		}
		return true;
	}

	
	/**
	* return cache data
	*
	* @sql (string)       query
	* @return (array)     sql results
	*/
	public function getSqlCache($sql) {
		/* uniq filename */
		$cache_file_name = md5(md5($sql) . md5($sql . 'salt')) . '.dat';
		/* get file data and unserialize */
		$cache_data = file_get_contents(ROOT . '/sys/cache/sql/' . $cache_file_name);
		$cache_data = unserialize($cache_data);
		return $cache_data;
	}  	


	/**
	* write cache data
	*
	* @sql (string)       query
	* @data (array)       sql results
	* @return             none
	*/
	public function writeSqlCache($sql, $data) {
		/* check and create cache dir  */
		if (!file_exists(ROOT . '/sys/cache/sql/')) mkdir(ROOT . '/sys/cache/sql/', 0777, true);
		clearstatcache();
		/* uniq filename */
		$cache_file_name = md5(md5($sql) . md5($sql . 'salt')) . '.dat';
		/* get file data and unserialize */
		$file = fopen(ROOT . '/sys/cache/sql/' . $cache_file_name, 'w');
		fwrite($file, serialize($data));
		fclose($file);
		return;
	}


	/**
	* clean cache
	*
	* @return             none
	*/
	public function cleanSqlCache() {
		if (!file_exists(ROOT . '/sys/cache/sql/')) return;
		$files = glob(ROOT . '/sys/cache/sql/*');
		if (!empty($files)){
			foreach ($files as $file) {
				@unlink($file);
			}
		}
		return;
	} 


	
	
	/**
	 * Uses for singlton
	 * Allow initialize only one object
	 */
	public static function get() {
		if (!self::$instance) {
			self::$instance = new FpsDataBase;
		}
		return self::$instance;
	}
}



/**
 * This class uses for insert SQL functions to
 * query. Without this your functions will be uses as simple string
 */
class Expr {
	
	public $string;
	
	public function __construct($str) {
		$this->string = $str;
	}
	
	public function __toString() {
		return $this->string; 
	}
}
?>