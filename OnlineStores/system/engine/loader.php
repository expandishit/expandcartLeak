<?php

use \ExpandCart\Foundation\Exceptions\FileException;

final class Loader {
	protected $registry;
	
	public function __construct($registry) {
		$this->registry = $registry;

        //global $vqmod;
        //require_once($vqmod->modCheck(DIR_SYSTEM . 'library/msloader.php'));
        require_once(DIR_SYSTEM . 'library/msloader.php');
        $registry->set('MsLoader', new MsLoader($registry));
	}
	
	public function __get($key) {
		return $this->registry->get($key);
	}

	public function __set($key, $value) {
		$this->registry->set($key, $value);
	}
		
	public function library($library) {
		$file = DIR_SYSTEM . 'library/' . $library . '.php';
		
		if (file_exists($file)) {
			include_once($file);
		} else {
			throw new FileException('Error: Could not load library ' . $library . '!');
			trigger_error('Error: Could not load library ' . $library . '!');
			exit();					
		}
	}
	
	public function helper($helper) {
		$file = DIR_SYSTEM . 'helper/' . $helper . '.php';
		
		if (file_exists($file)) {
			include_once($file);
		} else {
			throw new FileException('Error: Could not load helper ' . $helper . '!');
			trigger_error('Error: Could not load helper ' . $helper . '!');
			exit();					
		}
	}
		
	public function model($model, $options = false, $baseDir = null)
	{
		$dir = $baseDir ? $baseDir : DIR_APPLICATION;
		$file  = $dir . 'model/' . $model . '.php';
		$class = 'Model' . preg_replace('/[^a-zA-Z0-9]/', '', $model);
		
		if (file_exists($file)) { 
			include_once($file);

            $className = 'model_' . str_replace('/', '_', $model);

			$this->registry->set($className, new $class($this->registry));

			if (isset($options['return']) && $options['return'] === true) {
                return $this->registry->get($className);
            }

		} else {
			throw new FileException('Error: Could not load model ' . $model . '!');
			trigger_error('Error: Could not load model ' . $model . '!');
			if ( isset($options['return_false']) && $options['return_false'] == true )
			{
				return false;
			}
			exit();					
		}
	}
	 
	public function database($driver, $hostname, $username, $password, $database) {
		$file  = DIR_SYSTEM . 'database/' . $driver . '.php';
		$class = 'Database' . preg_replace('/[^a-zA-Z0-9]/', '', $driver);
		
		if (file_exists($file)) {
			include_once($file);
			
			$this->registry->set(str_replace('/', '_', $driver), new $class($hostname, $username, $password, $database));
		} else {
			throw new FileException('Error: Could not load database ' . $driver . '!');
			trigger_error('Error: Could not load database ' . $driver . '!');
			exit();				
		}
	}
	
	public function config($config) {
		$this->config->load($config);
	}
	
	public function language($language) {
		return $this->language->load($language);
	}		
} 
?>
