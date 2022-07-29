<?php
class ModelLocalisationStatuses extends Model {
    public function dtHandler($objName, $start = 0, $length = 10, $search = null, $orderColumn = "name", $orderType = "ASC") {
        $language_id = $this->config->get('config_language_id') ?: 1;
        $query = "SELECT * FROM " . DB_PREFIX . "$objName WHERE language_id=$language_id";

        $total = $this->db->query($query)->num_rows;
        $where = "";
        if (!empty($search)) {
            $where .= "(name like '%" . $this->db->escape($search) . "%') ";
            $query .= " AND " . $where;
        }

        $totalFiltered = $this->db->query($query)->num_rows;
        $query .= " ORDER by {$orderColumn} {$orderType}";

        if ($length != -1) {
            $query .= " LIMIT " . $start . ", " . $length;
        }

        $data = array(
            'data' => $this->db->query($query)->rows,
            'total' => $total,
            'totalFiltered' => $totalFiltered
        );

        return $data;
    }

//    public function insert($data, $objName) {
//        foreach ($data as $langId => $name) {
//            $this->db->query("INSERT INTO " . DB_PREFIX . "$objName SET language_id = '" . (int) $langId . "', name = '" . $this->db->escape($name) . "'");
//        }
//        return true;
//    }

    public function insert($data, $objName) {

        $bk_color = '';
        if($objName == 'order_status' && $data['bk_color']){
            $bk_color = ", bk_color = '" . $this->db->escape($data['bk_color']) . "'";
            unset($data['bk_color']);
        }

       foreach ($data as $langId => $name) {
           if (isset($status_id)) {
               $this->db->query("INSERT INTO " . DB_PREFIX . "$objName SET " . $objName . "_id = '" . (int) $status_id . "', language_id = '" . (int) $langId . "', name = '" . $this->db->escape($name) . "'".$bk_color);
           } else {
               $this->db->query("INSERT INTO " . DB_PREFIX . "$objName SET language_id = '" . (int) $langId . "', name = '" . $this->db->escape($name) . "'".$bk_color);

               $status_id = $this->db->getLastId();
           }
        }
        return $status_id;
    }

    public function delete($id, $objName) {
        $this->db->query("DELETE FROM " . DB_PREFIX . "$objName WHERE " . $objName . "_id = '" . (int)$id . "'");

        $this->cache->delete($objName);
    }

    public function get($id, $objName) {
        $query = "SELECT * FROM " . DB_PREFIX . "$objName WHERE " . $objName . "_id = '" . (int) $id . "'";
        return $this->db->query($query)->rows;
    }

    public function edit($id, $data, $objName) {
        $this->db->query("DELETE FROM " . DB_PREFIX . "$objName WHERE " . $objName . "_id = '" . (int)$id . "'");

        $bk_color = '';
        if($objName == 'order_status' && $data['bk_color']){
            $bk_color = ", bk_color = '" . $this->db->escape($data['bk_color']) . "'";
            unset($data['bk_color']);
        }

        foreach ($data as $language_id => $value) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "$objName SET " . $objName . "_id = '" . (int)$id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value) . "'".$bk_color);
        }

        $this->cache->delete($objName);
        return $this->db->getLastId();
    }
}
