<?php

class ControllerApiDomain extends Controller
{
    public function index()
    {
        try {

            $json = array();
            if (!isset($this->session->data['api_id'])) {
                $json['error'] = $this->language->get('error_permission');
            } else {
                $country_code = getallheaders()['Country'];

                $this->load->model('module/dedicated_domains/domains');
                $json = $this->registry->get('model_module_dedicated_domains_domains')->getDomainByCountry(strtoupper($country_code));

                if (is_bool($json)) {
                    $json = $this->registry->get('model_module_dedicated_domains_domains')->getDomainByCountry(strtoupper('www'));
                }
            }

            $this->response->addHeader('Content-Type: application/json');
            $this->response->addHeader('Access-Control-Allow-Origin: *');
            $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
            $this->response->addHeader('Access-Control-Allow-Credentials: true');
            $this->response->setOutput(json_encode($json));

        } catch (Exception $exception) {
            // No exception handler
        }
    }
}