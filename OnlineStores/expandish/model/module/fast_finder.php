<?php

class ModelModuleFastFinder extends Model
{
    public function search($searchText)
    {
        $result = [];
        $settings = $this->config->get('fast_finder');
        // $currentLanguage = $this->config->get('config_lang_id');

        $sqlSelections = [
            'p.product_id',
            'p.image',
            'pd.name'
        ];

        $separated_word_search = $settings['separated_word_search'];

        $result_count = $settings['result_count'];
        $canShowPrice = $this->config->get('config_customer_price') ? $this->customer->isLogged() : true;

        $settings['show_description'] === '1' ? $sqlSelections[] = ' pd.description' : '';
        $settings['show_price']       === '1' && $canShowPrice ? $sqlSelections[] = ' p.price' : '';
        $settings['show_quantity']    === '1' ? $sqlSelections[] = ' p.quantity' : '';
        $settings['show_discount']    === '1' ? $sqlSelections[] = ' ps.price as special' :'';

        $selections = implode(', ', $sqlSelections);

        $sql = "SELECT ".$selections;
        if($settings['show_discount']){
            $sql .= " , ( (ps.date_start <= NOW() AND ps.date_end >= NOW()) OR (ps.date_start <= NOW() AND ps.date_end='0000-00-00') 
                          OR (ps.date_start = '0000-00-00' AND ps.date_end >= NOW()) OR (ps.date_start ='0000-00-00' AND ps.date_end='0000-00-00') ) AS special_available ";
        }
        $sql .= " FROM " . DB_PREFIX . "product p";

        if (\Extension::isInstalled('multiseller')) {
            $sql .= ' LEFT JOIN ms_product msp ON p.product_id = msp.product_id';
            $sql .= ' LEFT JOIN ms_seller mss ON msp.seller_id = mss.seller_id';
        }

        if($settings['show_discount']){
            $sql .= " LEFT JOIN " . DB_PREFIX . "product_special ps ON (p.product_id = ps.product_id) ";
        }

        $sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) 
                  LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE 
                  p.status = '1' AND p.archived = '0' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

        if ($searchText) {
            $sql .= " AND (";

            $implode = array();

            if ($separated_word_search) {
                $implode[] = "(pd.name LIKE '%" . $this->db->escape($searchText) . "%' 
                            OR pd.name LIKE '%".$this->db->escape($searchText)."%' 
                            OR pd.name LIKE '%".$this->db->escape($searchText)."%')";
            } else {
                $words = explode(' ', trim(preg_replace('/\s\s+/', ' ', $searchText)));
                foreach ($words as $word) {
                    $implode[] = "(pd.name LIKE '%" . $this->db->escape($word) . "%' OR REPLACE(pd.name, '''', '') LIKE '%".$this->db->escape($word)."%')";            
                }
            }
            
            
            if ($implode) {
                $sql .= " " . implode(" AND ", $implode) . "";
            }

            $sql .= " OR LCASE(p.model) = '" . $this->db->escape(utf8_strtolower($searchText)) . "'";
            $sql .= " OR LCASE(p.sku) = '" . $this->db->escape(utf8_strtolower($searchText)) . "'";
            $sql .= " OR LCASE(p.upc) = '" . $this->db->escape(utf8_strtolower($searchText)) . "'";
            $sql .= " OR LCASE(p.ean) = '" . $this->db->escape(utf8_strtolower($searchText)) . "'";
            $sql .= " OR LCASE(p.jan) = '" . $this->db->escape(utf8_strtolower($searchText)) . "'";
            $sql .= " OR LCASE(p.isbn) = '" . $this->db->escape(utf8_strtolower($searchText)) . "'";
            $sql .= " OR LCASE(p.mpn) = '" . $this->db->escape(utf8_strtolower($searchText)) . "'";

            if (\Extension::isInstalled('multiseller')) {
                $sql .= ' AND (msp.product_id IS NULL OR mss.seller_id IS NULL OR mss.products_state = 1)';
            }

            $sql .= " ) GROUP BY pd.product_id";


            if((int)$result_count > 0){
                $sql .= " LIMIT $result_count";
            }else{
                $sql .= " LIMIT 10";
            }

            $result = $this->db->query($sql)->rows ?? [];
        }

        return $result;
    }
}
