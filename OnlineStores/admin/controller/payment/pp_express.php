<?php
class ControllerPaymentPPExpress extends Controller
{
	private $error = array();
	private $name = 'pp_express';

	public function index()
    {   
        $this->load->model('setting/setting');
        $settings = $this->model_setting_setting->getSetting($this->name);
        $this->data[$this->name] =  $settings[$this->name];

        if(!isset($settings['pp_status'])){
            $this->load->model('payment/pp_express');
            $this->model_payment_pp_express->install();
        }

		$this->data = array_merge($this->data, $this->language->load('payment/pp_standard'));
		$this->document->setTitle($this->language->get('heading_title_express'));

		if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {
            if ( !$this->validate() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
                
                $this->response->setOutput(json_encode($result_json));
                
                return;
            }

            $this->load->model('setting/setting');

            $this->model_setting_setting->checkIfExtensionIsExists('payment', 'pp_express', true);

                        $this->tracking->updateGuideValue('PAYMENT');


			$this->model_setting_setting->insertUpdateSetting($this->name, $this->request->post);

			$this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');

            $result_json['success'] = '1';

            $this->response->setOutput(json_encode($result_json));

			return;
		}

		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
            'href'      => $this->url->link('common/home'),
       		'text'      => $this->language->get('text_home'),
      		'separator' => FALSE
   		);

   		$this->data['breadcrumbs'][] = array(
            'href'      => $this->url->link('extension/payment'),
       		'text'      => $this->language->get('text_payment'),
      		'separator' => ' :: '
   		);

   		$this->data['breadcrumbs'][] = array(
            'href'      => $this->url->link('payment/'. $this->name ),
       		'text'      => $this->language->get('heading_title_express'),
      		'separator' => ' :: '
   		);

		$this->data['action'] = $this->url->link('payment/'. $this->name );
		$this->data['cancel'] = $this->url->link('extension/payment');

		$this->id       = 'content';
		$this->template = 'payment/' . $this->name . '.expand';

		/* 14x backwards compatibility */
		if (method_exists($this->document, 'addBreadcrumb')) { //1.4.x
			$this->document->breadcrumbs = $this->data['breadcrumbs'];
			unset($this->data['breadcrumbs']);
		}//

		$this->children = array(
            'common/header',
            'common/footer'
        );

		/* END COMMON STUFF */
        $this->data['current_currency_code'] = $this->currency->getCode();
		$this->load->model('localisation/geo_zone');
        $this->load->model('localisation/order_status');
        $this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $this->response->setOutput($this->render(TRUE));
	}

	private function validate()
    {
		if ( ! $this->user->hasPermission('modify', 'payment/' . $this->name) )
        {
			$this->error['error'] = $this->language->get('error_permission');
		}

        if ( ! $this->request->post[$this->name]['username'] )
        {
            $this->error['pp_express_username'] = $this->language->get('error_username');
        }

        if ( ! $this->request->post[$this->name]['password'] )
        {
            $this->error['pp_express_password'] = $this->language->get('error_password');
        }

        if ( ! $this->request->post[$this->name]['signature'] )
        {
            $this->error['pp_express_signature'] = $this->language->get('error_signature');
        }

		if ( $this->error && !isset($this->error['error']) )
        {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        if ( $this->error )
        {
            return false;
        }
        else
        {
            return true;
        }
	}
}
