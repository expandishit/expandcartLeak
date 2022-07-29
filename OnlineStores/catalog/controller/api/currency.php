<?php
class ControllerApiCurrency extends Controller {
    public function getCurrencies()
    {
        try {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->addHeader('Access-Control-Allow-Origin: *');
            $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
            $this->response->addHeader('Access-Control-Allow-Credentials: true');


            $json = array();
            if (!isset($this->session->data['api_id'])) {
                $json['error'] = $this->language->get('error_permission');
            } else {
                $json['currencies'] = $this->currency->getCurrencies();
            }

            $this->response->setOutput(json_encode($json));
        } catch (Exception $exception) {
            $json['error'] = $exception->getMessage();
        }
    }

    public function getCurrentCurrency()
    {
        try {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->addHeader('Access-Control-Allow-Origin: *');
            $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
            $this->response->addHeader('Access-Control-Allow-Credentials: true');


            $json = array();
            if (!isset($this->session->data['api_id'])) {
                $json['error'] = $this->language->get('error_permission');
            } else {
                $json['currency'] = $this->currency->getCode();
            }

            $this->response->setOutput(json_encode($json));
        } catch (Exception $exception) {
            $json['error'] = $exception->getMessage();
        }
    }
}
