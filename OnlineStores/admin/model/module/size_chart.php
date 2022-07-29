<?php

class ModelModuleSizeChart extends Model
{
    public function install()
    {
        $this->db->query("CREATE TABLE size_chart (
            `id` int NOT NULL AUTO_INCREMENT,
            `chart_row_size` int NOT NULL DEFAULT 0,
            `chart_col_size` int NOT NULL DEFAULT 0,
            `chart_sizes` TEXT DEFAULT NULL,
            PRIMARY KEY (id)
        );");

        $this->db->query("CREATE TABLE size_chart_details (
            `id` int NOT NULL AUTO_INCREMENT,
            `size_chart_id` int NOT NULL,
            `lang_id` int NOT NULL,
            `name` varchar(255) DEFAULT NULL,
            `description` TEXT DEFAULT NULL,
            PRIMARY KEY (id)
        );");

        $this->db->query("CREATE TABLE size_chart_relationables (
            `id` int NOT NULL AUTO_INCREMENT,
            `size_chart_id` int NOT NULL,
            `relationable_type` varchar(255) DEFAULT NULL,
            `relationable_id` int NOT NULL,
            PRIMARY KEY (id)
        );");

    }

    public function uninstall()
    {
        $this->db->query("DROP TABLE size_chart_relationables");
        $this->db->query("DROP TABLE size_chart_details");
        $this->db->query("DROP TABLE size_chart");
    }


    public function insertSizeChart($data) {
        $data['chart_sizes'] = serialize($data['chart_sizes']);
        $this->db->query("INSERT INTO `size_chart` (chart_row_size, chart_col_size, chart_sizes) VALUES ('{$this->db->escape($data['chart_row_size'])}', '{$this->db->escape($data['chart_col_size'])}', '{$this->db->escape($data['chart_sizes'])}')");
        return $this->db->getLastId();
    }

    public function insertSizeChartDetails($data) {
        $stmt = "INSERT INTO `size_chart_details` (`size_chart_id`, `lang_id`, `name`, `description`) VALUES ";
        foreach ($data['size_chart_description'] as $key => $value) {
            $stmt .= "('{$this->db->escape($data['size_chart_id'])}', '{$this->db->escape($key)}', '{$this->db->escape($value['name'])}', '{$this->db->escape($value['description'])}'), ";
        }

        $stmt = rtrim($stmt, ', ');
        $this->db->query($stmt);
    }

    public function insertSizeChartRelationables($data) {
        $stmt = "INSERT INTO `size_chart_relationables` (`size_chart_id`, `relationable_type`, `relationable_id`) VALUES ";

        foreach ($data['categories_ids'] as $value) {
            $stmt .= "('{$data['size_chart_id']}', '{$this->db->escape('categories')}', '{$this->db->escape($value)}'), ";
        }

        foreach ($data['products_ids'] as $value) {
            $stmt .= "('{$data['size_chart_id']}', '{$this->db->escape('products')}', '{$this->db->escape($value)}'), ";
        }

        foreach ($data['countries_ids'] as $value) {
            $stmt .= "('{$data['size_chart_id']}', '{$this->db->escape('countries')}', '{$this->db->escape($value)}'), ";
        }

        $stmt = rtrim($stmt, ', ');
        $this->db->query($stmt);
    }

    public function getCharts($lang_id) {

        return $this->db->query("SELECT sc.id, scd.name FROM size_chart sc LEFT JOIN size_chart_details scd ON scd.size_chart_id=sc.id WHERE `lang_id`='{$this->db->escape($lang_id)}'")->rows;
    }


    public function getChart($id) {
        $size_chart = $this->db->query("SELECT * FROM size_chart WHERE `id`='{$this->db->escape($id)}'")->row;
        $size_chart_details = $this->db->query("SELECT * FROM size_chart_details WHERE `size_chart_id`='{$this->db->escape($id)}'")->rows;

        $size_chart_relationables = $this->db->query("SELECT * FROM size_chart_relationables WHERE `size_chart_id`='{$this->db->escape($id)}'")->rows;
        $size_chart_relationables = $this->groupBy('relationable_type', $size_chart_relationables);

        $size_chart_categories = array_column($size_chart_relationables['categories'], 'relationable_id');
        $size_chart_products = array_column($size_chart_relationables['products'], 'relationable_id');
        $size_chart_countries = array_column($size_chart_relationables['countries'], 'relationable_id');

        return compact('size_chart', 'size_chart_details', 'size_chart_categories', 'size_chart_products', 'size_chart_countries');
    }

    public function deleteChart($id) {
        $this->deleteChartDependancies($id);
        $this->db->query("DELETE FROM `size_chart` WHERE `id`={$this->db->escape($id)}");
    }


    public function deleteChartDependancies($id) {
        $this->db->query("DELETE FROM `size_chart_relationables` WHERE `size_chart_id`={$this->db->escape($id)}");
        $this->db->query("DELETE FROM `size_chart_details` WHERE `size_chart_id`={$this->db->escape($id)}");
    }


    public function updateChart($data) {

        $data['chart_sizes'] = serialize($data['chart_sizes']);
        $this->db->query("UPDATE `size_chart` SET `chart_row_size`='{$this->db->escape($data['chart_row_size'])}', `chart_col_size`='{$this->db->escape($data['chart_col_size'])}', `chart_sizes`='{$this->db->escape($data['chart_sizes'])}' WHERE `id`='{$data['size_chart_id']}'");
    
    }

    public function groupBy($key, $data) {
        $result = array();
    
        foreach($data as $val) {
            if(array_key_exists($key, $val)){
                $result[$val[$key]][] = $val;
            }else{
                $result[""][] = $val;
            }
        }
    
        return $result;
    }
    
}