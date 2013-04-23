<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.0                           |
| @Project:      CMS                           |
| @package       CMS Fapos                     |
| @subpackege    Polls Model                   |
| @copyright     ©Andrey Brykin 2010-2013      |
| @last mod      2013/01/24                    |
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



/**
 *
 */
class PollsModel extends FpsModel
{
	public $Table = 'polls';

    protected $RelatedEntities;



	/**
     * @param $id
     * @return bool
     */
	public function getByUrl($id)
	{
        $Register = Register::getInstance();
		$entity = $this->getDbDriver()->select($this->Table, DB_FIRST, array(
			'cond' => array(
				'url' => $id
			)
		));

		if (!empty($entity[0])) {
            $entity = $this->getAllAssigned($entity);
			$entityClassName = $Register['ModManager']->getEntityNameFromModel(get_class($this));
			$entity = new $entityClassName($entity[0]);
			return (!empty($entity)) ? $entity : false;
		}
		return false;
	}
	
	
	public function getTree($id)
	{
		$params = array(
			'joins' => array(
				array(
					'table' => 'pages',
					'alias' => 'b',
					'type' => 'LEFT',
					'cond' => array("`b`.`id` = '" . $id . "'"),
				),
			),
			'cond' => array("`a`.`path` LIKE CONCAT(`b`.`path`, '%')"),
			'alias' => 'a',
			'order' => '`a`.`path`',
			'fields' => array(
				"`a`.*",
			),
		);
		$tree = $this->getDbDriver()->select($this->Table, DB_ALL, $params);
		
		//if (!empty($tree)) {
		//	foreach($tree as $k => $v) {
		//		$tree[$k] = new PagesEntity($v);
		//	}
		//}
		
		return $tree;
	}
	
	
	public function getOtherTrees($id)
	{
		$tree = $this->getById($id);
		if (!empty($tree)) {
			$path = ('.' === $tree->getPath()) ? $tree->getId() : $tree->getPath() . $tree->getId();
			$path .= '.';
			$other = $this->getCollection(array(
				"`path` NOT LIKE '" . $path . "%' AND `id` != '" . $id . "'"
			));
			return $other;
		}
		return false;
	}
	
	
	public function add($params)
	{
		if (!empty($params['parent_id'])) {
			$parent = $this->getById($params['parent_id']);
		}
		
		if (!empty($parent)) {
			if ('.' === $parent->getPath()) {
				$params['path'] = $parent->getId() . '.';
			} else {
				$params['path'] = $parent->getPath() . $parent->getId() . '.';
			}
		} else {
			$params['path'] = '1.';
		}
		
		
		if (isset($params['id'])) unset($params['id']);
		$id = $this->getDbDriver()->save($this->Table, $params);
		return !empty($id) ? $id : false;
	}
	
	
	public function delete($id)
	{
		$entity = $this->getById($id);
		if (!empty($entity)) {
			$this->getDbDriver()->delete($this->Table, array(
				"`path` LIKE '" . $entity->getPath() . $entity->getPath() . ".%' OR `id` = '" . $id . "'", 
			));
			return true;
		}
		throw new Exception('Entity not found');
	}
	
	
	public function replace($id, $new_parent_id)
	{
		$new_parent = $this->getById($new_parent_id);
		$replaced = $this->getById($id);
		if (!empty($replaced) && !empty($new_parent)) {
		
		
			$old_path_mask = $replaced->getPath() . $replaced->getId() . '.';
			$new_path = ('.' === $new_parent->getPath()) ? null : $new_parent->getPath();
			$new_path_mask = $new_path . $new_parent->getId() . '.';
			
			$query = "UPDATE `" . $this->getDbDriver()->getFullTableName($this->Table) . "`
				SET `path` = REPLACE(`path`, '" . $old_path_mask . "', '" . $new_path_mask . "')
				WHERE `path` LIKE '" . $old_path_mask . "%'";
			$query2 = "UPDATE `" . $this->getDbDriver()->getFullTableName($this->Table) . "`
				SET `parent_id` = '" . $new_parent_id . "'
				, `path` = '" . $new_path_mask . "'
				WHERE `id` = '" . $id . "'";
			$this->getDbDriver()->query($query);
			$this->getDbDriver()->query($query2);
			return true;
		}
		return false;
	}
	
	
	public function getEntitiesByHomePage($latest_on_home)
	{
        $Register = Register::getInstance();
        $materials = array();
		$sql = '';

		if (in_array('news', $latest_on_home)) 
		$sql .= "(SELECT `title`, `main`, `date`, `on_home_top`, `id`, `views`, `author_id`, (SELECT \"news\") AS skey  FROM `" 
			 . $Register['DB']->getFullTableName('news') . "` "
			 . "WHERE `view_on_home` = '1' AND `available` = '1') ";
		if (in_array('loads', $latest_on_home)) {
			if (!empty($sql)) $sql .= 'UNION ';
			$sql .= "(SELECT `title`, `main`, `date`, `on_home_top`, `id`, `views`, `author_id`, (SELECT \"loads\") AS skey   FROM `" 
				 . $Register['DB']->getFullTableName('loads') . "` "
				 . "WHERE `view_on_home` = '1' AND `available` = '1') ";
		}
		if (in_array('stat', $latest_on_home)) {
			if (!empty($sql)) $sql .= 'UNION ';
			$sql .= "(SELECT `title`, `main`, `date`, `on_home_top`, `id`, `views`, `author_id`, (SELECT \"stat\") AS skey  FROM `" 
				 . $Register['DB']->getFullTableName('stat') . "` "
				 . "WHERE `view_on_home` = '1' AND `available` = '1') ";
		}


		if (!empty($sql)) {
			$sql .= 'ORDER BY `on_home_top` DESC, `date` DESC LIMIT ' . $Register['Config']->read('cnt_latest_on_home');
			$materials = $Register['DB']->query($sql);
            if ($materials) {
                foreach ($materials as $key => $mat) {


                    switch ($mat['skey']) {
                        case 'news':
                            $materials[$key] = new NewsEntity($mat);
                            break;
                        case 'stat':
                            $materials[$key] = new StatEntity($mat);
                            break;
                        case 'loads':
                            $materials[$key] = new LoadsEntity($mat);
                            break;
                    }
                }
            }

        }

        return $materials;
	}

}