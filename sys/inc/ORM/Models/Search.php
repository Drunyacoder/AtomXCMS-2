<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.3                           |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
| @subpackege    News Model                    |
| @copyright     ©Andrey Brykin 2010-2013      |
| @last mod      2013/11/11                    |
|----------------------------------------------|
|											   |
| any partial or not partial extension         |
| CMS AtomX,without the consent of the         |
| author, is illegal                           |
|----------------------------------------------|
| Любое распространение                        |
| CMS AtomX или ее частей,                     |
| без согласия автора, является не законным    |
\---------------------------------------------*/



/**
 *
 */
class SearchModel extends FpsModel
{
	public $Table = 'search_index';

    protected $RelatedEntities = array();

	

    public function truncateTable()
    {
        $this->getDbDriver()->query("TRUNCATE `" . $this->getDbDriver()->getFullTableName('search_index') . "`");
    }
	
	
	
	public function getSearchResults($search, $limit, $modules)
	{
		$lmsql = '';
		if (is_array($modules)) {
			$lmsql .= '(';
			foreach ($modules as $module) {
				if ($module != $modules[0]) {
					$lmsql .= ' OR ';
				}
				$lmsql .= '`module` = \''.$module.'\'';
			}
			$lmsql .= ') AND';
		}
		$results = $this->getDbDriver()->query("
			SELECT * FROM `" . $this->getDbDriver()->getFullTableName('search_index') . "`
			WHERE ".$lmsql." MATCH (`index`) AGAINST ('" . $search . "' IN BOOLEAN MODE)
			ORDER BY MATCH (`index`) AGAINST ('" . $search . "' IN BOOLEAN MODE) DESC LIMIT " . $limit);
		if ($results) {
			foreach ($results as $key => $res) {
				$results[$key] = new SearchEntity($res);
			}
		}
			
		return $results;
	}
}