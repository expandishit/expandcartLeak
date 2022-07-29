<?php

class ModelShippingShipaDelivery extends Model
{
    /**
     * Shipping method settings key.
     *
     * @var string
     */
    protected $settingsGroup = 'shipa_delivery';

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * update the module settings.
     *
     * @param array $inputs
     *
     * @return void
     */
    public function updateSettings($inputs)
    {
        $this->load->model('setting/setting');

        $this->model_setting_setting->insertUpdateSetting(
            'shipping', [$this->settingsGroup => $inputs]
        );
    }

    /**
     * Get payment settings.
     *
     * @return array|null
     */
    public function getSettings()
    {
        return $this->config->get($this->settingsGroup);
    }


    public function getErrors()
    {
        return [1, 2];
    }

    public function addShipmentDetails($orderId, $details, $status)
    {
        $query = $fields = [];

        $query[] = 'INSERT INTO  ' . DB_PREFIX . 'shipments_details SET';
        $fields[] = 'order_id="' . $orderId . '"';
        $fields[] = 'details=\'' . json_encode($details,JSON_UNESCAPED_UNICODE) . '\'';
        $fields[] = 'shipment_status="' . $status . '"';
        $fields[] = 'shipment_operator="shipa_delivery"';
        $query[] = implode(', ', $fields);
        $this->db->query(implode(' ', $query));
    }

    public function getShipmentDetails($orderId)
    {
       $result = $this->db->query("SELECT * FROM  " . DB_PREFIX . "shipments_details where `order_id` = $orderId AND `shipment_operator` = 'shipa_delivery' ");

       return $result->row;
      
    }

    public function deleteShipment($orderId)
    {
        $result = $this->db->query("DELETE FROM  " . DB_PREFIX . "shipments_details where `order_id` = $orderId AND `shipment_operator` = 'shipa_delivery' ");

        return $result->row;

    }

    public function install()
    {
        $this->create_cities_table();
        $this->insertCities();
    }

    public function uninstall()
    {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "shipa_delivery_cities`;");
    }

    public function getAllCities()
    {
        $isTableExists = $this->db->query('SHOW TABLES LIKE "%shipa_delivery_cities%"');
        if(!$isTableExists->row) {
            $this->create_cities_table();
            $this->insertCities();
        }
        $result = $this->db->query("SELECT * FROM `" . DB_PREFIX . "shipa_delivery_cities` ORDER BY `name` ");

        return $result->rows;
    }
    private function create_cities_table()
    {
        $this->db->query("
        CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "shipa_delivery_cities` 
        (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(200) NOT NULL ,
            `city_value` varchar(200) NOT NULL ,
            PRIMARY KEY (`id`)
        ) 
        ENGINE=InnoDB DEFAULT COLLATE=utf8_general_ci;");

    }
    private function insertCities()
    {
        $isTableExists = $this->db->query('SHOW TABLES LIKE "%shipa_delivery_cities%"');

        if(!$isTableExists->row) {
            $this->create_cities_table();
        }
        $query = 'INSERT INTO  `' . DB_PREFIX . 'shipa_delivery_cities` (`name`,`city_value`) VALUES ';

        $query .= "('Dubai', 'Dubai'),('Abu Dhabi', 'Abu Dhabi'),('Al Ain', 'Al Ain'),('Ajman', 'Ajman'),('Fujairah', 'Fujairah'),('Ras Al Khaimah', 'Ras Al Khaimah'),('Sharjah', 'Sharjah'),('Umm Al Quwain', 'Umm Al Quwain'),('Kuwait', 'Kuwait'),";
        $query .= "('Kuwait City', 'Kuwait City'),('Hawalli', 'Hawalli'),";
        $query .= "('Farwaniya', 'Farwaniya'),('Mubarak Al-Kabeer', 'Mubarak Al-Kabeer'),('Ahmadi', 'Ahmadi'),";
        $query .= "('Jahra', 'Jahra'),('Riyadh', 'Riyadh'),('Abha', 'Abha'),('Abqaiq', 'Abqaiq'),('Al Hassa', 'Al Hassa'),('Anak', 'Anak'),('Dammam', 'Dammam'),";
        $query .= "('Dhahran', 'Dhahran'),('Hofuf', 'Hofuf'),('Jeddah', 'Jeddah'),('Jubail', 'Jubail'),('Jumum', 'Jumum'),('Khamis Mushait', 'Khamis Mushait'),";
        $query .= "('Kharj', 'Kharj'),('Khobar', 'Khobar'),('Khulais', 'Khulais'),('Madinah', 'Madinah'),('Makkah', 'Makkah'),('Qatif', 'Qatif'),";
        $query .= "('Rabigh', 'Rabigh'),('Ras Tanura', 'Ras Tanura'),('Safwa', 'Safwa'),('Seihat', 'Seihat'),('Taif', 'Taif'),('Alrass', 'Alrass'),";
        $query .= "('Badaya', 'Badaya'),('Bukeiriah', 'Bukeiriah'),('Buraidah', 'Buraidah'),('Hail', 'Hail'),('Jizan', 'Jizan'),('Midinhab', 'Midinhab'),";
        $query .= "('Onaiza', 'Onaiza'),('Qassim', 'Qassim'),('Riyadh Al Khabra', 'Riyadh Al Khabra'),('Sabya', 'Sabya'),('Tabuk', 'Tabuk'),('Al Qunfudhah', 'Al Qunfudhah'),";
        $query .= "('BilJurashi', 'BilJurashi'),('Baha', 'Baha'),('Aqiq', 'Aqiq'),('Mandak', 'Mandak'),('Yanbu', 'Yanbu'),";
        $query .= "('Alghat', 'Alghat'),('Dhurma', 'Dhurma'),('Tayma', 'Tayma'),('Thadek', 'Thadek'),('Thumair', 'Thumair'),('Turaif', 'Turaif'),('Turba', 'Turba'),('Umluj', 'Umluj'),";
        $query .= "('Majma', 'Majma'),('Zulfi', 'Zulfi'),('Najran', 'Najran'),('Hafer Al Batin', 'Hafer Al Batin'),('Qaysoomah', 'Qaysoomah'),('Towal', 'Towal'),";
        $query .= "('Tarut', 'Tarut'),('Mubaraz', 'Mubaraz'),('Unayzah', 'Unayzah'),('Wadi El Dwaser', 'Wadi El Dwaser'),('Wajeh', 'Wajeh'),('Al Wajh', 'Al Wajh'),";
        $query .= "('Ahad Rufaidah', 'Ahad Rufaidah'),('Abu Areish', 'Abu Areish'),('Khafji', 'Khafji'),('Bader', 'Bader'),('Khaibar', 'Khaibar'),('Afif', 'Afif'),('Ahad Masarha', 'Ahad Masarha'),('Al Baha', 'Al Baha'),('Arar', 'Arar'),('Balasmar', 'Balasmar'),('Bisha', 'Bisha'),";
        $query .= "('Daelim', 'Daelim'),('Dawadmi', 'Dawadmi'),('Dereiyeh', 'Dereiyeh'),('Domat Al Jandal', 'Domat Al Jandal'),('Duba', 'Duba'),('Duwadimi', 'Duwadimi'),('Haqil', 'Haqil'),";
        $query .= "('Hareeq', 'Hareeq'),('Hawtat Bani Tamim', 'Hawtat Bani Tamim'),('Horaimal', 'Horaimal'),('Jouf', 'Jouf'),('Khurma', 'Khurma'),('Majarda', 'Majarda'),('Manakh', 'Manakh'),";
        $query .= "('Mohayel Aseer', 'Mohayel Aseer'),('Muhayil', 'MUHAYIL'),('Muzahmiah', 'Muzahmiah'),('Noweirieh', 'Noweirieh'),('Oula', 'Oula'),('Qariya Al Olaya', 'Qariya Al Olaya'),('Qurayat', 'Qurayat'),('Quweiieh', 'Quweiieh'),";
        $query .= "('Rafha', 'Rafha'),('Sakaka', 'Sakaka'),('Samtah', 'Samtah'),('Shaqra', 'Shaqra'),('Sharourah', 'Sharourah'),('Sulaiyl', 'Sulaiyl'),('Tatleeth', 'Tatleeth')";

        $this->db->query($query);

    }
}
