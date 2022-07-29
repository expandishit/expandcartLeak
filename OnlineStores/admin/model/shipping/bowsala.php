<?php

class ModelShippingBowsala extends Model{

  public function login(){
    $username   = $this->config->get('bowsala_user_name');
    $password = $this->config->get('bowsala_password');
    $url = "https://app.shipsy.in/api/CustomerAnalytics/login";

    $data = "username=$username&password=$password";
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS =>$data,
      CURLOPT_HTTPHEADER => array(
        "Content-Type: application/x-www-form-urlencoded",
        "organisation-url: store.bowsala.sa"
      ),
    ));
    $response = json_decode(curl_exec($curl), true);
    curl_close($curl);
    return $response;
  }
  /**
	* [POST]Create new shipment Order.
	*
  * @param Array   $order data to be shipped.
	*
	* @return Response Object contains newly created order details
	*/
  public function createShipment($headers, $data){
    $url = "https://onlinebookingmultitenantbackend.shipsy.in/consignment/business/save";

    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS =>json_encode($data),
      CURLOPT_HTTPHEADER => array(
        "Content-Type: application/json",
        "organisation-url: store.bowsala.sa",
        "organisation-id: ".$headers['org_id'],
        "user-id: ".$headers['customer_id'],
        "access-token: ".$headers['access_token'],
      ),
    ));
    $response = json_decode(curl_exec($curl), true);
    curl_close($curl);
    return $response;
  }

  public function trackShipment($headers,$ref_number){
    $url = "https://onlinebookingmultitenantbackend.shipsy.in/consignment/business/tracking?referenceNumber=$ref_number";
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_HTTPHEADER => array(
        "organisation-id: ".$headers['org_id'],
        "user-id: ".$headers['customer_id'],
        "access-token: ".$headers['access_token'],
      ),
    ));
    $response = json_decode(curl_exec($curl), true);
    curl_close($curl);
    return $response;
  }

  public function install()
  {
    $this->create_cities_table();
    $this->insertCities();
  }

  public function uninstall()
  {
      $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "bowsala_cities`;");
  }

  public function getCities()
  {
    $result = $this->db->query("SELECT name_".$this->config->get('config_admin_language')." AS name, pincode FROM `" . DB_PREFIX . "bowsala_cities` ORDER BY `pincode` ");
    return $result->rows;
  }

  public function getCityName($pincode)
  {
    $result = $this->db->query("SELECT name_en AS name FROM " . DB_PREFIX . " `bowsala_cities` WHERE pincode = '" .$pincode. "'");
    return $result->row['name'];
  }

  private function create_cities_table()
  {
    $this->db->query("
    CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "bowsala_cities` 
    (
        `bowsala_city_id` int(11) NOT NULL AUTO_INCREMENT,
        `name_ar` varchar(200) NOT NULL ,
        `name_en` varchar(200) NOT NULL ,
        `pincode` varchar(50) NOT NULL ,
        PRIMARY KEY (`bowsala_city_id`)
    ) 
    ENGINE=InnoDB DEFAULT COLLATE=utf8_general_ci;");

  }

    private function insertCities()
    {
      $query = 'INSERT INTO  `' . DB_PREFIX . 'bowsala_cities` (`name_en`,`name_ar`,`pincode`) VALUES ';
      $query .= "
                 ('Abo Ajram','أبو عجرم ','AJRM'),
                 ('AbuAreesh','أبو عريش','ABUARSH'),
                 ('AdDilam','الدلم ','DILM'),
                 ('AdDiriyah','الدرعيه ','DRYA'),
                 ('Afif','عفيف','Afif'),
                 ('Ahad AlMasarihah','احد المسارحه','AHDMASAR'),
                 ('Ahad Rafidah','احد رفيدة','AHDRFD'),
                 ('Ain Dar','عين دار ','ANDR'),
                 ('Al Dulaymiyah','الدليميه','DLMY'),
                 ('Al Hariq','الحريق ','HARQ'),
                 ('Al Hulwah','الحلوه','HLWH'),
                 ('Al Jafr','الجفر ','JFR'),
                 ('Al Lith','الليث','LTH'),
                 ('Al Tuwal','الطوال ','TUW'),
                 ('AlAflaj','الافلاج ','AFLAJ'),
                 ('AlAwamiyah','العواميه','AWMY'),
                 ('AlBadai','البدايع ','BAD'),
                 ('Al-Badie Al-Shamali ','البديع الشمالي ','BADSH'),
                 ('AlBaha','الباحه','ABT'),
                 ('AlBukayriyah','البكيريه ','BAK'),
                 ('Alhawiyah','الحويه','HWYH'),
                 ('AlJumoom','الجموم','JOMM'),
                 ('AlKhabra','الخبراء','KHAB'),
                 ('AlKhafji','الخفجي ','KFJ'),
                 ('AlKharj','الخرج','KRG'),
                 ('AlKhurmah','الخرمه','KHRMA'),
                 ('AlMajmaah','المجمعه','KRG'),
                 ('AlMakhwah','المخواه','MKHWA'),
                 ('AlMidhnab','المذنب ','MUZ'),
                 ('AlMubarraz','المبرز','MUBRZ'),
                 ('AlMuzahimiyah','المزاحميه','MZH'),
                 ('AlQatif','القطيف ','QTF'),
                 ('AlQudaih','القديح','QDEH'),
                 ('AlQunfudhah','القنفذه','QNFD'),
                 ('AlQurayyat','القريات','URY'),
                 ('AlQuwayiyah','القويعيه','QWYA'),
                 ('AlQuz','القوز','QUZ'),
                 ('AlUla','العلاء','OLA'),
                 ('AlUyun','العيون','UYON'),
                 ('AlWajh','الوجه','WJH'),
                 ('An Nabhaniyah','النبهانيه','NBHN'),
                 ('Anak','عنك','ANK'),
                 ('Ar Ruwaidhah','الرويضه','RWDH'),
                 ('Arar','عرعر','RAE'),
                 ('As Sulayyil','السليل','SULIL'),
                 ('Athuqbah','الثقبه ','THQB'),
                 ('Badr','بدر ','BDR'),
                 ('Bahrah','بحره','BHR'),
                 ('Bahrain Causeway','جسر البحرين','BHRCW'),
                 ('Baljurashi','بلجرشي ','BLJRSH'),
                 ('Bariq','بارق','BRQ'),
                 ('Batha','بطحاء ','BTHA'),
                 ('Bish','بيش','BSH'),
                 ('Bisha','بيشه','BHH'),
                 ('Buqayq','بقيق','BQQ'),
                 ('Buraidah','بريده','BRD'),
                 ('Damad','ضمد','DMD'),
                 ('Dammam','الدمام','DMM'),
                 ('Darb/abha','الدرب','DRB'),
                 ('Dawadmi','الدوادمي ','DWD'),
                 ('Dhahran','الظهران','DHRN'),
                 ('Dhahran Al Janub','ظهران الجنوب ','DHJAN'),
                 ('Dhurma','ضرماء','DRM'),
                 ('Duba','ضباء','DBA'),
                 ('Dukhnah','دخنه','DKHN'),
                 ('Dumat alJandal','دومه الجندل ','JNDL'),
                 ('Ghat','الغاط ','GHT'),
                 ('hafar alBatin','حفر الباطن ','HFR'),
                 ('hail','حايل ','HAS'),
                 ('Hali AlQunfudhah','حلي القنفذه','HALIQF'),
                 ('haql','حقل ','HQL'),
                 ('Hassa','الاحساء','HOF'),
                 ('Hawtat Sudayr','حوطه سدير ','HWSD'),
                 ('Hotat Bani Tamim','حوطه بني تميم','HOBT'),
                 ('Huraymila ','حريملا','HRMLA'),
                 ('Jash','جاش','JSH'),
                 ('Jeddah','جده ','JED'),
                 ('Jizan','جازان','GIZ'),
                 ('Jubail','الجبيل ','JBL'),
                 ('Khamis Mushait','خميس مشيط','KMS2'),
                 ('Khobar','الخبر ','KHBR'),
                 ('Layla','ليلى','LYLA'),
                 ('Mahail','محايل ','MHL'),
                 ('Majardah','المجارده','MJRD'),
                 ('malham','ملهم ','MLHM'),
                 ('Mecca','مكه ','MKH'),
                 ('Medina','المدينه ','MED2'),
                 ('Nairiyah','النعيريه','NYRH'),
                 ('Najran','نجران','EAM'),
                 ('Nakeea','نقيه','NKE'),
                 ('Namas','النماص','NMS'),
                 ('Qaisumah','القيصومه','AQI'),
                 ('Qarya Al Uliya','قريه العليا','QRYAUL'),
                 ('Rabigh','رابغ ','RBGH'),
                 ('Rafha','رفحاء ','RAH'),
                 ('Ranyah','رنيه ','RNYA'),
                 ('Ras Tannurah','راس تنوره','RSTNRH'),
                 ('Rass','الرس','RAS'),
                 ('Rijal Alma','رجال المع ','RJMA'),
                 ('Riyadh','الرياض','RUH'),
                 ('Riyadh AlKhabra','رياض الخبراء','RYDKHO'),
                 ('Rumah','رماح ','RMH'),
                 ('Sabt AlUlayah','سبت العلايا ','SBTULY'),
                 ('Sabya','صبيا ','SABY'),
                 ('Safwa','صفوى','SFWA'),
                 ('Saihat','سيهات ','SEHT'),
                 ('Sajir','ساجر ','SJR'),
                 ('Sakakah','سكاكا','AJF'),
                 ('Salbukh','صلبوخ','SLBKH'),
                 ('Salwa','سلوى','SLWA'),
                 ('Samtah','صامطه ','SAMT'),
                 ('Sarat Abideh','سراة عبيدة','SRTABD'),
                 ('Shaqraa','شقراء','SHQR'),
                 ('Sharurah','شروره','SHW'),
                 ('tabarjal','طبرجل ','TBRJ'),
                 ('Tabuk','تبوك ','TUU'),
                 ('Taif','الطايف ','TIF'),
                 ('Taraf','الطرف','TRF'),
                 ('Tarut','تاروت','TRT'),
                 ('Tathlith','تثليث','TATHL'),
                 ('Tayma','تيماء ','TYM'),
                 ('Thwal','ثول','THL'),
                 ('Turabah','تربه','TRB'),
                 ('Turaif','طريف','TUI'),
                 ('Udhayliyah','العضيلية','UDLYA'),
                 ('Ummluj','املج ','UMLJ'),
                 ('Unaizah','عنيزه','ONEZ'),
                 ('Uqlat As Suqur','عقله الصقور ','UQSR'),
                 ('Uthmaniyah','العثمانيه','UTHM'),
                 ('Uyun al jawa','عيون الجوى ','UJWA'),
                 ('Wadi adDawasir','وادي الدواسر ','WAE'),
                 ('Wadi Bin Hashbal','وادي بن هشبل ','WDBH'),
                 ('Yanbu','ينبع','YNB'),
                 ('zalom','ظلم ','ZLM'),
                 ('Zulfi','زلفي','ZLF')";
      $this->db->query($query);
    }

}
?>
