<?php
namespace LMS\Core;

class SessionManager extends \SessionHandler
{
    private $name;
    private $lifetime;
    private $path;
    private $ssl;
    private $domain;
    private $httponly;
    
    public function __construct() 
    {
        $this->name = SESSION_NAME;
        $this->lifetime = SESSION_LIFE_TIME;
        $this->path = ini_get('session.cookie_path');
        $this->ssl = isset($_SERVER['HTTPS']) ? true : false;
        $this->httponly = true;
        $this->domain = $_SERVER['SERVER_NAME'];
        
        $this->prepare();
    }
    
    private function prepare()
    {
        // Make sure session uses cookies and only cookies
        ini_set('session.use_cookies', 1);
        ini_set('session.use_only_cookies', 1);
        
        // Set the session name 
        session_name($this->name);
        
        // Set the save handler to files
        ini_set('session.save_handler', 'files');
        
        // Set the session save handler to this instance
        session_set_save_handler($this, true);
        
        // Set the new session save path to the one defined in our configuration
        session_save_path(SESSION_SAVE_PATH);
        
        // Set the session cookie parameter
        session_set_cookie_params(
                $this->lifetime, $this->path, 
                $this->domain, $this->ssl, 
                $this->httponly
        );
    }
    
    public function start()
    {
        if (session_id() === '') {
            if (session_start()) {
                if(version_compare('5.5.0', PHP_VERSION) === 1) {
                    return session_regenerate_id(true);
                }
                return self::create_sid();
            }
        }
        return false;
    }
    
    public function read($id)
    {
        return mcrypt_decrypt(MCRYPT_3DES, APP_SAULT, parent::read($id), MCRYPT_MODE_ECB);
    }

    public function write($id, $data)
    {
        return parent::write($id, mcrypt_encrypt(MCRYPT_3DES, APP_SAULT, $data, MCRYPT_MODE_ECB));
    }
    
    public function __get($key) {
        if(isset($_SESSION[$key])) {
            $data = @unserialize($_SESSION[$key]);
            if($data === false) {
                return $_SESSION[$key];
            } else {
                return $data;
            }
        } else {
            trigger_error('No session key ' . $key . ' exists', E_USER_NOTICE);
        }
    }
    
    public function __set($key, $value) {
        if(is_object($value)) {
            $_SESSION[$key] = serialize($value);
        } else {
            $_SESSION[$key] = $value;
        }
    }
    
    public function kill() {
        if (session_id() === '') {
            return false;
        }

        $_SESSION = [];

        setcookie(
            $this->name, '', time() - 42000,
            $this->path, $this->domain,
            $this->ssl, $this->httponly
        );

        return session_destroy();
    }
}