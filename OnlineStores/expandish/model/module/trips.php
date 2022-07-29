<?php

class ModelModuleTrips extends Model {

    public function getTripProduct($product_id) {
        $language_id = $this->config->get('config_language_id');
		$query = $this->db->query("SELECT tp.pickup_point,tp.destination_point,
                                   tp.min_no_seats,tp.max_no_seats,tp.from_date,tp.to_date,time, DATEDIFF(tp.to_date,tp.from_date) As duration,
                                   (SELECT name FROM " . DB_PREFIX . "geo_area_locale ga WHERE ga.area_id = tp.area_id AND ga.lang_id = '" . (int)$language_id . "') AS area
                                  
         FROM " . DB_PREFIX . "trips_product tp WHERE product_id = '" . (int)$product_id . "'");
		
		return $query->row;
	}
    public function getCustomCategoriesIDs($table) 
    {
		$query = $this->db->query("SELECT category_id FROM " . DB_PREFIX .$table);
        $categoriesIDs=array();
        foreach($query->rows as $cat_id){
            $categoriesIDs[]=$cat_id['category_id'];
        }
		return $categoriesIDs;
	}
	
    public function getCustomCategories($data,$language_id) {
       
		if( !$language_id ) $language_id = $this->config->get('config_language_id');

        $sql = "SELECT * FROM " . DB_PREFIX . "category c 
	            LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) 
	            LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) 
				WHERE cd.language_id = '" . (int)$language_id . "'";
                if(!empty($data))
                {
                    $sql .= "AND c.category_id in (".implode(',',$data).")";
                }
        if(is_array($parent_id) && count($parent_id) > 0)
            $sql .= " AND c.parent_id IN (".implode(',', $parent_id).")";
        else
            $sql .= " AND c.parent_id = '" . (int)$parent_id . "'";

	       $sql  .=" AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'
	            AND c.status = '1' 
	            ORDER BY c.sort_order, LCASE(cd.name)";

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

		$query = $this->db->query($sql);

		return $query->rows;
	}
    public function getOrderProductsTrips($order_id,$tripsDate="") {

		$sql = "SELECT * FROM " . DB_PREFIX . "trips_product trips_pro 
		LEFT JOIN " . DB_PREFIX . " order_product oP ON (trips_pro.product_id = oP.product_id) ";
        $sql .=	"WHERE  order_id = '" . (int)$order_id . "'";

        if ($tripsDate =="cancel") {
            $sql .= " AND trips_pro.product_id IN ( SELECT product_id FROM trips_orders WHERE product_id IS NOT NULL)";
        }
        elseif($tripsDate=="upcoming" ||$tripsDate =="past" ){
           
            $sql .= " AND order_id NOT IN ( SELECT order_id FROM trips_orders WHERE order_id IS NOT NULL)";
        }
       
		if ($tripsDate =="upcoming") {
			
            $sql .= " AND trips_pro.from_date >= CURDATE()";
        }
		if ($tripsDate =="past") {
			
            $sql .= " AND trips_pro.from_date < CURDATE()";
        }
		$query=$this->db->query($sql);
	
		return $query->rows;
	}
    public function cancelTripConfig($product_id)
    {
       $tripconfig_rider_cancelation_limit= $this->config->get('trips')['tripconfig_rider_cancelation_limit'];
       $tripconfig_rider_cancelation_limit_uint= $this->config->get('trips')['tripconfig_rider_cancelation_limit_unit'];
       $tripData=$this->getTripProduct($product_id); 
       $cancelFlag=false;
       
       if ($tripconfig_rider_cancelation_limit_uint==1)
       {   ///days
           $todayDate=date('Y-m-d');
           $tripDate=$tripData['from_date'];
           $dateAfterSubtractLimit=date('Y-m-d', strtotime($tripData['from_date']. ' -'.$tripconfig_rider_cancelation_limit. 'days'));
           
           if($todayDate<=$dateAfterSubtractLimit)
           {
               
               $cancelFlag=true;
           }
       }
       elseif ($tripconfig_rider_cancelation_limit_uint==2)
       {    //hours.
           $timeAfterSubtractLimit=date('g:i', strtotime($tripData['time']. ' -'.$tripconfig_rider_cancelation_limit. 'hours'));
           $currentTime=date('g:i');
           if($currentTime<=$timeAfterSubtractLimit)
           {
               $cancelFlag=true;
           }
       }
   
       return $cancelFlag;

    }
    public function cancelTrip($data)
    {
        if($data['canceled_by']=='rider'){$canceledCol='isRiderCancelTrip';}else{$canceledCol='	isDriverCancelTrip';}
        $seller_id=$this->MsLoader->MsProduct->getSellerId($data['product_id']);
        $customer_id=$this->customer->getId();
        $cancel_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "trips_orders WHERE customer_id = '" . (int)$customer_id . "' AND  product_id = '" . (int)$data['product_id'] . "' AND  order_id = '" . (int)$data['order_id'] . "'");
		if($cancel_query->row)
        $query = $this->db->query("UPDATE " . DB_PREFIX . "trips_orders SET ".$canceledCol." = 1 WHERE customer_id = '" . (int)$customer_id . "' AND  product_id = '" . (int)$data['product_id'] . "'AND  order_id = '" . (int)$data['order_id'] . "' ");
        else
        $query=$this->db->query("INSERT INTO " . DB_PREFIX . "trips_orders SET customer_id = '" . (int)$customer_id . "',seller_id = '" . (int)$seller_id . "', product_id = '" . (int)$data['product_id']. "', order_id = '" . (int)$data['order_id']. "', ".$canceledCol."= 1");
        if($query)return true; else return false;
    } 
    public function cancelStatus($order_id)
    {   
        $cancel_status=false;
        $cancel_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "trips_orders WHERE order_id = '" . (int)$order_id . "'");
        $result= $cancel_query->row;
        if($result['isRiderCancelTrip']==1){
            $cancel_status='canceled_by_rider';
        }
        else if($result['isDriverCancelTrip']==1){
            $cancel_status='canceled_by_driver';
        }
        return $cancel_status;
    }
    public function IsTrip($product_id) {
        $query = $this->db->query("SELECT product_id FROM " . DB_PREFIX . "trips_product WHERE product_id = ".$product_id." LIMIT 1");
         if ($query->row) return true;
         else return false;
     }

}