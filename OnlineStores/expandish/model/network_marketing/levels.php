<?php

class ModelNetworkMarketingLevels extends Model
{
    private $levelsTable = DB_PREFIX . 'nm_levels';

    public function getLevelById($levelId)
    {
        $queryString = [];
        $queryString[] = 'SELECT * FROM ' . $this->levelsTable;
        $queryString[] = 'WHERE level_id=' . $levelId;

        $data = $this->db->query(implode(' ', $queryString));

        if ($data->num_rows) {
            return $data->row;
        } else {
            return false;
        }
    }
}
