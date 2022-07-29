<?php
class ModelModuleProductClassificationYear extends Model {
    // define table name
    private $table = "pc_year";

    public function addYear($data) {
       $dateFrom =  (int)$data['year_from'];
       $dateTo =  (int)$data['year_to'];

       if($dateFrom > $dateTo){
            $from = $dateTo;
            $to = $dateFrom;
        }else{
           $from = $dateFrom;
           $to = $dateTo;
       }

        for ($i = $from; $i <= $to; $i++){
            // check if year is not exists before
            if(!$this->checkIfYearExists($i)){
                $queryString = $fields = [];
                $queryString[] = "INSERT INTO " . DB_PREFIX.$this->table . " SET";
                $fields[] = "status = '" . (int)$data['status'] . "'";
                $fields[] = "name = '" . (int)$i. "'";
                $queryString[] = implode(',', $fields);
                $this->db->query(implode(' ', $queryString));
            }
        }

        return true;
    }


    public function editYear($year_id, array $data) {
        $queryString = $fields = [];
        $queryString[] = "UPDATE " . DB_PREFIX.$this->table . " SET";

        $fields[] = "status = '" . $data['status'] . "'";
        $fields[] = "name = '" . $data['name'] . "'";

        $queryString[] = implode(',', $fields);
        $queryString[] = 'WHERE pc_year_id="' . $year_id . '"';

        $this->db->query(implode(' ', $queryString));

        return True;

    }

    public function deleteYear($year_id) {
        $this->db->query("DELETE FROM " . DB_PREFIX.$this->table . " WHERE 	pc_year_id = '" . (int)$year_id . "'");

        return true;
    }

    public function getYear($year_id) {

        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX.$this->table  . "  WHERE pc_year_id = '" . (int)$year_id . "' ");

        return $query->row;
    }

    public function checkIfYearExists($year,$year_id = NULL) {
        $queryString = "SELECT  * FROM " . DB_PREFIX.$this->table  . "  WHERE name = '" . (int)$year . "' ";

        if($year_id != NULL){
            $queryString .= " AND `pc_year_id` != ".$year_id;
        }

        $query = $this->db->query($queryString);

        return (count($query->row) > 0) ? TRUE : FALSE;
    }


    public function getYears($data = array()) {
        $sql = "SELECT *  FROM " . DB_PREFIX .$this->table . " ";

        if (!empty($data['filter_name'])) {
            $sql .= " AND name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
        }

        $sort_data = array(
            'name',
            'pc_year_id'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY name";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getTotalYears() {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM ".DB_PREFIX.$this->table. " ");
        return $query->row['total'];
    }


}
?>