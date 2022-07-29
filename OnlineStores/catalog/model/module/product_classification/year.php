<?php
class ModelModuleProductClassificationYear extends Model {
    // define table name
    private $table = "pc_year";

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