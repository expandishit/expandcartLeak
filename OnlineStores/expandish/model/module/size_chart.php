<?php

class ModelModuleSizeChart extends Model  {	
	
    public function getChart($data = []) {
        $result = [];
        $size_charts = $this->db->query("SELECT * FROM size_chart")->rows;
        foreach ($size_charts as  $size_chart) {
            $size_chart['chart_sizes'] = unserialize($size_chart['chart_sizes']);

            $size_chart_details = $this->db->query("SELECT * FROM size_chart_details WHERE `size_chart_id`='{$size_chart['id']}'")->rows;
            $_size_chart_details = [];
            foreach ($size_chart_details as $detail) {
                $_size_chart_details[$detail['lang_id']] = $detail;
            }
            $size_chart_details = $_size_chart_details;

            $size_chart_relationables = $this->db->query("SELECT * FROM size_chart_relationables WHERE `size_chart_id`='{$size_chart['id']}'")->rows;
            $size_chart_relationables = $this->groupBy('relationable_type', $size_chart_relationables);

            $size_chart_categories = array_column($size_chart_relationables['categories'], 'relationable_id');
            $size_chart_products = array_column($size_chart_relationables['products'], 'relationable_id');
            
            $size_chart_countries = implode(',', array_column($size_chart_relationables['countries'], 'relationable_id'));
            if (!empty($size_chart_countries)) {
                $size_chart_countries = $this->db->query("SELECT `country_id`, `name` FROM countries_locale WHERE country_id IN ({$size_chart_countries}) AND lang_id={$this->config->get('config_language_id')}")->rows;
            }

            $result[] = compact('size_chart', 'size_chart_details', 'size_chart_categories', 'size_chart_products', 'size_chart_countries');
        }

        return $result;
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
