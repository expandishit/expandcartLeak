<?php

class ModelModuleNetworkMarketingLevels extends Model
{
    private $levelsTable = DB_PREFIX . 'nm_levels';

    public $numRows = 0;

    private $errors = [];

    public function getLevels()
    {
        $queryString = [];

        $queryString[] = 'SELECT * FROM ' . $this->levelsTable . ' ORDER BY level_order, level_id';

        $data = $this->db->query(implode(' ', $queryString));

        $this->numRows = $data->num_rows;

        return $data->rows;
    }

    public function truncateLevels()
    {
        $query = 'TRUNCATE TABLE ' . $this->levelsTable;

        $this->db->query($query);
    }

    public function getLevelById($id)
    {
        $data = $this->db->query('SELECT * FROM ' . $this->levelsTable . ' WHERE level_id=' . $this->db->escape($id));

        if ($data->num_rows > 0) {
            return $data->row;
        }

        return false;
    }

    public function insertOrUpdate($levels)
    {
        foreach ($levels as $key => $level) {
            $queryString = $fields = [];
            if ($this->getLevelById($key)) {
                $queryString[] = 'UPDATE ' . $this->levelsTable . ' SET';
                $fields[] = 'fixed=' . $this->db->escape($level['fixed']);
                $fields[] = 'percentage=' . $this->db->escape($level['percentage']);
                $fields[] = 'level_order=' . $this->db->escape($level['order']);
                $queryString[] = implode(',', $fields);
                $queryString[] = 'WHERE level_id=' . $this->db->escape($key);
            } else {
                $queryString[] = 'INSERT INTO ' . $this->levelsTable . ' SET';
                $fields[] = 'fixed=' . $this->db->escape($level['fixed']);
                $fields[] = 'percentage=' . $this->db->escape($level['percentage']);
                $fields[] = 'level_order=' . $this->db->escape($level['order']);
                $queryString[] = implode(',', $fields);
            }


            $this->db->query(implode(' ', $queryString));
        }

    }

    public function validateLevels($levels)
    {
        $validate = function ($input) {
            if (!preg_match('#(?<=^| )\d+(\.\d+)?(?=$| )$#', $input)) {
                return false;
            }

            if (empty($input)) {
                return false;
            }

            return true;
        };

        $validateOrderInput = function ($input) {
            if (!preg_match('#^[0-9]+$#', $input)) {
                return false;
            }

            if (empty($input)) {
                return false;
            }

            return true;
        };

        foreach ($levels as $key => $level) {
            if (!$validate($level['fixed'])) {
                $this->setError(
                    $this->language->get('error_invalid_fixed_field')
                );

                return false;

                break;
            }

            if (!$validate($level['percentage'])) {
                $this->setError(
                    $this->language->get('error_invalid_percentage_field')
                );

                return false;

                break;
            }

            if (!$validateOrderInput($level['order'])) {
                $this->setError(
                    $this->language->get('error_invalid_order_field')
                );

                return false;

                break;
            }
        }

        return true;
    }

    public function setError($error)
    {
        $this->errors[] = $error;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
