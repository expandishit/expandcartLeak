<?php
namespace Expandcart\System\Library\Config;

class Memory {
    private $data = array();

    public function get($key) {
        return (isset($this->data[$key]) ? $this->data[$key] : null);
    }

    public function set($key, $value) {
        $this->data[$key] = $value;
    }

    public function has($key) {
        return isset($this->data[$key]);
    }
    
    public function delete($key){
        unset($this->data[$key]);
   }

    public function load($filename) {
        $file = DIR_CONFIG . $filename . '.php';

        if (file_exists($file)) {
            $_ = array();

            require($file);

            $this->data = array_merge($this->data, $_);
        } else {
            trigger_error('Error: Could not load config ' . $filename . '!');
            exit();
        }
    }

    // public function delete($key) {

    //      unset($this->data[$key]);

    //      return;
    // }
}