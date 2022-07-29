<?php

class ModelShippingOgo extends Model {

    public function install() {
        $this->createAreasTable();
        $this->insertOgoAreas();
        $this->addOgoRequiredFields();
    }

    public function uninstall() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ogo_shipping_areas`;");
    }

    public function getOgoDeliveryVehicles() {

        $this->load->language('shipping/ogo');

        $deliveryVehicles = [
            'CAR' => $this->language->get('delivery_vehicles_car'),
            'BIKE' => $this->language->get('delivery_vehicles_bike'),
            'VAN' => $this->language->get('delivery_vehicles_van')
        ];

        return $deliveryVehicles;
    }

    public function getOgoPaymentType() {
        $this->load->language('shipping/ogo');

        $paymentTypes = [
            'COD' => $this->language->get('payment_method_cod'),
            'KNET' => $this->language->get('payment_method_knet'),
            'CREDITCARD' => $this->language->get('payment_method_creditcard')
        ];

        return $paymentTypes;
    }

    public function getOgoAreas() {
        $query = "SELECT * FROM `" . DB_PREFIX . "ogo_shipping_areas` where `language` = '" . $this->config->get('config_admin_language') . "';";
        $data = $this->db->query($query);
        return $data->rows;
    }

    public function getOgoFields($order_id) {
        $field_data = array();
        $lang_id = $this->config->get('config_language_id') ? (int) $this->config->get('config_language_id') : 1;

        $query = $this->db->query("SELECT ocf.value, ocf.section, qfd.field_title, qf.field_type,qf.field_type_name FROM " . DB_PREFIX . "order_custom_fields ocf
                                   LEFT JOIN " . DB_PREFIX . "qchkout_fields qf ON (ocf.field_id = qf.id) 
                                   LEFT JOIN " . DB_PREFIX . "qchkout_fields_description qfd ON (ocf.field_id = qfd.field_id) 
                                   WHERE order_id = '" . (int) $order_id . "'
                                   AND qfd.language_id = " . $lang_id);

        if (!$query->num_rows) {
            return false;
        }
        $skipTypes = ['label'];

        foreach ($query->rows as $field) {
            if ($field['field_type'] == 'checkbox')
                $field['value'] = ($field['value']) ? 'Yes' : 'No';

            if (in_array($field['field_type'], $skipTypes))
                continue;

            $field_data[$field['section']][] = $field;
        }

        return $field_data;
    }

    public function addShipmentDetails($orderId, $details, $status) {
        $query = $fields = [];

        $query[] = 'INSERT INTO  ' . DB_PREFIX . 'shipments_details SET';
        $fields[] = 'order_id="' . $orderId . '"';
        $fields[] = 'details=\'' . json_encode($details, JSON_UNESCAPED_UNICODE) . '\'';
        $fields[] = 'shipment_status="' . $status . '"';
        $fields[] = 'shipment_operator="ogo"';
        $query[] = implode(', ', $fields);
        $this->db->query(implode(' ', $query));
    }

    public function getShipmentDetails($orderId) {
        $result = $this->db->query("SELECT * FROM  " . DB_PREFIX . "shipments_details where `order_id` = $orderId AND `shipment_operator` = 'ogo' ");

        return $result->row;
    }

    private function createAreasTable() {
        $this->db->query("
        CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ogo_shipping_areas` 
        (
            `ogo_shipping_area_id` int(11) NOT NULL AUTO_INCREMENT,
            `area_id` int(11) NOT NULL ,
            `name` varchar(200) NOT NULL ,
            `language` varchar(3) NOT NULL ,
            PRIMARY KEY (`ogo_shipping_area_id`)
        ) 
        ENGINE=InnoDB DEFAULT COLLATE=utf8_general_ci;");
    }

    private function insertOgoAreas() {
        $query = "INSERT IGNORE INTO `" . DB_PREFIX . "ogo_shipping_areas` VALUES (1,124,'Auha Island','en'),(2,125,'Kubbar Island','en'),(3,123,'Mischan Island','en'),(4,106,'Mansouriya','en'),(5,114,'Nuzha','en'),(6,101,'Dasman','en'),(7,127,'Umm Al-Maradim Island','en'),(8,126,'Qaruh Island','en'),(9,111,'Qadsiya','en'),(10,103,'Mirqab','en'),(11,112,'Daiya','en'),(12,130,'Shuwaikh Industrial-3','en'),(13,202,'Adailiya','en'),(14,118,'Shuwaikh Industrial-2','en'),(15,107,'Dasma','en'),(16,109,'Shamiya','en'),(17,214,'Yarmouk','en'),(18,110,'Abdulla Al-Salem','en'),(19,201,'Khaldiya','en'),(20,128,'Umm Al-Namel Island','en'),(21,203,'Rawda','en'),(22,116,'Kifan','en'),(23,216,'Surra','en'),(24,104,'Al Sour Gardens','en'),(25,117,'Mubarakiya Camps','en'),(26,401,'Doha','en'),(27,134,'Northwest Sulaibikhat','en'),(28,115,'Shuwaikh','en'),(29,129,'Shuwaikh Industrial-1','en'),(30,120,'Failaka Island','en'),(31,455,'Sulaibikhat Cemetery','en'),(32,132,'The Sea Front','en'),(33,102,'Sharq','en'),(34,402,'Sulaibikhat','en'),(35,133,'Shuwaikh Port','en'),(36,113,'Faiha','en'),(37,105,'Qibla','en'),(38,446,'Jaber Al-Ahmad','en'),(39,108,'Bnaid Al-Qar','en'),(40,215,'Qortuba','en'),(41,119,'Ghornata','en'),(42,131,'Health Area','en'),(43,345,'Shalehat Doha','en'),(44,426,'Mina Doha','en'),(45,436,'Mubarak Al-Abdullah','en'),(46,235,'Hitteen','en'),(47,231,'Al Bida\'a','en'),(48,230,'Shuhada','en'),(49,209,'Mishrif ','en'),(50,207,'Salmiya','en'),(51,217,'Jabriya','en'),(52,213,'Bayan','en'),(53,237,'Salam','en'),(54,234,'Zahra','en'),(55,253,'Mubarakyia','en'),(56,229,'Ministries Area','en'),(57,204,'Hawalli','en'),(58,236,'Al-Siddiq','en'),(59,208,'Rumaithiya','en'),(60,210,'Salwa','en'),(61,205,'Shaab','en'),(62,239,'Anjafa','en'),(63,342,'Shalehat Zoor','en'),(64,359,'Khiran City','en'),(65,439,'Fahad Al-Ahmad','en'),(66,302,'Riqqa','en'),(67,340,'Jaber Al-Ali','en'),(68,338,'South-Sabahiya','en'),(69,433,'Sulaibyia','en'),(70,316,'Dhaher','en'),(71,303,'Hadiya','en'),(72,301,'Egaila','en'),(73,459,'Al shadadyia Industrial','en'),(74,333,'North Ahmadi','en'),(75,330,'South Ahmadi','en'),(76,432,'Janobyia Aljawakheer','en'),(77,312,'Ali Subah Al-Salem','en'),(78,309,'Sabahiya','en'),(79,314,'East Ahmadi','en'),(80,349,'Shalehat Mina Abdullah','en'),(81,320,'Mina Abdulla','en'),(82,447,'kabd Agricultural','en'),(83,356,'Sabah Al-Ahmad','en'),(84,321,'Ahmadi Governorate Desert','en'),(85,350,'Rajim Khashman ','en'),(86,311,'Shuaiba Industrial esterly','en'),(87,329,'Al-Nuwaiseeb','en'),(88,351,'Sabah Al-Ahmad Al-marine','en'),(89,325,'Zoor','en'),(90,315,'Wafra','en'),(91,357,'Sabah Al-Ahmad Services','en'),(92,458,'Sabah Al-Ahmad 6','en'),(93,354,'Sabah Al-Ahmad 3','en'),(94,317,'Shalehat Al-Nuwaiseeb','en'),(95,318,'Shalehat Al-Khiran','en'),(96,353,'Sabah Al-Ahmad 2','en'),(97,355,'Sabah Al-Ahmad 4','en'),(98,358,'Sabah Al-Ahmad Investment','en'),(99,352,'Sabah Al-Ahmad 1','en'),(100,319,'New Wafra','en'),(101,307,'Abu Halifa','en'),(102,434,'Shuaiba Industrial Western','en'),(103,306,'Mahboula','en'),(104,453,'Mina Abdullah Refinery','en'),(105,310,'Fahaheel','en'),(106,454,'Mina Al-Ahmadi Refinery','en'),(107,344,'Shalehat Bneder','en'),(108,337,'Wafra Farms ','en'),(109,332,'Middle of Ahmadi','en'),(110,308,'Mangaf','en'),(111,343,'Shalehat Jlea\'a','en'),(112,305,'Al-Fintas','en'),(113,346,'Shalehat Dba\'ayeh','en'),(114,322,'Magwa','en'),(115,909,'South Al Mutlaa 9','en'),(116,441,'Jawakher Al Jahra','en'),(117,405,'Naeem','en'),(118,435,'Nahda','en'),(119,408,'Sulaibiya Industrial 1','en'),(120,429,'Nasseem','en'),(121,440,'Kaerawan','en'),(122,407,'Sulaibiya','en'),(123,404,'Qasr ','en'),(124,444,'North West Jahra','en'),(125,437,'Saad Al-Abdulla City','en'),(126,417,'Kabd','en'),(127,348,'Shalehat Subiya','en'),(128,904,'South Al Mutlaa 4','en'),(129,442,'Bhaith','en'),(130,416,'Salmy','en'),(131,906,'South Al Mutlaa 6','en'),(132,908,'South Al Mutlaa 8','en'),(133,920,'South Al Mutlaa 10','en'),(134,911,'South','en'),(135,409,'Abdally','en'),(136,421,'Al Sheqaya','en'),(137,456,'South Amghara','en'),(138,122,'Bubyan Island','en'),(139,424,'Sulaibiya Agricultural','en'),(140,418,'Bar Al-Jahra Governorate','en'),(141,419,'Taima','en'),(142,903,'South Al Mutlaa 3','en'),(143,907,'South Al Mutlaa 7','en'),(144,451,'South Al Mutlaa','en'),(145,901,'South Al Mutlaa 1','en'),(146,902,'South Al Mutlaa 2','en'),(147,427,'Oyoun','en'),(148,413,'Kazima','en'),(149,403,'Jahra','en'),(150,423,'Sulaibiya Industrial 2','en'),(151,410,'Amghara Industrial','en'),(152,448,'Al Naayem','en'),(153,347,'Shalehat Kazima','en'),(154,422,'Subiya','en'),(155,905,'South Al Mutlaa 5','en'),(156,912,'South Al Mutlaa 12','en'),(157,431,'Jahra-Industrial','en'),(158,121,'Warba Island','en'),(159,420,'Waha','en'),(160,428,'Jahra Camps','en'),(161,415,'Umm Al-Aish','en'),(162,414,'Rawdatain','en'),(163,412,'Al Mutlaa','en'),(164,244,'Ardhiya 6','en'),(165,445,'Ardhiya Herafiya','en'),(166,246,'Dajeej','en'),(167,222,'Omariya','en'),(168,224,'Riggai','en'),(169,242,'Ardhiya 4','en'),(170,220,'Farwaniya','en'),(171,241,'Sabah Al-Nasser','en'),(172,223,'Rabiya','en'),(173,243,'Ashbeliah','en'),(174,226,'Ardhiya','en'),(175,225,'Andalus','en'),(176,252,'Khaitan','en'),(177,457,'Sabah Al-Salem University City','en'),(178,438,'Abdullah Mubarak Al-Sabah','en'),(179,450,'West Abdullah Al-Mubarak','en'),(180,221,'Rai','en'),(181,228,'Jleeb Al-Shiyoukh','en'),(182,247,'Rehab','en'),(183,227,'Ferdous','en'),(184,233,'International Airport','en'),(185,232,'Subhan Industrial','en'),(186,326,'Abu Ftaira','en'),(187,251,'Mubarak Al-Kabeer','en'),(188,443,'West Abu Ftirah Hirafyia','en'),(189,250,'Al-Qurain','en'),(190,249,'Al-Qusour','en'),(191,212,'Sabah Al-Salem','en'),(192,206,'Al Masayel','en'),(193,324,'Abu Hassaniah','en'),(194,245,'Wista','en'),(195,211,'Messila','en'),(196,430,'Al-Fnaitees','en'),(197,248,'Al-Adan','en'),(198,124,'جزيرة عوهة ','ar'),(199,125,'جزيرة كبر ','ar'),(200,123,'جزيرة مسكان ','ar'),(201,106,'المنصورية ','ar'),(202,114,'النزهة ','ar'),(203,101,'دسمان ','ar'),(204,127,'جزيرة أم المرادم ','ar'),(205,126,'جزيرة قاروه ','ar'),(206,111,'القادسية ','ar'),(207,103,'المرقاب ','ar'),(208,112,'الدعية ','ar'),(209,130,'الشويخ الصناعية 3 ','ar'),(210,202,'العديلية ','ar'),(211,118,'الشويخ الصناعية 2 ','ar'),(212,107,'الدسمة ','ar'),(213,109,'الشامية ','ar'),(214,214,'اليرموك ','ar'),(215,110,'ضاحية عبدالله السالم ','ar'),(216,201,'الخالدية ','ar'),(217,128,'جزيرة أم النمل ','ar'),(218,203,'الروضة ','ar'),(219,116,'كيفان ','ar'),(220,216,'السرة ','ar'),(221,104,'حدائق السور ','ar'),(222,117,'معسكرات المباركية ','ar'),(223,401,'الدوحة ','ar'),(224,134,'شمال غرب الصليبيخات ','ar'),(225,115,'الشويخ ','ar'),(226,129,'الشويخ الصناعية 1 ','ar'),(227,120,'جزيرة فيلكا ','ar'),(228,455,'مقبرة الصليبيخات ','ar'),(229,132,'الواجهة البحرية ','ar'),(230,102,'شرق ','ar'),(231,402,'الصليبيخات ','ar'),(232,133,'المنطقة الحرة ','ar'),(233,113,'الفيحاء ','ar'),(234,105,'القبلة ','ar'),(235,446,'جابر الأحمد ','ar'),(236,108,'بنيد القار ','ar'),(237,215,'قرطبة ','ar'),(238,119,'غرناطة ','ar'),(239,131,'المنطقة الصحية ','ar'),(240,345,'شاليهات الدوحة ','ar'),(241,426,'ميناء الدوحة ','ar'),(242,436,'مبارك العبدالله ','ar'),(243,235,'حطين ','ar'),(244,231,'البدع ','ar'),(245,230,'الشهداء ','ar'),(246,209,'مشرف ','ar'),(247,207,'السالمية ','ar'),(248,217,'الجابرية ','ar'),(249,213,'بيان ','ar'),(250,237,'السلام ','ar'),(251,234,'الزهراء ','ar'),(252,253,'المباركية ','ar'),(253,229,'منطقة الوزارات ','ar'),(254,204,'حولي ','ar'),(255,236,'الصديق ','ar'),(256,208,'الرميثية ','ar'),(257,210,'سلوى ','ar'),(258,205,'الشعب ','ar'),(259,239,'أنجفة ','ar'),(260,342,'شاليهات الزور ','ar'),(261,359,'الخيران السكنية ','ar'),(262,439,'فهد الأحمد ','ar'),(263,302,'الرقة ','ar'),(264,340,'ضاحية جابر العلي ','ar'),(265,338,'جنوب الصباحية ','ar'),(266,433,'الصليبية الصناعية 3 ','ar'),(267,316,'الظهر ','ar'),(268,303,'هدية ','ar'),(269,301,'العقيلة ','ar'),(270,459,'الشدادية الصناعية ','ar'),(271,333,'شمال الأحمدي ','ar'),(272,330,'جنوب الأحمدي ','ar'),(273,432,'الجنوبية الجواخير ','ar'),(274,312,'علي صباح السالم ','ar'),(275,309,'الصباحية ','ar'),(276,314,'شرق الأحمدي ','ar'),(277,349,'شاليهات ميناء عبدالله ','ar'),(278,320,'ميناء عبدالله ','ar'),(279,447,'كبد الزراعية ','ar'),(280,356,'صباح الأحمد 5 ','ar'),(281,321,'بر محافظة الأحمدي ','ar'),(282,350,'رجم خشمان ','ar'),(283,311,'الشعيبة الصناعية الشرقية ','ar'),(284,329,'النويصيب ','ar'),(285,351,'صباح الاحمد البحرية ','ar'),(286,325,'الزور ','ar'),(287,315,'الوفرة ','ar'),(288,357,'صباح الأحمد الخدمية ','ar'),(289,458,'صباح الأحمد 6 ','ar'),(290,354,'صباح الأحمد 3 ','ar'),(291,317,'شاليهات النويصيب ','ar'),(292,318,'شاليهات الخيران ','ar'),(293,353,'صباح الأحمد 2 ','ar'),(294,355,'صباح الأحمد 4 ','ar'),(295,358,'صباح الأحمد استثمارية ','ar'),(296,352,'صباح الأحمد 1 ','ar'),(297,319,'الوفرة الجديدة ','ar'),(298,307,'أبو حليفة ','ar'),(299,434,'الشعيبه الصناعيه الغربيه ','ar'),(300,306,'المهبولة ','ar'),(301,453,'مصفاة ميناء عبدالله ','ar'),(302,310,'الفحيحيل ','ar'),(303,454,'مصفاة ميناء الأحمدي ','ar'),(304,344,'شاليهات بنيدر ','ar'),(305,337,'مزارع الوفرة  ','ar'),(306,332,'وسط الأحمدي ','ar'),(307,308,'المنقف ','ar'),(308,343,'شاليهات الجليعة ','ar'),(309,305,'الفنطاس ','ar'),(310,346,'شاليهات الضباعية ','ar'),(311,322,'المقوع ','ar'),(312,909,'جنوب المطلاع 9 ','ar'),(313,441,'جواخير الجهراء ','ar'),(314,405,'النعيم ','ar'),(315,435,'النهضة ','ar'),(316,408,'الصليبية الصناعية 1 ','ar'),(317,429,'النسيم ','ar'),(318,440,'القيروان ','ar'),(319,407,'الصليبية الشعبية ','ar'),(320,404,'القصر ','ar'),(321,444,'شمال غرب الجهراء ','ar'),(322,437,'مدينة سعد العبدالله ','ar'),(323,417,'كبد ','ar'),(324,348,'شاليهات الصبية ','ar'),(325,904,'جنوب المطلاع 4 ','ar'),(326,442,'البحيث ','ar'),(327,416,'السالمي ','ar'),(328,906,'جنوب المطلاع 6 ','ar'),(329,908,'جنوب المطلاع 8 ','ar'),(330,920,'جنوب المطلاع 10 ','ar'),(331,911,'جنوب المطلاع 11 ','ar'),(332,409,'العبدلي ','ar'),(333,421,'الشقايا ','ar'),(334,456,'جنوب أمغره ','ar'),(335,122,'جزيرة بوبيان ','ar'),(336,424,'الصليبية الزراعية ','ar'),(337,418,'بر محافظة الجهراء ','ar'),(338,419,'تيماء ','ar'),(339,903,'جنوب المطلاع 3 ','ar'),(340,907,'جنوب المطلاع 7 ','ar'),(341,451,'جنوب المطلاع ','ar'),(342,901,'جنوب المطلاع 1 ','ar'),(343,902,'جنوب المطلاع 2 ','ar'),(344,427,'العيون ','ar'),(345,413,'كاظمة ','ar'),(346,403,'الجهراء ','ar'),(347,423,'الصليبية الصناعية 2 ','ar'),(348,410,'أمغرة الصناعية ','ar'),(349,448,'النعايم ','ar'),(350,347,'شاليهات كاظمة ','ar'),(351,422,'الصبية ','ar'),(352,905,'جنوب المطلاع 5 ','ar'),(353,912,'جنوب المطلاع 12 ','ar'),(354,431,'الجهراء الصناعية ','ar'),(355,121,'جزيرة وربة ','ar'),(356,420,'الواحة ','ar'),(357,428,'معسكرات الجهراء ','ar'),(358,415,'ام العيش ','ar'),(359,414,'الروضتين ','ar'),(360,412,'المطلاع ','ar'),(361,244,'العارضية 6 ','ar'),(362,445,'العارضية الحرفية ','ar'),(363,246,'ضجيج الطائرات ','ar'),(364,222,'العمرية ','ar'),(365,224,'الرقعي ','ar'),(366,242,'العارضية 4 ','ar'),(367,220,'الفروانية ','ar'),(368,241,'صباح الناصر ','ar'),(369,223,'الرابية ','ar'),(370,243,'أشبيلية ','ar'),(371,226,'العارضية ','ar'),(372,225,'الأندلس ','ar'),(373,252,'خيطان ','ar'),(374,457,'مدينة صباح السالم الجامعية ','ar'),(375,438,'عبدالله مبارك الصباح ','ar'),(376,450,'غرب عبدالله المبارك ','ar'),(377,221,'الري ','ar'),(378,228,'جليب الشيوخ ','ar'),(379,247,'الرحاب ','ar'),(380,227,'الفردوس ','ar'),(381,233,'المطار الدولي ','ar'),(382,232,'صبحان الصناعية ','ar'),(383,326,'ضاحية أبو فطيرة ','ar'),(384,251,'مبارك الكبير ','ar'),(385,443,'غرب أبو فطيرة الحرفية ','ar'),(386,250,'القرين ','ar'),(387,249,'القصور ','ar'),(388,212,'صباح السالم ','ar'),(389,206,'المسايل ','ar'),(390,324,'أبو الحصانية ','ar'),(391,245,'المنطقة الوسطى ','ar'),(392,211,'المسيلة ','ar'),(393,430,'ضاحية الفنيطيس ','ar'),(394,248,'العدان ','ar');";
        $this->db->query($query);
    }

    private function addOgoRequiredFields() {
//        $checkFieldTypeName = $this->checkIfFieldsExsits('field_type_name');
//        $checkFieldCanDelete = $this->checkIfFieldsExsits('can_delete');

//        if (empty($checkFieldTypeName)) {
//            $addFieldTypeNameQuery = "ALTER TABLE `" . DB_PREFIX . "qchkout_fields` ADD COLUMN field_type_name varchar(20) null;";
//            $this->db->query($addFieldTypeNameQuery);
//        }
//
//        if (empty($checkFieldCanDelete)) {
//            $addCanDeleteQuery = "ALTER TABLE `" . DB_PREFIX . "qchkout_fields` ADD COLUMN can_delete char(10) null;";
//            $this->db->query($addCanDeleteQuery);
//        }

        if (empty($this->checkIfFieldsDataExsits())) {
            $dataArray = [
                [
                    'section' => 'payment_address',
                    'field_type' => 'text',
                    'field_type_name' => 'block_id',
                    'can_delete' => 'no',
                    'filed_description' => [
                        ['language_id' => 1, 'field_title' => 'Block Number'],
                        ['language_id' => 2, 'field_title' => 'رقم المنطقة'],
                    ]
                ],
                [
                    'section' => 'shipping_address',
                    'field_type' => 'text',
                    'field_type_name' => 'block_id',
                    'can_delete' => 'no',
                    'filed_description' => [
                        ['language_id' => 1, 'field_title' => 'Block Number'],
                        ['language_id' => 2, 'field_title' => 'رقم المنطقة'],
                    ]
                ],
                [
                    'section' => 'payment_address',
                    'field_type' => 'text',
                    'field_type_name' => 'street_id',
                    'can_delete' => 'no',
                    'filed_description' => [
                        ['language_id' => 1, 'field_title' => 'Street Number'],
                        ['language_id' => 2, 'field_title' => 'رقم الشارع'],
                    ]
                ],
                [
                    'section' => 'shipping_address',
                    'field_type' => 'text',
                    'field_type_name' => 'street_id',
                    'can_delete' => 'no',
                    'filed_description' => [
                        ['language_id' => 1, 'field_title' => 'Street Number'],
                        ['language_id' => 2, 'field_title' => 'رقم الشارع'],
                    ]
                ],
                [
                    'section' => 'payment_address',
                    'field_type' => 'text',
                    'field_type_name' => 'house_id',
                    'can_delete' => 'no',
                    'filed_description' => [
                        ['language_id' => 1, 'field_title' => 'House Number'],
                        ['language_id' => 2, 'field_title' => 'رقم المنزل'],
                    ]
                ],
                [
                    'section' => 'shipping_address',
                    'field_type' => 'text',
                    'field_type_name' => 'house_id',
                    'can_delete' => 'no',
                    'filed_description' => [
                        ['language_id' => 1, 'field_title' => 'House Number'],
                        ['language_id' => 2, 'field_title' => 'رقم المنزل'],
                    ]
                ]
            ];

            foreach ($dataArray as $data) {
                $fieldsInsertQuery = "INSERT IGNORE INTO " . DB_PREFIX . "qchkout_fields SET `section` = '$data[section]',`field_type` = '$data[field_type]',`field_type_name` = '$data[field_type_name]',`can_delete` = '$data[can_delete]';";
                $this->db->query($fieldsInsertQuery);

                $field_id = $this->db->getLastId();

                foreach ($data['filed_description'] as $filedDescription) {
                    $fieldsDescriptionInsertQuery = "INSERT IGNORE INTO " . DB_PREFIX . "qchkout_fields_description SET `field_id` = '$field_id',`language_id` = '$filedDescription[language_id]',`field_title` = '$filedDescription[field_title]';";
                    $this->db->query($fieldsDescriptionInsertQuery);
                }
            }
        }
    }

//    private function checkIfFieldsExsits($field) {
//        $query = "SELECT * FROM information_schema.columns WHERE table_name = 'qchkout_fields' AND column_name = '$field';";
//        $data = $this->db->query($query);
//
//        return $data->row;
//    }

    private function checkIfFieldsDataExsits() {
        $query = "SELECT * FROM `" . DB_PREFIX . "qchkout_fields` WHERE field_type_name in ('street_id','house_id','block_id');";
        $data = $this->db->query($query);

        return $data->row;
    }

}
