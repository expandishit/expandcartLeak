<?php
require_once('jwt_helper.php');
class ControllerApiLogout extends Controller {

    public function index()
    {
        try {
            $this->load->language('api/logout');
            $json = array();

            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;
            $this->load->model('account/api');


            if (!isset($this->session->data['api_id'])) {
                $json['error']['warning'] = $this->language->get('error_permission');

            } else {
                if ($this->customer->isLogged()) {
                    $this->customer->logout();
                    $this->cart->clear();

                    unset($this->session->data['wishlist']);
                    unset($this->session->data['shipping_address_id']);
                    unset($this->session->data['shipping_country_id']);
                    unset($this->session->data['shipping_zone_id']);
                    unset($this->session->data['shipping_postcode']);
                    unset($this->session->data['shipping_method']);
                    unset($this->session->data['shipping_methods']);
                    unset($this->session->data['payment_address_id']);
                    unset($this->session->data['payment_country_id']);
                    unset($this->session->data['payment_zone_id']);
                    unset($this->session->data['payment_method']);
                    unset($this->session->data['payment_methods']);
                    unset($this->session->data['comment']);
                    unset($this->session->data['order_id']);
                    unset($this->session->data['coupon']);
                    unset($this->session->data['reward']);
                    unset($this->session->data['voucher']);
                    unset($this->session->data['vouchers']);

                    $this->load->model('account/api');
                    $this->model_account_api->updateSession($encodedtoken);


                    $results['message'] = $this->language->get('text_message');

                    $this->response->addHeader('Content-Type: application/json');
                    $this->response->addHeader('Access-Control-Allow-Origin: *');
                    $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
                    $this->response->addHeader('Access-Control-Allow-Credentials: true');
                    $this->response->setOutput(json_encode($results));

                } else {
                    $results['message'] = $this->language->get('text_message');
                    $this->response->addHeader('Content-Type: application/json');
                    $this->response->addHeader('Access-Control-Allow-Origin: *');
                    $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
                    $this->response->addHeader('Access-Control-Allow-Credentials: true');
                    $this->response->setOutput(json_encode($results));
                }
            }
        } catch (Exception $exception) {

        }

    }

}