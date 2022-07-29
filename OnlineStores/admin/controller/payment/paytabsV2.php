<?php
class ControllerPaymentPaytabsV2 extends Controller
{
    private $error = array();

    public function index() {

        $this->load->model('setting/setting');

        $this->load->model('localisation/order_status');

        $this->load->model('localisation/geo_zone');

        $this->language->load('payment/paytabsV2');

        $this->load->model('localisation/country');

       if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {

            if ( ! $this->validate() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;

                $this->response->setOutput(json_encode($result_json));

                return;
            }

            $this->model_setting_setting->checkIfExtensionIsExists('payment', 'paytabsV2', true);

                        $this->tracking->updateGuideValue('PAYMENT');

            $this->model_setting_setting->insertUpdateSetting('paytabsV2', $this->request->post);

            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

            $result_json['success'] = '1';

            $this->response->setOutput(json_encode($result_json));

            return;
        }

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_payment'),
            'href'      => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('payment/paytabsV2', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $this->data['action'] = $this->url->link('payment/paytabsV2', 'token=' . $this->session->data['token'], 'SSL');

        $this->data['cancel'] = $this->url->link('payment/paytabsV2', 'token=' . $this->session->data['token'], 'SSL');

        $this->load->model('localisation/language');

        $settings = $this->model_setting_setting->getSetting('paytabsV2');

        $this->data['languages'] = $languages = $this->model_localisation_language->getLanguages();

        foreach ( $languages as $language )
        {
            $this->data['paytabsV2_field_name_'.$language['language_id']] = $settings['paytabsV2_field_name_' . $language['language_id']];
            $this->data['paytabsV2_'.$language['language_id']] = $settings['paytabsV2_' . $language['language_id']];
        }

        $fields = [ 'status','profile_id', 'security', 'total', "url" ,  'geo_zone_id', "completed_order_status_id", "failed_order_status_id","show_shipping_billing"];

        foreach ($fields as $field)
        {
            $this->data['paytabsV2_' . $field] = $settings['paytabsV2_' . $field];
        }

        $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        $this->data['current_currency_code'] = $this->currency->getCode();

        $supportedCountries = ["Egypt", "Jordan", "Oman", "Saudi Arabia", "United Arab Emirates",  "other"];

        $this->data["countries"] = $this->mapCountriesToUrls($supportedCountries);

        $this->template = 'payment/paytabsV2.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());


    }

    private function mapCountriesToUrls($countries) {

        $result = array();

        foreach ($countries as $country) {


            if($country == "Egypt") {

                $result[] = array("url" => "https://secure-egypt.paytabs.com", "name" => $country);

            } else if($country == "Jordan") {

                $result[] = array("url" => "https://secure-jordan.paytabs.com", "name" => $country);

            } else if($country == "Oman") {

                $result[] = array("url" => "https://secure-oman.paytabs.com", "name" => $country);

            } else if($country == "Saudi Arabia") {

                $result[] = array("url" => "https://secure.paytabs.sa", "name" => $country);

            }else if ($country == "United Arab Emirates"){
                $result[] = array("url" => "https://secure.paytabs.com", "name" => $country);
            }

            else {

                $result[] = array("url" => "https://secure-global.paytabs.com", "name" => $this->language->get("otherOption"));
            }

        }

        return $result;
    }

    private function validate()
    {
        if ( ! $this->user->hasPermission('modify', 'payment/paytabsV2') )
        {
            $this->error['error'] = $this->language->get('error_permission');
        }
        if ( ! isset($this->request->post['paytabsV2_profile_id']) || empty($this->request->post['paytabsV2_profile_id']) )
        {
            $this->error['paytabsV2_profile_id'] = $this->language->get('error_profile_id');
        }
        if ( ! $this->request->post['paytabsV2_url'] )
        {
            $this->error['paytabsV2_country'] = $this->language->get('error_country');
        }

        if ( ! $this->request->post['paytabsV2_security'] )
        {
            $this->error['paytabsV2_security'] = $this->language->get('error_security');
        }



        if ( $this->error && !isset($this->error['error']) )
        {
            $this->error['warning'] = $this->language->get('error_warning');
        }



        return $this->error ? false : true;
    }
}
