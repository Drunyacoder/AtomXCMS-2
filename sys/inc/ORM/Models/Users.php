<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.1                           |
| @Project:      CMS                           |
| @package       CMS Fapos                     |
| @subpackege    Users Model                   |
| @copyright     ©Andrey Brykin 2010-2013      |
| @last mod      2013/04/25                    |
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
class UsersModel extends FpsModel
{
	public $Table  = 'users';

    protected $RelatedEntities = array(
        'inpm' => array(
            'model' => 'Messages',
            'type' => 'has_many',
            'foreignKey' => 'to',
        ),
        'outpm' => array(
            'model' => 'Messages',
            'type' => 'has_many',
            'foreignKey' => 'from',
        ),
    );
	
	
	
    public function getSameNics($nick)
    {
        $Register = Register::getInstance();
        // kirilic
        $rus = array( "А","а","В","Е","е","К","М","Н","О","о","Р","р","С","с","Т","Х","х" );
        // latin
        $eng = array( "A","a","B","E","e","K","M","H","O","o","P","p","C","c","T","X","x" );
        // Заменяем русские буквы латинскими
        $eng_new_name = str_replace( $rus, $eng, $nick );
        // Заменяем латинские буквы русскими
        $rus_new_name = str_replace( $eng, $rus, $nick );
        // Формируем SQL-запрос
        $res = $Register['DB']->query("SELECT * FROM `" . $Register['DB']->getFullTableName('users') . "`
			WHERE name LIKE '".$Register['DB']->escape( $nick )."' OR
			name LIKE '".$Register['DB']->escape( $eng_new_name )."' OR
			name LIKE '".$Register['DB']->escape( $rus_new_name )."';");
        return $res;
    }


    public function getMessage($id)
    {
        $Register = Register::getInstance();

        $messagesModel = $Register['ModManager']->getModelName('Messages');
        $messagesModel = new $messagesModel;
        $message = $messagesModel->getById($id);

        if ($message) {
            $to = $this->getById($message->getTo_user());
            $from = $this->getById($message->getFrom_user());
            $message->setToUser($to);
            $message->setFromUser($from);
            return $message;
        }
        return null;
    }


    public function getUserMessage($mid)
    {
        if (empty($_SESSION['user'])) return false;
        $Register = Register::getInstance();
        $user_id = $_SESSION['user']['id'];

        $messagesModel = $Register['ModManager']->getModelInstance('Messages');
        $messagesModel->bindModel('touser');
        $messagesModel->bindModel('fromuser');

        $condition = "(to_user = '" . $user_id . "' OR from_user = '" . $user_id . "')";
        $condition2 = "id_rmv <> '" . $user_id . "'";
        $message = $messagesModel->getCollection(array(
            $condition,
            $condition2,
            'id' => $mid,
        ));

        return (!empty($message[0])) ? $message[0] : false;
    }


    public function getInputMessages()
    {
        // Запрос на выборку входящих сообщений
        // id_rmv - это поле указывает на то, что это сообщение уже удалил
        // один из пользователей. Т.е. сначала id_rmv=0, после того, как
        // сообщение удалил один из пользователей, id_rmv=id_user. И только после
        // того, как сообщение удалит второй пользователь, мы можем удалить
        // запись в таблице БД TABLE_MESSAGES
        $Register = Register::getInstance();
        $messagesModel = $Register['ModManager']->getModelName('Messages');
        $messagesModel = new $messagesModel;
        $messagesModel->bindModel('fromuser');
        $messagesModel->bindModel('touser');

        $messages = $messagesModel->getCollection(array(
            'to_user' => $_SESSION['user']['id'],
            "id_rmv <> '" . $_SESSION['user']['id'] . "'",
        ), array(
            'order' => 'sendtime DESC'
        ));

        return $messages;
    }
	
	
    public function getOutputMessages()
    {
        // Запрос на выборку входящих сообщений
        // id_rmv - это поле указывает на то, что это сообщение уже удалил
        // один из пользователей. Т.е. сначала id_rmv=0, после того, как
        // сообщение удалил один из пользователей, id_rmv=id_user. И только после
        // того, как сообщение удалит второй пользователь, мы можем удалить
        // запись в таблице БД TABLE_MESSAGES
        $Register = Register::getInstance();
        $messagesModel = $Register['ModManager']->getModelName('Messages');
        $messagesModel = new $messagesModel;
        $messagesModel->bindModel('touser');
        $messagesModel->bindModel('fromuser');

        $messages = $messagesModel->getCollection(array(
            'from_user' => $_SESSION['user']['id'],
            "id_rmv <> '" . $_SESSION['user']['id'] . "'",
        ), array(
            'order' => 'sendtime DESC'
        ));

        return $messages;
    }


    /**
     * Get all users collocutors with last message from the correspondence
     *
     * @param $user_id
     * @return mixed
     */
    public function getUserDialogs($user_id)
    {
        $Register = Register::getInstance();
        $messagesModel = $Register['ModManager']->getModelName('Messages');
        $messagesModel = new $messagesModel;
        $messagesModel->bindModel('touser');
        $messagesModel->bindModel('fromuser');


        $condition = "(to_user = '" . $user_id . "' OR from_user = '" . $user_id . "')";
        $condition2 = "id_rmv <> '" . $user_id . "'";
        $messages = $messagesModel->getCollection(array(
            $condition,
            $condition2,
            "`sendtime` IN (SELECT MAX(sendtime) FROM `" . $messagesModel->getTable() . "`
                WHERE " . $condition ." AND " . $condition2 . " GROUP BY to_user, from_user)",
        ), array(
            'order' => 'sendtime DESC',
        ));



        if (is_array($messages) && count($messages)) {
            $users = array();
            foreach ($messages as $k => &$message) {
                if ($message->getTo_user() === $user_id) {
                    if (in_array($message->getFrom_user(), $users)) unset($messages[$k]);
                    else $users[] = $message->getFrom_user();
                } else {
                    if (in_array($message->getTo_user(), $users)) unset($messages[$k]);
                    else $users[] = $message->getTo_user();
                }
                $message->setDirection(($message->getTo_user() === $user_id) ? 'in' : 'out');
            }
        }

        return $messages;
    }


    /**
     * Get users dialog with one collocutor with all messages from the correspondence
     *
     * @param $owner_id
     * @param $collocutor_id
     * @return mixed
     */
    public function getDialog($owner_id, $collocutor_id, $from_time = false)
    {
        $Register = Register::getInstance();
        $messagesModel = $Register['ModManager']->getModelName('Messages');
        $messagesModel = new $messagesModel;
        $messagesModel->bindModel('touser');
        $messagesModel->bindModel('fromuser');


        $condition = "((to_user = '" . $owner_id . "' AND from_user = '" . $collocutor_id . "') "
            . "OR (from_user = '" . $owner_id . "' AND to_user = '" . $collocutor_id . "'))";
        $condition2 = "id_rmv <> '" . $owner_id . "'";
        $condition3 = (!empty($from_time)) ? "`sendtime` > '" . $from_time . "'" : '';
        $messages = $messagesModel->getCollection(array(
            $condition,
            $condition2,
            $condition3
        ), array(
            'order' => 'sendtime DESC',
        ));

        if (is_array($messages) && count($messages)) {
            foreach ($messages as $k => &$message) {
                $message->setDirection(($message->getTo_user() === $owner_id) ? 'in' : 'out');
            }
        }

        return $messages;
    }


    public function getFullUserStatistic($user_id)
    {
		if (empty($user_id)) return false;
	
        $Register = Register::getInstance();
        $stat = array();
        $modules = glob(ROOT . '/modules/*', GLOB_ONLYDIR);
        if (count($modules)) {
            foreach ($modules as $path) {
                $title = substr(strrchr($path, '/'), 1);
                $classname = $Register['ModManager']->getModelName($title);

                // Is module on?
                if (Config::read($title . '.active') && class_exists($classname)) {
                    @$mod = new $classname;

                    if (isset($mod)) {
                        if (is_callable(array($mod, 'getUserStatistic'))) {
                            $stats = $mod->getUserStatistic($user_id);
                            if (is_array($stats) && count($stats)) {
                                $stat[] = $stats;
                            }
                        }
                        unset($mod);
                    }
                }
            }
        }

        uasort($stat, function($a, $b){
            if (!empty($a['text']) && !empty($b['text'])) {
                if ($a['text'] == $b['text']) {
                    return ($a['text'] < $b['text']) ? -1 : 1;
                }
            }
            return 0;
        });

        return $stat;
    }
	
	
	public function getByName($name)
	{
        $Register = Register::getInstance();
		$entity = $this->getDbDriver()->select($this->Table, DB_FIRST, array(
			'cond' => array(
				'name' => $name
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
	
	
	public function getByNamePass($name, $password)
	{
        $Register = Register::getInstance();
		$entity = $Register['DB']->query("SELECT *, UNIX_TIMESTAMP(last_visit) as unix_last_visit
			FROM `" . $Register['DB']->getFullTableName('users') . "`  WHERE name='"
			.$Register['DB']->escape( $name )."' AND passw='".$Register['DB']->escape( md5( $password ) )
			."' LIMIT 1");
		
		if (!empty($entity[0])) {
            $entity = $this->getAllAssigned($entity);
			$entityClassName = $Register['ModManager']->getEntityNameFromModel(get_class($this));
			$entity = new $entityClassName($entity[0]);
			return (!empty($entity)) ? $entity : false;
		}
		return false;
	}
	

	public function getNewPmMessages($uid)
	{
		$res = $this->getDbDriver()->query("SELECT COUNT(*) as cnt
				FROM `" . $this->getDbDriver()->getFullTableName('messages') . "` 
				WHERE `to_user` = ".$uid."
				AND `viewed` = 0 AND `id_rmv` <> ".$uid);

		return (!empty($res[0]) && !empty($res[0]['cnt'])) ? (string)$res[0]['cnt'] : 0;
	}
	
	
	function getCountComments($user_id = null) 
	{
		$user_id = intval($user_id);
		if ($user_id < 1) return false;
		
		$Register = Register::getInstance();

		
		$commentsModel = $Register['ModManager']->getModelInstance('comments');
		$cnt = $commentsModel->getTotal(array('cond' => array('user_id' => $user_id)));
		
		
		return ($cnt) ? $cnt : false;
	}

	
	function getComments($user_id = null, $offset = null, $per_page = null) 
	{
		$user_id = intval($user_id);
		if ($user_id < 1) return false;
	
		$Register = Register::getInstance();
		
		
		$per_page = intval($per_page);
		$per_page = (!empty($per_page)) ? $per_page : 50;
		
		
		$offset = intval($offset);
		$page = (!empty($offset)) ? ceil($offset / $per_page) : 1;
		
		
		$commentsModel = $Register['ModManager']->getModelInstance('comments');
		$comments = $commentsModel->getCollection(array('user_id' => $user_id), array('page' => $page, 'limit' => $per_page));
		
		return ($comments) ? $comments : false;
	}
}