<?php

class ControllerApiCountry extends Controller
{

    public function getAllCountries()
    {
        $json = [];
        $this->response->addHeader('Content-Type: application/json');
        $this->response->addHeader('Access-Control-Allow-Origin: *');
        $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
        $this->response->addHeader('Access-Control-Allow-Credentials: true');

        $data = (array)json_decode(file_get_contents('php://input'));

        $this->language->load('blog/blog');

        if (!isset($this->session->data['api_id'])) {
            $json['error']['warning'] = $this->language->get('error_permission');
            $this->response->setOutput(json_encode($json));
            return;
        }

        $this->initializer([
            'country' => 'catalog/country',
        ]);

        try {

            $countries = $this->country->getAllCountries(
                $data['language_id']
            );
            if ($countries)
            {
                $json['success'] = true;
                $json['data'] = $countries;
            }

        } catch (Exception $exception) {
            http_response_code(500);
            $json['error']['warning'] = 'Something went wrong';
        }
        $this->response->setOutput(json_encode($json));
    }
}
