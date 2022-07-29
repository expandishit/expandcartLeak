<?php
class ModelShippingAymakan extends Model {

	 /**
	  * @const strings API URLs.
	  */
    const BASE_API_TESTING_URL  = 'https://dev.aymakan.com.sa/api/v2/';
    const BASE_API_LIVE_URL     = 'https://aymakan.com.sa/api/v2/';


    public function install(){
      $this->load->model('setting/setting');

      //Add statuses
      $this->model_setting_setting->insertUpdateSetting( 'aymakan', [ 'aymakan_statuses' => 
        [
          0 => 
          [
            'code' => 'AY-0001',
            'status' => 'Submitted',
            'status_ar' => 'تم إصدار بوليصة شحن(لم تستلم]',
          ],
          1 => 
          [
            'code' => 'AY-0002',
            'status' => 'Picked from Collection Point',
            'status_ar' => 'تم إستلام الشحنة',
          ],
          2 => 
          [
            'code' => 'AY-0005',
            'status' => 'Delivered',
            'status_ar' => 'تم التوصيل',
          ],
          3 => 
          [
            'code' => 'AY-0004',
            'status' => 'Out for Delivery',
            'status_ar' => 'الشحنة خارجة للتوصيل',
          ],
          4 => 
          [
            'code' => 'AY-0006',
            'status' => 'Not Delivered',
            'status_ar' => 'لم يتم التوصيل',
          ],
          5 => 
          [
            'code' => 'AY-0008',
            'status' => 'Returned',
            'status_ar' => 'تم إرجاع الشحنة',
          ],
          6 => 
          [
            'code' => 'AY-0009',
            'status' => 'In-Transit',
            'status_ar' => 'في النقل',
          ],
          7 => 
          [
            'code' => 'AY-0010',
            'status' => 'Delayed',
            'status_ar' => 'شحنة مؤجلة',
          ],
          8 => 
          [
            'code' => 'AY-0011',
            'status' => 'Cancelled',
            'status_ar' => 'ملغي',
          ],
          9 => 
          [
            'code' => 'AY-0013',
            'status' => 'Missing info Phone number/ Address',
            'status_ar' => 'معلومات الشحنة غير مكتملة (رقم الهاتف أو العنوان]',
          ],
          10 => 
          [
            'code' => 'AY-22',
            'status' => 'Shipment handed over',
            'status_ar' => 'تم تسليم الشحنة للشركة الشاحنة',
          ],
          11 => 
          [
            'code' => 'AY-0007',
            'status' => 'Cancelled',
            'status_ar' => 'ملغي',
          ],
          12 => 
          [
            'code' => 'AY-0017',
            'status' => 'Received Back in Warehouse',
            'status_ar' => 'تم إرجاع الشحنة لمركز التوزيع',
          ],
          13 => 
          [
            'code' => 'AY-0026',
            'status' => 'Received at Riyadh Warehouse',
            'status_ar' => 'تم الاستلام في مستودع الرياض',
          ],
          14 => 
          [
            'code' => 'AY-0027',
            'status' => 'Received at Qaseem Warehouse',
            'status_ar' => 'تم الاستلام في مستودع القصيم',
          ],
          15 => 
          [
            'code' => 'AY-0028',
            'status' => 'Out for Return to Shipper',
            'status_ar' => 'في طريق ارجاعها الي المرسل',
          ],
          16 => 
          [
            'code' => 'AY-0029',
            'status' => 'Pickup Cancelled',
            'status_ar' => 'تم الغاء استلام الشحنه',
          ],
          17 => 
          [
            'code' => 'AY-0030',
            'status' => 'Received at JED WH',
            'status_ar' => 'تم إستلام الشحنة في مركز توزيع جدة',
          ],
          18 => 
          [
            'code' => 'AY-0032',
            'status' => 'Pending',
            'status_ar' => 'معلقة',
          ],
          19 => 
          [
            'code' => 'AY-0034',
            'status' => 'Received at DMM WH',
            'status_ar' => 'تم استلام الشحنة في مركز توزيع الدمام',
          ],
        ]
      ]);

      $this->_insertNeighbourhoods();

      //Add Tracking column in Order table if it doesn't exist
      if( !$this->db->check(['order' => ['tracking']], 'column') ){
        $this->db->query("ALTER TABLE `order` ADD COLUMN `tracking`  varchar(3000) NULL");
      }
    }

    
	  /**
	   * [POST]Create new shipment Order.
     * @param Array   $order data to be shipped.
	   * @return Response Object contains newly created order details
	   */
    public function createShipment($order){

    	$url     = $this->_getBaseUrl() . 'shipping/create';
      $api_key = $this->config->get('aymakan_api_key');

      $curl = curl_init();
      curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($order),
        CURLOPT_HTTPHEADER => array(
          "Accept: application/json",
          "Authorization: $api_key",
          "Content-Type: application/json",
          "cache-control: no-cache"
        ),
      ));

      $response = curl_exec($curl);
      $err = curl_error($curl);

      curl_close($curl);
      $response = json_decode($response, true);

      if($response['error']){
        $err = $response['response'];
      }

      return [
        'error' => $err ,
        'result' => $response
      ];
    }

    /**
    *
    */
    public function getCities(){

      $url = $this->_getBaseUrl() . 'cities';

      $curl = curl_init();

      curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
          "Accept: application/json",
          "cache-control: no-cache"
        ),
      ));

      $response = json_decode(curl_exec($curl), TRUE);
      $error = curl_error($curl);

      curl_close($curl);

      if($response['success'] == true && !empty($response['data']['cities']) ){
        $lang_id = $this->config->get('config_language_id');
        return array_column($response['data']['cities'], $lang_id == 1 ? 'city_en' : 'city_ar','id');
      }

      return [];
    }
    
    public function getNeighbourhoods(){
        $neighbourhoods = $this->config->get('aymakan_neighbourhoods');
        return array_column($neighbourhoods, $this->config->get('config_admin_language'), 'id');
    }

    /*  Helper Methods */
    private function _getBaseUrl(){
    	//Check if API is in Debugging Mode..
    	$is_debugging_mode = $this->config->get('aymakan_debugging_mode');

    	return ( isset($is_debugging_mode) && $is_debugging_mode == 1 ) ? self::BASE_API_TESTING_URL : self::BASE_API_LIVE_URL;
    }

    private function _insertNeighbourhoods(){
        $neighbourhoods = [
            0 => ['id' => '2', 'en' => 'Riyadh', 'ar' => 'الرياض'],
            1 => ['id' => '4', 'en' => 'Alghat', 'ar' => 'الغاط'],
            2 => ['id' => '5', 'en' => 'AlRass', 'ar' => 'الرس'],
            3 => ['id' => '6', 'en' => 'Assiyah', 'ar' => 'الأسياح'],
            4 => ['id' => '7', 'en' => 'Ayn Fuhayd', 'ar' => 'عين ابن فهيد'],
            5 => ['id' => '8', 'en' => 'Badaya', 'ar' => 'البدايع'],
            6 => ['id' => '9', 'en' => 'Bukeiriah', 'ar' => 'البكيرية'],
            7 => ['id' => '10', 'en' => 'Buraidah', 'ar' => 'بريدة'],
            8 => ['id' => '11', 'en' => 'Daelim', 'ar' => 'الدلم'],
            9 => ['id' => '12', 'en' => 'Deraab', 'ar' => 'ديراب'],
            10 => ['id' => '13', 'en' => 'Dere\'iyeh', 'ar' => 'الدرعية'],
            11 => ['id' => '14', 'en' => 'Dhurma', 'ar' => 'ضرما'],
            12 => ['id' => '15', 'en' => 'Hareeq', 'ar' => 'الحريق'],
            13 => ['id' => '16', 'en' => 'Hawtat Bani Tamim', 'ar' => 'حوطة بني تميم'],
            14 => ['id' => '18', 'en' => 'Horaimal', 'ar' => 'حريملاء'],
            15 => ['id' => '19', 'en' => 'Hotat Sudair', 'ar' => 'حوطة سدير'],
            16 => ['id' => '20', 'en' => 'Kharj', 'ar' => 'الخرج'],
            17 => ['id' => '21', 'en' => 'Majma', 'ar' => 'المجمعة'],
            18 => ['id' => '22', 'en' => 'Muzahmiah', 'ar' => 'المزاحمية'],
            19 => ['id' => '23', 'en' => 'Onaiza', 'ar' => 'عنيزة'],
            20 => ['id' => '24', 'en' => 'Oyaynah', 'ar' => 'العيينة'],
            21 => ['id' => '25', 'en' => 'Qasab', 'ar' => 'القصب'],
            22 => ['id' => '26', 'en' => 'Qassim', 'ar' => 'القصيم'],
            23 => ['id' => '28', 'en' => 'Riyadh Al Khabra', 'ar' => 'رياض الخبراء'],
            24 => ['id' => '31', 'en' => 'Shaqra', 'ar' => 'شقراء'],
            25 => ['id' => '34', 'en' => 'Zulfi', 'ar' => 'الزلفي'],
            26 => ['id' => '35', 'en' => 'Midinhab', 'ar' => 'المذنب'],
            27 => ['id' => '36', 'en' => 'Jeddah', 'ar' => 'جدة'],
            28 => ['id' => '37', 'en' => 'Ushayqer', 'ar' => 'اشيقر'],
            29 => ['id' => '38', 'en' => 'Tharmada`a', 'ar' => 'ثرمداء'],
            30 => ['id' => '39', 'en' => 'Al Qareenah', 'ar' => 'القرينية'],
            31 => ['id' => '40', 'en' => 'Al Jubailah', 'ar' => 'الجبيلة'],
            32 => ['id' => '41', 'en' => 'Qusor almoqbel', 'ar' => 'قصور المقبل'],
            33 => ['id' => '43', 'en' => 'Al Dhabe`ah', 'ar' => 'الضبيعة'],
            34 => ['id' => '45', 'en' => 'Al Amajiyah', 'ar' => 'العمجية'],
            35 => ['id' => '46', 'en' => 'Na`am', 'ar' => 'نعام'],
            36 => ['id' => '47', 'en' => 'Al Helwah', 'ar' => 'الحلوة'],
            37 => ['id' => '48', 'en' => 'Benban', 'ar' => 'بنبان'],
            38 => ['id' => '49', 'en' => 'Mulhem', 'ar' => 'ملهم'],
            39 => ['id' => '50', 'en' => 'Al Hasi', 'ar' => 'الحسي'],
            40 => ['id' => '51', 'en' => 'Sudayer', 'ar' => 'سدير'],
            41 => ['id' => '52', 'en' => 'Tameer', 'ar' => 'تمير'],
            42 => ['id' => '53', 'en' => 'Temeriyah', 'ar' => 'تميرية'],
            43 => ['id' => '54', 'en' => 'Jalajil', 'ar' => 'جلاجل'],
            44 => ['id' => '57', 'en' => 'Hermah', 'ar' => 'حرمة'],
            45 => ['id' => '58', 'en' => 'Maleeh', 'ar' => 'مليح'],
            46 => ['id' => '60', 'en' => 'Roudhah Sudayer', 'ar' => 'روضة سدير'],
            47 => ['id' => '62', 'en' => 'Asheerah Sudayer', 'ar' => 'عشيرة سدير'],
            48 => ['id' => '63', 'en' => 'Awdah Sudayer', 'ar' => 'عودة سدير'],
            49 => ['id' => '64', 'en' => 'Al Ma`ashabah', 'ar' => 'المعشبة'],
            50 => ['id' => '65', 'en' => 'Salbokh', 'ar' => 'صلبوخ'],
            51 => ['id' => '66', 'en' => 'Thadeq', 'ar' => 'ثادق'],
            52 => ['id' => '67', 'en' => 'Uyun Al Jiwa', 'ar' => 'عيون الجواء'],
            53 => ['id' => '68', 'en' => 'Al Qawarah', 'ar' => 'القوارة'],
            54 => ['id' => '69', 'en' => 'Al Shamasiyah', 'ar' => 'الشماسية'],
            55 => ['id' => '70', 'en' => 'Qusayba\'a', 'ar' => 'قصيباء'],
            56 => ['id' => '71', 'en' => 'Al Bateen', 'ar' => 'البطين'],
            57 => ['id' => '72', 'en' => 'Aba Al Woroud', 'ar' => 'أبا الورود'],
            58 => ['id' => '74', 'en' => 'Al Khabra', 'ar' => 'الخبراء'],
            59 => ['id' => '75', 'en' => 'Al Basar', 'ar' => 'البصر'],
            60 => ['id' => '76', 'en' => 'Al Nab\'haniyah', 'ar' => 'النبهانية'],
            61 => ['id' => '78', 'en' => 'Al Fuwaileq', 'ar' => 'الفويلق'],
            62 => ['id' => '79', 'en' => 'Bin Aqeel Palace', 'ar' => 'قصر بن عقيل'],
            63 => ['id' => '80', 'en' => 'Al Ammar', 'ar' => 'العمار'],
            64 => ['id' => '81', 'en' => 'Al Dulymiyah', 'ar' => 'الدليمية'],
            65 => ['id' => '84', 'en' => 'Al Shehiyah', 'ar' => 'الشيحية'],
            66 => ['id' => '86', 'en' => 'Khobar', 'ar' => 'الخبر'],
            67 => ['id' => '87', 'en' => 'Dammam', 'ar' => 'الدمام'],
            68 => ['id' => '100', 'en' => 'Anak', 'ar' => 'عنك'],
            69 => ['id' => '106', 'en' => 'Awamiah', 'ar' => 'العوامية'],
            70 => ['id' => '121', 'en' => 'Dhahran', 'ar' => 'الظهران'],
            71 => ['id' => '165', 'en' => 'Qatif', 'ar' => 'القطيف'],
            72 => ['id' => '175', 'en' => 'Ras Tanura', 'ar' => 'رأس تنورة'],
            73 => ['id' => '181', 'en' => 'Safwa', 'ar' => 'صفوى'],
            74 => ['id' => '188', 'en' => 'Seiha', 'ar' => 'سيهات'],
            75 => ['id' => '227', 'en' => 'Jubail', 'ar' => 'الجبيل'],
            76 => ['id' => '228', 'en' => 'Makkah', 'ar' => 'مكة المكرمة'],
            77 => ['id' => '229', 'en' => 'Al Thaniah', 'ar' => 'الثانية'],
            78 => ['id' => '230', 'en' => 'Al Ogam', 'ar' => 'الاوجام'],
            79 => ['id' => '231', 'en' => 'Ghezlan', 'ar' => 'غزلان'],
            80 => ['id' => '232', 'en' => 'Al Fanateer', 'ar' => 'الفناتير'],
            81 => ['id' => '233', 'en' => 'Khamis Mushait', 'ar' => 'خميس مشيط'],
            82 => ['id' => '234', 'en' => 'Abha', 'ar' => 'أبها'],
            83 => ['id' => '235', 'en' => 'Ahad Rafidah', 'ar' => 'أحد رفيدة'],
            84 => ['id' => '236', 'en' => 'Rejal Alma\'a', 'ar' => 'رجال ألمع'],
            85 => ['id' => '238', 'en' => 'Balasmar', 'ar' => 'بللسمر'],
            86 => ['id' => '239', 'en' => 'Balahmar', 'ar' => 'بللحمر'],
            87 => ['id' => '240', 'en' => 'Sarat Abidah', 'ar' => 'سراة عبيدة'],
            88 => ['id' => '241', 'en' => 'Tendaha', 'ar' => 'تندحة'],
            89 => ['id' => '242', 'en' => 'Tanuma', 'ar' => 'تنومة'],
            90 => ['id' => '243', 'en' => 'Mohayel Aseer', 'ar' => 'محايل عسير'],
            91 => ['id' => '244', 'en' => 'Saihat', 'ar' => 'سيهات'],
            92 => ['id' => '245', 'en' => 'Taif', 'ar' => 'الطائف'],
            93 => ['id' => '246', 'en' => 'Khulais', 'ar' => 'خليص'],
            94 => ['id' => '247', 'en' => 'Thuwal', 'ar' => 'ثول'],
            95 => ['id' => '248', 'en' => 'Asfan', 'ar' => 'عسفان'],
            96 => ['id' => '249', 'en' => 'King Abdullah Economic City', 'ar' => 'مدينة الملك عبدالله الأقتصادية'],
            97 => ['id' => '250', 'en' => 'Dahaban', 'ar' => 'ذهبان'],
            98 => ['id' => '251', 'en' => 'As Sail Al Kabeer', 'ar' => 'السيل الكبير'],
            99 => ['id' => '252', 'en' => 'As Sail Al Sager', 'ar' => 'السيل الصغير'],
            100 => ['id' => '253', 'en' => 'Al quwaiiyah', 'ar' => 'القويعية'],
            101 => ['id' => '254', 'en' => 'Jilah', 'ar' => 'جله'],
            102 => ['id' => '255', 'en' => 'Alhada', 'ar' => 'الهدا'],
            103 => ['id' => '256', 'en' => 'Alshafa', 'ar' => 'الشفاء'],
            104 => ['id' => '257', 'en' => 'Abqyq', 'ar' => 'ابقيق'],
            105 => ['id' => '258', 'en' => 'Eayan dar aljadida', 'ar' => 'عين دار الجديدة'],
            106 => ['id' => '259', 'en' => 'Eayan dar alqadima', 'ar' => 'عين دار القديمة'],
            107 => ['id' => '260', 'en' => 'Yathrib', 'ar' => 'يثرب'],
            108 => ['id' => '261', 'en' => 'Fawada', 'ar' => 'فودة'],
            109 => ['id' => '262', 'en' => 'Alshahili', 'ar' => 'الشهيلي'],
            110 => ['id' => '263', 'en' => 'Aldaghimia', 'ar' => 'الدغيمية'],
            111 => ['id' => '264', 'en' => 'Aleuyun', 'ar' => 'العيون'],
            112 => ['id' => '265', 'en' => 'Alhufuf', 'ar' => 'الهفوف'],
            113 => ['id' => '266', 'en' => 'Alwazia', 'ar' => 'الوزية'],
            114 => ['id' => '267', 'en' => 'Almubriz', 'ar' => 'المبرز'],
            115 => ['id' => '268', 'en' => 'Alqurra', 'ar' => 'القراء'],
            116 => ['id' => '269', 'en' => 'Alsulmania', 'ar' => 'السلمانية'],
            117 => ['id' => '270', 'en' => 'Salasil', 'ar' => 'صلاصل'],
            118 => ['id' => '271', 'en' => 'Aleumran', 'ar' => 'العمران'],
            119 => ['id' => '272', 'en' => 'Aleaqir', 'ar' => 'العقير'],
            120 => ['id' => '273', 'en' => 'Aljafr', 'ar' => 'الجفر'],
            121 => ['id' => '274', 'en' => 'Industrial 3', 'ar' => 'الصناعية الثالثة'],
            122 => ['id' => '275', 'en' => 'Alihsa', 'ar' => 'الأحساء'],
            123 => ['id' => '276', 'en' => 'Heet', 'ar' => 'هيت'],
            124 => ['id' => '277', 'en' => 'Alhazim', 'ar' => 'الحزم'],
            125 => ['id' => '278', 'en' => 'ad dalfaah', 'ar' => 'الضلفعة'],
            126 => ['id' => '279', 'en' => 'tanumah qassim', 'ar' => 'تنومه القصيم'],
            127 => ['id' => '280', 'en' => 'alkhasiybah', 'ar' => 'الخشيبية'],
            128 => ['id' => '281', 'en' => 'dayida', 'ar' => 'ضيده'],
            129 => ['id' => '282', 'en' => 'aljialah', 'ar' => 'الجيله'],
            130 => ['id' => '283', 'en' => 'tarif qassim', 'ar' => 'طريف القصيم'],
            131 => ['id' => '284', 'en' => 'at turfiyah', 'ar' => 'الطرفية'],
            132 => ['id' => '285', 'en' => 'Uglat Asugour', 'ar' => 'عقلة الصقور'],
            133 => ['id' => '286', 'en' => 'Al Amaar', 'ar' => 'العمار'],
            134 => ['id' => '287', 'en' => 'Al Petra', 'ar' => 'البتراء'],
            135 => ['id' => '288', 'en' => 'Thadij', 'ar' => 'ثادج'],
            136 => ['id' => '289', 'en' => 'Al Khushaybi', 'ar' => 'الخشيبي'],
            137 => ['id' => '290', 'en' => 'Al Fawwarah', 'ar' => 'الفوارة'],
            138 => ['id' => '291', 'en' => 'Wadi bin Hashbal', 'ar' => 'وادي بن هشبل'],
            139 => ['id' => '292', 'en' => 'Al Waddean', 'ar' => 'الوديين'],
            140 => ['id' => '293', 'en' => 'Military City', 'ar' => 'المدينة العسكريه'],
            141 => ['id' => '294', 'en' => 'Al Reesh', 'ar' => 'الريش'],
          ];
           
        $this->model_setting_setting->insertUpdateSetting( 'aymakan', [ 'aymakan_neighbourhoods' => $neighbourhoods] );
    }
}
