<?php

class ModelShippingR2sLogistics extends Model
{
    /**
     * Shipping method settings key.
     *
     * @var string
     */
    protected $settingsGroup = 'r2s_logistics';

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
        $fields[] = 'shipment_operator="r2s_logistics"';
        $query[] = implode(', ', $fields);
        $this->db->query(implode(' ', $query));
    }

    public function getShipmentDetails($orderId)
    {
       $result = $this->db->query("SELECT * FROM  " . DB_PREFIX . "shipments_details where `order_id` = $orderId AND `shipment_operator` = 'r2s_logistics' ");

       return $result->row;
      
    }

    public function deleteShipment($orderId)
    {
        $result = $this->db->query("DELETE FROM  " . DB_PREFIX . "shipments_details where `order_id` = $orderId AND `shipment_operator` = 'r2s_logistics' ");

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
        $this->create_states_table();
        $this->insertCities();
        $this->insertStates();
    }
    public function uninstall()
    {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "r2s_shipping_cities`;");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "r2s_shipping_states`;");
    }

    public function getAllStates()
    {
        $result = $this->db->query("SELECT * FROM `" . DB_PREFIX . "r2s_shipping_states` ORDER BY `name` ");

        return $result->rows;
    }

    public function getCitiesByStateId($stateId)
    {
        $result = $this->db->query("SELECT * FROM `" . DB_PREFIX . "r2s_shipping_cities` WHERE `r2s_shipping_states_id` = ".(int)$stateId." ORDER BY `name` ");

        return $result->rows;
    }

    private function create_cities_table()
    {
        $this->db->query("
        CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "r2s_shipping_cities` 
        (
            `r2s_shipping_cities_id` int(11) NOT NULL AUTO_INCREMENT,
            `r2s_shipping_states_id` int(11) NOT NULL ,
            `name` varchar(200) NOT NULL ,
            PRIMARY KEY (`r2s_shipping_cities_id`)
        ) 
        ENGINE=InnoDB DEFAULT COLLATE=utf8_general_ci;");

    }
    private function create_states_table()
    {
        $this->db->query("
        CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "r2s_shipping_states` 
        (
            `r2s_shipping_states_id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(200) NOT NULL ,
            `state_code` varchar(200) NOT NULL ,
            PRIMARY KEY (`r2s_shipping_states_id`)
        ) 
        ENGINE=InnoDB DEFAULT COLLATE=utf8_general_ci;");

    }
    private function insertStates()
    {
        $query = 'INSERT INTO  `' . DB_PREFIX . 'r2s_shipping_states` (`name`,`state_code`) VALUES ';
        $query .= "('Cairo','CAIRO'),('Giza','GIZA'),('Alexandria','ALEXANDRIA'),('Qalyubia','QALYUBIA'),";
        $query .= "('Al Meniya','ALMENIYA'),('Al Gharbia','ALGHARBIA'),('Al Monufia','ALMONUFIA'),('Al Beheira','ALBEHEIRA'),";
        $query .= "('Al Sharqia','ALSHARQIA'),('Asyut','ASYUT'),('Al Daqahliya','ALDAQAHLIYA'),('Aswan','ASWAN'),";
        $query .= "('Al Fayoum','ALFAYOUM'),('Suez','SUEZ'),('Luxor','LUXOR'),('New Valley','NEWVALLEY'),";
        $query .= "('Ismailia','ISMAILIA'),('Bani Souaif','BANISOUAIF'),('Damietta','DAMIETTA'),('Qena','QENA'),";
        $query .= "('Kafr El Sheikh','KAFRELSHEIKH'),('Red Sea','REDSEA'),('Matrooh','MATROOH'),('South Sinai','SOUTHSINAI'),";
        $query .= "('North Sinai','NORTHSINAI'),('Port Said','PORTSAID')";
        $this->db->query($query);

    }

    private function insertCities()
    {
       $query = 'INSERT INTO  `' . DB_PREFIX . 'r2s_shipping_cities` (`name`,`r2s_shipping_states_id`) VALUES ';
       $query .= "('10th of Ramdan City',9),('15th of May City',1),('1st Settlement',1),('3rd Settlement',1),";
       $query .= "('5th Settlement',1),('6th of October',2),('Abaseya',1),('Abdeen',1),";
       $query .= "('Abdo Basha',1),('Abees',3),('Abnoub',10),('Abo Korkas',5),";
       $query .= "('Abo Sultan',17),('Abou Al Matamer',8),('Abou Rawash',2),('Abou Teag',10),";
       $query .= "('Abu Hammad',9),('Abu Hummus',8),('Abu Kbeer',9),('Abu Keer',3),('Abu Simbel',12),";
       $query .= "('Abu Swer',17),('Abu Tesht',20),('Abu Zaabal',4),('Aga',11),";
       $query .= "('Agouza',2),('Ahnaseaa',18),('Ain Al Sukhna',14),('Ain Shams',1),";
       $query .= "('Akhmem',22),('Al Amriah',3),('Al Adabya',14),('Al Arish',25),";
       $query .= "('Al Azhar',1),('Al Barageel',2),('Al Beheira',8),('Al Bitash',3),";
       $query .= "('Al Daher',1),('Al Daqahliya',11),('Al Delengat',8),('Al Fayoum',13),";
       $query .= "('Al Gharbia',6),('Al Hasiniya',9),('Al Ibrahimiya',9),('Al Kalaa',1),";
       $query .= "('Al Kasaseen',17),('Al Kasr Al Einy',1),('Al Khanka',4),('Al Kom Al Ahmer',2),";
       $query .= "('Al Mahala Al Kobra',6),('Al Mahmoudiyah',8),('Al Manashi',2),('Al Mansoura',11),";
       $query .= "('Al Matareya',1),('Al Meniya',5),('Al Moatamadia',2),('Al Monib',2),";
       $query .= "('Al Monufia',7),('Al Moski',1),('Al Nahda Al Amria',3),('Al Nobariah',2),";
       $query .= "('Al Rahmaniyah',8),('Al Rehab',1),('Al Riadh',21),('Al Sad Al Aali',12),";
       $query .= "('Al Saf',2),('Al Salam City',1),('Al Salhiya Al Gedida',9),('Al Shareaa Al Gadid',4),";
       $query .= "('Al Sharqia',9),('Al Soyof',3),('Al Suez',14),('Al Wahat',2),";
       $query .= "('Al Zarkah',19),('Al Zeitoun',1),('Alexandria',3),('Almaza',1),";
       $query .= "('Alsanta',6),('Amiria',1),('Aossim',2),('Armant Gharb',15),";
       $query .= "('Armant Sharq',15),('Asafra',3),('Ashmoon',7),('Assuit Elgdeda',10),";
       $query .= "('Aswan',12),('Asyut',10),('Ataka District',14),('Atsa',13),";
       $query .= "('Awaied-Ras Souda',3),('Awlad Saqr',9),('Azarita',3),('Badr City',1),";
       $query .= "('Badrashin',2),('Bahteem',4),('Balteem',21),('Bangar EL Sokar',3),";
       $query .= "('Banha',4),('Bani Mazar',5),('Bani Souaif',18),('Basateen',1),";
       $query .= "('Basyoon',6),('Bebaa',18),('Bela',21),('Belbes',9),";
       $query .= "('Belqas',9),('Berak Alkiaam',2),('Berket Al Sabei',7),('Bolak Al Dakrour',2),";
       $query .= "('Borg El Arab',3),('Borollos',21),('Cairo',1),('City Center',3),('Cornish Al Nile',2),";
       $query .= "('Dahab',25),('Damanhour',8),('Damietta',19),('Dar Al Salam',1),";
       $query .= "('Dar Elsalam',22),('Darb Negm',9),('Dayrout',10),('Dekernes',11),";
       $query .= "('Dermwas',5),('Deshna',20),('Desouq',21),('Dokki',2),";
       $query .= "('Down Town',1),('Draw',12),('Ebshoy',13),('Edfina',8),";
       $query .= "('Edfo',12),('Edko',8),('El Aagamen',13),('El Alamein',24),";
       $query .= "('El Arbeen District',14),('El Badari',10),('El Borg El Kadem',3),('El Dabaa',25),";
       $query .= "('El Fashn',18),('El Ghnayem',10),('El Herafieen',1),('El Kanater EL Khayrya',4),";
       $query .= "('El Karnak',15),('El Kharga',16),('El Khsos',4),('El Klabsha',12),";
       $query .= "('El Korimat',18),('El Korna',15),('EL Marg',1),('El Monshah',22),";
       $query .= "('El Nubariyah',8),('El Oboor',4),('El Qalag',4),('El Qusya',10),";
       $query .= "('El Shorouk',1),('El Sinblaween',11),('El Tahrir',1),('El Tal El Kebir',17),";
       $query .= "('El Wastaa',18),('El-Agamy',3),('Eladwa',5),('Elbalyna',22),";
       $query .= "('Elfath',10),('Elganaien District',14),('Elsalhia Elgdida',17),('Esnaa',15),";
       $query .= "('Etay Al Barud',8),('Ezbet El Nakhl',1),('Faisal',2),('Faqous',9),";
       $query .= "('Fareskor',19),('Farshoot',20),('Fayed',17),('Fooh',21),";
       $query .= "('Fustat',1),('Garden City',1),('Gerga',22),('Gesr Al Suez',1),";
       $query .= "('Ghamrah',1),('Ghena',22),('Giza',2),('Glem',3),";
       $query .= "('Gouna',23),('Hadayek Al Qobah',1),('Hadayek Al Zaiton',1),('Hadayek Helwan',1),";
       $query .= "('Hadayek Maadi',1),('Hadayeq El Ahram',2),('Hamool',21),('Haram',2),";
       $query .= "('Hawamdya',2),('Al Sharqia',9),('Heliopolis',1),('Helmeya',1),";
       $query .= "('Helmiet Elzaitoun',1),('Helwan',1),('Hosh Issa',8),('Hurghada',23),";
       $query .= "('Imbaba',2),('Ismailia',17),('Kafer Abdou',3),('Kafr Alzia',6),";
       $query .= "('Kafr El Dawwar',8),('Kafr El Sheikh',21),('Kafr Saad',19),('Kafr Saqr',9),";
       $query .= "('Kafr Shokr',4),('Katamiah',1),('Kerdasa',1),('Khorshid',3),";
       $query .= "('Kit Kat',2),('Kofooer Elniel',13),('Kom Hamadah',8),('Kom Ombo',12),";
       $query .= "('Luran',3),('Luxor',15),('Maadi',1),('Maadi Degla',1),";
       $query .= "('Maamora',3),('Madinty',1),('Mahtet El-Raml',3),('Malawi',5),";
       $query .= "('Mandara',3),('Manflout',10),('Manial',2),('Manial Al Rodah',1),";
       $query .= "('Manshaa Abdalla',13),('Manshaa Elgamal',13),('Manshia',3),('Mansoureya',2),";
       $query .= "('Manzala',11),('Maragha',22),('Markaz Naser',12),('Marsa Alam',23),";
       $query .= "('Marsa Matrooh',24),('Masaken Sheraton',1),('Mashtool Al Sooq',9),('Matai',5),";
       $query .= "('Meet Ghamr',11),('Meet Nama',4),('Menit El Nasr',11),('Meniya Alqamh',9),";
       $query .= "('Menoof',7),('Metobas',21),('Mghagha',5),('Miami',3),";
       $query .= "('Minya',5),('Mirage City',1),('Misr El Kadima',1),('Mohandessin',2),";
       $query .= "('Mokattam',1),('Mostorod',4),('Muntazah',3),('Nabroo',11),";
       $query .= "('Naga Hamadi',20),('Naqada',20),('Naser',18),('Nasr City',1),";
       $query .= "('Nasr Elnoba',12),('New Bani Souaif',18),('New Cairo',1),('New Damietta',19),";
       $query .= "('New El Marg',1),('New Fayoum',13),('New Maadi',1),('New Nozha',1),";
       $query .= "('New Valley',16),('Neweibaa',25),('Nfeesha',17),('North Sinai',26),";
       $query .= "('Om Bayoumi',4),('Omraneya',2),('Orabi',2),('Port Fouad',27),";
       $query .= "('Port Said',27),('Qaha',4),('Qalyoob',4),('Qalyubia',4),";
       $query .= "('Qantara Gharb',17),('Qantara Sharq',17),('Qeleen',21),('Qena',20),";
       $query .= "('Qism el Giza',2),('Qoos',20),('Qotoor',6),('Qouseir',23),";
       $query .= "('Quesna',7),('Ramsis',1),('Ras El Bar',19),('Ras Ghareb',23),";
       $query .= "('Rashid',8),('Red Sea',23),('Rod El Farag',1),('Roshdy',3),";
       $query .= "('Sadat City',7),('Safaga',23),('Saft El Laban',2),('Sahel Selim',10),";
       $query .= "('Saint Catherine',25),('Sakiat Mekki',2),('Samaloot',5),('Samanood',6),";
       $query .= "('San Stefano',3),('Sanhoor',13),('Saqatlah',22),('Sayeda Zeinab',1),";
       $query .= "('Sedi Bisher',3),('Sedi Gaber',3),('Sedi Kreir',3),('Seedy Salem',21),";
       $query .= "('Serfa',10),('Sersenaa',13),('Shabramant',2),('Sharm Al Sheikh',25),";
       $query .= "('Sheben Alkanater',4),('Shebin El Koom',7),('Sheikh Zayed',2),('Shohada',7),";
       $query .= "('Shoubra Alkhema',4),('Shrbeen',11),('Shubra',1),('Shubrakhit',8),";
       $query .= "('Sidi Abdel Rahman',24),('Smart Village',2),('Smostaa',18),('Smouha',3),";
       $query .= "('Sohag',22),('Sonores',13),('South Sinai',25),('Sporting',3),";
       $query .= "('Srabioom',17),('Stanly',3),('Suez',14),('Taba',25),";
       $query .= "('Tahta',22),('Tala',7),('Talkha',11),('Tameaa',13),";
       $query .= "('Tanta',6),('Tema',22),('Tirsa',2),('Tookh',4),";
       $query .= "('Toor Sinai',25),('Wadi Al Natroun',8),('Warraq',2),('Youssef Sadek',13),";
       $query .= "('Zakazik',9),('Zamalek',1),('Zefta',6),('Zezenya',3),";
       $query .= "('Zohoor District',27)";
       $this->db->query($query);
    }
}
