<?php

/*
 * we using this class to create functions (routes that using in api) to use them in general cases
 */

require_once('jwt_helper.php');

class ControllerApiGeneral extends Controller {

    public function generalFilter() {
        $json = array();
        $data = [];
        $filterFunctionArray = [
            'category' => ['model' => 'catalog/category', 'function' => 'searchCategories', 'objectName' => "model_catalog_category"],
            'brand' => ['model' => 'catalog/manufacturer', 'function' => 'searchManufacturers', 'objectName' => "model_catalog_manufacturer"],
        ];

        try {

            $this->load->language('api/login');
            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;

            if (!isset($this->session->data['api_id'])) {
                http_response_code(400);
                $json['error']['warning'] = $this->language->get('error_permission');
            } else {

                $filters = $params->filters;
                $filterText = $params->filterText;
                $filterStart = $params->start;
                $filterLimit = $params->limit;

                $filtersArray = explode(',', $filters);

                foreach ($filtersArray as $filterData) {
                    
                    if(isset($filterFunctionArray[$filterData]))
                    {
                        $modelPath = $filterFunctionArray[$filterData]['model'];
                        $modelFunction = $filterFunctionArray[$filterData]['function'];
                        $modelObjectName = $filterFunctionArray[$filterData]['objectName'];

                        $this->load->model($modelPath);

                        $data['filter_name'] = $filterText;
                        $data['start'] = $filterStart;
                        $data['limit'] = $filterLimit;

                        $json[$filterData] = $this->{$modelObjectName}->{$modelFunction}($data);
                    }   
                }
            }
        } catch (Exception $exception) {
            http_response_code(500);
            $json['error']['warning'] = 'Something went wrong';
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->addHeader('Access-Control-Allow-Origin: *');
        $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
        $this->response->addHeader('Access-Control-Allow-Credentials: true');

        $this->response->setOutput(json_encode($json));
    }
    public function getAreas()
    {
        try {

            $this->load->language('api/login');
            $params = json_decode(file_get_contents('php://input'));
            $json = array();
            if (!isset($this->session->data['api_id'])) {
                $json['error'] = $this->language->get('error_permission');
            } else {
                $this->load->model('localisation/area');
                $json['areas'] = $this->model_localisation_area->getAreas();
            }
            $this->response->addHeader('Content-Type: application/json');
            $this->response->addHeader('Access-Control-Allow-Origin: *');
            $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
            $this->response->addHeader('Access-Control-Allow-Credentials: true');

            $this->response->setOutput(json_encode($json));
        } catch (Exception $exception) {
            $json['error'] = $exception->getMessage();
        }
    }
  
}

?>
