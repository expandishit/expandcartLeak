<?php

class ModelModuleGameballSettings extends Model
{
    public function getSettings()
    {
        return $this->config->get('gameball');
    }

    public function isActive()
    {
        $settings = $this->getSettings();

        if (isset($settings) && $settings['status'] == 1) {
            return true;
        }

        return false;
    }

    public function addNewGameballPlayer($playerData) {
        // load model
        $this->load->model('module/gameball/settings');
        // get app settings
        $appSittings = $this->model_module_gameball_settings->getSettings();
        // check test mode
        $apikey = ($appSittings['environment'] == 1) ? $appSittings['test_apikey'] : $appSittings['live_apikey'];

        $url = 'https://api.gameball.co/api/v3.0/integrations/player';
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($playerData));
        $options = array(
            CURLOPT_RETURNTRANSFER => true,   // return web page
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Apikey: '.$apikey,
            ],
            CURLOPT_FOLLOWLOCATION => true,   // follow redirects
            CURLOPT_MAXREDIRS => 10,     // stop after 10 redirects
            CURLOPT_ENCODING => "",     // handle compressed
            CURLOPT_AUTOREFERER => true,   // set referrer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,    // time-out on connect
            CURLOPT_TIMEOUT => 120,    // time-out on response
            CURLOPT_CUSTOMREQUEST => 'POST',

        );
        curl_setopt_array($curl, $options);


        $response = curl_exec($curl);
        $response = json_decode($response);
        curl_close($curl);

    }

    public function sendGameballEvent($eventData) {
        // load model
        $this->load->model('module/gameball/settings');
        // get app settings
        $appSittings = $this->model_module_gameball_settings->getSettings();
        // check test mode
        $apikey = ($appSittings['environment'] == 1) ? $appSittings['test_apikey'] : $appSittings['live_apikey'];

        $url = 'https://api.gameball.co/api/v2.0/integrations/event';
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($eventData));
        $options = array(
            CURLOPT_RETURNTRANSFER => true,   // return web page
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Apikey: '.$apikey,
            ],
            CURLOPT_FOLLOWLOCATION => true,   // follow redirects
            CURLOPT_MAXREDIRS => 10,     // stop after 10 redirects
            CURLOPT_ENCODING => "",     // handle compressed
            CURLOPT_AUTOREFERER => true,   // set referrer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,    // time-out on connect
            CURLOPT_TIMEOUT => 120,    // time-out on response
            CURLOPT_CUSTOMREQUEST => 'POST',

        );
        curl_setopt_array($curl, $options);


        $response = curl_exec($curl);
        $response = json_decode($response);
        curl_close($curl);

    }

    // get player points
    public function getpoints($playerUniqueId){
        $url = 'https://api.gameball.co/api/v3.0/integrations/player/' . $playerUniqueId .'/balance';
        $appSittings = $this->model_module_gameball_settings->getSettings();
        // check test mode
        $apikey = ($appSittings['environment'] == 1) ? $appSittings['test_apikey'] : $appSittings['live_apikey'];
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
          'Content-Type:application/json',
          'apiKey:'.$apikey,
          'secretkey:'.$appSittings['transaction_key']
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  
        // Close cURL session
        curl_close($curl);

        $this->load->model('setting/extension');
        
        $responseData = json_decode($response,true);

        return $responseData;
    }

}
