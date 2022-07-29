<?php
class ControllerPaymentHalalah extends Controller
{

	private $error = array();
	private $name = 'halalah';

	public function index()
    {
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
			
			$this->load->model('setting/setting');

            $this->model_setting_setting->checkIfExtensionIsExists('payment', 'halalah', true);

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
       		'text'      => $this->language->get('heading_title'),
      		'separator' => ' :: '
   		);

		$this->data['action'] = $this->url->link('payment/'. $this->name );
		$this->data['cancel'] = $this->url->link('extension/payment');

		$this->id       = 'content';
		$this->template = 'payment/' . $this->name . '.expand';

        $fields = [ 'mid', 'mccode', 'order_status_id', 'mname', 'status', 'mcity', 'mname_ar', 'mcity_ar', 'mterminal', 'sort_order', 'client_id', 'client_secret' ];

        $settings = $this->model_setting_setting->getSetting($this->name);

        foreach ($fields as $field)
        {
            $this->data['halalah_' . $field] = $settings['halalah_' . $field];
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

		/* END COMMON STUFF */

		//$this->load->model('localisation/geo_zone');
		$this->load->model('localisation/order_status');
		//$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $this->response->setOutput($this->render(TRUE));
	}


	private function validate()
    {
		if ( ! $this->user->hasPermission('modify', 'payment/' . $this->name) )
        {
			$this->error['error'] = $this->language->get('error_permission');
		}

        // if ( ! $this->request->post['halalah_mid'] )
        // {
        //     $this->error['halalah_mid'] = $this->language->get('error_mid');
        // }

        if ( ! $this->request->post['halalah_client_id'] )
        {
            $this->error['halalah_client_id'] = $this->language->get('error_client_id');
        }

        if ( ! $this->request->post['halalah_client_secret'] )
        {
            $this->error['halalah_client_secret'] = $this->language->get('error_client_secret');
        }

        if ( ! $this->request->post['halalah_mterminal'] )
        {
            $this->error['halalah_mterminal'] = $this->language->get('error_mterminal');
        }

        if ( ! $this->request->post['halalah_mccode'] )
        {
            $this->error['halalah_mccode'] = $this->language->get('error_mccode');
        }

        if ( ! $this->request->post['halalah_mname'] )
        {
            $this->error['halalah_mname'] = $this->language->get('error_mname');
        }

        if ( ! $this->request->post['halalah_mcity'] )
        {
            $this->error['halalah_mcity'] = $this->language->get('error_mcity');
        }

        if ( ! $this->request->post['halalah_mname_ar'] )
        {
            $this->error['halalah_mname_ar'] = $this->language->get('error_mname_ar');
        }

        if ( ! $this->request->post['halalah_mcity_ar'] )
        {
            $this->error['halalah_mcity_ar'] = $this->language->get('error_mcity_ar');
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
