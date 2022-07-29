<?php

class ModelShippingEsnad extends Model
{
    /**
     * Shipping method settings key.
     *
     * @var string
     */
    protected $settingsGroup = 'esnad';

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

    public function addShipmentDetails($orderId, $details)
    {
        $query = $fields = [];

        $query[] = 'INSERT INTO  ' . DB_PREFIX . 'shipments_details SET';
        $fields[] = 'order_id="' . $orderId . '"';
        $fields[] = 'details=\'' . json_encode($details,JSON_UNESCAPED_UNICODE) . '\'';
        $fields[] = 'shipment_operator="esnad"';
        $query[] = implode(', ', $fields);
        $this->db->query(implode(' ', $query));
    }

    public function getShipmentDetails($orderId)
    {
       $result = $this->db->query("SELECT * FROM  " . DB_PREFIX . "shipments_details where `order_id` = $orderId AND `shipment_operator` = 'esnad' ");

       return $result->row;
      
    }


    public function deleteShipment($orderId)
    {
        $result = $this->db->query("DELETE FROM  " . DB_PREFIX . "shipments_details where `order_id` = $orderId AND `shipment_operator` = 'esnad' ");

        return $result->row;

    }
    public function getProductWeight($product_id)
    {
        $result = $this->db->query("SELECT weight, weight_class_id FROM " . DB_PREFIX . "product WHERE product_id = '" . $product_id . "'");

        return $result->row;

    }
    public function install()
    {
        $this->create_cities_table();
        $this->insertCities();
    }
    public function uninstall()
    {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "esnad_cities`;");
    }

    public function getAllCities()
    {
        $result = $this->db->query("SELECT * FROM `" . DB_PREFIX . "esnad_cities` ORDER BY `name` ");

        return $result->rows;
    }

    public function getEsnadCity($esnadCityId)
    {
        $result = $this->db->query("SELECT * FROM  `" . DB_PREFIX . "esnad_cities` WHERE `esnad_city_id` = ".$esnadCityId  );

        return $result->row;

    }


    private function create_cities_table()
    {
        $this->db->query("
        CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "esnad_cities` 
        (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(200) NOT NULL ,
            `esnad_city_id` int(11) NOT NULL ,
            PRIMARY KEY (`id`)
        ) 
        ENGINE=InnoDB DEFAULT COLLATE=utf8_general_ci;");

    }
    private function insertCities()
    {
        $query = 'INSERT INTO  `' . DB_PREFIX . 'esnad_cities` (`name`,`esnad_city_id`) VALUES ';

        $query .= "('Abha', '25978'),('Seihat', '26052'),('Abqaiq', '26040'),('Othmanyah', '232823'),('Abu Arish', '26078'),('Al Aflaj', '232797'),('M Ahad Al Masarihah', '26080'),('Ahad Rufaidah', '25980'),('Ain Dar', '232801'),";
        $query .= "('Al Hassa', '232843'),('Anak', '26026'),";
        $query .= "('Asfan', '232729'),('Ayn Fuhayd', '232745'),('Al Bada', '232845'),";
        $query .= "('Madinah', '25874'),('Badaya', '25894'),('Bahara', '26110'),('Balasmar', '232647'),('Jaaraneh', '232703'),('Bareq', '232649'),('Batha', '232805'),";
        $query .= "('Horaimal', '232763'),('Bisha', '26084'),('Bukeiriah', '25896'),('Buraydah', '25908'),('Damad', '26088'),('Dammam', '25998'),";
        $query .= "('Ad Darb', '232681'),('Mahad Al Dahab', '232695'),('Ad Dilam', '25940'),('Dhahran', '26038'),('Dhahran Al Janoob', '25992'),('Al Dalemya', '232741'),";
        $query .= "('Duba', '26142'),('Nwariah', '232705'),('Dariyah', '463208'),('Farasan', '232683'),('Alghat', '232779'),('Gizan', '26090'),";
        $query .= "('Halat Ammar', '463195'),('Haqil', '26144'),('Harjah', '232651'),('Haweyah', '232809'),('Hinakeya', '232691'),('Hofuf', '26004'),";
        $query .= "('Jeddah', '26112'),('Al Jubail', '26006'),('Jumum', '26100'),('Khaibar', '232693'),('Khamis Mushait', '25988'),('Al Kharj', '25950'),";
        $query .= "('Al Khobar', '26012'),('Khulais', '232731'),('Al Laith', '232723'),('Al Ardah', '463207'),('Majma', '25952'),";
        $query .= "('Midinhab', '25926'),('Mubaraz', '232815'),";
        $query .= "('Riyadh Al Khabra', '232751'),('Najran', '26124'),('Namas', '25984'),('Onaiza', '25910'),('Oula', '25880'),('Oyaynah', '232765'),";
        $query .= "('Al Qarah', '232825'),('Qatif', '26016'),";
        $query .= "('Uqlat Al Suqur', '232755'),('Zulfi', '25962'),('Bader', '25882'),('AlRass', '25904'),('Ras Tanura', '232831'),('Rejal Almaa', '232659'),('Dereiyeh', '25942'),('Remah', '25968'),('Riyadh', '25958'),('Sabt Al Alayah', '232661'),('Sabya', '26092'),";
        $query .= "('Safwa', '26048'),('Nimran', '245212'),('Salwa', '232835'),('Samtah', '26094'),('Sarat Obeida', '232663'),('Shaqra', '25970'),('Sharourah', '26126'),";
        $query .= "('Tabuk', '26146'),('Taif', '26108'),('Tanomah', '232665'),('Tarout', '26054'),('Tatleeth', '232667'),('Tayma', '26148'),('Alhada', '232739'),";
        $query .= "('Mastura', '232733'),('Yanbu', '25886'),('Makkah', '26114'),('Thuwal', '232715'),('Turba', '26120'),('Udhailiyah', '232841'),('Majarda', '232655'),('At Taniem', '463005'),";
        $query .= "('Umluj', '245214'),('Mohayel Aseer', '232657'),('Uyun', '26024'),('Salbookh', '232771'),('Wadeien', '232669'),('Al Wajh', '26138'),('Zahban', '232737')";

        $this->db->query($query);

    }

}
