<?php
/*---------------------------------------------\
|											   |
| @Author:       Andrey Brykin (Drunya)        |
| @Version:      1.0                           |
| @Project:      CMS                           |
| @Package       AtomX CMS                     |
| @subpackege    Forum Model                   |
| @copyright     ©Andrey Brykin 2010-2012      |
| @last mod      2012/05/21                    |
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
class ForumModel extends FpsModel
{
	public $Table = 'forums';

    protected $RelatedEntities = array(
        'themeslist' => array(
            'model' => 'Themes',
            'type' => 'has_many',
            'foreignKey' => 'id_forum',
      	),
        'category' => array(
            'model' => 'ForumCat',
            'type' => 'has_one',
            'internalKey' => 'id_cat',
        ),
        'last_theme' => array(
            'model' => 'Themes',
            'type' => 'has_one',
            'internalKey' => 'last_theme_id',
        ),
        'parent_forum' => array(
            'model' => 'Forum',
            'type' => 'has_one',
            'internalKey' => 'parent_forum_id',
        ),
        'subforums' => array(
            'model' => 'Forum',
            'type' => 'has_many',
            'foreignKey' => 'parent_forum_id',
        ),
    );
	
	
	public function getStats()
	{
		$result = $this->getDbDriver()->query("
			SELECT `id` as last_user_id
			, (SELECT `name` FROM `" . $this->getDbDriver()->getFullTableName('users') . "` ORDER BY `puttime` DESC LIMIT 1) as last_user_name
			, (SELECT COUNT(*) FROM `" . $this->getDbDriver()->getFullTableName('posts') . "`) as posts_cnt
			, (SELECT COUNT(*) FROM `" . $this->getDbDriver()->getFullTableName('themes') . "`) as themes_cnt
			FROM `" . $this->getDbDriver()->getFullTableName('users') . "` ORDER BY `puttime` DESC LIMIT 1");
		return $result;
	}
	
	
	public function updateForumCounters($fid)
	{
		$this->getDbDriver()->query(
				"UPDATE `" . $this->getDbDriver()->getFullTableName('forums') . "` SET `themes` = 
				(SELECT COUNT(*) FROM `" . $this->getDbDriver()->getFullTableName('themes') . "` 
				WHERE `id_forum` = '" . $fid . "'), `posts` = 
				(SELECT COUNT(b.`id`) FROM `" . $this->getDbDriver()->getFullTableName('themes') . "` a 
				LEFT JOIN `" . $this->getDbDriver()->getFullTableName('posts') . "` b ON a.`id`=b.`id_theme`),
				`last_theme_id`=IFNULL((SELECT `id` FROM `" . $this->getDbDriver()->getFullTableName('themes') . "` 
				WHERE `id_forum`='" . $fid . "'
				ORDER BY `last_post` DESC  LIMIT 1), 0) WHERE `id` = '" . $fid . "'" ); 
	}
	
	
	public function deleteThemesPostsCollisions()
	{
		$this->getDbDriver()->query("DELETE FROM `" . $this->getDbDriver()->getFullTableName('themes') . "` WHERE id NOT IN (SELECT DISTINCT id_theme FROM `" . $this->getDbDriver()->getFullTableName('posts') . "`)");
		$this->getDbDriver()->query("DELETE FROM `" . $this->getDbDriver()->getFullTableName('posts') . "` WHERE id_theme NOT IN (SELECT DISTINCT id FROM `" . $this->getDbDriver()->getFullTableName('themes') . "`)");
	}
	
	
	public function upThemesPostsCounters($theme, $self_update = false)
	{
		// Обновляем таблицу USERS
		$this->getDbDriver()->query(
			"UPDATE `" . $this->getDbDriver()->getFullTableName('users') . "` SET 
			`themes` = (SELECT COUNT(*) FROM `" . $this->getDbDriver()->getFullTableName('themes') . "` 
			WHERE `id_author` = '" . $theme->getId_author() . "')
			, `posts` = (SELECT COUNT(*) FROM `" . $this->getDbDriver()->getFullTableName('posts') . "` 
			WHERE `id_author` = '" . $theme->getId_author() . "')
			WHERE `id` = '" . $theme->getId_author() . "'");

		//update forum info
		$this->getDbDriver()->query(
			"UPDATE `" . $this->getDbDriver()->getFullTableName('forums') . "` SET `themes` = 
			(SELECT COUNT(*) FROM `" . $this->getDbDriver()->getFullTableName('themes') . "` 
			WHERE `id_forum` = '" . $theme->getId_forum() . "'), `posts` = 
			(SELECT COUNT(b.`id`) FROM `" . $this->getDbDriver()->getFullTableName('themes') . "` a 
			LEFT JOIN `" . $this->getDbDriver()->getFullTableName('posts') . "` b ON a.`id`=b.`id_theme`
			WHERE a.`id_forum` = '" . $theme->getId_forum() . "'),
			`last_theme_id`=(SELECT `id` FROM `" . $this->getDbDriver()->getFullTableName('themes') . "` 
			WHERE `id_forum`='" . $theme->getId_forum() . "'
			ORDER BY `last_post` DESC  LIMIT 1) WHERE `id` = '" . $theme->getId_forum() . "'" );

        // update theme
        if ($self_update === true) {
            $this->getDbDriver()->query(
            "UPDATE `" . $this->getDbDriver()->getFullTableName('themes') . "`
            SET `posts` = (SELECT COUNT(*) FROM `" . $this->getDbDriver()->getFullTableName('posts') . "`
			WHERE `id_theme` = '" . $theme->getId() . "') - 1,
			`id_last_author` = (SELECT `id_author` FROM `" . $this->getDbDriver()->getFullTableName('posts') . "`
			WHERE `id_theme` = '" . $theme->getId() . "' ORDER BY `time` DESC LIMIT 1),
			`last_post`= (SELECT `id` FROM `" . $this->getDbDriver()->getFullTableName('posts') . "`
			WHERE `id_theme` = '" . $theme->getId() . "'
			ORDER BY `time` DESC  LIMIT 1) WHERE `id` = '" . $theme->getId() . "'" );
        }
	}
	
	
	
	public function upLastPost($from_forum, $id_forum)
	{
		$this->getDbDriver()->query("UPDATE `" . $this->getDbDriver()->getFullTableName('forums') . "` as forum SET 
			forum.`last_theme_id` = IFNULL((SELECT `id` FROM `" . $this->getDbDriver()->getFullTableName('themes') . "` 
			WHERE `id_forum` = forum.`id` ORDER BY `last_post` DESC LIMIT 1), 0) 
			WHERE forum.`id` IN ('" . $from_forum . "', '" . $id_forum . "')");
	}
	
	
	public function deleteCollisions()
	{
		$this->getDbDriver()->query("DELETE FROM `" .$this->getDbDriver()->getFullTableName('themes') 
		. "` WHERE id NOT IN (SELECT DISTINCT id_theme FROM `posts`)");
		$this->getDbDriver()->query("DELETE FROM `" . $this->getDbDriver()->getFullTableName('posts') 
		. "` WHERE id_theme NOT IN (SELECT id FROM `themes`)");
	}
	
	
	public function addLastAuthors($forums)
	{
		$Register = Register::getInstance();
		$uids = array();
		if (!empty($forums)) {
			foreach ($forums as $forum) {
				if (!$forum->getLast_theme()) continue;
				
				$uid = $forum->getLast_theme()->getId_last_author();
				if (0 != $uid) {
					$uids[] = $uid;
				}
			}
			
			
			if (!empty($uids)) {
				$uids = implode(', ', $uids);
				$userModelName = $Register['ModManager']->getModelName('Users');
				$userModel = new $userModelName;
				$users = $userModel->getCollection(array("`id` IN ({$uids})"));
				
				
				if (!empty($users)) {
					foreach ($forums as $forum) {
						if (!$forum->getLast_theme()) continue;
						foreach ($users as $user) {
							if ( $forum->getLast_theme()->getId_last_author() === $user->getId()) {
								$forum->setLast_author($user);
							}
						}
					}
				}
			}

		}
		return $forums;
	}


    public function movePostsToTheme($old_theme, $new_theme, $posts_ids, $unit = false) {
        $Register = Register::getInstance();
        $postsModel = $Register['ModManager']->getModelInstance('Posts');


        $post = $this->getDbDriver()->select('posts', DB_FIRST, array('cond' => array('`id_theme`' => $new_theme->getId()), 'limit' => 1, 'order' => 'time ASC'));
		
		
		if ($unit === true) {
			if (is_array($post) && count($post) > 0) {
				$time = $post[0]['time'];
				$this->getDbDriver()->query("UPDATE `" . $this->getDbDriver()->getFullTableName('posts') . "`
					SET `time` = '" . $time . "' + INTERVAL 1 SECOND
					WHERE `id` IN 
					(SELECT `id` FROM `" . $this->getDbDriver()->getFullTableName('posts') . "` 
					WHERE `id_theme` = " . $old_theme->getId() . ") 
					AND `time` < '" . $time . "'");
			}
			$this->getDbDriver()->query("UPDATE `" . $this->getDbDriver()->getFullTableName('forum_attaches') . "`
				SET `theme_id` = " . $new_theme->getId() . " WHERE `post_id` IN 
				(SELECT `id` FROM `" . $this->getDbDriver()->getFullTableName('posts') . "` 
				WHERE `id_theme` = " . $old_theme->getId() . ")");
			$this->getDbDriver()->query("UPDATE `" . $this->getDbDriver()->getFullTableName('posts') . "`
				SET `id_theme` = " . $new_theme->getId() . " 
				WHERE `id_theme` = " . $old_theme->getId() . "");
				
			if (!$new_theme->getPolls() && $old_theme->getPolls()) {
				$old_polls = $old_theme->getPolls();
				if (!empty($old_polls[0])) {
					$new_theme->setPolls($old_polls[0]);
					$new_theme->save();
				}
				
			}
		
			$old_theme->delete();
			$this->upThemesPostsCounters($old_theme, false);
		} else {
			$this->getDbDriver()->query("UPDATE `" . $this->getDbDriver()->getFullTableName('posts') . "`
				SET `id_theme` = " . $new_theme->getId() . " 
				WHERE `id` IN (" . implode(',', (array)$posts_ids) . ")");

			if (is_array($post) && count($post) > 0) {
				$time = $post[0]['time'];
				$this->getDbDriver()->query("UPDATE `" . $this->getDbDriver()->getFullTableName('posts') . "`
					SET `time` = '" . $time . "' + INTERVAL 1 SECOND
					WHERE `id` IN (" . implode(',', (array)$posts_ids) . ") 
					AND `time` < '" . $time . "'");
			}
			$this->getDbDriver()->query("UPDATE `" . $this->getDbDriver()->getFullTableName('forum_attaches') . "`
				SET `theme_id` = " . $new_theme->getId() . " 
				WHERE `post_id` IN (" . implode(',', (array)$posts_ids) . ")");
				
				
			$this->upThemesPostsCounters($old_theme, true);
			$this->upThemesPostsCounters($new_theme, true);
		}


		return true;
    }


    /**
     * @param $user_id
     * @return array|bool
     */
    function getUserStatistic($user_id) {
        $user_id = intval($user_id);
        if ($user_id > 0) {
            $result = $this->getDbDriver()->select('users', DB_FIRST, array('cond' => array('`id`' => $user_id), 'limit' => 1));
            if (is_array($result) && count($result) > 0) {
                $res = array();
                /*
                if ($result[0]['themes'] > 0) {
                    $res[] = array(
                        'text' => __('themes'),
                        'count' => $result[0]['themes'],
                        'url' => get_url('/forum/user_themes/' . $user_id),
                    );
                }
                */
                if ($result[0]['posts'] > 0) {
                    $res[] = array(
                        'text' => __('messages'),
                        'count' => $result[0]['posts'],
                        'url' => get_url('/forum/user_posts/' . $user_id),
                    );
                }

                return $res;
            }
        }
        return false;
    }
}