<?php
class ControllerPaymentUpay extends Controller
{

	private $error = array();
	private $name = 'upay';

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
            
			$this->load->model('setting/setting');

            $this->model_setting_setting->checkIfExtensionIsExists('payment', 'upay', true);

			$this->model_setting_setting->insertUpdateSetting($this->name, $this->request->post);

                        $this->tracking->updateGuideValue('PAYMENT');

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
        $this->data[$this->name] =  $settings[$this->name];

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

		$this->load->model('localisation/order_status');
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $this->response->setOutput($this->render(TRUE));
	}


	private function validate()
    {
		if ( ! $this->user->hasPermission('modify', 'payment/' . $this->name) )
        {
			$this->error['error'] = $this->language->get('error_permission');
		}

        if ( ! $this->request->post[$this->name]['merchant_id'] )
        {
            $this->error['upay_merchant_id'] = $this->language->get('error_merchant_id');
        }

        if ( ! $this->request->post[$this->name]['api_key'] )
        {
            $this->error['upay_api_key'] = $this->language->get('error_api_key');
        }

        if ( ! $this->request->post[$this->name]['uname'] )
        {
            $this->error['upay_uname'] = $this->language->get('error_uname');
        }

        if ( ! $this->request->post[$this->name]['password'] )
        {
            $this->error['upay_password'] = $this->language->get('error_password');
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
