<?php
namespace Expandcart\System\Library\Config;

class Redis
{
    private $expire;
    private $cache;

    public function __construct($expire = 43200)
    {
        $this->expire = $expire;

        $this->cache = new \Redis();
        $this->cache->pconnect(REDIS_HOSTNAME, REDIS_PORT, 0.8);
        if(REDIS_PASSWORD) {
            $this->cache->auth(REDIS_PASSWORD);
        }
    }

    public function get($key)
    {
        $data = $this->cache->get(CONFIG_PREFIX . $key);

        return json_decode($data, true);
    }

    public function set($key, $value, $expire = '')
    {
        if (!$expire) {
            $expire = $this->expire;
        }

        $status = $this->cache->set(CONFIG_PREFIX . $key, json_encode($value));

        if ($status) {
            $this->cache->expire(CONFIG_PREFIX . $key, $expire);
        }

        return $status;
    }

    public function has($key)
    {
        return $this->cache->exists($key);
    }

    public function delete($key)
    {
        $this->cache->del(CONFIG_PREFIX . $key);
    }

    public function load($filename)
    {
        $file = DIR_CONFIG . $filename . '.php';

        if (file_exists($file)) {
            $_ = array();

            require($file);
            foreach ($_ as $k => $v) {
                $this->set($k, $v);
            }
            //$this->data = array_merge($this->data, $_);
        } else {
            trigger_error('Error: Could not load config ' . $filename . '!');
            exit();
        }
    }
}