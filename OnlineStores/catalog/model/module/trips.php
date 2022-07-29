<?php
class ModelModuleTrips extends Model {


    public function addTripCustomer($data){
        $car_license= $data['car_license'] ?? '';
        $driving_license = $data['driving_license'] ?? '';
        $tourism_license = $data['tourism_license'] ?? '';

        $sql = "INSERT INTO " . DB_PREFIX . "trips_customer
        SET customer_id = " . (int)$data['customer_id'] . ",
            area_id = " . (int)$data['area_id'] . ",
            category_id = " . (int)$data['category_id'] . ",
            car_license = '" . $this->db->escape($car_license) . "',
            driving_license = '" . $this->db->escape($driving_license) . "',
            tourism_license = '" . $this->db->escape($tourism_license) . "',
            car_type = '" . $this->db->escape($data['car_type']) . "'";
       $this->db->query($sql);

    }
    public function getTripCustomer($customer_id) {
	
        $imagepath= rtrim(\Filesystem::getUrl(), '/') . '/image/';
        $language_id = $this->config->get('config_language_id');
		$query = $this->db->query(
        "SELECT DISTINCT ms.seller_id, c.firstname as name, c.email, ms.mobile as phone, ms.avatar
                        ,tc.car_type,ms.bank_iban,ms.personal_id,
                        (SELECT name FROM " . DB_PREFIX . "category_description cd 
                        WHERE cd.category_id = tc.category_id AND cd.language_id = '" . (int)$language_id . "') AS category,
                        (SELECT name FROM " . DB_PREFIX . "geo_area_locale ga 
                        WHERE ga.area_id = tc.area_id 
                        AND ga.lang_id = '" . (int)$language_id . "') AS area,tc.car_license,tc.driving_license,tc.tourism_license
        FROM " . DB_PREFIX . "customer c 
		LEFT JOIN " . DB_PREFIX . "ms_seller ms
		 ON (c.customer_id = ms.seller_id)
		LEFT JOIN " . DB_PREFIX . "trips_customer tc 
		ON (c.customer_id = tc.customer_id) 
		WHERE c.customer_id = '" . (int)$customer_id . "' ");
        $data = $query->row;
        $data['avatar'] =  $imagepath. $data['avatar'];
        $data['car_license'] =  $imagepath. $data['car_license'];
        $data['driving_license'] =  $imagepath. $data['driving_license'];
        $data['tourism_license'] =  $imagepath. $data['tourism_license'];
		return  $data;
	}
    public function getDriver($customer_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "trips_customer WHERE customer_id = '" . (int)$customer_id . "'");
		
		return $query->row;
	}
    public function editDriver($data) {
		$this->db->query("UPDATE " . DB_PREFIX . "trips_customer SET 
        area_id = '" . $this->db->escape($data['area_id']) . "', 
        category_id = '" . $this->db->escape($data['category_id']) . "',
        car_type = '" . $this->db->escape($data['car_type']) . "',
        car_license = '" . $this->db->escape($data['car_license']) . "', 
        driving_license = '" . $this->db->escape($data['driving_license']) . "', 
        tourism_license = '" . $this->db->escape($data['tourism_license']) . "' 
        WHERE customer_id = '" . (int)$this->customer->getId() . "'");
	}
    
    public function addTripProduct($data){

        $sql = "INSERT INTO " . DB_PREFIX . "trips_product
        SET product_id = " . (int)$data['product_id'] . ",
            area_id = " . (int)$data['area_id'] . ",
            pickup_point ='" . $this->db->escape($data['pickup_point']) . "',
            destination_point ='" . $this->db->escape($data['destination_point']) . "',
            min_no_seats = " . (int)$data['min_no_seats'] . ",
            max_no_seats = " . (int)$data['max_no_seats'] . ",
            from_date ='" . $this->db->escape($data['from_date']) . "',
            to_date ='" . $this->db->escape($data['to_date']) . "',
            time ='" . $this->db->escape($data['time']) . "' ";
       $this->db->query($sql);

    }
    public function getTripProduct($product_id) {
        $language_id = $this->config->get('config_language_id');
		$query = $this->db->query("SELECT tp.pickup_point,tp.destination_point,
                                   tp.min_no_seats,tp.max_no_seats,tp.from_date,tp.to_date,time, DATEDIFF(tp.to_date,tp.from_date) As duration,
                                   (SELECT name FROM " . DB_PREFIX . "geo_area_locale ga WHERE ga.area_id = tp.area_id AND ga.lang_id = '" . (int)$language_id . "') AS area
                                  
         FROM " . DB_PREFIX . "trips_product tp WHERE product_id = '" . (int)$product_id . "'");
		
		return $query->row;
	}
    public function getTripsQuestionnaire() {
        $queryIDs = $this->db->query("SELECT option_id FROM " . DB_PREFIX . "trips_questionnaire ");
        $data=array();
        foreach($queryIDs->rows as $option_id){
            $data[]=$option_id['option_id'];
        }
        if (!empty($data)) 
        {
        $sql = "SELECT * FROM `" . DB_PREFIX . "option` o 
        LEFT JOIN " . DB_PREFIX . "option_description od 
        ON (o.option_id = od.option_id) 
        WHERE od.language_id = '" . (int)$this->config->get('config_language_id') . "'";
        $sql .= " AND o.option_id in (".implode(',',$data).")";
		$sql .= " GROUP BY od.option_id ORDER BY name";
		$query = $this->db->query($sql);
        $result=$query->rows;
        }
        else $result=false;

		return $result;
	}
    public function updateArrivalStatus($data)
    {
        $arrived_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "trips_arrived_riders WHERE customer_id = '" . (int)$data['customer_id'] . "' AND  product_id = '" . (int)$data['product_id'] . "' AND  order_id = '" . (int)$data['order_id'] . "'");
		if($arrived_query->row)
        $query = $this->db->query("UPDATE " . DB_PREFIX . "trips_arrived_riders SET arrived = IF(arrived=1, 0, 1) WHERE customer_id = '" . (int)$data['customer_id'] . "' AND  product_id = '" . (int)$data['product_id'] . "'AND  order_id = '" . (int)$data['order_id'] . "' ");
        else
        $query=$this->db->query("INSERT INTO " . DB_PREFIX . "trips_arrived_riders SET customer_id = '" . (int)$data['customer_id'] . "', product_id = '" . (int)$data['product_id']. "', order_id = '" . (int)$data['order_id']. "', arrived = '1'");
        $arrived_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "trips_arrived_riders WHERE customer_id = '" . (int)$data['customer_id'] . "' AND  product_id = '" . (int)$data['product_id'] . "' AND  order_id = '" . (int)$data['order_id'] . "'");
        return $arrived_query->row;

    }
    public function getTripArrivalStatus($data)
    {
        $query= $this->db->query("SELECT arrived FROM " . DB_PREFIX . "trips_arrived_riders WHERE customer_id = '" . (int)$data['customer_id'] . "' AND  product_id = '" . (int)$data['product_id'] . "' AND  order_id = '" . (int)$data['order_id'] . "' ");
       if($query->row) return $query->row;
       else return '0';
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

    public function isTripsAppInstalled()
    {
        if(\Extension::isInstalled('trips') &&$this->config->get('trips')['status']==1) return true;
        else return false;
    }

}
