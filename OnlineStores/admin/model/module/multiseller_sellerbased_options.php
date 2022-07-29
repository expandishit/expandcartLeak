<?php

class  ModelModuleMultisellerSellerbasedOptions extends Model
{
    private $options_table = DB_PREFIX . 'ms_sellerbased_options';
    private $description_table = DB_PREFIX . 'ms_sellerbased_options_description';

    public $errors = [];

    public function getList()
    {
        $queryString = 'SELECT * FROM ' . $this->description_table . ' as d';
        $queryString .= ' INNER JOIN ' . $this->options_table . ' as o';
        $queryString .= ' ON d.sellerbased_options_id =o.sellerbased_options_id ';
        $queryString .= ' WHERE language_id=' . $this->config->get('config_language_id');

        $data = $this->db->query($queryString);

        if ($data->num_rows > 0) {
            return ['data' => $data->rows, 'count' => $data->num_rows];
        }

        return false;
    }

    public function newOption($data)
    {
        $queryString = $fields = [];
        $queryString[] = 'INSERT INTO ' . $this->options_table . ' SET';

        $fields[] = 'sellerbased_options_status	='.$this->resolveValue($data['status']);
        $fields[] = 'sellerbased_options_default=' . $this->resolveValue($data['sellerbased_options_default']);

        $queryString[] = implode(',', $fields);

        $this->db->query(implode(' ', $queryString));

        return $this->db->getLastId();
    }

    public function newOptionDescription($data, $lastId)
    {
        foreach ($data as $id => $language) {
            $queryString = $fields = [];
            $queryString[] = 'INSERT INTO ' . $this->description_table . ' SET';

            $fields[] = 'sellerbased_options_id =' . $lastId;
            $fields[] = 'language_id=' . $this->resolveValue($id);
            $fields[] = 'title=' . $this->resolveValue($language['title']);

            $queryString[] = implode(',', $fields);

            $this->db->query(implode(' ', $queryString));
        }
    }

    private  function resolveValue($value)
    {
        return is_numeric($value) ? $value : '"' . $value . '"';
    }

    public function getOption($id)
    {
        $queryString = [];
        $queryString[] = 'SELECT * FROM ' . $this->options_table . ' AS options';

        $queryString[] = 'LEFT JOIN ' . $this->description_table . ' AS descriptions';
        $queryString[] = 'on options.sellerbased_options_id=descriptions.sellerbased_options_id';
        $queryString[] = 'WHERE options.sellerbased_options_id=' . $id;
//        $queryString[] = 'AND descriptions.language_id=' . $this->config->get('config_language_id');

        $data = $this->db->query(implode(' ', $queryString));

        /*
         * We went with this function instead the internal PHP function `array_diff` because array_diff
         * ignores the returned elements with value = 1
         * */
        $arrayDiff = function ($data) {
            $newDataSet = [];

            $row = $data->row;
            $rows = $data->rows;

            $newDataSet['sellerbased_options_id'] = $row['sellerbased_options_id'];
            $newDataSet['sellerbased_options_status'] = $row['sellerbased_options_status'];
            $newDataSet['sellerbased_options_default'] = $row['sellerbased_options_default'];

            foreach ($rows as $row) {
                $newDataSet['details'][$row['language_id']] = $row;
            }

            return [
                'num_rows' => $data->num_rows,
                'data' => $newDataSet
            ];
        };

        $newDataSet = $arrayDiff($data);

        if ($newDataSet['num_rows'] > 0) {
            return $newDataSet['data'];
        } else {
            return false;
        }
    }

    public function updateOption($id, $data)
    {
        $queryString = $fields = [];

        $queryString[] = 'UPDATE ' . $this->options_table . ' SET';

        $fields[] = 'sellerbased_options_status	=' . $this->resolveValue($data['status']);
        $fields[] = 'sellerbased_options_default	=' . $this->resolveValue($data['sellerbased_options_default']);

        $queryString[] = implode(', ', $fields);

        $queryString[] = 'WHERE sellerbased_options_id=' . $this->resolveValue($id);

        $this->db->query(implode(' ', $queryString));
    }

    public function updateOptionDescription($id, $data)
    {
        foreach ($data as $language => $row) {
            $queryString = $fields = [];

            $queryString[] = 'UPDATE ' . $this->description_table . ' SET';

            $fields[] = 'title=' . $this->resolveValue($row['title']);
        
            $queryString[] = implode(', ', $fields);

            $queryString[] = 'WHERE sellerbased_options_id=' . $this->resolveValue($id);
            $queryString[] = 'AND language_id=' . $language;

            $this->db->query(implode(' ', $queryString));
        }
    }

    public function deleteOption($id)
    {
        $queryString  = [];
        $queryString[] = 'DELETE ' . $this->options_table . ',' . $this->description_table;
        $queryString[] = 'FROM ' . $this->options_table . ',' . $this->description_table;
        $queryString[] = 'WHERE ' . $this->options_table . '.sellerbased_options_id=' . $id;
        $queryString[] = 'AND ' . $this->description_table . '.sellerbased_options_id=' . $id;

        $this->db->query(implode(' ', $queryString));
    }

   

    public function validate($data)
    {
      
        $details = $data['details'];

        foreach ($details as $detail) {
            if (!$detail['title'] || strlen($detail['title']) === 0) {
                $this->errors[] = $this->language->get('seller_options_invalid_title_field');
            }
        }

        if (count($this->errors) == 0) {

            return true;
        }
        $this->errors['warning'] = $this->language->get('error_warning');
        return false;
    }
    
   
}
