<?php

class ModelLoghistoryHistories extends Model
{
    public function addHistory($data)
    {
        $this->db->query(
            "INSERT INTO " . DB_PREFIX . "log_history SET
            user_id = '" . $this->customer->getId() . "',
            type = '" . $this->db->escape($data['type']) . "',
            old_value = '" . $data['old_value'] . "',
            new_value = '" . $data['new_value'] . "',
            action = '" . $data['action'] . "',
            reference_id = '" . (int)$data['reference_id'] . "',
            date_added = NOW()"
        );

        return true;
    }


}
