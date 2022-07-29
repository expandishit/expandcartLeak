<?php

class ECDateTime
{
    public function __construct()
    {

    }

    /**
    * @return DateTime or String $current_date_time the current date time in MYSQL's FORMAT
    */

    public function get_current_date_time_in_mysql_format()
    {
        // $current_date_time = date('Y-m-d H:i:s', time());
        $current_date_time = date_create()->format('Y-m-d H:i:s');
        
        return $current_date_time;
    }
}