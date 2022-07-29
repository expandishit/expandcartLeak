<?php
class ControllerPaymentOneCard extends Controller {
	private $error = array();
	private $name = '';

	public function index() {

		/* START COMMON STUFF */
		$this->name = str_replace('vq2-admin_controller_payment_', '', basename(__FILE__, '.php'));

		if (!isset($this->session->data['token'])) { $this->session->data['token'] = 0; }
		$this->data['token'] = $this->session->data['token'];
		$this->data = array_merge($this->data, $this->load->language('payment/' . $this->name));

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {
            if ( !$this->validate() )
            {
                $result_json['success'] = '0';
                $result_json['errors'] = $this->error;
                
                $this->response->setOutput(json_encode($result_json));
                
                return;
            }

            foreach ( $this->request->post as $key => $value )
            {
                if (is_array($value)) { $this->request->post[$key] = implode(',', $value); }
            }

            $this->model_setting_setting->checkIfExtensionIsExists('payment', 'onecard', true);

                        $this->tracking->updateGuideValue('PAYMENT');

            $this->load->model('setting/setting');

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
       		'text'      => $this->language->get('heading_title'),
      		'separator' => ' :: '
   		);

        $this->data['action'] =  $this->url->link('payment/'. $this->name );
        $this->data['cancel'] =  $this->url->link('extension/payment');

		$this->id       = 'content';
		$this->template = 'payment/' . $this->name . '.expand';

        $fields = [ 'mid', 'key', 'key2', 'order_status_id', 'geo_zone_id', 'status', 'test', 'debug' ];

        $settings = $this->model_setting_setting->getSetting('onecard');

        foreach ($fields as $field)
        {
            $this->data['onecard_' . $field] = $settings['onecard_' . $field];
        }

		/* 14x backwards compatibility */
		if (method_exists($this->document, 'addBreadcrumb')) { //1.4.x
			$this->document->breadcrumbs = $this->data['breadcrumbs'];
			unset($this->data['breadcrumbs']);
		}//

		$this->children = array(
            'common/header',
            'common/footer'
        );

        foreach ($errors as $error) {
			if (isset($this->error[$error])) {
				$this->data['error_' . $error] = $this->error[$error];
			} else {
				$this->data['error_' . $error] = '';
			}
		}
		/* END COMMON STUFF */
		$this->load->model('localisation/geo_zone');
		$this->load->model('localisation/order_status');
		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $this->response->setOutput($this->render(TRUE));
	}

    private function validate()
    {
        if ( !$this->user->hasPermission('modify', 'payment/' . $this->name) )
        {
            $this->error['error'] = $this->language->get('error_permission');
        }

        if ( ! $this->request->post['onecard_mid'] )
        {
            $this->error['onecard_mid'] = $this->language->get('error_mid');
        }

        if ( ! $this->request->post['onecard_key'] )
        {
            $this->error['onecard_key'] = $this->language->get('error_key');
        }

        if ( ! $this->request->post['onecard_key2'] )
        {
            $this->error['onecard_key2'] = $this->language->get('error_key2');
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
