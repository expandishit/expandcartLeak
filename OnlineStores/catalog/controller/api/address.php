<?php
require_once('jwt_helper.php');
class ControllerApiAddress extends Controller {
	
    public function insert() {

        $json = [];
        $this->response->addHeader('Content-Type: application/json');
        $this->response->addHeader('Access-Control-Allow-Origin: *');
        $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
        $this->response->addHeader('Access-Control-Allow-Credentials: true');


        if (!isset($this->session->data['api_id'])) {
            $json['error']['warning'] = $this->language->get('error_permission');
            $this->response->setOutput(json_encode($json));
            return;
        }

        try {

            $data = (array)json_decode(file_get_contents('php://input'));

            $this->language->load('account/address');

            if (!isset($data['customer_id']) || !$data['customer_id']) {
                $json['error']['customer_id'] = $this->language->get('error_customer_id');
            }
            if (!isset($data['firstname']) || !$data['firstname']) {
                $json['error']['firstname'] = $this->language->get('error_firstname');
            }
            if (!isset($data['lastname']) || !$data['lastname']) {
                $json['error']['lastname'] = $this->language->get('error_lastname');
            }
            if (!isset($data['address_1']) || !$data['address_1']) {
                $json['error']['address_1'] = $this->language->get('error_address_1');
            }
            if (!isset($data['city']) || !$data['city']) {
                $json['error']['city'] = $this->language->get('error_city');
            }
            if (!isset($data['zone_id']) || !$data['zone_id']) {
                $json['error']['zone_id'] = $this->language->get('error_zone');
            }
            if (!isset($data['country_id']) || !$data['country_id']) {
                $json['error']['country_id'] = $this->language->get('error_country');
            }
            if (!isset($data['telephone']) || !$data['telephone']) {
                $json['error']['telephone'] = $this->language->get('error_telephone');
            }

            if (isset($json['error']) && !empty($json['error'])) {
                $json['status'] = 'validation_error';
            } else {
                
                $this->load->model('account/address');
                $data['default'] = isset($data['default']) && $data['default'] ? (bool)$data['default'] : false;
                $address = $this->model_account_address->addAddress($data);
                $json = [
                    'status' => 'ok'
                ];
            }

        } catch (Exception $exception) {
            http_response_code(500);
            $json['error']['warning'] = 'Something went wrong';
        }
        $this->response->setOutput(json_encode($json));
    }

    public function edit() {

        $json = [];
        $this->response->addHeader('Content-Type: application/json');
        $this->response->addHeader('Access-Control-Allow-Origin: *');
        $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
        $this->response->addHeader('Access-Control-Allow-Credentials: true');


        if (!isset($this->session->data['api_id'])) {
            $json['error']['warning'] = $this->language->get('error_permission');
            $this->response->setOutput(json_encode($json));
        }

        try {

            $data = (array)json_decode(file_get_contents('php://input'));

            $this->language->load('account/address');

            if (!isset($data['address_id']) || !$data['address_id']) {
                $json['error']['address_id'] = $this->language->get('error_address_id');
            }
            if (!isset($data['customer_id']) || !$data['customer_id']) {
                $json['error']['customer_id'] = $this->language->get('error_customer_id');
            }
            if (!isset($data['firstname']) || !$data['firstname']) {
                $json['error']['firstname'] = $this->language->get('error_firstname');
            }
            if (!isset($data['lastname']) || !$data['lastname']) {
                $json['error']['lastname'] = $this->language->get('error_lastname');
            }
            if (!isset($data['address_1']) || !$data['address_1']) {
                $json['error']['address_1'] = $this->language->get('error_address_1');
            }
            if (!isset($data['city']) || !$data['city']) {
                $json['error']['city'] = $this->language->get('error_city');
            }
            if (!isset($data['zone_id']) || !$data['zone_id']) {
                $json['error']['zone_id'] = $this->language->get('error_zone_id');
            }
            if (!isset($data['country_id']) || !$data['country_id']) {
                $json['error']['country_id'] = $this->language->get('error_country_id');
            }
            if (!isset($data['telephone']) || !$data['telephone']) {
                $json['error']['telephone'] = $this->language->get('error_telephone');
            }

            if (isset($json['error']) && !empty($json['error'])) {
                $json['status'] = 'validation_error';
            } else {
                
                $this->load->model('account/address');
                $data['default'] = isset($data['default']) && $data['default'] ? (bool)$data['default'] : false;
                $address = $this->model_account_address->editAddress($data['address_id'], $data);
                $json = [
                    'status' => 'ok'
                ];
            }

        } catch (Exception $exception) {
            http_response_code(500);
            $json['error']['warning'] = 'Something went wrong';
        }
        $this->response->setOutput(json_encode($json));
    }

    public function delete() {

        $json = [];
        $this->response->addHeader('Content-Type: application/json');
        $this->response->addHeader('Access-Control-Allow-Origin: *');
        $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
        $this->response->addHeader('Access-Control-Allow-Credentials: true');


        if (!isset($this->session->data['api_id'])) {
            $json['error']['warning'] = $this->language->get('error_permission');
            $this->response->setOutput(json_encode($json));
        }

        try {

            $data = (array)json_decode(file_get_contents('php://input'));

            $this->language->load('account/address');

            if (!isset($data['address_id']) || !$data['address_id']) {
                $json['error']['address_id'] = $this->language->get('error_address_id');
            }
            if (!isset($data['customer_id']) || !$data['customer_id']) {
                $json['error']['customer_id'] = $this->language->get('error_customer_id');
            }

            if (isset($json['error']) && !empty($json['error'])) {
                $json['status'] = 'validation_error';
            } else {
                
                $this->load->model('account/address');
                $address = $this->model_account_address->deleteAddress($data['address_id'], $data['customer_id']);
                $json = [
                    'status' => 'ok'
                ];
            }

        } catch (Exception $exception) {
            http_response_code(500);
            $json['error']['warning'] = 'Something went wrong';
        }
        $this->response->setOutput(json_encode($json));
    }

    public function all() {

        $json = [];
        $this->response->addHeader('Content-Type: application/json');
        $this->response->addHeader('Access-Control-Allow-Origin: *');
        $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
        $this->response->addHeader('Access-Control-Allow-Credentials: true');


        if (!isset($this->session->data['api_id'])) {
            $json['error']['warning'] = $this->language->get('error_permission');
            $this->response->setOutput(json_encode($json));
            return;
        }

        try {

            $data = (array)json_decode(file_get_contents('php://input'));

            $this->language->load('account/address');

            if (!isset($data['customer_id']) || !$data['customer_id']) {
                $json['error']['customer_id'] = $this->language->get('error_customer_id');
            }

            if (isset($json['error']) && !empty($json['error'])) {
                $json['status'] = 'validation_error';
            } else {
                
                $this->load->model('account/address');
                $addresses = array_values($this->model_account_address->getAddresses($data['customer_id']));
                $json = [
                    'status' => 'ok',
                    'data' => compact('addresses')
                ];
            }

        } catch (Exception $exception) {
            http_response_code(500);
            $json['error']['warning'] = 'Something went wrong';
        }
        $this->response->setOutput(json_encode($json));
    }
}