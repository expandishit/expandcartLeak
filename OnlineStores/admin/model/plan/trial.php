<?php

class ModelPlanTrial extends Model
{
    /**
     * @var string
     */
    private $plan_trial = 'plan_trial';


    /**
     * Get plan trial
     **
     * @return array|false
     */
    public function getLastTrial()
    {
        $query = [];

        $fields = [
            '`plan_trial`.`plan_id`',
        ];
        $query[] = 'SELECT ' . implode(', ', $fields) . ' FROM ' . $this->plan_trial;
        $query[] = 'WHERE';
        $query[] = 'created_at > (DATE(NOW()) - INTERVAL 7 DAY) and deleted_at IS NULL';
        $query[] = 'order by id desc';
        $query[] = 'LIMIT 1';

        $data = $this->db->query(implode(' ', $query));

        if ($data->num_rows) {
            return $data->row;
        }

        return false;
    }

    /**
     * Get plan trial by plan id
     **
     * @return array|false
     */
    public function getByPlanId($plan_id)
    {
        $query = [];

        $fields = [
            'count(id) as total',
        ];
        $query[] = 'SELECT ' . implode(', ', $fields) . ' FROM ' . $this->plan_trial;
        $query[] = 'WHERE `plan_id`="' . (int) $plan_id . '"';

        $data = $this->db->query(implode(' ', $query));

        if ($data->num_rows) {
            return $data->row;
        }

        return false;
    }

    public function add($plan_id){
        $sql = "INSERT INTO " . DB_PREFIX .$this->plan_trial. " SET plan_id = '" . (int)$plan_id."'";
        $this->db->query(
            $sql
        );
    }

    public function end($plan_id)
    {
        $query = [];
        $query[] = 'UPDATE plan_trial';
        $query[] = 'SET deleted_at=DATE(NOW())';
        $query[] = 'WHERE plan_id="' . $this->db->escape($plan_id) . '"';
        $this->db->query(implode(' ', $query));
    }

    /**
     * Get plan trials
     **
     * @return array|false
     */
    public function getAllTrials()
    {
        $result=array();
        $query = [];

        $fields = [
            '`plan_trial`.`plan_id`',
        ];
        $query[] = 'SELECT ' . implode(', ', $fields) . ' FROM ' . $this->plan_trial;

        $data = $this->db->query(implode(' ', $query));

        if ($data->num_rows) {
            foreach ($data->rows as $row){
                $result[]=$row['plan_id'];
            }
            return $result;
        }

        return false;
    }
}
