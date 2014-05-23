<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.0                           |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
| @subpackege    Blog Model                    |
| @copyright     ©Andrey Brykin 2010-2014      |
| @last mod      2014/05/07                    |
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
class BlogModel extends FpsModel
{
	public $Table = 'blog';

    protected $RelatedEntities = array(
        'author' => array(
            'model' => 'Users',
            'type' => 'has_one',
            'foreignKey' => 'author_id',
      	),
        'category' => array(
            'model' => 'BlogCategories',
            'type' => 'has_one',
            'foreignKey' => 'category_id',
        ),
        'comments_' => array(
            'model' => 'Comments',
            'type' => 'has_many',
            'foreignKey' => 'entity_id',
			'additionCond' => array("module = 'blog'"),
        ),
        'attaches' => array(
            'model' => 'BlogAttaches',
            'type' => 'has_many',
            'foreignKey' => 'entity_id',
        ),
    );

	
    /**
     * @param array $params
     * @param array $addParams
     * @return array|bool
     */
    public function getCollection($params = array(), $addParams = array())
   	{
        $entities = parent::getCollection($params, $addParams);
		$entities = $this->getMaterialsAttaches($entities, 'blog');
		return $entities;
   	}
	

    /**
     * @param $user_id
     * @return array|bool
     */
    function getUserStatistic($user_id) {
        $user_id = intval($user_id);
        if ($user_id > 0) {
            $result = $this->getTotal(array('cond' => array('author_id' => $user_id)));
            if ($result) {
                $res = array(
                    'text' => __('Blog'),
                    'count' => intval($result),
                    'url' => get_url('/blog/user/' . $user_id),
                );

                return $res;
            }
        }
        return false;
    }
}