<?php
require_once( DIR_SYSTEM.'library/vendor/webtopay/libwebtopay/WebToPay.php' );


ini_set('display_errors', 0);

class ControllerPaymentPaysera extends Controller
{
	private $error = array();

	public function index()
    {

		$this->language->load('payment/paysera');

        $this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

        $this->load->model('localisation/order_status');

        $this->load->model('localisation/geo_zone');

		if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
		{

            if ( ! $this->validate() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
                
                $this->response->setOutput(json_encode($result_json));
                
                return;
            }

            $this->model_setting_setting->checkIfExtensionIsExists('payment', 'paysera', true);

            $this->tracking->updateGuideValue('PAYMENT');

            $this->model_setting_setting->insertUpdateSetting('paysera', $this->request->post);
            
            $this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

            $result_json['success'] = '1';
            
            $this->response->setOutput(json_encode($result_json));
            
            return;
		}

        // ======================== breadcrumbs =============================

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL')
		);

		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_payment'),
			'href' => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL')
		);

		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('payment/paysera', 'token=' . $this->session->data['token'], 'SSL')
		);

        $this->data['action'] = $this->url->link('payment/paysera', 'token=' . $this->session->data['token'], 'SSL');

        $this->data['action'] = $this->url->link('payment/paysera', 'token=' . $this->session->data['token'], 'SSL');

        // ======================== /breadcrumbs =============================

		$this->data['paysera_project'] = $this->config->get('paysera_project');

        $this->data['paysera_sign'] = $this->config->get('paysera_sign');

		$this->data['paysera_lang'] = $this->config->get('paysera_lang');

		$this->data['callback'] = HTTP_CATALOG . 'index.php?route=payment/paysera/callback';

		$this->data['paysera_test'] = $this->config->get('paysera_test');

		$this->data['paysera_order_status_id'] = $this->config->get('paysera_order_status_id');

		$this->data['paysera_new_order_status_id'] = $this->config->get('paysera_new_order_status_id');

		$this->data['paysera_display_payments_list'] = $this->config->get('paysera_display_payments_list');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses(); //here

		$this->data['payment_countries'] = $this->getCountries();

		$this->data['paysera_geo_zone_id'] = $this->config->get('paysera_geo_zone_id');
		
        $this->data['default_payment_country'] = $this->config->get('default_payment_country');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		$this->data['paysera_status'] = $this->config->get('paysera_status');

		$this->data['paysera_sort_order'] = $this->config->get('paysera_sort_order');

		$this->template = 'payment/paysera.expand';
		$this->children = array(
			'common/header',
            'common/footer'
		);

		$this->response->setOutput($this->render());

	}


	private function validate()
    {
		if ( ! $this->user->hasPermission('modify', 'payment/paysera') )
        {
			$this->error['error'] = $this->language->get('error_permission');
		}

		if ( ! $this->request->post['paysera_project'] )
        {
			$this->error['paysera_project'] = $this->language->get('error_project');
		}

		if ( ! $this->request->post['paysera_sign'] )
        {
			$this->error['paysera_sign'] = $this->language->get('error_sign');
		}

        if ( ! $this->request->post['paysera_lang'] )
        {
            $this->error['paysera_lang'] = $this->language->get('error_lang');
        }


        if ( $this->error && !isset($this->error['error']) )
        {
            $this->error['warning'] = $this->language->get('error_warning');
        }
        
        return $this->error ? false : true;
	}

	public function getCountries() {
	    try {
            $language = $this->language->get('code');
            $projectId = $this->config->get('paysera_project');

            if (!$projectId || !$language) {
                return null;
            }

            $methods = WebToPay::getPaymentMethodList($projectId)->setDefaultLanguage($language);

            return $methods->getCountries();
        }
        catch (Exception $e) {
            $this->error['warning'] = $this->language->get('error_server'); //$e->getMessage();
        }
	}
}

