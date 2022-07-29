<?php
final class MySQL {
	private $link;
    private $databaseName;
	
	public function __construct($hostname, $username, $password, $database) {
		if (!$this->link = mysql_connect($hostname, $username, $password)) {
      		trigger_error('Error: Could not make a database link using ' . $username . '@' . $hostname);
		}

    	if (!mysql_select_db($database, $this->link)) {
      		trigger_error('Error: Could not connect to database ' . $database);
    	}
		
		mysql_query("SET NAMES 'utf8'", $this->link);
		mysql_query("SET CHARACTER SET utf8", $this->link);
		mysql_query("SET CHARACTER_SET_CONNECTION=utf8", $this->link);
		mysql_query("SET SQL_MODE = ''", $this->link);

        $this->databaseName = $database;
  	}
		
  	public function query($sql) {
        if (!mysql_select_db($this->databaseName, $this->link)) {
            trigger_error('Error: Could not connect to database ' . $this->databaseName);
        }

		if ($this->link) {
			$resource = mysql_query($sql, $this->link);
	
			if ($resource) {
				if (is_resource($resource)) {
					$i = 0;
			
					$data = array();
			
					while ($result = mysql_fetch_assoc($resource)) {
						$data[$i] = $result;
			
						$i++;
					}
					
					mysql_free_result($resource);
					
					$query = new stdClass();
					$query->row = isset($data[0]) ? $data[0] : array();
					$query->rows = $data;
					$query->num_rows = $i;
					
					unset($data);
					
					return $query;	
				} else {
					return true;
				}
			} else {
			    if (!strpos($sql, "ALTER TABLE")) {
                    trigger_error('Error: ' . mysql_error($this->link) . '<br />Error No: ' . mysql_errno($this->link) . '<br />' . $sql);
                    exit();
                } else {
			        return true;
                }
			}
		}
  	}
	
	public function escape($value) {
		if ($this->link) {
			return mysql_real_escape_string($value, $this->link);
		}
	}
	
  	public function countAffected() {
		if ($this->link) {
    		return mysql_affected_rows($this->link);
		}
  	}

  	public function getLastId() {
		if ($this->link) {
    		return mysql_insert_id($this->link);
		}
  	}	

  	public function changeDatabase($database) {
        $this->databaseName = $database;
    }

	public function __destruct() {
		if ($this->link && is_resource($this->link)) {
			mysql_close($this->link);
		}
	}
}
?>