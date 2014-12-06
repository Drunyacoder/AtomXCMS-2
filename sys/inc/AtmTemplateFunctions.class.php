<?php

/**
 * Class AtmTemplateFunctions
 *
 * Uses to define a custom template functions.
 */
class AtmTemplateFunctions
{
    public static function get()
    {
        $functions = array();


        /**
         * Get one or couple entities.
         * If get one entity of the UsersModel, we also get user statistic
         *
         * @param $modelName
         * @param array $id
         * @return array
         * @throws Exception
         */
        $functions['fetch'] = function ($modelName, $id = array())
        {
            $Register = Register::getInstance();
            try {
                $model = $Register['ModManager']->getModelInstance($modelName);

                // get collection of entities
                if (is_array($id) && count($id)) {

                    $id = array_map(function($n){
                        $n = intval($n);
                        if ($n < 1) throw new Exception('Only integer value might send as ID.');
                        return $n;
                    }, $id);
                    $ids = implode(", ", $id);
                    $result = $model->getCollection(array("`id` IN ($ids)"));

                    // get one entity
                } else if (is_numeric($id)) {
                    $id = intval($id);
                    if ($id < 1) throw new Exception('Only integer value might send as ID.');
                    $result = $model->getById($id);

                    if ($result && strtolower($modelName) == 'users') {
                        $stat = $model->getFullUserStatistic($id);
                        $result->setStatistic($stat);
                    }
                }

            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }

            return (!empty($result)) ? $result : array();
        };


        /**
         * Format date.
         * If date_format == 'atm-format', just call AtmGetSimpleDate.
         *
         * @param $date string
         * @param $format string
         * @return string
         */
        $functions['AtmGetDate'] = function ($date, $format = false) {
            return AtmDateTime::getDate($date, $format);
        };


        /**
         * Return date formatted as(example) - "3 seconds before"
         *
         * @param $date string
         * @return string
         */
        $functions['AtmGetSimpleDate'] = function ($date) {
            return AtmDateTime::getSimpleDate($date);
        };


        /**
         * Check access according with ACL rules.
         *
         * @param $params array
         * @return bool
         */
        $functions['checkAccess'] = function ($params = array()) {
            if (isset($params) && is_array($params)) {
                $Register = Register::getInstance();
                return $Register['ACL']->turn($params, false);
            }
            return false;
        };


        $functions['get_url'] = function($url, $notRoot = false, $useLang = true)
        {
            return get_url($url, $notRoot, $useLang);
        };


        /**
         * Return URl to the user avatar
         * or default image if avatar image is not exists.
         *
         * @param null $id_user
         * @param null $email_user
         * @return string
         */
        $functions['getAvatar'] = function($id_user = null, $email_user = null)
        {
            $def = get_url('/template/' . getTemplateName() . '/img/noavatar.png', false, false);

            if (isset($id_user) && $id_user > 0) {
                if (is_file(ROOT . '/sys/avatars/' . $id_user . '.jpg')) {
                    return get_url('/sys/avatars/' . $id_user . '.jpg', false, false);

                } else {
                    $Register = Register::getInstance();
                    $Viewer = $Register['Viewer'];

                    if (Config::read('use_gravatar', 'users') && $Viewer->customFunctionExists('getGravatar')) {
                        if (!isset($email_user)) {
                            $usersModel = $Register['ModManager']->getModelInstance('Users');
                            $user = $usersModel->getById($id_user);
                            if ($user) {
                                $email_user = $user->getEmail();
                            } else {
                                return $def;
                            }
                        }
                        return $Viewer->runCustomFunction('getGravatar', array($email_user));
                    } else {
                        return $def;
                    }
                }
            } else {
                return $def;
            }
        };

        /**
         * Get either a Gravatar URL or complete image tag for a specified email address.
         *
         * @param string $email The email address
         * @param string $s Size in pixels, defaults to 80px [ 1 - 2048 ]
         * @param string $d Default imageset to use [ 404 | mm | identicon | monsterid | wavatar ]
         * @param string $r Maximum rating (inclusive) [ g | pg | r | x ]
         * @return String containing either just a URL or a complete image tag
         */
        $functions['getGravatar'] = function($email, $s = 120, $d = 'mm', $r = 'g')
        {
            $url = 'http://www.gravatar.com/avatar/' . md5(strtolower(trim($email))) . ".png?s=$s&d=$d&r=$r";
            return $url;
        };


        $functions['getOrderLink'] = function($params)
        {
            if (!$params || !is_array($params) || count($params) < 2) return '';
            $order = (!empty($_GET['order'])) ? strtolower(trim($_GET['order'])) : '';
            $new_order = strtolower($params[0]);
            $active = ($order === $new_order);
            $asc = ($active && isset($_GET['asc']));


            $url = $_SERVER['REQUEST_URI'];
            $url = preg_replace('#(order=[^&]*[&]?)|(asc=[^&]*[&]?)#i', '', $url);
            if (substr($url, -1) !== '&' && substr($url, -1) !== '?') {
                $url .= (!strstr($url, '?')) ? '?' : '&';
            }

            return '<a href="' . $url . 'order=' . $new_order . ($asc ? '' : '&asc=1') . '">' . $params[1] . ($active ? ' ' . ($asc ? '↑' : '↓') : '') . '</a>';
        };
		
		
		/**
		 * Checks is an user online or not.
		 *
		 * @param $user_id int
		 */
        $functions['CheckUserOnline'] = function($user_id)
        {
			$users = getOnlineUsers();
			return array_key_exists($user_id, $users);
        };
		
		
		/**
		 * Returns user rank img such as Stars or Progressbar
		 *
		 * @param $rating int
		 */
        $functions['getUserRatingImg'] = function($rating)
        {
			$Register = Register::getInstance();
			$settingsModel = $Register['ModManager']->getModelInstance('UsersSettings');
			$rating_settings = $settingsModel->getCollection(array('type' => 'rating'));
			$rating_settings = (count($rating_settings) > 0) ? $rating_settings[0]->getValues() : ''; 
			
			$rank = getUserRating($rating, $rating_settings);
			return $rank['img'];
        };


        $custom_functions = self::loadCustomTemplateFunctions();
        if (is_array($custom_functions))
            $functions = array_merge($functions, $custom_functions);


        return $functions;
    }


    public static function loadCustomTemplateFunctions()
    {
        $path = ROOT . '/template/' . getTemplateName() . '/customize/AtmCustomTemplateFunctions.class.php';
        if (file_exists($path) && is_readable($path)) {
            include_once $path;

            if (class_exists('AtmCustomTemplateFunctions')) {
                return AtmCustomTemplateFunctions::get();
            }
        }

        return array();
    }
}