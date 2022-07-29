<?php

class ControllerApiZone extends Controller
{

    public function getZones()
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

        if (!isset($data['country_id']) || empty($data['country_id']))
        {
            $json['success'] = false;
            $json['data'] = "country_id is required";
        }

        $this->initializer([
            'zone' => 'catalog/zone',
        ]);

        try {

            $zones = $this->zone->getZones(
                $data["country_id"], $data['language_id']
            );
            if ($zones)
            {
                $json['success'] = true;
                $json['data'] = $zones;
            }

        } catch (Exception $exception) {
            http_response_code(500);
            $json['error']['warning'] = 'Something went wrong';
        }
        $this->response->setOutput(json_encode($json));
    }
}
