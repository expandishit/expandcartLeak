<?php
require_once('jwt_helper.php');

class ControllerApiSeller extends Controller
{
    public function index()
    {
        try {
            $this->load->language('api/login');

            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;
            $this->load->model('account/api');

            if (!isset($this->session->data['api_id'])) {
                $json['error']['warning'] = $this->language->get('error_permission');
            } else {
                $this->load->model('multiseller/seller');

                $data = [
                    'search_key' => $this->request->get['search_key'],
                    'working_outside' => $this->request->get['working_outside']
                    && in_array($this->request->get['working_outside'], [true, false])
                        ? $this->request->get['working_outside'] : null,
                    'offset' => $this->request->get['offset'] ?: 0,
                    'limit' => $this->request->get['limit'] ?: 50,
                    'sort_by' => $this->request->get['sort_by'] ?: 'desc',
                ];

                $results['message'] = 'successfully query';
                $results['data'] = $this->model_multiseller_seller->index($data);


                $this->response->addHeader('Content-Type: application/json');
                $this->response->addHeader('Access-Control-Allow-Origin: *');
                $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
                $this->response->addHeader('Access-Control-Allow-Credentials: true');
                $this->response->setOutput(json_encode($results));
            }
        } catch (Exception $exception) {

        }
    } 
    public function get()
    {
        try {
            $this->load->language('api/login');

            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;
            $this->load->model('account/api');

            if (!isset($this->session->data['api_id'])) {
                $json['error']['warning'] = $this->language->get('error_permission');
            } else {
                $seller_id = $this->request->get['seller_id'];
                $results = [];

                if ($seller_id != null) {
                    $this->load->model('multiseller/seller');
                    $data = $this->model_multiseller_seller->get($seller_id);   
                    if (is_bool($data)) {
                        $results['message'] = 'failed! no such seller';
                        $results['data'] = [];
                        http_response_code(422);
                    }  elseif (is_string($data)) {
                        $results['message'] = 'error happened';
                        $results['data'] = [];
                        http_response_code(500);
                    } else {
                        $results['message'] = 'successful query';
                        $results['data'] = $data;
                    }
                } else {
                    $results['message'] = 'failed! seller_id is required';
                    $results['data'] = [];
                    http_response_code(422);
                }


                $this->response->addHeader('Content-Type: application/json');
                $this->response->addHeader('Access-Control-Allow-Origin: *');
                $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
                $this->response->addHeader('Access-Control-Allow-Credentials: true');
                $this->response->setOutput(json_encode($results));
            }
        } catch (Exception $exception) {

        }

    }
    
    /**
     * Get all messages related to the given customer and seller
     */
    public function getSellerMessages()
    {
        try {
            $this->load->language('api/login');

            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;
            $this->load->model('account/api');

            if (!isset($this->session->data['api_id'])) {
                $results['error']['warning'] = $this->language->get('error_permission');
            } else {
                $customer_id = $params->customer_id;
                $seller_id = $params->seller_id;
                $results = [];
                if ($seller_id != null && $customer_id != null) {
                    $this->load->model('multiseller/seller');
                    
                    $seller_info = $this->model_multiseller_seller->get($seller_id);
                    if (is_bool($seller_info)) {
                        $results['message'] = 'failed! no such seller';
                        $results['data'] = [];
                        http_response_code(422);
                    }  elseif (is_string($seller_info)) {
                        $results['message'] = 'error happened';
                        $results['data'] = [];
                        http_response_code(500);
                    } else {
                        $results['message'] = 'successful query';
                        $results['seller_info'] = $seller_info;
                    }
                    $results['results'] = $this->model_multiseller_seller->getsellerMessages($customer_id,$seller_id);
                } else {
                    $results['message'] = 'failed! seller_id is required';
                    $results['data'] = [];
                    http_response_code(422);
                }
            }

            $this->response->addHeader('Content-Type: application/json');
            $this->response->addHeader('Access-Control-Allow-Origin: *');
            $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
            $this->response->addHeader('Access-Control-Allow-Credentials: true');
            $this->response->setOutput(json_encode($results));
        } catch (Exception $exception) {
            http_response_code(500);

        }
    }

    /**
     * Post new Message From the customer To the seller
     */
    public function postMessageToSeller()
    {
        try {
            $this->load->language('api/login');

            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;
            $this->load->model('account/api');

            if (!isset($this->session->data['api_id'])) {
                $results['error']['warning'] = $this->language->get('error_permission');
            } else {
                $customer_id = $params->customer_id;
                $seller_id = $params->seller_id;
                $subject = $params->subject;
                $message = $params->message;
                $product_id = $params->product_id;
                $results = [];
                if ($seller_id != null && $customer_id != null && $subject != null && $product_id != null && $message != null ) {
                    $this->load->model('multiseller/seller');
                    $data['user1_id'] = $customer_id;
                    $data['user2_id'] = $seller_id;
                    $data['subject'] = $subject;
                    $data['msg'] = $message;
                    $data['product_id'] = $params->product_id;
                    $results['result']['success'] = $this->model_multiseller_seller->PostMessageToSeller($data);
                } else {
                    $results['message'] = 'failed! seller_id is required';
                    $results['data'] = [];
                    http_response_code(422);
                }
            }

            $this->response->addHeader('Content-Type: application/json');
            $this->response->addHeader('Access-Control-Allow-Origin: *');
            $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
            $this->response->addHeader('Access-Control-Allow-Credentials: true');
            $this->response->setOutput(json_encode($results));
        } catch (Exception $exception) {
            http_response_code(500);
        }
    }

    /**
     * Post new Message Between the given customer and all other sellers
     */
    public function getAllConversations()
    {
        try {
            $this->load->language('api/login');

            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;
            $this->load->model('account/api');

            if (!isset($this->session->data['api_id'])) {
                $results['error']['warning'] = $this->language->get('error_permission');
            } else {
                $customer_id = $params->customer_id;
                $results = [];
                if ( $customer_id != null ) {
                    $this->load->model('multiseller/seller');
                    $data['customer_id'] = $customer_id;
                    $results['results'] = $this->model_multiseller_seller->getConversations($customer_id);
                } else {
                    $results['message'] = 'failed! customer_id is required';
                    $results['data'] = [];
                    http_response_code(422);
                }
            }

            $this->response->addHeader('Content-Type: application/json');
            $this->response->addHeader('Access-Control-Allow-Origin: *');
            $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
            $this->response->addHeader('Access-Control-Allow-Credentials: true');
            $this->response->setOutput(json_encode($results));
        } catch (Exception $exception) {
            http_response_code(500);
        }
    }

  


}