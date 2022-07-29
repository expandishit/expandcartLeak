<?php

class ControllerProductCommerceProduct extends Controller
{
    private $error = array();

    public function addProductGeneral()
    {
        $json = array();
        $data = $this->request->get ?? [];

        if ($data) {
            if ((utf8_strlen($data['product_name']) < 3) || (utf8_strlen($data['product_name']) > 255)) {
                $json['msg'] = 'Product Name must be greater than 3 and less than 255 characters!';
            }

            if ((utf8_strlen($data['meta_title']) < 3) || (utf8_strlen($data['meta_title']) > 255)) {
                $json['msg'] = 'Meta Title must be greater than 3 and less than 255 characters!';
            }

            if (!$json) {
                $this->load->model('checkout/commerce');
                $return = $this->model_checkout_commerce->addEbayProductGeneral($data);
                if ($return) {
                    $json['success'] = true;
                    $json['product_id'] = $return;
                } else {
                    $json['error'] = true;
                    $json['is_exist'] = true;
                    $json['msg'] = 'Product is already imported, please try some other product!';
                }
            } else {
                $json['error'] = true;
            }
        } else {
            $json['error'] = true;
            $json['msg'] = 'There is some issue, please try again later!';
        }
        $this->response->addHeader('Content-Type: application/javascript');
        $this->response->setOutput($data['callback'].'( '.json_encode($json).' )');
    }

    public function addOptions()
    {
        $json = array();
        $data = $this->request->get ?? [];
        if ($data && !empty($data['productOptions'])) {
            $this->load->model('checkout/commerce');
            $return = $this->model_checkout_commerce->addOptionsToCore($data['productOptions'], $data['product_id']);
            if ($return) {
                $json['success'] = true;
            } else {
                $json['error'] = true;
            }
        } elseif (empty($data['productOptions'])) {
            $json['success'] = true;
        } else {
            $json['error'] = true;
            $json['msg'] = 'Warning: Options could not be imported successfully!';
        }
        $this->response->addHeader('Content-Type: application/javascript');
        $this->response->setOutput($data['callback'].'( '.json_encode($json).' )');
    }

    public function addProductOption()
    {
        $json = array();
        $data = $this->request->get ?? [];
        if ($data) {
            $this->load->model('checkout/commerce');
            $return = $this->model_checkout_commerce->addEbayProductOption($data);
            if ($return) {
                $json['success'] = true;
            } else {
                $json['error'] = true;
            }
        } else {
            $json['error'] = true;
            $json['msg'] = 'Warning: Product could not be imported successfully, Please try it again!';
        }
        $this->response->addHeader('Content-Type: application/javascript');
        $this->response->setOutput($data['callback'].'( '.json_encode($json).' )');
    }

    public function addProductCommerce()
    {
        $json = array();
        $data = $this->request->get ?? [];
        if ($data) {
            $this->load->model('checkout/commerce');
            $return = $this->model_checkout_commerce->addEbayProductCommerce($data);
            if ($return) {
                $json['success'] = true;
            } else {
                $json['error'] = true;
            }
        } else {
            $json['error'] = true;
            $json['msg'] = "Warning: Product's review could not be imported successfully, Please try it again!";
        }
        $this->response->addHeader('Content-Type: application/javascript');
        $this->response->setOutput($data['callback'].'( '.json_encode($json).' )');
    }

    public function addProductAttribute()
    {
        $json = array();
        $data = $this->request->get ?? [];
        if ($data) {
            $this->load->model('checkout/commerce');
            $return = $this->model_checkout_commerce->addEbayProductAttribute($data);
            if ($return) {
                $json['success'] = true;
            } else {
                $json['error'] = true;
            }
        } else {
            $json['error'] = true;
            $json['msg'] = "Warning: Product's option could not be imported successfully, Please try it again!";
        }
        $this->response->addHeader('Content-Type: application/javascript');
        $this->response->setOutput($data['callback'].'( '.json_encode($json).' )');
    }

    public function authenticateUser()
    {
        $data = $this->request->get ?? [];
        $json = array();
        if ($data) {
            if ($this->config->get('module_wk_ebay_dropship_status')) {
                if (isset($data['username']) && $data['username']) {
                    $sentUsername = $data['username'];
                }

                if (isset($data['token']) && $data['token']) {
                    $sentToken = $data['token'];
                }

                $username = $this->config->get('wk_dropship_ebay_username');
                $token = $this->config->get('wk_dropship_ebay_token');

                if (isset($sentUsername) && isset($sentToken)) {
                    $defaultWeight = 0;
                    $defaultWeightClass = 1;
                    if ((float)$this->config->get('wk_dropship_ebay_default_weight') > 0) {
                        $defaultWeight = $this->config->get('wk_dropship_ebay_default_weight');
                    }

                    if ((int)$this->config->get('wk_dropship_ebay_default_weight_class') > 0) {
                        $defaultWeightClass = $this->config->get('wk_dropship_ebay_default_weight_class');
                    }

                    if ($sentUsername == $username && $sentToken == $token) {
                        $json['price_type'] = $this->config->get('wk_dropship_ebay_price_type');
                        $json['default_weight'] = $defaultWeight;
                        $json['default_weight_class'] = $defaultWeightClass;
                        $json['success'] = true;
                        $json['msg'] = 'Success Login';
                    } else {
                        $json['error'] = true;
                        $json['msg'] = 'Warning: Authentication failed, please provide valid username and token!';
                    }
                }
            } else {
                $json['error'] = true;
                $json['msg'] = 'Ebay Integration App is not installed on this store!';
            }
        } else {
            $json['error'] = true;
            $json['msg'] = 'Not authenticated';
        }
        $this->response->addHeader('Content-Type: application/javascript');
        $this->response->setOutput($data['callback'].'( '.json_encode($json).' )');
    }

    public function urlAuthentication()
    {
        $data = $this->request->get;
        $result['success'] = true;
        $this->response->addHeader('Content-Type: application/javascript');
        $this->response->setOutput($data['callback'].'( '.json_encode($result).' )');
    }

    //update code starts here

    public function updateProductGeneralInfo()
    {
        $json = array();
        $data = $this->request->get ??[];

        if ($data) {
            if ((utf8_strlen($data['product_name']) < 3) || (utf8_strlen($data['product_name']) > 255)) {
                $json['msg'] = 'Product Name must be greater than 3 and less than 255 characters!';
            }

            if ((utf8_strlen($data['meta_title']) < 3) || (utf8_strlen($data['meta_title']) > 255)) {
                $json['msg'] = 'Meta Title must be greater than 3 and less than 255 characters!';
            }

            if (!$json) {
                $this->load->model('checkout/commerce');
                $return = $this->model_checkout_commerce->updateProductGeneralInfo($data);
                if ($return) {
                    $json['success'] = true;
                    $json['product_id'] = $return;
                } else {
                    $json['error'] = true;
                    $json['msg'] = 'Product is not Found, please try some other product!';
                }
            } else {
                $json['error'] = true;
            }
        } else {
            $json['error'] = true;
            $json['msg'] = 'There is some issue, please try again later!';
        }

        $this->response->addHeader('Content-Type: application/javascript');
        $this->response->setOutput($data['callback'].'( '.json_encode($json).' )');
    }

    public function updateOptions()
    {
        $json = array();
        $data = $this->request->get ??[];
        if ($data && !empty($data['productOptions'])) {
            $this->load->model('checkout/commerce');
            $return = $this->model_checkout_commerce->updateOptionsToCore($data['productOptions'], $data['product_id']);
            if ($return) {
                $json['success'] = true;
            } else {
                $json['error'] = true;
            }
            $json['success'] = true;
        } elseif (empty($data['productOptions'])) {
            $json['success'] = true;
        } else {
            $json['error'] = true;
            $json['msg'] = 'Warning: Options could not be updated successfully!';
        }
        $this->response->addHeader('Content-Type: application/javascript');
        $this->response->setOutput($data['callback'].'( '.json_encode($json).' )');
    }

    public function UpdateProductOption()
    {
        $json = array();
        $data = $this->request->get ?? [];
        if ($data) {
            $this->load->model('checkout/commerce');
            $return = $this->model_checkout_commerce->UpdateEbayProductOption($data);
            if ($return) {
                $json['success'] = true;
            } else {
                $json['error'] = true;
            }
        } else {
            $json['error'] = true;
            $json['msg'] = 'Warning: Product could not be Updated successfully, Please try it again!';
        }

        $this->response->addHeader('Content-Type: application/javascript');
        $this->response->setOutput($data['callback'].'( '.json_encode($json).' )');
    }

    public function updateProductAttribute()
    {
        $json = array();
        $data = $this->request->get ?? [];

        if ($data) {
            $this->load->model('checkout/commerce');
            $return = $this->model_checkout_commerce->addEbayProductAttribute($data, true);
            if ($return) {
                $json['success'] = true;
            } else {
                $json['error'] = true;
            }
        } else {
            $json['error'] = true;
            $json['msg'] = "Warning: Product's option could not be Updated successfully, Please try it again!";
        }
        $this->response->addHeader('Content-Type: application/javascript');
        $this->response->setOutput($data['callback'].'( '.json_encode($json).' )');
    }

    /************* Get Ebay Product Details By ID *************/
    public function getEbayProductDetailsById(){

        $json = array();
        $data = $this->request->get ?? [];
        if ($data && isset($data['item_id'])) {
            $this->load->model('checkout/commerce');
            $result= $this->model_checkout_commerce->getEbayProductDetailsById($data['item_id']);
        }
        $this->response->addHeader('Content-Type: application/javascript');
        $this->response->setOutput($data['callback'].'( '.json_encode($result).' )');
    }
}
