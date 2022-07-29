<?php


class ModelShippingAsphalt extends Model {

  	/**
  	 * @const strings API URLs.
  	 */
    const BASE_API = 'https://asphalt-eg.com/';

    private $settings = [];

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->settings = $this->config->get('asphalt');
    }

    public function mapGoverments()
    {
        $asphalt_zones = $this->getAllGovernments();

        if(!empty($asphalt_zones)){
            //Get egypt country id
            $this->load->model('localisation/country');
            $country_id = $this->model_localisation_country->getCountryByName('egypt')['country_id'];

            if( !$country_id ){
                //Add country egypt
                $country_id = $this->model_localisation_country->addCountry([
                    'status' => 1,
                    'countryLang1' => 'Egypt',
                    'iso_code_2'   => 'EG',
                    'iso_code_3'   => 'EGY',
                    'address_format'    => '',
                    'postcode_required' => 0,
                    'phonecode'    => '20',
                ]);
            }

            //get expand zones in egypt
            $this->load->model('localisation/zone');
            $expand_zones  = $this->db->query("SELECT lower(zl.`name`) AS `name`, zl.zone_id FROM zones_locale zl JOIN zone ON zone.zone_id = zl.zone_id WHERE zl.country_id = ".(int)$country_id)->rows;
            
            //Normalize arabic names to prevent duplication...
            array_walk($expand_zones,function(&$zone){
                $zone['name'] = $this->normalize_name($zone['name']);
            });
           
            //mapping asphalt zones to expand zones ids
            $map = [];

            foreach($asphalt_zones as $asphalt_zone){

                $matches = $this->search_2d_array($asphalt_zone['name'],'name', $expand_zones);
                if(!empty($matches)){
                    foreach($matches as $expand_zone){
                        $map[$expand_zone['zone_id']] = $asphalt_zone['id'];
                    }
                }
                else{
                    //add new zone
                    $expand_zone_id = $this->model_localisation_zone->addZone([
                        'status' => 1,
                        'names' => [
                            '1' => $asphalt_zone['name'],
                            '2' => $asphalt_zone['name'],
                        ],
                        'code'  => $asphalt_zone['id'],
                        'country_id' => $country_id
                    ]);
                    $map[$expand_zone_id] = $asphalt_zone['id'];
                }               
            }
            $this->load->model('setting/setting');
            $this->model_setting_setting->insertUpdateSetting( 'asphalt', [ 'asphalt_governments' => $map ]);
        }
    }

    public function install()
    {
        //        
    }

    public function uninstall()
    {
        //
    }

    public function getAllGovernments()
    {
        $url = self::BASE_API.'asphalt_v2_api_gov?api_key='.$this->settings['api_key'];
        return $this->curl($url);       
    }

    public function getShipmentContentType()
    {
        $url = self::BASE_API.'asphalt_v2_api_shipment_content?api_key='.$this->settings['api_key'];
        return $this->curl($url);     
    }

    public function getShipmentPackagingType()
    {
        $url = self::BASE_API.'asphalt_v2_api_shipment_packing?api_key='.$this->settings['api_key'];
        return $this->curl($url);     
    }

    public function getShipmentCollectMethod()
    {
        $url = self::BASE_API.'asphalt_v2_api_shipment_collect_method?api_key='.$this->settings['api_key'];
        return $this->curl($url);     
    }

    public function getShipmentAmountCollectType()
    {
        $url = self::BASE_API.'shipment_price_add?api_key='.$this->settings['api_key'];
        return $this->curl($url);     
    }

    public function getShipmentDeliveryType()
    {
        $url = self::BASE_API.'shipment_type?api_key='.$this->settings['api_key'];
        return $this->curl($url);     
    }

    public function getShipmentBranchIds()
    {
        $url = self::BASE_API.'asphalt_v2_api_get_branch_ids?api_key='.$this->settings['api_key'];
        return $this->curl($url);     
    } 

    public function getAllRegions($gov_id)
    {
        $url = self::BASE_API.'asphalt_v2_api_regions?gov_id='.$gov_id.'&api_key='.$this->settings['api_key'];
        return array_map(function($element){
            $element['normalized_name'] = $this->normalize_name($element['region_name']);
            return $element;

        },$this->curl($url));
    }

  	/**
  	* [POST]Create new shipment Order.
  	*
    * @param Array   $order data to be shipped.
  	*
  	* @return Response Object contains newly created order details
  	*/
    public function createShipment($order)
    {
        $url = self::BASE_API.'API_v1_create_shipment';
        return $this->curl($url, 'POST', $order, ['Content-Type: application/x-www-form-urlencoded']);
    }

    //Helpers Functions
    private function curl($url, $method = 'GET', $data = '', $headers = [])
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => (string)$method,
          CURLOPT_HTTPHEADER => array(
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36",
            "Accept-Language:en-US,en;q=0.5"
          )      
        ));

        if(strcasecmp($method , 'POST') === 0)
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response, true);
    }

    private function search_2d_array($value, $column, $array)
    {
        $result = [];
        foreach($array as $row){
            if($row[$column] == $value)
                $result[] = $row;
        }
        return $result;
    }

    //For arabic comparison.
    public function normalize_name($name) 
    {
        $patterns     = array( "/إ|أ|آ/" ,"/ة/", "/َ|ً|ُ|ِ|ٍ|ٌ|ّ/" );
        $replacements = array( "ا" ,  "ه"      , ""         );
        return preg_replace($patterns, $replacements, $name);
    }
}
