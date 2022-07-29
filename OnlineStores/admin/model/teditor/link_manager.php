<?php
class ModelTeditorLinkManager extends Model {

    private $mainPages = array();
    public function getLinks($search_word,$data_type="select") {

        designeditorLog([
            'note' => '$data_type = ' . $data_type . ' - link_mannager model'
        ]);

        $categories=array();
        $products=array();
        $pages=array();
        $this->load->language('common/home');
        $lang_id = $this->config->get('config_language_id') ? (int)$this->config->get('config_language_id') : 1;
        if($search_word != ""){
            $sql ="SELECT l.link_id as id,l.name,title,route FROM `link` as l left join link_description as l_d on l.link_id = l_d.link_id";
            $sql.=" where language_id = $lang_id";

            $results = $this->db->query($sql);
            $data_source=array();
            foreach ($results->rows as $value) {
                if($value['name'] == "home" && preg_match("/$search_word/i",$value['title'])){
                    if($data_type && strpos($data_type, 'select') !== false){

                        designeditorLog([
                            'note' => 'entered if - getLinks function - link_mannager model',
                            'strpos_result' => strpos($data_type, 'select')
                        ]);

                        $data_source[]=array(
                            'name'=>$value['title'],
                            'icon'=>"fa fa-home",
                            'item'=>$value['title'],
                            'route'=>$value['route']
                        );
                    }else{

                        designeditorLog([
                            'note' => 'entered else - getLinks function - link_mannager model',
                            'strpos_result' => strpos($data_type, 'select')
                        ]);

                        $data_source[]=array(
                            'name'=>$value['title'],
                            'icon'=>"fa fa-home",
                            'items'=>[
                                $value['title']
                            ]
                        );
                    }
                }
                elseif($value['name'] == "category"){
                    
                    // Get Categories
                    $sql = " SELECT c.category_id, cd.name  FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id)";
                    $sql .= " Where cd.name like '%".$this->db->escape($search_word)."%'";
                    $sql .= " ORDER BY cd.name";
                    $categories_data = $this->db->query($sql);
                    if($categories_data->num_rows > 0){
                        foreach ($categories_data->rows as $row) {
                            if ($data_type && strpos($data_type, 'select') !== false){
                                $route = str_replace("{cat_id}",$row['category_id'],$value['route']);
                                $data_source[]=array(
                                    'name'=>$value['title'],
                                    'icon'=>"fa fa-sitemap",
                                    'item'=>html_entity_decode($row['name']),
                                    'route'=>"route=".$route
                                );
                            }else{
                                # code...
                                $categories[]=html_entity_decode($row['name']);
                            }
                        }
                        if (count($categories)){
                            $data_source[]=array(
                                'name'=>$value['title'],
                                'icon'=>"fa fa-sitemap",
                                'items'=>$categories
                            );
                        }
                    }
                    
                }
                elseif($value['name']=="product"){
                    
                    $lang_id = $this->config->get('config_language_id') ? (int)$this->config->get('config_language_id') : 1;
                    $sql = "SELECT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)";
                    $sql .= " where name like '%".$this->db->escape($search_word)."%'";
                    $sql .= " GROUP BY p.product_id";
                    $products_data = $this->db->query($sql);
                    if($products_data->num_rows > 0){
                        foreach ($products_data->rows as $row) {
                            if ($data_type && strpos($data_type, 'select') !== false){
                                $route = str_replace("{p_id}",$row['product_id'],$value['route']);
                                $product=$row['name'];
                                $data_source[]=array(
                                    'name'=>$value['title'],
                                    'icon'=>"fa fa-mobile",
                                    'item'=>$product,
                                    'route'=>"route=".$route
                                );
                            }else{
                                # code...
                                $products[]=$row['name'];
                            }
                        }
                        if (count($products)){
                            $data_source[]=array(
                                'name'=>$value['title'],
                                'icon'=>"fa fa-mobile",
                                'items'=>$products
                            );
                        }
                    }
                }
                elseif($value['name']=="web page"){
                    
                    $lang_id = $this->config->get('config_language_id') ? (int)$this->config->get('config_language_id') : 1;
                    $sql = "SELECT * FROM " . DB_PREFIX . "information i LEFT JOIN " . DB_PREFIX . "information_description i_d ON (i.information_id = i_d.information_id)";
                    $sql .= " where title like '%".$this->db->escape($search_word)."%' and status = 1";
                    
                    $pages_data = $this->db->query($sql);
                    if($pages_data->num_rows > 0){
                        foreach ($pages_data->rows as $row) {
                            # code...
                            if ($data_type && strpos($data_type, 'select') !== false){
                                $route = str_replace("{info_id}",$row['information_id'],$value['route']);
                                $page=$row['title'];
                                $data_source[]=array(
                                    'name'=>$value['title'],
                                    'icon'=>"fa fa-link",
                                    'item'=>$page,
                                    'route'=>"route=".$route
                                );
                            }else{
                                # code...
                                $pages[]=$row['title'];
                            }

                        }
                        if (count($pages)){
                            $data_source[]=array(
                                'name'=>$value['title'],
                                'icon'=>"fa fa-link",
                                'items'=>$pages
                            );
                        }

                    }
                }
                else{
                    if(preg_match("/$search_word/i",$value['title'])){
                        $this->mainPages[]=$value['title'];
                    }
                }
            }
            if(count($this->mainPages) > 0){
                if ($data_type && strpos($data_type, 'select') !== false){
                    $data_source[]=array(
                        'name'=>$this->language->get('text_other_pages'),
                        'icon'=>"fa fa-home",
                        'items'=>$this->mainPages,
                        'route'=>""
                    );
                }else{
                    $data_source[]=array(
                        'name'=>$this->language->get('text_other_pages'),
                        'icon'=>"fa fa-home",
                        'items'=>$this->mainPages
                    );
                }

            }
            return $data_source;
        }
        else{
            if($data_type == 'tob_banner_select'){

                $sql ="SELECT l.link_id as id,l.name,title,route FROM `link` as l left join link_description as l_d on l.link_id = l_d.link_id where name = 'category' and language_id = $lang_id";
                $result = $this->db->query($sql);
                $value = $result->row;

                $sql = " SELECT c.category_id, cd.name  FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id AND cd.language_id=$lang_id)";
                $sql .= " ORDER BY RAND()";
                $sql .= " LIMIT 5";
                $categories_data = $this->db->query($sql);
                if($categories_data->num_rows > 0){
                    foreach ($categories_data->rows as $row) {
                        $route = str_replace("{cat_id}",$row['category_id'],$value['route']);
                        $data_source[]=array(
                            'name'=>$value['title'],
                            'icon'=>"fa fa-sitemap",
                            'item'=>html_entity_decode($row['name']),
                            'route'=>"route=".$route
                        );
                    }
                }

                return $data_source;
            } else {
                return [];
            }
        }
        
    }

}