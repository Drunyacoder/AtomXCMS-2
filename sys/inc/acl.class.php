<?php
##################################################
##												##
## Author:       Andrey Brykin (Drunya)         ##
## Version:      1.3                            ##
## Project:      CMS                            ##
## package       CMS Fapos                      ##
## subpackege    ACL library                    ##
## copyright     ©Andrey Brykin 2010-2014       ##
## last mod.     2014/01/09                     ##
##################################################


##################################################
##												##
## any partial or not partial extension         ##
## CMS Fapos,without the consent of the         ##
## author, is illegal                           ##
##################################################
## Любое распространение                        ##
## CMS Fapos или ее частей,                     ##
## без согласия автора, является не законным    ##
##################################################

//rules and groups files



class ACL {

	private $rules;
	private $forumsModerators;
	private $groups;
	private $whatModeratorCanDo = array(
		'view_forums_list', 
		'view_forums', 
		'view_themes', 
		'edit_themes', 
		'delete_themes',
		'delete_mine_themes',
		'close_themes',
		'edit_posts',
		'delete_posts',
		'delete_mine_posts',
	);


	public function __construct($path) {
        include_once $path . 'acl_rules.php';
        include_once $path . 'acl_groups.php';
        include_once $path . 'forums_moderators.php';

		$this->rules = $acl_rules;
		$this->groups = $acl_groups;
		$this->forumsModerators = $forums_moderators;
	}


	
	/**
	 * @param array $params
	 * @param bool $redirect
	 * @param int $group
	 * @param int $userId
	 * @param bool $onlySpecialAccess - Check only special access
	 */
	public function turn($params, $redirect = true, $group = false, $userId = false, $onlySpecialAccess = false) {
		
		// If check access for forum actions, we must get id for this forum
		$forum_id = (!empty($params[2])) ? intval($params[2]) : false;

	
		if ($group === false) {
			$user_group = (!empty($_SESSION['user']['status'])) ? $_SESSION['user']['status'] : 0;
		} else {
			$user_group = (int)$group;
		}
		
		if ($userId === false) {
			$user_id = (!empty($_SESSION['user']['id'])) ? $_SESSION['user']['id'] : 0;
		} else {
			$user_id = (int)$userId;
		}
		
		
		if ($forum_id)
			$access_string_with_id = implode('.', array($params[0], $forum_id, $params[1]));
		$access_string = (count($params) == 2) 
			? implode('.', $params) 
			: implode('.', array($params[0], $params[1]));

		
		$access = false;
		$rules = $this->rules;
		if (!$onlySpecialAccess) $rules = $this->mergeModeratorsPermissions($rules);
		
		
		switch (count($params)) {
			case 1:
				$access = (bool)in_array($user_group, $rules[$access_string]['groups']);
				break;
				
			case 2:
				if (!empty($rules[$access_string])) {
					$access = (bool)(array_key_exists('users', $rules[$access_string]) && 
						in_array($user_id, $rules[$access_string]['users']));
					if ($access) break;
				}
			
				if (!$onlySpecialAccess) {
					if (!empty($rules[$access_string])) {
						$access = (bool)in_array($user_group, $rules[$access_string]['groups']);
					}
				}
				break;
				
			// gemor mode. We must check user permissions to concrete forum by user id, 
			// then by group, then check permissions to other forums by ID & by group
			case 3:
				if (!empty($rules[$access_string_with_id])) {
					$access = (bool)(array_key_exists('users', $rules[$access_string_with_id]) && 
						in_array($user_id, $rules[$access_string_with_id]['users']));
					if ($access) break;
				}  
 
				if (!empty($rules[$access_string])) {
					$access = (bool)(array_key_exists('users', $rules[$access_string]) && 
						in_array($user_id, $rules[$access_string]['users']));
					if ($access) break;
				}  
				
				if (!$onlySpecialAccess) {
					if (!empty($rules[$access_string])) {
						$access = (bool)in_array($user_group, $rules[$access_string]['groups']);
					}
				}
				break;
				
			default:
				$access = false;
				break;
		}
		
		
	
		if (empty($access) && $redirect) {
			redirect('/error.php?ac=403');
		} else {
			return $access;
		}
	}
	
	
	/**
	*
	*/
	public function save_rules($rules) {
		$rules = $this->cleanRules($rules);
		
		if ($fopen = fopen(ROOT . '/sys/settings/acl_rules.php', 'w')) {
			fputs($fopen, '<?php ' . "\n" . '$acl_rules = ' . var_export($rules, true) . "\n" . '?>');
			fclose($fopen);
			return true;
		} else {
			return false;
		}
	}
	
	
	/**
	*
	*/
	public function save_groups($groups) {
		if ($fopen=@fopen(ROOT . '/sys/settings/acl_groups.php', 'w')) {
			@fputs($fopen, '<?php ' . "\n" . '$acl_groups = ' . var_export($groups, true) . "\n" . '?>');
			@fclose($fopen);
			return true;
		} else {
			return false;
		}
	}
	
	
	
	public function getGroups()
	{
		$out= array();
		foreach ($this->groups as $k => $v) {
			$out[$k] = $v;
			$out[$k]['id'] = $k;
		}
		return $out;
	}
	
	
	/**
	* @return user group info
	*/
	public function get_group_info() {
		return $this->groups;
	}


    public function getRules()
    {
        return $this->rules;
    }
	
	
	/**
	* @param int $id - user group ID
	*
	* @return string group title
	*/
	public function get_user_group($id) {
		if (!empty($this->groups[$id])) return $this->groups[$id];
		return false;
	}
	
	
	
	public function getModerators() {
		return $this->forumsModerators;
	}
	
	
	
	/**
	 * Return users objects(moderators) of concrete forum
	 *
	 * @param int $forum_id
	 */
	public function getForumModerators($forum_id) {
		if (!array_key_exists($forum_id, $this->forumsModerators)) return array();
		
		$result = $this->forumsModerators[$forum_id];
		
		if (!empty($result)) {
			$Register = Register::getInstance();
			$usersModel = $Register['ModManager']->getModelInstance('users');
			
			$ids = implode(',', $result);
			$users = $usersModel->getCollection(array('id IN (' . $ids . ')'));
			
			if ($users) return $users;
		}
		
		return array();
	}
	
	
	
	/**
	 * Save forums moderators to file
	 *
	 * @param array $rules
	 */
	public function saveForumsModerators($rules) {
		if ($fopen = fopen(ROOT . '/sys/settings/forums_moderators.php', 'w')) {
			fputs($fopen, '<?php ' . "\n" . '$forums_moderators = ' . var_export($rules, true) . "\n" . '?>');
			fclose($fopen);
			return true;
		} else {
			return false;
		}
	}
	
	
	
	/**
	 * Convert forums moderators data to simple ACL rules data
	 * (Moderator permissions convert to special user permissions for concrete forum)
	 */
	private function mergeModeratorsPermissions($rules) {
		$result = $rules;
		
		if (empty($this->forumsModerators)) return $result;
		
		foreach ($this->forumsModerators as $forum_id => $users) {
			foreach ($this->whatModeratorCanDo as $rule) {
				$rule_key = 'forum.' . $forum_id . '.' . $rule;
				
				if (!array_key_exists($rule_key, $result)) {
					$result[$rule_key] = array('users' => $users);
					continue;
				}
				
				$result[$rule_key]['users'] = array_unique(array_merge($result[$rule_key]['users'], $users));
			}
		}
		
		return $result;
	}
	
	
	
	private function cleanRules($rules) {
		foreach ($rules as $rule_key => &$rules_data) {
			if (preg_match('#^forum\.[\d]\.#', $rule_key)) {
				if (!array_key_exists('users', $rules_data) || empty($rules_data['users'])) {
					unset($rules[$rule_key]);
				}
			}
			
			if (!is_array($rules_data['users']) || !count($rules_data['users'])) {
				unset($rules_data['users']);
			}
		}
		
		return $rules;
	}
	
	
	
	/**
	 *
	 */
	static public function checkCategoryAccess($catAccessStr) {
		if ($catAccessStr === '') return true;
		$uid = (!empty($_SESSION['user']['status'])) ? intval($_SESSION['user']['status']) : 0;
		
		$accessAr = explode(',', $catAccessStr);
		if (count($accessAr) < 1) return true;
		
		foreach ($accessAr as $key => $groupId) {
			if (intval($groupId) === $uid) {
				return false;
			}
		}
		return true;
	}

}
