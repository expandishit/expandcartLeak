<?php

class MsLoader {
	public $appVer = "6.0";
	public $dbVer = "2.1.0.0";
	
	public function __construct($registry) {
		$this->registry = $registry;
		spl_autoload_register(array('MsLoader', '_autoloadLibrary'));
		spl_autoload_register(array('MsLoader', '_autoloadController'));
	}

	public function __get($class) {
		if (!isset($this->$class)){
			$this->$class = new $class($this->registry);
		}

		return $this->$class;
	}

	private static function _autoloadLibrary($class) {
	 	$file = DIR_SYSTEM . 'library/' . strtolower($class) . '.php';
        //var_dump($file);
		if (file_exists($file)) {
            //global $vqmod;
			require_once($file);
		}
	}

	private static function _autoloadController($class) {
		preg_match_all('/((?:^|[A-Z])[a-z]+)/',$class,$matches);
		
		if (isset($matches[0][1]) && isset($matches[0][2])) {
			$file = DIR_APPLICATION . 'controller/' . strtolower($matches[0][1]) . '/' . strtolower($matches[0][2]) . '.php';
			if (file_exists($file)) {
                //global $vqmod;
				require_once($file);
			}
		}
	}

	/**
	 * Checks if the multi seller applicatoin is installed.
     *
     * @return bool
     */
    public function isInstalled()
    {
        if (\Extension::isInstalled('multiseller')) {
            return true;
        }

        return false;
    }
}
