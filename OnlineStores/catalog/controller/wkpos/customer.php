<?php
class ControllerWkposCustomer extends Controller {
	public function index() {
		$json['customers'] = array();

		$this->load->model('wkpos/customer');
		$json['customers'] = array();
		$customers = $this->model_wkpos_customer->getCustomers();
		foreach ($customers as $customer)  {
			if (is_null($customer['credit_amount'])) {
				$credit_uf = 0;
				$credit_f = $this->currency->format($credit_uf, $this->session->data['currency']);
			} else {
				$credit_uf = $this->currency->convert($customer['credit_amount'], $this->config->get('config_currency'), $this->session->data['currency']);
				$credit_f = $this->currency->format($credit_uf, $this->session->data['currency']);
			}
			$json['customers'][] = array(
				'customer_id'			=> $customer['customer_id'],
				'name'						=> $customer['name'],
				'email'						=> $customer['email'],
				'telephone'				=> $customer['telephone'],
				'credit_uf'				=> $credit_uf,
				'credit_f'				=> $credit_f,
			);
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

    public function addCustomer() {
        if (defined('LOGIN_MODE') && LOGIN_MODE === 'identity' && $this->identity->isStoreOnWhiteList()) {
            $this->addCustomerV2();
            return; 
        }
        
		$json = array();
		$this->load->language('account/register');
		$this->load->language('account/address');
		$this->load->model('account/customer');

		if ((utf8_strlen(trim($this->request->post['firstname'])) < 1) || (utf8_strlen(trim($this->request->post['firstname'])) > 32)) {
			$json['firstname'] = $this->language->get('error_firstname');
		}

		if ((utf8_strlen(trim($this->request->post['lastname'])) < 1) || (utf8_strlen(trim($this->request->post['lastname'])) > 32)) {
			$json['lastname'] = $this->language->get('error_lastname');
		}

		if ((utf8_strlen(trim($this->request->post['email'])) > 96) || !filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
			$json['email'] = $this->language->get('error_email');
		}

		if ($this->model_account_customer->getTotalCustomersByEmail($this->request->post['email'])) {
			$json['email'] = $this->language->get('error_exists');
		}

		if ((utf8_strlen($this->request->post['telephone']) < 3) || (utf8_strlen($this->request->post['telephone']) > 32)) {
			$json['telephone'] = $this->language->get('error_telephone');
		}

		if ((utf8_strlen(trim($this->request->post['address_1'])) < 3) || (utf8_strlen(trim($this->request->post['address_1'])) > 128)) {
			$json['address_1'] = $this->language->get('error_address_1');
		}

		if ((utf8_strlen(trim($this->request->post['city'])) < 2) || (utf8_strlen(trim($this->request->post['city'])) > 128)) {
			$json['city'] = $this->language->get('error_city');
		}

		$this->load->model('localisation/country');

		$country_info = $this->model_localisation_country->getCountry($this->request->post['country_id']);

		if ($country_info && $country_info['postcode_required'] && (utf8_strlen(trim($this->request->post['postcode'])) < 2 || utf8_strlen(trim($this->request->post['postcode'])) > 10)) {
			$json['postcode'] = $this->language->get('error_postcode');
		}

		if ($this->request->post['country_id'] == '') {
			$json['country'] = $this->language->get('error_country');
		}

		if (!isset($this->request->post['zone_id']) || $this->request->post['zone_id'] == '' || !is_numeric($this->request->post['zone_id'])) {
			$json['zone'] = $this->language->get('error_zone');
		}

		// Customer Group
		if ($this->config->get('wkpos_new_customer_group_id') && is_array($this->config->get('config_customer_group_display')) && in_array($this->config->get('wkpos_new_customer_group_id'), $this->config->get('config_customer_group_display'))) {
			$customer_group_id = $this->config->get('wkpos_new_customer_group_id');
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}

		if ($json) {
			$json['error'] = 1;
		}

		if (!$json) {
			$this->load->model('account/customer');
			$this->load->model('account/address');
			$data = array();
			$data = $this->request->post;
			$data['fax'] = '';
			$data['address_2'] = '';
			$data['company'] = '';
			$data['customer_group_id'] = $customer_group_id;
			if ($this->config->get('wkpos_customer_password')) {
				$data['password'] = $this->config->get('wkpos_customer_password');
			} else {
				$data['password'] = '1234';
			}
			if ($this->config->get('wkpos_newsletter')) {
				$data['newsletter'] = $this->config->get('wkpos_newsletter');
			} else {
				$data['newsletter'] = 0;
			}
			$json['customer_id'] = $this->model_account_customer->addCustomer($data);
			$data['default'] = true;
            $data['customer_id'] = $json['customer_id'];
			$this->model_account_address->addAddress($data);
			$json['success'] = 'Customer added successfully';
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
    
    public function addCustomerV2()
    {
        $rData = []; // response data
        $pData = $this->request->post; // post data
        $language = $this->language;
        $cFields = $this->getCustomerRegistrationFields()['registration']; // customer setting fields
        $aFields = $this->getCustomerRegistrationFields()['address']; // address setting fields

        $this->load->language('account/register');
        $this->load->language('account/address');
        $this->load->model('account/customer');
        $this->load->model('account/address');

        // start validate fields ...
        if ($cFields['firstname'] == 1 && empty($pData['firstname'])) $rData['firstname'] = $language->get('error_required_field');
        if ($cFields['email'] == 1 && empty($pData['email'])) $rData['email'] = $language->get('error_required_field');
        if (!empty($pData['email']) && !filter_var($pData['email'], FILTER_VALIDATE_EMAIL)) $rData['email'] = $language->get('error_invalid_field');
        if ($cFields['email'] == 1 && !empty($pData['email']) && $this->model_account_customer->getTotalCustomersByEmail($pData['email'])) $rData['email'] = $language->get('error_exists');
        if ($cFields['telephone'] == 1 && empty($pData['telephone'])) $rData['telephone'] = $language->get('error_required_field');
        if (!empty($pData['telephone']) && !$this->simpleValidatePhone($pData['telephone'])) $rData['telephone'] = $language->get('error_invalid_field');
        if ($cFields['gender'] == 1 && empty($pData['gender'])) $rData['gender'] = $language->get('error_required_field');
        if ($cFields['dob'] == 1 && empty($pData['dob'])) $rData['dob'] = $language->get('error_required_field');
        if ($cFields['company'] == 1 && empty($pData['company'])) $rData['company'] = $language->get('error_required_field');

        if ($aFields['country_id'] == 1 && (!isset($pData['country_id']) || $pData['country_id'] == '' || !is_numeric($pData['country_id']))) $rData['country_id'] = $language->get('error_required_field');
        if ($aFields['zone_id'] == 1 && (!isset($pData['zone_id']) || $pData['zone_id'] == '' || !is_numeric($pData['zone_id']))) $rData['zone_id'] = $language->get('error_required_field');
        if ($aFields['area_id'] == 1 && (!isset($pData['area_id']) || $pData['area_id'] == '' || !is_numeric($pData['area_id']))) $rData['area_id'] = $language->get('error_required_field');
        if ($aFields['address_1'] == 1 && utf8_strlen($pData['address_1']) < 3) $rData['address_1'] = $language->get('error_required_field');
        if ($aFields['address_2'] == 1 && utf8_strlen($pData['address_2']) < 3) $rData['address_2'] = $language->get('error_required_field');
        if ($aFields['telephone'] == 1 && !$this->simpleValidatePhone($pData['shipping_telephone'])) $rData['shipping_telephone'] = $language->get('error_invalid_field');
        if ($aFields['telephone'] == 1 && !strlen($pData['shipping_telephone'])) $rData['shipping_telephone'] = $language->get('error_required_field');
        if ($aFields['postcode'] == 1 && (!isset($pData['postcode']) || $pData['postcode'] == '')) $rData['postcode'] = $language->get('required_input_postcode');
        if (!empty($pData['postcode']) && !is_numeric($pData['postcode'])) $rData['postcode'] = $language->get('invalid_input_postcode');
        // end validate fields

        if (!empty($rData)) {
            $rData['error'] = 1;
        }

        if (empty($rData)) {
            if (empty($pData['customer_group_id'])) {
                if ($this->config->get('wkpos_new_customer_group_id') && is_array($this->config->get('config_customer_group_display')) && in_array($this->config->get('wkpos_new_customer_group_id'), $this->config->get('config_customer_group_display'))) {
                    $pData['customer_group_id'] = $this->config->get('wkpos_new_customer_group_id');
                } else {
                    $pData['customer_group_id'] = $this->config->get('config_customer_group_id');
                }
            }

            if ($this->config->get('wkpos_customer_password')) {
                $pData['password'] = $this->config->get('wkpos_customer_password');
            } else {
                $pData['password'] = '1234';
            }

            $rData['customer_id'] = $this->model_account_customer->addCustomer($pData);

            if ($rData['customer_id']) $this->model_account_address->addAddress(array_merge($pData, ['customer_id' => $rData['customer_id'], 'default' => true]));

            $rData['success'] = 'Customer added successfully';
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($rData));
    }
    
    private function getCustomerRegistrationFields()
    {
        $fields = $this->config->get('config_customer_fields');
        $fields['registration']['email'] = (int)!$this->identity->isLoginByPhone();
        $fields['registration']['telephone'] = (int)!$fields['registration']['email'];
        
        return $fields;
    }
    
    private function simpleValidatePhone(string $phone = null)
    {
        if (!$phone) return false;

        preg_match('/^[0-9\-\(\)\/\+\s]*$/', $phone, $matches);

        return !empty($matches);
    }
}
