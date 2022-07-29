<?php
//namespace Expandcart\System\Library;
class Cache {
    private $adaptor;

    /**
     * Constructor
     *
     * @param	string	$adaptor	The type of storage for the cache.
     * @param	int		$expire		Optional parameters
     *
     */
    public function __construct(/*$adaptor = "file",*/ $expire = 3600) {
        $adaptor =  defined('CACHE_DRIVER') ? CACHE_DRIVER : "file";
        if (file_exists(DIR_SYSTEM . 'library/cache/' . $adaptor . '.php')) {
            require_once(DIR_SYSTEM . 'library/cache/' . $adaptor . '.php');
        } else {
            exit('Error: Could not load cache adaptor ' . $adaptor . '!');
        }

        $class = 'Expandcart\System\Library\Cache\\' . $adaptor;

        if (class_exists($class)) {
            $this->adaptor = new $class($expire);
        } else {
            error_log('Error: Could not load cache adaptor ' . $adaptor . ' cache!');
        }
    }

    /**
     * Gets a cache by key name.
     *
     * @param	string $key	The cache key name
     *
     * @return	string
     */
    public function get(string $key) {
        return $this->adaptor->get($key);
    }

    /**
     * Sets a cache by key value.
     *
     * @param	string	$key	The cache key
     * @param	string	$value	The cache value
     *
     * @return	string
     */
    public function set(string $key, $value, $expire = '') {
        return $this->adaptor->set($key, $value, $expire);
    }

    /**
     * Deletes a cache by key name.
     *
     * @param	string	$key	The cache key
     */
    public function delete(string $key) {
        return $this->adaptor->delete($key);
    }
}