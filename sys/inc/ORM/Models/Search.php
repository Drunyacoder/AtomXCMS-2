<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.2                           |
| @Project:      CMS                           |
| @package       CMS Fapos                     |
| @subpackege    News Model                    |
| @copyright     ©Andrey Brykin 2010-2012      |
| @last mod      2012/06/04                    |
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
class SearchModel extends FpsModel
{
	public $Table = 'search_index';

    protected $RelatedEntities = array();


    public function truncateTable()
    {
        $this->getDbDriver()->query("TRUNCATE `" . $this->getDbDriver()->getFullTableName('search_index') . "`");
    }
	
	
	public function getSearchResults($search, $limit)
	{
		$results = $this->getDbDriver()->query("
			SELECT * FROM `" . $this->getDbDriver()->getFullTableName('search_index') . "`
			WHERE MATCH (`index`) AGAINST ('" . $search . "' IN BOOLEAN MODE)
			ORDER BY MATCH (`index`) AGAINST ('" . $search . "' IN BOOLEAN MODE) DESC LIMIT " . $limit);
			
		if ($results) {
			foreach ($results as $key => $res) {
				$results[$key] = new SearchEntity($res);
			}
		}
			
		return $results;
	}
}