<?php


class ModelCatalogReviewOptions extends Model {



    public function insert($data){

        $this->db->query("
            INSERT INTO " . DB_PREFIX . "review_options SET
            type = '" . $this->db->escape($data['type']) . "',
            status = '" . $this->db->escape($data['status']) . "'
        ");

        $option_id = $this->db->getLastId();
        foreach ($data['option_name'] as $language_id => $value) {
   
            $this->db->query("
                INSERT INTO " . DB_PREFIX . "review_options_description SET
                review_option_id = '" . (int)$option_id . "',
                language_id = '" . (int)$language_id . "',
                name = '" . $this->db->escape($value['name']) . "'
            ");
                
        }

    }
    public function dtUpdateStatus($option_id,$status){
        $this->db->query("UPDATE " . DB_PREFIX . "review_options SET status=".(int)$status." WHERE option_id=".$option_id);
        return true;
    }
    public function dtDelete($option_id){

        $this->db->query("DELETE FROM " . DB_PREFIX . "review_options WHERE option_id=".$option_id);
        $this->db->query("DELETE FROM " . DB_PREFIX . "review_options_description WHERE review_option_id=".$option_id);

        return true;
    }
    public function getOptionById($option_id){
        $option = $this->db->query("SELECT * FROM " . DB_PREFIX . "review_options INNER JOIN review_options_description ON option_id = review_option_id WHERE option_id=".$option_id);

        return $option->rows;
    }
    public function getStatus($option_id){
        $option = $this->db->query("SELECT status FROM " . DB_PREFIX . "review_options WHERE option_id=".$option_id." LIMIT 1");
        return $option->row['status'];
    }

    public function update($newData,$option_id){



        $this->db->query("
            UPDATE " . DB_PREFIX . "review_options SET
            type = '" . $this->db->escape($newData['type']) . "',
            status = '" . $this->db->escape($newData['status']) . "'
            WHERE option_id='".$option_id."' 
        ");

        foreach ($newData['option_name'] as $language_id => $value) {
   
            $this->db->query("
                UPDATE " . DB_PREFIX . "review_options_description SET
                name = '" . $this->db->escape($value['name']) . "'
                WHERE review_option_id='".$option_id."' AND language_id='".$language_id."'
            ");
                
        }

        return true;

    }

    public function getList($languageId){
        $query = $this->db->query(
            "SELECT * FROM " . DB_PREFIX . "review_options INNER JOIN review_options_description ON option_id = review_option_id WHERE language_id=".$languageId
        );
        return $query->rows;
    }
    public function getReviewsOptionText($languageId){
        $query = $this->db->query(
            "SELECT t1.*,t2.name as enName FROM (SELECT * FROM review_options INNER JOIN review_options_description ON option_id = review_option_id WHERE review_options_description.language_id=".(int)$languageId." AND status=1 AND type='text') as t1 
            INNER JOIN( SELECT review_options.option_id,review_options_description.name FROM review_options INNER JOIN review_options_description ON option_id = review_option_id JOIN language ON language.language_id = review_options_description.language_id WHERE language.code='en' AND review_options.status=1 AND type='text' ) AS t2 
            WHERE t1.option_id=t2.option_id"
        );
        return $query->rows;
    }
    public function getReviewsOptionRate($languageId){
        $query = $this->db->query(
            "SELECT t1.*,t2.name as enName FROM (SELECT * FROM review_options INNER JOIN review_options_description ON option_id = review_option_id WHERE review_options_description.language_id=".(int)$languageId." AND status=1 AND type='rate') as t1 
            INNER JOIN( SELECT review_options.option_id,review_options_description.name FROM review_options INNER JOIN review_options_description ON option_id = review_option_id JOIN language ON language.language_id = review_options_description.language_id WHERE language.code='en' AND review_options.status=1 AND type='rate' ) AS t2 
            WHERE t1.option_id=t2.option_id"
        );
        return $query->rows;
    }
    public function getReviewsOptionList(){
        $query = $this->db->query(
            "SELECT review_options_description.name FROM review_options_description INNER JOIN review_options ON review_options.option_id = review_options_description.review_option_id INNER JOIN language ON review_options_description.language_id = language.language_id WHERE language.code = 'en' AND review_options.status=1 ORDER BY review_options.type"
        );
        $options_list = array();
        foreach($query->rows as $value) {
            array_push($options_list,array_values($value)[0]);
        }

        return $options_list;
    }

    // public function getNextId(){
    //     $last_id = $this->db->query("SELECT option_id FROM " . DB_PREFIX . "review_options ORDER BY option_id DESC LIMIT 1");

    //     return $last_id->row['option_id'] + 1;

    // }
}
















?>