<?php

class ControllerApiArea extends Controller
{

    public function getAreasByZone()
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
            'area' => 'catalog/area',
        ]);

        try {

            $json['areas'] = $this->area->getAreasByZone(
                $data['zone_id'], $data['language_id']
            );

            $json['status'] = 'ok';

        } catch (Exception $exception) {
            http_response_code(500);
            $json['error']['warning'] = 'Something went wrong';
        }
        $this->response->setOutput(json_encode($json));
    }
}
