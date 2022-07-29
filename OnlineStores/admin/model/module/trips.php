<?php

class ModelModuleTrips extends Model
{ 

    /**
     * update the module settings.
     *
     * @param array $inputs
     *
     * @return bool|\Exception
     */
    public function updateSettings($inputs)
    {
        $this->load->model('setting/setting');

        $this->model_setting_setting->editSetting(
            'trips', $inputs
        );

        return true;
    }

    public function getSettings()
    {
        return $this->config->get('trips');
    }

    public function isActive()
    {
        $settings = $this->getSettings();

        if (isset($settings) && $settings['status'] == 1) {
            return true;
        }

        return false;
    }


    /**
    *   Install the required values for the application.
    *
    *   @return boolean whether successful or not.
    */
    public function install($store_id = 0)
    {
        try 
        {
            $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trips_customer` 
                    (
                        `trips_customer_id` int(11) NOT NULL AUTO_INCREMENT,
                        `customer_id` int(11) NOT NULL,
                        `area_id` int(11) NOT NULL,
                        `category_id` int(11) NOT NULL,
                        `car_license` varchar(255) NOT NULL,
                        `driving_license` varchar(255) NOT NULL,
                        `tourism_license` varchar(255) NOT NULL,
                        `car_type` varchar(191) NOT NULL ,
                        PRIMARY KEY (`trips_customer_id`),
                        INDEX (`customer_id`,`area_id`, `category_id`)
                    ) 
                    ENGINE=InnoDB DEFAULT CHARSET=utf8;";

            $this->db->query($sql);

            $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trips_product` 
            (
                `trips_product_id` int(11) NOT NULL AUTO_INCREMENT,
                `product_id` int(11) NOT NULL,
                `area_id` int(11) NOT NULL,
                `pickup_point_lat` varchar(32)  NULL,
                `pickup_point_long` varchar(32)  NULL,
                `destination_point_lat` varchar(32) NULL,
                `destination_point_long` varchar(32) NULL,
                `pickup_point` varchar(255)  NULL,
                `destination_point` varchar(255) NULL,
                `min_no_seats` int(11) NOT NULL,
                `max_no_seats` int(11) NOT NULL,
                `from_date` date NOT NULL ,
                `to_date` date NOT NULL,
                `time` varchar(32) NOT NULL,
                PRIMARY KEY (`trips_product_id`),
                INDEX (`product_id`,`area_id`)
            ) 
            ENGINE=InnoDB DEFAULT CHARSET=utf8;";

            $this->db->query($sql);

            $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trips_categories` 
                    (
                        `trips_categories_id` int(11) NOT NULL AUTO_INCREMENT,
                        `category_id` int(11) NOT NULL,
                        PRIMARY KEY (`trips_categories_id`)
                    ) 
                    ENGINE=InnoDB DEFAULT CHARSET=utf8;";   
            $this->db->query($sql);    

            $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trips_questionnaire` 
            (
                `trips_questionnaire_id` int(11) NOT NULL AUTO_INCREMENT,
                `option_id` int(11) NOT NULL,
                PRIMARY KEY (`trips_questionnaire_id`)
            ) 
            ENGINE=InnoDB DEFAULT CHARSET=utf8;";   
            $this->db->query($sql);  

            $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trips_orders` 
            (
                `trips_orders_id` int(11) NOT NULL AUTO_INCREMENT,
                `product_id` int(11) NOT NULL,
                `customer_id` int(11) NOT NULL,
                `seller_id` int(11) NOT NULL,
                `order_id` int(11) NOT NULL,
                `arrived` int(2) NOT NULL DEFAULT '0',
                `isRiderCancelTrip` int(2) NOT NULL DEFAULT '0',
                `isDriverCancelTrip` int(2) NOT NULL DEFAULT '0',
                PRIMARY KEY (`trips_orders_id`),
                INDEX (`product_id`,`customer_id`)
            ) 
            ENGINE=InnoDB DEFAULT CHARSET=utf8;";   
            $this->db->query($sql);  
            
               return true;
  
        } 
        catch (Exception $e) 
        {
            return false;
        }
    }

    /**
    *   Remove the values from the database.
    *
    *   @return boolean whether successful or not.
    */
    public function uninstall()
    {
        try
        {
            $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "trips_customer`;");
            $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "trips_product`;");
            $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "trips_categories`;");
            $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "trips_questionnaire`;");
            $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "trips_orders`;");
            return true;
        } 
        catch (Exception $e)
        {
            return false;
        }
    }

    public function addTripsCategories($data)
	{
		$this->DeleteTripsCategories();
		if (isset($data)) {
			foreach($data as $cat)
			$this->db->query("INSERT INTO " . DB_PREFIX . "trips_categories SET category_id = '" . (int)$cat . "'");
			}	
        
	}
	public function getTripsCategoriesIDs() 
    {
		$query = $this->db->query("SELECT category_id FROM " . DB_PREFIX . "trips_categories ");
        $categoriesIDs=array();
        foreach($query->rows as $cat_id){
            $categoriesIDs[]=$cat_id['category_id'];
        }
		return $categoriesIDs;
	}
	
    public function getTripsCategories($data=[]) {
        if (!empty($data)) 
        {
        $sql = "SELECT cp.category_id AS category_id, GROUP_CONCAT(cd1.name ORDER BY cp.level SEPARATOR ' &gt; ') AS name, c.parent_id, c.sort_order,c.image 
        FROM " . DB_PREFIX . "category_path cp 
        LEFT JOIN " . DB_PREFIX . "category c ON (cp.path_id = c.category_id) 
        LEFT JOIN " . DB_PREFIX . "category_description cd1 ON (c.category_id = cd1.category_id) 
        LEFT JOIN " . DB_PREFIX . "category_description cd2 ON (cp.category_id = cd2.category_id) 
        WHERE  cd1.language_id = '" . (int)$this->config->get('config_language_id') . "' 
        AND cd2.language_id = '" . (int)$this->config->get('config_language_id') . "' ";
        $sql .= " AND c.category_id in (".implode(',',$data).")";
		$sql .= " GROUP BY cp.category_id ORDER BY name";
		$query = $this->db->query($sql);
        $result=$query->rows;
        }
        else $result=false;

		return $result;
	}
    public function DeleteTripsCategories()
	{
     $this->db->query("DELETE FROM " . DB_PREFIX . "trips_categories ");	
	}
    public function addTripsQuestionnaire($data)
	{
		$this->DeleteTripsQuestionnaire();
		if (isset($data)) {
			foreach($data as $option)
			$this->db->query("INSERT INTO " . DB_PREFIX . "trips_questionnaire SET option_id = '" . (int)$option . "'");
			}	
        
	}
    public function getTripsquestionnaireIDs() 
    {
		$query = $this->db->query("SELECT option_id FROM " . DB_PREFIX . "trips_questionnaire ");
        $optionsIDs=array();
        foreach($query->rows as $option_id){
            $optionsIDs[]=$option_id['option_id'];
        }
		return $optionsIDs;
	}
    public function getTripsQuestionnaire($data=[]) {
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
    public function DeleteTripsQuestionnaire()
	{
     $this->db->query("DELETE FROM " . DB_PREFIX . "trips_questionnaire");	
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
    public function IsTrip($product_id) {
        $query = $this->db->query("SELECT product_id FROM " . DB_PREFIX . "trips_product WHERE product_id = ".$product_id." LIMIT 1");
         if ($query->row) return true;
         else return false;
     }
     public function getTripProduct($product_id) {
        $language_id = $this->config->get('config_language_id');
		$query = $this->db->query("SELECT tp.pickup_point,tp.destination_point,
                                   tp.min_no_seats,tp.max_no_seats,tp.from_date,tp.to_date,time, DATEDIFF(tp.to_date,tp.from_date) As duration,
                                   (SELECT name FROM " . DB_PREFIX . "geo_area_locale ga WHERE ga.area_id = tp.area_id AND ga.lang_id = '" . (int)$language_id . "') AS area
                                  
         FROM " . DB_PREFIX . "trips_product tp WHERE product_id = '" . (int)$product_id . "'");
		
		return $query->row;
	}
    public function isTripsAppInstalled()
    {
        if(\Extension::isInstalled('trips') &&$this->config->get('trips')['status']==1) return true;
        else return false;
    }


        /**
     * Get all sellers from database
     *
     * @param array $data
     * @param array $sort
     * @param array $cols
     *
     * @return array
     */
   
}
