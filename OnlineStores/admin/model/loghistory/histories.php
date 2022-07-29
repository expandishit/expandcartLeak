<?php

class ModelLoghistoryHistories extends Model
{
    public function addHistory($data)
    {
        $this->db->execute("INSERT INTO " . DB_PREFIX . "log_history SET user_id=?, `type`=?, `old_value`=?, `new_value`=? , `action`=? , `reference_id`=? ", [
            (int)$this->user->getId(),
            $this->db->escape($data['type']),
            $data['old_value'],
            $data['new_value'],
            $this->db->escape($data['action']),
            (int)$data['reference_id']
        ]);

        return true;
    }

    public function getLog($log_id)
    {
        $queryString = [];

        $queryString[] = "SELECT DISTINCT * FROM " . DB_PREFIX . "log_history WHERE log_history_id = '" . (int)$log_id . "'";
        $query = $this->db->query(implode(' ', $queryString));

        return $query->row;
    }


}
