<?php

namespace App;

use App\Application;
use App\Helper;

class Identity {

    const USER_SESSIONS_TABLE = 'sessions';

    const USER_ID = 'user_id';
    const USER_SESS_CODE = 'user_sess_code';
    const USER_HTTP_AGENT = 'user_http_agent';
    const USER_STATUS = 'user_status';

    const STATUS_ACTIVE = '0';

    private $user_id = 0;
    private $user_sess_code = '';

    public function __construct() {
        session_start();
        $this->user_sess_code = Helper::generateCode(15);
        return $this->check();
    }

    public function check() {
        $user_id = $this->checkSession();
        if ($user_id) {
            return $this->user_id = $user_id;
        }
        return false;
    }

    public function checkin($user_id) {
        $this->setSession($user_id);
        $this->setCookie();        
    }

    public function checkout() {
        $this->user_id = 0;
        $this->killSession();
        $this->killCookie();
    }

    public function signin($user_id) {
        $this->addSession($user_id);
        $this->check();
    }

    private function killSession() {
        unset($_SESSION[self::USER_ID]);
        unset($_SESSION[self::USER_SESS_CODE]);
    }

    private function killCookie() {
        setcookie(self::USER_ID, '', time()-3600);
        setcookie(self::USER_SESS_CODE, '', time()-3600);
    }

    private function addSession($user_id) { 
        if (!empty($user_id)) {
            $user_id = (int)$user_id;
            $user_sess_code = $this->user_sess_code;
            
            $user_http_agent = $_SERVER['HTTP_USER_AGENT'];

            $user = Application::DB()->selectOne([
                        'table'  => self::USER_SESSIONS_TABLE,
                        'fields' => [
                            self::USER_ID,
                        ],
                        'where'  => [
                            self::USER_ID         => $user_id,
                        ],
                    ]);

    				if (sizeof($user) == 0) {
                $user = Application::DB()->insert([
                        'table'  => self::USER_SESSIONS_TABLE,
                        'fields' => [
                            self::USER_ID         => $user_id,
                            self::USER_SESS_CODE  => $user_sess_code,
                            self::USER_HTTP_AGENT => $user_http_agent,
                            self::USER_STATUS     => self::STATUS_ACTIVE,
                        ],
                    ]);
                if ($user > 0) {                
                    $this->setSession($user_id);
                    $this->setCookie();
                    return true;
                }
            }
        }
        return false; 
    }

    private function checkSession() {
        $session = $this->getSession();
    		if (isset($session[self::USER_ID]) && isset($session[self::USER_SESS_CODE])) {
		        return $session[self::USER_ID];
        }
        return false;
    }

    private function getSession() {
        $session_user_id = !empty($_SESSION[self::USER_ID])? $_SESSION[self::USER_ID]: NULL;
        $session_sess_code = !empty($_SESSION[self::USER_SESS_CODE])? $_SESSION[self::USER_SESS_CODE]: NULL;
        if ($session_user_id && $session_sess_code) {
            return [
                self::USER_ID => $session_user_id,
                self::USER_SESS_CODE => $session_sess_code,
            ];
        }                
        return false;
    }

    private function setSession($user_id = NULL) {
        $cookie = $this->getCookie();

        if (!empty($user_id)) {
            $user_id = (int)$user_id;
            $user_sess_code = $this->user_sess_code;
            
            $user_http_agent = $_SERVER['HTTP_USER_AGENT'];

            $user = Application::DB()->update([
                        'table'  => self::USER_SESSIONS_TABLE,
                        'fields' => [
                            self::USER_SESS_CODE  => $user_sess_code,
                            self::USER_HTTP_AGENT => $user_http_agent,
                        ],
                        'where'  => [
                            self::USER_ID         => $user_id,
                            self::USER_STATUS     => self::STATUS_ACTIVE,
                        ],
                    ]);
            $_SESSION[self::USER_ID]        = $user_id;
            $_SESSION[self::USER_SESS_CODE] = $user_sess_code;
            $this->setCookie();
            return true;

        } else if (isset($cookie[self::USER_ID]) and isset($cookie[self::USER_SESS_CODE])) {

            $cookie_user_id = Helper::screen($cookie[self::USER_ID]);
            $cookie_sess_code= Helper::screen($cookie[self::USER_SESS_CODE]);

            $user_http_agent = $_SERVER['HTTP_USER_AGENT'];

            $user = Application::DB()->selectOne([
                        'table'  => self::USER_SESSIONS_TABLE,
                        'fields' => [
                            self::USER_SESS_CODE,
                            self::USER_HTTP_AGENT,
                        ],
                        'where'  => [
                            self::USER_ID         => $cookie_user_id,
                            self::USER_SESS_CODE  => $cookie_sess_code,
                            self::USER_HTTP_AGENT => $user_http_agent,
                            self::USER_STATUS     => self::STATUS_ACTIVE,
                        ],
                    ]);

    				if (sizeof($user) > 0) {
                $_SESSION[self::USER_ID]        = $cookie_user_id;
                $_SESSION[self::USER_SESS_CODE] = $user[self::USER_SESS_CODE];
                $this->setCookie();
                return true;
            }
				}
        return false;
    }

    private function getCookie() {
        $cookie_user_id = !empty($_COOKIE[self::USER_ID])? Helper::FilterInput($_COOKIE[self::USER_ID]): NULL;
        $cookie_user_sess_code = !empty($_COOKIE[self::USER_SESS_CODE])? Helper::FilterInput($_COOKIE[self::USER_SESS_CODE]): NULL;

        if ($cookie_user_id && $cookie_user_sess_code) {
            return [
                self::USER_ID        => $cookie_user_id,
                self::USER_SESS_CODE => $cookie_user_sess_code,
            ];
        }                
        return false;

    }

    private function setCookie() {
        $session = $this->getSession();

        setcookie(self::USER_ID, $session[self::USER_ID], $this->getCookieLifetime());
        setcookie(self::USER_SESS_CODE, $session[self::USER_SESS_CODE], $this->getCookieLifetime());
    }

    private function getCookieLifetime() {
        return time()+3600*24*14;
    }
}