<?php
class DB {
	private $driver;
	
	public function __construct($driver, $hostname, $username, $password, $database) {
		if (file_exists(DIR_DATABASE . $driver . '.php')) {
			require_once(DIR_DATABASE . $driver . '.php');
		} else {
			exit('Error: Could not load database file ' . $driver . '!');
		}
				
		$this->driver = new $driver($hostname, $username, $password, $database);
	}

  	public function query($sql) {
		return $this->driver->query($sql);
  	}
    public function execute($sql, $values=[], $types=null) {
        return $this->driver->execute($sql, $values, $types);
    }
	public function getError() {
        return $this->driver->getError();
    }
    public function autocommit($value) {
        return $this->driver->autocommit($value);
    }
    public function rollback() {
        return $this->driver->rollback();
    }
    public function prepare($query) {
        return $this->driver->prepare($query);
    }
	public function escape($value) {
		return $this->driver->escape($value);
	}
	
  	public function countAffected() {
		return $this->driver->countAffected();
  	}

  	public function getLastId() {
		return $this->driver->getLastId();
  	}

    public function changeDatabase($database) {
        $this->driver->changeDatabase($database);
    }

    /**
     * Check tables/columns exist
     * @param $data , array
     * @param $typ  ,'column'/'table'
     *
     * TODO: log errors with missing table/column name
     */
    public function check($data, $typ) {
        $status = 1;
        if(!is_array($data))
            return  $status;

        if($typ == 'column'){
            foreach ($data as $table => $cols){
                foreach ($cols as $col){
                    if(!$this->query("SHOW COLUMNS FROM `".$table."` LIKE '".$col."'")->num_rows){
                        $status = 0;
                        break 2;
                    }
                }
            }
        }else if($typ == 'table'){
            foreach ($data as $itm){
                if(!$this->query("SHOW TABLES LIKE '".$itm."'")->num_rows){
                    $status = 0;
                    break;
                }
            }
        }
        return $status;
    }
}
?>
