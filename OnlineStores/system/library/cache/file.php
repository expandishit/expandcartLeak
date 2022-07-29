<?php
namespace Expandcart\System\Library\Cache;
class File {
    private $expire = 3600;

    public function get($key) {
        $files = glob(DIR_CACHE . 'cache.' . preg_replace('/[^A-Z0-9\._-]/i', '', $key) . '.*');

        if ($files) {
            $cache = file_get_contents($files[0]);

            $data = unserialize($cache);

            foreach ($files as $file) {
                $time = substr(strrchr($file, '.'), 1);

                if ($time < time()) {
                    if (file_exists($file)) {
                        unlink($file);
                    }
                }
            }

            return $data;
        }
    }

    public function set($key, $value, $expire = false) {
        $this->delete($key);

        if(!$expire)
            $expire = $this->expire;

        $file = DIR_CACHE . 'cache.' . preg_replace('/[^A-Z0-9\._-]/i', '', $key) . '.' . (time() + $expire);

        if (is_writable(DIR_CACHE)) {

            $handle = fopen($file, 'w');

            fwrite($handle, serialize($value));

            fclose($handle);

        }
    }

    public function delete($key) {
        $files = glob(DIR_CACHE . 'cache.' . preg_replace('/[^A-Z0-9\._-]/i', '', $key) . '.*');

        if ($files) {
            foreach ($files as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
        }
    }
}
