<?php 

class ControllerLocalisationCurrency extends Controller
{
	private $error = array();
 
	public function index() {

        if ($this->request->get['card_name']){
            $this->session->data['card_name'] = $this->request->get['card_name'];
        }

		$this->language->load('localisation/currency');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('localisation/currency');
		
		$this->getList();
	}

	public function insert() {
		$this->language->load('localisation/currency');
        $this->load->model('setting/setting');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('localisation/currency');
		
		if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
		{

            if ( !$this->validateForm() )
            {

                $response['success'] = '0';
                $response['errors'] = $this->error;

                $this->response->setOutput(json_encode($response));
                return;
            }

            $currency_id = $this->model_localisation_currency->addCurrency($this->request->post);

            // add data to log_history
            $this->load->model('setting/audit_trail');
            $pageStatus = $this->model_setting_audit_trail->getPageStatus("lang_settings");
            if($pageStatus){
                $log_history['action'] = 'add';
                $log_history['reference_id'] = $currency_id;
                $log_history['old_value'] = NULL;
                $log_history['new_value'] = json_encode($this->request->post,JSON_UNESCAPED_UNICODE);
                $log_history['type'] = 'currency';
                $this->load->model('loghistory/histories');
                $this->model_loghistory_histories->addHistory($log_history);
            }

            $this->tracking->updateGuideValue("CURRENCY");

            $response['success'] = '1';
            $response['success_msg'] = $this->language->get('notification_inserted_successfully');

            $this->response->setOutput(json_encode($response));
            return;
		}

        $this->data['links'] = [
            'submit' => $this->url->link('localisation/currency/insert', '', 'SSL'),
            'cancel' => $this->url->link('localisation/currency', '', 'SSL')
        ];

		$this->getForm();
	}

	public function update() {
		$this->language->load('localisation/currency');
        $this->load->model('setting/setting');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('localisation/currency');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {

		    if (!isset($this->request->post['status'])) {
                $this->request->post['status'] = '0';
            }

            if (!$this->validateForm()) {
                $response['status'] = 'error';
                $response['errors'] = $this->error;

                $this->response->setOutput(json_encode($response));
                return;
            }

            $oldValue = $this->model_localisation_currency->getCurrency($this->request->get['currency_id']);
            // add data to log_history
            $this->load->model('setting/audit_trail');
            $pageStatus = $this->model_setting_audit_trail->getPageStatus("lang_settings");
            if($pageStatus){
                $log_history['action'] = 'update';
                $log_history['reference_id'] = $this->request->get['currency_id'];
                $log_history['old_value'] = json_encode($oldValue,JSON_UNESCAPED_UNICODE);
                $log_history['new_value'] = json_encode($this->request->post,JSON_UNESCAPED_UNICODE);
                $log_history['type'] = 'currency';
                $this->load->model('loghistory/histories');
                $this->model_loghistory_histories->addHistory($log_history);
            }

			$this->model_localisation_currency->editCurrency($this->request->get['currency_id'], $this->request->post);

			$this->tracking->updateGuideValue('CURRENCY');

            $response['success'] = '1';
            $response['title'] = $this->language->get('notification_success_title');
            $response['success_msg'] = $this->language->get('notification_updated_successfully');

            $this->response->setOutput(json_encode($response));
            return;
		}

        $this->data['links'] = [
            'submit' => $this->url->link('localisation/currency/update', 'currency_id=' . $this->request->get['currency_id'], 'SSL'),
            'cancel' => $this->url->link('localisation/currency', '', 'SSL')
        ];

		$this->getForm();
	}

	public function delete() {
		$this->language->load('localisation/currency');
        $this->load->model('setting/setting');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('localisation/currency');
		
		if (isset($this->request->post['selected']) && $this->validateDelete()) {
            $this->load->model('setting/audit_trail');
            $pageStatus = $this->model_setting_audit_trail->getPageStatus("lang_settings");
            $this->load->model('loghistory/histories');

			foreach ($this->request->post['selected'] as $currency_id) {
                $oldValue = $this->model_localisation_currency->getCurrency($currency_id);
                $this->model_localisation_currency->deleteCurrency($currency_id);
                // add data to log_history
                if($pageStatus){
                    $log_history['action'] = 'delete';
                    $log_history['reference_id'] = $currency_id;
                    $log_history['old_value'] = json_encode($oldValue,JSON_UNESCAPED_UNICODE);
                    $log_history['new_value'] = NULL;
                    $log_history['type'] = 'currency';
                    $this->model_loghistory_histories->addHistory($log_history);
                }
			}
			
			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
			
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->tracking->updateGuideValue('CURRENCY');

			$this->redirect($this->url->link('localisation/currency', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	protected function getList() {
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'title';
		}
		
		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}
		
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}
		
		$url = '';
	
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
		
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

  		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('localisation/currency', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);
		
		$this->data['insert'] = $this->url->link('localisation/currency/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('localisation/currency/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');
		
		$this->data['currencies'] = array();

		$data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_admin_limit'),
			'limit' => $this->config->get('config_admin_limit')
		);
		
		$currency_total = $this->model_localisation_currency->getTotalCurrencies();

		$results = $this->model_localisation_currency->getCurrencies($data);

		foreach ($results as $result) {
			$this->data['currencies'][] = array(
				'currency_id'   => $result['currency_id'],
				'title'         => $result['title'] . (($result['code'] == $this->config->get('config_currency')) ? $this->language->get('text_default') : null),
				'code'          => $result['code'],
				'value'         => $result['value'],
				'date_modified' => date($this->language->get('date_format_short'), strtotime($result['date_modified'])),
				'status' => $result['status'],
			);
		}	
	
		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_no_results'] = $this->language->get('text_no_results');

		$this->data['column_title'] = $this->language->get('column_title');
    	$this->data['column_code'] = $this->language->get('column_code');
		$this->data['column_value'] = $this->language->get('column_value');
		$this->data['column_date_modified'] = $this->language->get('column_date_modified');
		$this->data['column_action'] = $this->language->get('column_action');

		$this->data['button_insert'] = $this->language->get('button_insert');
		$this->data['button_delete'] = $this->language->get('button_delete');

 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}
		
		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];
		
			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}

		$url = '';

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		
		$this->data['sort_title'] = $this->url->link('localisation/currency', 'token=' . $this->session->data['token'] . '&sort=title' . $url, 'SSL');
		$this->data['sort_code'] = $this->url->link('localisation/currency', 'token=' . $this->session->data['token'] . '&sort=code' . $url, 'SSL');
		$this->data['sort_value'] = $this->url->link('localisation/currency', 'token=' . $this->session->data['token'] . '&sort=value' . $url, 'SSL');
		$this->data['sort_date_modified'] = $this->url->link('localisation/currency', 'token=' . $this->session->data['token'] . '&sort=date_modified' . $url, 'SSL');
		
		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}
												
		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $currency_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('localisation/currency', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');
			
		$this->data['pagination'] = $pagination->render();
		
		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->template = 'localisation/currency/list.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}

	
	private function getForm()
	{

  		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('localisation/currency', 'token=' . $this->session->data['token'] . $url, 'SSL'),      		
      		'separator' => ' :: '
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => ! isset($this->request->get['currency_id']) ? $this->language->get('breadcrumb_insert') : $this->language->get('breadcrumb_update'),
			'href'      => $this->url->link('localisation/currency', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);


		if (!isset($this->request->get['currency_id'])) {
			$this->data['action'] = $this->url->link('localisation/currency/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('localisation/currency/update', 'token=' . $this->session->data['token'] . '&currency_id=' . $this->request->get['currency_id'] . $url, 'SSL');
		}
				
		$this->data['cancel'] = $this->url->link('localisation/currency', 'token=' . $this->session->data['token'] . $url, 'SSL');

		if (isset($this->request->get['currency_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$currency_info = $this->model_localisation_currency->getCurrency($this->request->get['currency_id']);
		}

		if (isset($this->request->post['title'])) {
			$this->data['title'] = $this->request->post['title'];
		} elseif (!empty($currency_info)) {
			$this->data['title'] = $currency_info['title'];
		} else {
			$this->data['title'] = '';
		}

		if (isset($this->request->post['code'])) {
			$this->data['code'] = $this->request->post['code'];
		} elseif (!empty($currency_info)) {
			$this->data['code'] = $currency_info['code'];
		} else {
			$this->data['code'] = '';
		}

		if (isset($this->request->post['symbol_left'])) {
			$this->data['symbol_left'] = $this->request->post['symbol_left'];
		} elseif (!empty($currency_info)) {
			$this->data['symbol_left'] = $currency_info['symbol_left'];
		} else {
			$this->data['symbol_left'] = '';
		}

		if (isset($this->request->post['symbol_right'])) {
			$this->data['symbol_right'] = $this->request->post['symbol_right'];
		} elseif (!empty($currency_info)) {
			$this->data['symbol_right'] = $currency_info['symbol_right'];
		} else {
			$this->data['symbol_right'] = '';
		}

		if (isset($this->request->post['decimal_place'])) {
			$this->data['decimal_place'] = $this->request->post['decimal_place'];
		} elseif (!empty($currency_info)) {
			$this->data['decimal_place'] = $currency_info['decimal_place'];
		} else {
			$this->data['decimal_place'] = '';
		}

		if (isset($this->request->post['value'])) {
			$this->data['value'] = $this->request->post['value'];
		} elseif (!empty($currency_info)) {
			$this->data['value'] = $currency_info['value'];
		} else {
			$this->data['value'] = '';
		}

    	if (isset($this->request->post['status'])) {
      		$this->data['status'] = $this->request->post['status'];
    	} elseif (!empty($currency_info)) {
			$this->data['status'] = $currency_info['status'];
		} else {
      		$this->data['status'] = '';
    	}

		$this->template = 'localisation/currency/form.expand';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}
	
	protected function validateForm() { 
		if (!$this->user->hasPermission('modify', 'localisation/currency')) { 
			$this->error['warning'] = $this->language->get('error_permission');
		} 

		if ((utf8_strlen($this->request->post['title']) < 3) || (utf8_strlen($this->request->post['title']) > 32)) {
			$this->error['title'] = $this->language->get('error_title');
		}

		if (utf8_strlen($this->request->post['code']) != 3) {
			$this->error['code'] = $this->language->get('error_code');
		}

		if (!$this->error) { 
			return true;
		} else {
			return false;
		}
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'localisation/currency')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		$this->load->model('setting/store');
		$this->load->model('sale/order');
		
        $this->language->load('localisation/currency');

		foreach ($this->request->post['selected'] as $currency_id) {
			$currency_info = $this->model_localisation_currency->getCurrency($currency_id);

			if ($currency_info) {
				if ($this->config->get('config_currency') == $currency_info['code']) {
					$this->error['warning'] = $this->language->get('error_default');
				}
				
				$store_total = $this->model_setting_store->getTotalStoresByCurrency($currency_info['code']);
	
				if ($store_total) {
					$this->error['warning'] = sprintf($this->language->get('error_store'), $store_total);
				}					
			}
			
			$order_total = $this->model_sale_order->getTotalOrdersByCurrencyId($currency_id);

			if ($order_total) {
				$this->error['warning'] = sprintf($this->language->get('error_order'), $order_total);
			}					
		}
		
		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}

    public function dtDelete()
    {
        $this->load->model('localisation/currency');
        $this->load->model('setting/setting');

        if (isset($this->request->post['selected']) && $this->validateDelete()) {

            $this->load->model('setting/audit_trail');
            $pageStatus = $this->model_setting_audit_trail->getPageStatus("lang_settings");
            $this->load->model('loghistory/histories');
            foreach ($this->request->post['selected'] as $id) {

                $oldValue = $this->model_localisation_currency->getCurrency($id);
                $this->model_localisation_currency->deleteCurrency($id);
                // add data to log_history
                if($pageStatus){
                    $log_history['action'] = 'delete';
                    $log_history['reference_id'] = $id;
                    $log_history['old_value'] = json_encode($oldValue,JSON_UNESCAPED_UNICODE);
                    $log_history['new_value'] = NULL;
                    $log_history['type'] = 'currency';
                    $this->model_loghistory_histories->addHistory($log_history);
                }
            }

            $response['status'] = 'success';
            $response['title'] = $this->language->get('notification_success_title');
            $response['message'] = $this->language->get('message_deleted_successfully');

            $this->tracking->updateGuideValue('CURRENCY');
        } else {
            $response['status'] = 'error';
            $response['title'] = $this->language->get('notification_error_title');
            $response['errors'] = $this->error;
        }

        $this->response->setOutput(json_encode($response));
        return;
    }

    public function dtUpdateStatus()
    {
        $this->load->model('localisation/currency');
        $this->load->model('setting/setting');
        if (isset($this->request->post["id"]) && isset($this->request->post["status"])) {
            $id = $this->request->post["id"];
            $status = $this->request->post["status"];


            $data = $this->model_localisation_currency->getCurrency($id);
            $oldValue = $data;
            $data["status"] = $status;
            $this->model_localisation_currency->editCurrency($id, $data);

            // add data to log_history
            $this->load->model('setting/audit_trail');
            $pageStatus = $this->model_setting_audit_trail->getPageStatus("lang_settings");
            if($pageStatus){
                $log_history['action'] = 'update';
                $log_history['reference_id'] = $id;
                $log_history['old_value'] = json_encode($oldValue,JSON_UNESCAPED_UNICODE);
                $log_history['new_value'] = json_encode($data,JSON_UNESCAPED_UNICODE);
                $log_history['type'] = 'currency';
                $this->load->model('loghistory/histories');
                $this->model_loghistory_histories->addHistory($log_history);
            }

            $this->tracking->updateGuideValue('CURRENCY');

            $response['status'] = 'success';
            $response['title'] = $this->language->get('notification_success_title');
            $response['message'] = $this->language->get('message_updated_successfully');
        } else {
            $response['status'] = 'error';
            $response['title'] = $this->language->get('notification_error_title');
            $response['errors'] = $this->language->get('notification_unknoen_error');
        }

        $this->response->setOutput(json_encode($response));
        return;
    }
}
