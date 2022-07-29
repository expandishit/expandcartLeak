<?php

use \ExpandCart\Foundation\Exceptions\DatabaseException;

final class MySQLiz {
	private $mysqli_handler;
    private $databaseName;

	public function __construct($hostname, $username, $password, $database) {
		$this->mysqli_handler = new mysqli($hostname, $username, $password, $database);

		if ($this->mysqli_handler->connect_error) {
            throw new DatabaseException(
                'Error: Could not make a database link (' . $this->mysqli_handler->connect_errno . ') ' . $this->mysqli_handler->connect_error
            );
      		trigger_error('Error: Could not make a database link (' . $this->mysqli_handler->connect_errno . ') ' . $this->mysqli_handler->connect_error);
		}

		// $this->mysqli_handler->report_mode = MYSQLI_REPORT_ALL;
		$this->mysqli_handler->query("SET NAMES 'utf8'");
		$this->mysqli_handler->query("SET CHARACTER SET utf8");
		$this->mysqli_handler->query("SET CHARACTER_SET_CONNECTION=utf8");
		$this->mysqli_handler->query("SET SQL_MODE = ''");
        $this->databaseName = $database;
    }

    public function query($sql) {
		if(str_contains($sql, '-- ')) {
			throw new DatabaseException("Error: Could not execute this query!");
		}
        if (!$this->mysqli_handler->select_db($this->databaseName)) {
            throw new DatabaseException('Error: Could not connect to database ' . $this->databaseName);
            trigger_error('Error: Could not connect to database ' . $this->databaseName);
        }

		$result = $this->mysqli_handler->query($sql, MYSQLI_STORE_RESULT);
		if ($result !== FALSE) {
			if (is_object($result)) {
				$i = 0;

				$data = array();

				while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
					$data[$i] = $row;

					$i++;
				}

				$result->close();

				$query = new stdClass();
				$query->row = isset($data[0]) ? $data[0] : array();
				$query->rows = $data;
				$query->num_rows = count($data);

				unset($data);




				return $query;


			}
			else {
				return true;
			}
		}
		else {
			if ( DEV_MODE )
			{
				$this->logError($this->mysqli_handler->error, $sql);
			}
            throw new DatabaseException(
                'Error: ' . $this->mysqli_handler->error . '<br />Error No: ' . $this->mysqli_handler->errno . '<br />' . $sql
            );
			trigger_error('Error: ' . $this->mysqli_handler->error . '<br />Error No: ' . $this->mysqli_handler->errno . '<br />' . $sql);
			exit();
		}
  }

	public function execute($query, $values = [], $types=null)
	{
		if(!$types) {
			$types = $this->getVariablesTypes($values);
		}

		$stmt = $this->mysqli_handler->prepare($query);
		if(count($values)) {
			$stmt->bind_param(implode("", $types), ...$values);
		}
		$stmt->execute();

		if (!$this->mysqli_handler->errno) {
			$res = $stmt->get_result();
			if ($res instanceof \mysqli_result) {
				$data = array();

				while ($row = $res->fetch_assoc()) {
					$data[] = $row;
				}
				$result = new \stdClass();
				$result->num_rows = $res->num_rows;
				$result->row = isset($data[0]) ? $data[0] : array();
				$result->rows = $data;
				$stmt->close();
				return $result;
			} else {
				$stmt->close();
				return true;
			}
		} else {
			throw new DatabaseException(
				'Error: ' . $this->connection->error  . '<br />Error No: ' . $this->connection->errno . '<br />' . $sql
			);
		}

	}

	protected function getVariablesTypes($vars)
	{
		$types = [];
		foreach ($vars as $var) {
			switch (gettype($var)) {
				case "integer":
					$types[] = "i";
					break;
				case "double":
					$types[] = "d";
					break;
//                case "string":
//                    $types[] = "s";
//                    break;
				default:
					$types[] = "s";
					break;
			}
		}
		return $types;
	}

	public function getError(){
  		return $this->mysqli_handler->error;
  	}

    public function rollback() {
        return $this->mysqli_handler->rollback();
    }

	public function autocommit($value)
	{
  		return $this->mysqli_handler->autocommit($value);
  	}

	public function prepare($query)
	{
		return $this->mysqli_handler->prepare($query);
	}
	public function escape($value) {
		return $this->mysqli_handler->real_escape_string($value);
	}

	public function countAffected() {
		return $this->mysqli_handler->affected_rows;
	}

	public function getLastId() {
		return $this->mysqli_handler->insert_id;
	}

    public function changeDatabase($database) {
        $this->databaseName = $database;
    }

	public function __destruct() {
		$this->mysqli_handler->close();
	}


	protected function logError( $errorString, $query )
	{
		$separator = DIRECTORY_SEPARATOR;
		//$logsPath = __DIR__ . $separator . '..' . $separator . '..' . $separator . "logs";
		$logsPath = DIR_LOGS;

		if ( ! is_dir($logsPath) )
		{
			try{
				mkdir($logsPath);
			} catch (\Exception $e) {
				return false;
			}
		}

		$logFile = $logsPath . $separator . "mysqli_error.txt";

		$date = date("Y-M-d (D) / H:i:s");

		$handler = fopen($logFile, 'a');

		$logString = "{$date} => \n Error : {$errorString} \n Query : {$query} \n ******************* \n \n";

		fwrite($handler, $logString);
		fclose($handler);

		return True;
	}
}
?>
