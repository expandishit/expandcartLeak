<?php
//namespace DB;

use \ExpandCart\Foundation\Exceptions\DatabaseException;

final class MySQLix {
    private $connection;
    private $connected;
    public function __construct($hostname, $username, $password, $database, $port = '3306') {
        try {
            mysqli_report(MYSQLI_REPORT_STRICT);
            $this->connection = @new \mysqli($hostname, $username, $password, $database, $port);
        } catch (\mysqli_sql_exception $e) {
            if(DEV_MODE) {
                throw new DatabaseException(
                    'Error: Could not make a database link using ' . $username . '@' . $hostname . '!'
                );
            } else {
                throw new DatabaseException('Error: Could not make a database link!');
            }
        }
        // $this->connection->report_mode = MYSQLI_REPORT_ALL;
        $this->connection->set_charset("utf8");
        $this->connection->query("SET SQL_MODE = ''");
    }
    public function query($sql) {
        if(str_contains($sql, '-- ')) {
            throw new DatabaseException("Error: Could not execute this query!");
        }
        $query = $this->connection->query($sql);
        if (!$this->connection->errno) {
            if ($query instanceof \mysqli_result) {
                $data = array();
                while ($row = $query->fetch_assoc()) {
                    $data[] = $row;
                }
                $result = new \stdClass();
                $result->num_rows = $query->num_rows;
                $result->row = isset($data[0]) ? $data[0] : array();
                $result->rows = $data;
                $query->close();
                return $result;
            } else {
                return true;
            }
        } else {
            throw new DatabaseException(
                'Error: ' . $this->connection->error  . '<br />Error No: ' . $this->connection->errno . '<br />' . $sql
            );
        }
    }

    public function execute($query, $values = [], $types=null)
    {
        if(!$types) {
            $types = $this->getVariablesTypes($values);
        }

        $stmt = $this->connection->prepare($query);
        if(count($values)) {
            $stmt->bind_param(implode("", $types), ...$values);
        }
        $stmt->execute();

        if (!$this->connection->errno) {
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
        return $this->connection->error;
    }

    public function rollback() {
        return $this->connection->rollback();
    }

    public function autocommit($value)
    {
        return $this->connection->autocommit($value);
    }

    public function prepare($query)
    {
        return $this->connection->prepare($query);
    }

    public function escape($value) {
        return $this->connection->real_escape_string($value);
    }

    public function countAffected() {
        return $this->connection->affected_rows;
    }
    public function getLastId() {
        return $this->connection->insert_id;
    }

    public function isConnected() {
        return $this->connection->ping();
    }

    public function __destruct() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
}
