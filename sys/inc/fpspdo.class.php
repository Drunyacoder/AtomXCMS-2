<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.0                           |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
| @subpackege    Fps PDO library               |
| @copyright     ©Andrey Brykin 2010-2013      |
| @last mod.     2013/10/10                    |
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


if (!defined('DB_ALL')) define ('DB_ALL', 'DB_ALL');
if (!defined('DB_FIRST')) define ('DB_FIRST', 'DB_FIRST');
if (!defined('DB_COUNT')) define ('DB_COUNT', 'DB_COUNT');


/**
 * @version       1.0.0
 * @author        Andrey Brykin
 * @url           http://fapos.net
 */
class FpsPDO {

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
	
	/**
	 *
	 */
	private $dbh;
	
	private $queryParams = array();

    private $statement;
	
	
	public function __construct()
    {
        $dblocation = Config::read('host', 'db');
        $dbuser = Config::read('user', 'db');
        $dbpasswd = Config::read('pass', 'db');
        $dbname = Config::read('name', 'db');

		
		try {
			$this->dbh = new PDO("mysql:host=$dblocation;dbname=$dbname", $dbuser, $dbpasswd);
		} catch (PDOException $e) {
			print "Error!: " . $e->getMessage() . "<br/>";
			die();
		}
		
		$this->dbh->query("SET NAMES 'utf8'");
		
		$this->dbh->query("SET GLOBAL time_zone = '+00:00';");
		$this->dbh->query("SET @@session.time_zone = '+00:00';");
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
		$this->queryParams = array();
		if (in_array($type, array('DB_FIRST', 'DB_ALL', 'DB_COUNT'))) $this->DB_TYPE = $type;

		$query = $this->__buildQuery($params, $table);

		// trying cache querys 
		if (Config::read('cache_querys') == 1) {
			if ($this->turnSqlCache($query)) {
				return $this->getSqlCache($query);
			}
		}
		
		
		$start = getMicroTime();
		$data = $this->runQuery($query);
		$took = getMicroTime($start);
		
		
		// querys list 
		$redirect = true;
		if (Config::read('debug_mode') == 1) {
			AtmDebug::addRow('DB Queries', array($this->getQueryDump($query), $took));
			$redirect = false;
		}
		if (!$data) {
			showErrorMessage('Произошла ошибка при запросе к базе данных!'. 
			'<br /><br />' . $query, $redirect, '/');
			die();
		}
		
		
		// compact results  
		if ($data) {
			if ($type == 'DB_COUNT') { 		//if type is COUNT
				$_result = $data->fetchColumn();
			} else { 					//if type not COUNT
				//$_result = $data->fetchAll(PDO::FETCH_ASSOC);
				$_result = $this->prepareOutput($data);
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
		$this->queryParams = array();
        $Register = Register::getInstance();
		if ($cache) $this->cleanSqlCache();
		
		
		$query = array('alias' => null, 'table' => null, 'cond' => null, 'fields' => null);
		$query['table'] = $this->getFullTableName($table);
	
		//if we have id of record
		if ((array_key_exists('id', $values) && !empty($values['id'])) || !empty($params)) {
			if (!empty($values['id'])) {
				$conditions = array('id' => $values['id']);
                $this->queryParams['id'] = $values['id'];
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
				$valueInsert[] = $this->__name($fields[$i]) . ' = ' . $this->__value($values[$i], $fields[$i]);
			}
			$query['fields'] = implode(', ', $valueInsert);
			$query = $this->__renderQuery('update', $query);


            $start = getMicroTime();
            $result = $this->runQuery($query);
            $took = getMicroTime($start);
		
		// if not $id or $params
		} else {
			unset($values['id']);
			$fields = array_keys($values);
			$values = array_values($values);
			$count = count($values);
			
			for ($i = 0; $i < $count; $i++) {
				$valueInsert[] = $this->__value($values[$i], $fields[$i]);
			}
			for ($i = 0; $i < $count; $i++) {
				$fieldInsert[] = $this->__name($fields[$i]);
			}
			$query['fields'] = implode(', ', $fieldInsert);
			$query['values'] = implode(', ', $valueInsert);

			$query = $this->__renderQuery('insert', $query);


            $start = getMicroTime();
			$this->runQuery($query);
            $took = getMicroTime($start);
			$result = $this->dbh->lastInsertId();
		}

		if ($Register['Config']->read('debug_mode') == 1)
            AtmDebug::addRow('DB Queries', array($this->getQueryDump($query), $took));

		return $result;
	}
	
	
	public function query($data, $cached = false) {
		$this->queryParams = array();
		if (empty($data)) die('argunent for query must not be NULL ');
		
		/* trying cache querys */
		if (Config::read('cache_querys') == 1 && $cached) {
			if ($this->turnSqlCache($data)) {
				return $this->getSqlCache($data);
			}
		}

		
		$result = '';
		$start = getMicroTime();
		$sql = $this->runQuery($data);
		$took = getMicroTime($start);
		
		
		if (Config::read('debug_mode') == 1)
            AtmDebug::addRow('DB Queries', array($this->getQueryDump($data), $took));
		
		if ($sql !== true) {
			if (!empty($sql)) {
				$result = $sql->fetchAll(PDO::FETCH_ASSOC);
				
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
		$this->queryParams = array();
		$cond = array();
		
		
		$data = array();
		foreach ($params as $field => $value) {
			if (is_int($field)) {
				$cond[] = $value;
			} else {
				$cond[] = "`$field` = :$field";
				$data[":$field"] = $value;
			}
		}
		$cond = implode(' AND ', $cond);
		$this->queryParams = $data;
		
		
		$query = $this->__renderQuery('delete', array(
			'conditions' => $cond,
			'table' => $table,
		));
		

		$start = getMicroTime();
		$this->runQuery($query);
		$took = getMicroTime($start);
		if (Config::read('debug_mode') == 1)
            AtmDebug::addRow('DB Queries', array($this->getQueryDump($query), $took));
	}


    private function prepareOutput(PDOStatement $query) {
        $rows = $query->fetchAll(PDO::FETCH_NUM);

        $model_conf = (!empty($this->relationsMap)) ? $this->relationsMap : array();
        $this->relationsMap = array();
        $meta = array();
        $affected_rows = array();

        foreach(range(0, $query->columnCount() - 1) as $column_index){
            $meta[$column_index] = $query->getColumnMeta($column_index);
        }

        if (empty($model_conf)) {
            return $this->prepareSimplifiedOutput($rows, $meta);
        }


        foreach ($rows as $k => $row) {
            foreach($row as $column_index => $column_value) {
                $column_db_name = $meta[$column_index]['name'];
                $column_db_table = $meta[$column_index]['table'];

                if (!isset($$column_db_table)) $$column_db_table = array();
                if (!array_key_exists($column_db_table, $affected_rows)) $affected_rows[$column_db_table] = array();

                $$column_db_table = array_merge($$column_db_table, array($column_db_name => $column_value));
            }

            foreach (array_keys($affected_rows) as $affected_table) {
                $affect_t = $$affected_table;
                if (array_key_exists('id', $affect_t))
                    $affected_rows[$affected_table][$affect_t['id']] = $affect_t;
                else
                    $affected_rows[$affected_table][] = $affect_t;
            }
            unset($rows[$k]);
        }


        foreach ($model_conf as $table1 => $params) break;
        $rows = &$affected_rows[$table1];
        $this->prepareTableOutput($table1, $affected_rows, $rows, $model_conf);



        if (empty($affected_rows)) return array();
        $affected_rows = array_shift($affected_rows);

        if (empty($affected_rows)) return array();
        $affected_rows = array_values($affected_rows);
        //pr($affected_rows); die();
        return $affected_rows;
    }


    private function prepareTableOutput($table1, $affected_rows, &$rows, &$model_conf, $root_record_id = null)
    {
        $mergeRows = function($row1, $row2, $table1, $table2, $root_record_id) use (&$model_conf, $affected_rows) {
            $params = $model_conf[$table1][$table2];
            switch ($params['type']) {
                case 'has_one':
                    if (!empty($params['foreignKey']))
                        if ($row2[$params['foreignKey']] !== $row1['id']) continue;
                    if (!empty($params['internalKey']))
                        if ($row1[$params['internalKey']] !== $row2['id']) continue;
                    if (!empty($params['rootForeignKey'])) {
                        if (!empty($root_record_id) && $row2[$params['rootForeignKey']] === $root_record_id)
                            $row1[$table2] = $row2;
                        continue;
                    }
                    $row1[$table2] = $row2;
                    break;
                case 'has_many':
                    if (!array_key_exists($table2, $row1) || !is_array($row1[$table2])) $row1[$table2] = array();
                    if (!empty($params['foreignKey']))
                        if ($row2[$params['foreignKey']] !== $row1['id']) continue;
                    if (!empty($params['internalKey']))
                        if ($row1[$params['internalKey']] !== $row2['id']) continue;

                    $row1[$table2][$row2['id']] = $row2;

                    break;
                case 'many_to_many':
                    if (array_key_exists($table2, $model_conf)) {
                        reset($model_conf[$table2]);
                        $t2 = key($model_conf[$table2]);
                    }

                    if (empty($t2) || $model_conf[$table2][$t2]['type'] !== 'many_to_many') {
                        $foreignKey2 = (!empty($model_conf[$table1][$table2]['foreignKey']))
                            ? $model_conf[$table1][$table2]['foreignKey'] : '';

                        if (!empty($foreignKey2)) {
                            if ($row1[$foreignKey2] === $row2['id']) {
                                $row1 = array_merge($row1, $row2);
                            }
                        }
                        break;
                    }

                    if (!array_key_exists($table2, $row1) || !is_array($row1[$table2])) $row1[$table2] = array();
                    if (!empty($params['foreignKey'])) {
                        if ($row1['id'] === $row2[$params['foreignKey']]) {
                            $row1[$table2][$row2['id']] = $row2;
                        }
                    }
                    break;
                case 'has_many_through':
                    $t2 = null;
                    $lefter_table = false;
                    if (array_key_exists($table2, $model_conf)) {
                        reset($model_conf[$table2]);
                        $t2 = key($model_conf[$table2]);
                        if ($model_conf[$table2][$t2]['type'] === 'has_many_through') $lefter_table = true;
                    }

                    if ($lefter_table) {
                        $rows = array(&$row2);
                        $this->prepareTableOutput($table2, $affected_rows, $rows, $model_conf, $root_record_id);
                        if ($row1[$params['foreignKey']] === $row2['id']) {
                            if (empty($row1[$table2])) $row1[$table2] = array();
                            $row1[$table2] = $row2[$t2];
                        }
                    } else {
                        if ($row1['id'] === $row2[$params['foreignKey']] &&
                        !array_key_exists($params['foreignKey'], $row1)) {
                            if (empty($row1[$table2])) $row1[$table2] = array();
                            $row1[$table2][] = $row2;
                        }
                    }
                    break;
            }

            return $row1;
        };


        foreach ($model_conf[$table1] as $table2 => $params) {
			if (empty($rows)) break;
            foreach ($rows as $id => $row) {
                $root_id = (empty($root_record_id)) ? $row['id'] : $root_record_id;
                if (empty($affected_rows[$table2])) {
                    $rows[$id] = array();
                    continue;
                }
                foreach ($affected_rows[$table2] as $row2) {
                    $row = $mergeRows($row, $row2, $table1, $table2, $root_id);
                    if (array_key_exists($table2, $model_conf) && array_key_exists($table2, $row)) {
                        foreach ($row[$table2] as $key => $value) {
                            if (!is_numeric($key))
                                $rows2 = array(&$row[$table2]);
                            else
                                $rows2 = &$row[$table2];
                            break;
                        }
                        $this->prepareTableOutput($table2, $affected_rows, $rows2, $model_conf, $root_id);
                    }
                    $rows[$id] = $row;
                }
            }
        }
    }


    private function prepareSimplifiedOutput($rows, $meta)
    {

        if (empty($rows)) return array();
        if (empty($meta)) throw new Exception('Empty meta data during prepare SQL query output. Query(' . $this->statement->queryString . ')');

        $result = array();
        foreach ($rows as $k => $row) {
            if (!array_key_exists($k, $result)) $result[$k] = array();

            foreach($row as $column_index => $column_value) {
                $column_db_name = $meta[$column_index]['name'];
                //$column_db_table = $meta[$column_index]['table'];
                $result[$k][$column_db_name] = $column_value;
            }
            unset($rows[$k]);
        }

        return $result;
    }

	
	private function runQuery($query) 
	{
        $this->statement = $statement = $this->dbh->prepare($query);
        //pr($query);
        //pr($this->queryParams);
		$statement->execute($this->queryParams);
		return $statement;
	}
	
	
	/**
	 * similar to mysql_real_escape_string
	 */
	public function escape($value) {
		return trim($this->dbh->quote($value), "'");
	}
	
	
	private function getQueryDump($query) {
		if (empty($this->queryParams)) return $query;
		foreach ($this->queryParams as $k => $v) {
			$v = "'$v'";
			$query = preg_replace('#([ =,\(])('.$k.')([ \),]{1}|$)#i', "$1".$v."$3", $query);
		}

		return $query;
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
        if ($this->DB_TYPE === 'DB_FIRST') $params['limit'] = 1;
        $params = $this->repairParams($params);

		if (!empty($params['joins'])) {
			$count = count($params['joins']);
			for ($i = 0; $i < $count; $i++) {
				if (is_array($params['joins'][$i])) {
					$params['joins'][$i] = $this->__buildJoin($params['joins'][$i]);
				}
			}

            if ($this->__limit($params['limit'], $params['offset'])/* && false*/) {
                $cond = array();
                if (!empty($params['cond']) && is_array($params['cond'])) {
                    foreach ($params['cond'] as $k => $v) {
                        if (is_numeric($k) || !strstr($k, '.')) {
                            if (!empty($params['alias']) && preg_match('#^' . $params['alias'] . '\.\w+#', $v))
								$v = str_replace($params['alias'] . '.', '', $v);
							$cond[$k] = $v;
                            unset($params['cond'][$k]);
                        }
                    }
                }
				
                $params_ = array(
                    'table' => $this->__name($this->getFullTableName($table)),
                    'limit' => $params['limit'],
                    'offset' => $params['offset'],
                    'cond' => $cond,
                    //'cond' => $params['cond'],
                );
                $sub_query = $this->__buildQuery($params_, $table);
                $params['limit'] = null;
                $params['offset'] = null;
                //$params['cond'] = null;
            }
		}

		return $this->__renderQuery('select', array(
			'conditions' => $this->__conditions($params['cond'], true, true),
			'fields' => $this->__fields($params['fields']),
			'table' => (!empty($sub_query))
                    ? '(' . $sub_query . ')'
                    : $this->__name($this->getFullTableName($table)),
			'alias' => (!empty($params['alias'])) ? $this->alias . $this->__name($params['alias']) : '',
			'order' => $this->__order($params['order'], $params['alias']),
			'limit' => $this->__limit($params['limit'], $params['offset']),
			'joins' => implode(' ', $params['joins']),
			'group' => $this->__group($params['group'])
		));
	}


    private function repairParams($params) {
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
        return $params;
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
	private function __order($order, $alias = null) {
		if (empty($order)) return null;
		return ' ORDER BY '
            . ((!empty($alias) && !strstr($order, '.') && !strstr($order, '`')) 
				? $this->__name($alias) . '.' 
				: '')
            . $order;
	}
	
	
	/**
	 * Return part of SQL string with LIMIT fragment
	 *
	 * @param int $limit
	 */
	private function __limit($limit, $offset = null) {
		if ($limit) {
			$rt = '';
			if (!strpos(strtolower($limit), 'limit') || strpos(strtolower($limit), 'limit') === 0) {
				$rt = ' LIMIT';
			}

			if ($offset) {
				$rt .= ' ' . $offset . ',';
			}

			$rt .= ' ' . $limit;
			return $rt;
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
							$data .= implode(', ', $this->__value($value, $key));
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
		$value = $this->__value($value, $key);
		$key = $this->__name($key);
		return  $key . ' = ' . $value;
	}
	
	
	/**
	 * Quote and escape values for SQL query
	 *
	 * @param mixed $value
	 */
	private function __value($value, $key = null) 
	{
        if (!empty($key) && strstr($key, '.')) $key = strtr($key, array('.' => '_'));

		if (empty($value) && is_int($value)) $this->queryParams[":$key"] = '0';
		if (empty($value)) $this->queryParams[":$key"] = "''";

		
		if (is_array($value) && !empty($value)) {
			foreach ($value as $k => $v) {
				$value[$k] = $this->__value($v, $key);
			}
			
			
		} else {
			if ($value instanceof Expr) {
				return (string)$value;
			}
			
			$this->queryParams[":$key"] = $value;
		}
		return ":$key";
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

	
	
	public function getErrorInfo() {
		$info = $this->dbh->errorInfo();
		return $info[2];
	}

	
	
	/**
	 * Uses for singlton
	 * Allow initialize only one object
	 */
	public static function get() {
		if (!self::$instance) {
			self::$instance = new FpsPDO;
		}
		return self::$instance;
	}
}



if (!class_exists('Expr')) {
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
}
