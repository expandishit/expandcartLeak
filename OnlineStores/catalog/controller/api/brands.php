<?php
class ControllerApiBrands extends Controller
{
    public function index()
    {
        $this->response->addHeader('Content-Type: application/json');
        $this->response->addHeader('Access-Control-Allow-Origin: *');
        $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
        $this->response->addHeader('Access-Control-Allow-Credentials: true');

        $this->load->language('api/brands');
        $json = array();

        $params = json_decode(file_get_contents('php://input'));
        $encodedtoken = $params->token;
        $this->load->model('catalog/manufacturer');

        if (!isset($this->session->data['api_id'])) {
            $json['error']['warning'] = $this->language->get('error_permission');
            $this->response->setOutput(json_encode($json));
            return;
        }

        $data['start'] = $params->start ?: 0;
        $data['limit'] = $params->limit ?: 10;
        $data['sort'] = $params->sort ?: 'm.name';
        $data['order'] = $params->order ?: 'ASC';

        $columns = ['m.manufacturer_id', 'm.name', 'm.image', 'm.slug'];

        $data['columns'] = $columns;

        $brands = $this->model_catalog_manufacturer->getManufacturers($data);
        foreach ($brands as &$brand){
            $brand['categories'] = $this->model_catalog_manufacturer->getCategoryByManufacturerId($brand['manufacturer_id']);
            $brand['brand_image']= \Filesystem::getUrl('image/' .str_replace(' ', '%20', $brand['image']) );
        }
        $json['status'] = 'OK';
        $json['data'] = $brands;

        $this->model_account_api->updateSession($encodedtoken);

        $this->response->setOutput(json_encode($json));
    }

    public function get()
    {
        $this->response->addHeader('Content-Type: application/json');
        $this->response->addHeader('Access-Control-Allow-Origin: *');
        $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
        $this->response->addHeader('Access-Control-Allow-Credentials: true');

        $this->load->language('api/brands');
        $json = array();

        $params = json_decode(file_get_contents('php://input'));
        $encodedtoken = $params->token;
        $this->load->model('catalog/manufacturer');

        if (!isset($this->session->data['api_id'])) {
            $json['error']['warning'] = $this->language->get('error_permission');
            $this->response->setOutput(json_encode($json));
            return;
        }

        $params->brand_id = $params->brand_id ?: $this->request->get['brand_id'];

        if (
            isset($params->brand_id) == false ||
            filter_var($params->brand_id, FILTER_VALIDATE_INT) === false
        ) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'INVALID_CREDENTIALS',
                'errors' => [$this->language->get('invalid_credentials')]
            ]));
            return;
        }

        $brandId = $params->brand_id;

        $columns = ['m.manufacturer_id', 'm.name', 'm.image', 'm.slug'];

        $data['columns'] = $columns;

        $brand = $this->model_catalog_manufacturer->getManufacturer($brandId);

        if (!$brand) {
            $this->response->setOutput(json_encode([
                'status' => 'ERR',
                'error' => 'UNDEFINED_ROW',
                'errors' => [$this->language->get('undefined_row')]
            ]));
            return;
        }

        $json['status'] = 'OK';
        $json['data'] = $brand;
        $this->model_account_api->updateSession($encodedtoken);

        $this->response->setOutput(json_encode($json));
    }
}
