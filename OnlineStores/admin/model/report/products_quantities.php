<?php

class ModelReportProductsQuantities extends Model
{
    /**
     * Get products quantities and products options quantities
     *
     * @param array $params
     *
     * @return array
     */
    public function getTree($params)
    {
        $final_data = [];
        $languageId = $this->config->get('config_language_id');

        $query=[];
        $query[]= "SELECT count(p.product_id ) as count from product p  ";
        $query[] = ' WHERE (p.status = 1 AND p.date_available < NOW() AND p.quantity > 0) ';
        if($params['product_id'] ) {
            $query[] = ' AND p.product_id = ' .$params['product_id'];
        }
        $count = $this->db->query(implode(' ', $query))->row['count'];

        $query = [];
        $query[] = 'SELECT  pd.name,  p.product_id , p.quantity ';
        $query[]  = ', GROUP_CONCAT(  (SELECT name FROM category_description AS cd WHERE cd.category_id=p2c.category_id AND cd.language_id="' . $languageId .'") SEPARATOR " &gt; " ) AS categories_names';

        $query[] = ' FROM product p ';
        $query[] = ' left join product_description pd on pd.product_id =  p.product_id';

        $query[] = ' LEFT JOIN ' . DB_PREFIX . 'product_to_category AS p2c';
        $query[] = ' ON (p.product_id = p2c.product_id)';


        $query[] = ' WHERE (p.status = 1 AND p.date_available < NOW() AND p.quantity > 0) 
        AND pd.language_id = ' .$languageId;

        if($params['product_id'] ) {
            $query[] = ' AND p.product_id = ' .$params['product_id'];
        }

        $query[] = ' Group by p.product_id  ';

        if($params['length'] != -1) {
            $query[] = " LIMIT " . $params['start'] . ", " . $params['length'];
        }

        $products = $this->db->query(implode(' ', $query));
        $data = $products->rows;
        $i =0;

        foreach ( $data as $key => $row){
            $sql = ' SELECT od.name as fn, pov.option_id,pov.quantity,pov.product_id,pov.option_value_id,ovd.name as ln FROM `product_option_value` pov 
             left join option_value_description ovd on ovd.option_value_id = pov.option_value_id 
             left join option_description od on pov.option_id = od.option_id             
             where product_id='.$row['product_id'] ;

            $sql .= " group by pov.option_value_id";
            $product_option_value = $this->db->query($sql)->rows;

            foreach ($product_option_value as $key_1 => $value){
                $value['categories_names']= $row['categories_names'];
                $value['name']= $row['name'];
                $value['product_quantity']= $row['quantity'];
                $final_data[$i]=$value;
                $i++;
            }
        }

        return [
            'data' => $final_data,
            'total' => count($final_data),
            'totalFiltered' => $count,

        ];
    }
}
