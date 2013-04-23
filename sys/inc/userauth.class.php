<?php


/**
 *
 */
class UserAuth
{



    /**
     * @return void
     */
    public function setTimeVisit()
    {
        $Register = Register::getInstance();
        $FpsDB = $Register['DB'];
        
        $query = "UPDATE `" . $FpsDB->getFullTableName('users') . "`
                SET last_visit=NOW()
                WHERE id=".$_SESSION['user']['id'];
        $FpsDB->query( $query );
    }



    /**
     * @return bool|void
     */
    public function autoLogin()
    {
        $Register = Register::getInstance();
        $FpsDB = $Register['DB'];

        
        // Если не установлены cookie, содержащие логин и пароль
        if ( !isset( $_COOKIE['userid'] ) or !isset( $_COOKIE['password'] ) ) {
            $path ='/';
            if ( isset( $_COOKIE['userid'] ) ) setcookie( 'userid', '', time() - 1, $path );
            if ( isset( $_COOKIE['password'] ) ) setcookie( 'password', '', time() - 1, $path );
            if ( isset( $_COOKIE['autologin'] ) ) setcookie( 'autologin', '', time() - 1, $path );
            return false;
        }
        // Проверяем переменные cookie на недопустимые символы
        $user_id = intval($_COOKIE['userid']);
        if ($user_id < 1) return false;
        // Т.к. пароль зашифрован с помощью md5, то он представляет собой
        // 32-значное шестнадцатеричное число
        $password = substr( $_COOKIE['password'], 0, 32 );
        $password = preg_replace( "#[^0-9a-f]#i", '', $password );


        // Выполняем запрос на получение данных пользователя из БД
        $query = "SELECT *, UNIX_TIMESTAMP(last_visit) as unix_last_visit
                FROM `" . $FpsDB->getFullTableName('users') . "`
                WHERE `id`='".mysql_real_escape_string( $user_id )."'
                AND `passw`='".mysql_real_escape_string( $password )."'
                LIMIT 1";
        $res = $FpsDB->query( $query );


        // Если пользователь с таким логином и паролем не найден -
        // значит данные неверные и надо их удалить
        if ( count( $res ) < 1 ) {
            //$tmppos = strrpos( $_SERVER['PHP_SELF'], '/' ) + 1;
            //$path = substr( $_SERVER['PHP_SELF'], 0, $tmppos );
            $path = '/';
            setcookie( 'autologin', '', time() - 1, $path );
            setcookie( 'userid', '', time() - 1, $path );
            setcookie( 'password', '', time() - 1, $path );
            return false;
        }


        $user = $res[0];
        if ( !empty( $user['activation'] ) ) {
            //$tmppos = strrpos( $_SERVER['PHP_SELF'], '/' ) + 1;
            //$path = substr( $_SERVER['PHP_SELF'], 0, $tmppos );
            $path = '/';
            setcookie( 'autologin', '', time() - 1, $path );
            setcookie( 'userid', '', time() - 1, $path );
            setcookie( 'password', '', time() - 1, $path );
            return showInfoMessage(__('Your account not activated'), '/' );
        }

        // Если пользователь заблокирован
        if ( $user['locked'] ) {
            //$tmppos = strrpos( $_SERVER['PHP_SELF'], '/' ) + 1;
            //$path = substr( $_SERVER['PHP_SELF'], 0, $tmppos );
            $path = '/';
            setcookie( 'autologin', '', time() - 1, $path );
            setcookie( 'userid', '', time() - 1, $path );
            setcookie( 'password', '', time() - 1, $path );
            redirect('/users/baned/');
        }

        $_SESSION['user'] = $user;

        // Функция getNewThemes() помещает в массив $_SESSION['newThemes'] ID тем,
        // в которых были новые сообщения со времени последнего посещения пользователя
        $this->getNewThemes();

        return true;
    }



    public function getNewThemes()
    {
        $Register = Register::getInstance();
        $FpsDB = $Register['DB'];
        
	    $query = "SELECT a.id, MAX(UNIX_TIMESTAMP(b.time)) AS unix_last_post
			FROM `" . $FpsDB->getFullTableName('themes') . "` a
			INNER JOIN `" . $FpsDB->getFullTableName('posts') . "` b
			ON a.id=b.id_theme
			GROUP BY a.id
			HAVING unix_last_post>" . $_SESSION['user']['unix_last_visit'];

	    $res = $FpsDB->query($query);

	    if ($res) {
            foreach ($res as $key => $row) {
                $_SESSION['newThemes'][$row['id']] = $row['unix_last_post'];
            }
		}
	}



    public static function countNewMessages() {
        global $Register;
        $FpsDB = $Register['DB'];
        
        $res = $FpsDB->query("SELECT COUNT(*) as cnt
                FROM `" . $FpsDB->getFullTableName('messages') . "`
                WHERE to_user=".(int)$_SESSION['user']['id']."
                AND viewed=0 AND id_rmv<>".(int)$_SESSION['user']['id']);
        if ( $res ) {
            return $res[0]['cnt'];
        } else {
            return 0;
        }
    }
}
