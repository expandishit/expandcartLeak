<?php
//namespace Expandcart\System\Library;
class Config {
    private $adaptor;

    /**
     * Constructor
     *
     * @param	string	$adaptor	The type of storage for the cache.
     * @param	int		$expire		Optional parameters
     *
     */
    public function __construct(/*$adaptor = "memory",*/ $expire = 3600) {
        $adaptor= defined('CONFIG_DRIVER') ? CONFIG_DRIVER : "memory";

        if (file_exists(DIR_SYSTEM . 'library/config/' . $adaptor . '.php')) {
            require_once(DIR_SYSTEM . 'library/config/' . $adaptor . '.php');
        } else {
            exit('Error: Could not load config adaptor ' . $adaptor . '!');
        }

        $class = 'Expandcart\System\Library\Config\\' . $adaptor;

        if (class_exists($class)) {
            $this->adaptor = new $class($expire);
        } else {
            error_log('Error: Could not load config adaptor ' . $adaptor . ' cache!');
        }
    }

    public function get($key) {
        return $this->adaptor->get($key);
    }

    public function set($key, $value, $expire = '') {
        return $this->adaptor->set($key, $value, $expire);
    }

    public function has($key) {
        return$this->adaptor->has($key);
    }

    public function delete($key) {
        $this->adaptor->delete($key);
    }

    public function load($filename) {
        $this->adaptor->load($filename);
    }
}
?>