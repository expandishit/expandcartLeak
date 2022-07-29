<?php

class ControllerProductWarehouseProduct extends Controller
{
    private $error = array();

    public function addProductGeneral()
    {
        $json = array();
        $data = array();
        $data = $this->request->get;
        if ($data) {
            if ((utf8_strlen($data['product_name']) < 3) || (utf8_strlen($data['product_name']) > 255)) {
                $json['msg'] = 'Product Name must be greater than 3 and less than 255 characters!';
            }

            if ((utf8_strlen($data['meta_title']) < 3) || (utf8_strlen($data['meta_title']) > 255)) {
                $json['msg'] = 'Meta Title must be greater than 3 and less than 255 characters!';
            }

            if (!$json) {
                $this->load->model('checkout/warehouse');
                $return = $this->model_checkout_warehouse->addAliexpressProductGeneral($data);
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
        if(STORECODE == "EOOXDQ2433"){
            header("Access-Control-Allow-Origin: *");
        }else{
            $HttpOrigin = $_SERVER['HTTP_ORIGIN'];

            if(preg_match('/^(?:.+\.)?aliexpress\.com$/', $HttpOrigin))
            {
                header("Access-Control-Allow-Origin: $HttpOrigin");
            }
        }

        $json = array();
        $data = array();
        $product_id = (int)$this->request->post['product_id'];
        if (!preg_match("/^[1-9][0-9]*$/", $product_id)) {
            $json['error'] = true;
        } else {
            $data = $this->request->post;
            if ($data && !empty($data['productOptions'])) {
                $this->load->model('checkout/warehouse');
                $return = $this->model_checkout_warehouse->addOptionsToCore($data['productOptions'], $data['product_id']);
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
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function addProductOption()
    {
        $json = array();
        $data = array();

        $product_id = (int)$this->request->get['product_id'];
        if(!preg_match("/^[1-9][0-9]*$/", $product_id)) {
            $json['error'] = true;
        }else{
            $data = $this->request->get;

            if ($data) {
                $this->load->model('checkout/warehouse');
                $return = $this->model_checkout_warehouse->addAliexpressProductOption($data);
                if ($return) {
                    $json['success'] = true;
                } else {
                    $json['error'] = true;
                }
            } else {
                $json['error'] = true;
                $json['msg'] = 'Warning: Product could not be imported successfully, Please try it again!';
            }
        }
        $this->response->addHeader('Content-Type: application/javascript');
        $this->response->setOutput($data['callback'].'( '.json_encode($json).' )');
    }

    public function addProductAttribute()
    {
        $json = array();
        $data = array();
        $product_id = (int)$this->request->get['product_id'];
        if (!preg_match("/^[1-9][0-9]*$/", $product_id)) {
            $json['error'] = true;
        } else {
            $data = $this->request->get;
            if ($data) {
                $this->load->model('checkout/warehouse');
                $return = $this->model_checkout_warehouse->addAliexpressProductAttribute($data);
                if ($return) {
                    $json['success'] = true;
                } else {
                    $json['error'] = true;
                }
            } else {
                $json['error'] = true;
                $json['msg'] = "Warning: Product's option could not be imported successfully, Please try it again!";
            }
        }
        $this->response->addHeader('Content-Type: application/javascript');
        $this->response->setOutput($data['callback'].'( '.json_encode($json).' )');
    }

    public function addProductReview()
    {
        $json = array();
        $data = array();
        $product_id = (int)$this->request->get['product_id'];
        if (!preg_match("/^[1-9][0-9]*$/", $product_id)) {
            $json['error'] = true;
        } else {
            $data = $this->request->get;
            if ($data) {
                $this->load->model('checkout/warehouse');
                $return = $this->model_checkout_warehouse->addAliexpressProductReview($data);
                if ($return) {
                    $json['success'] = true;
                } else {
                    $json['error'] = true;
                }
            } else {
                $json['error'] = true;
                $json['msg'] = "Warning: Product's attribute could not be imported successfully, Please try it again!";
            }
        }
        $this->response->addHeader('Content-Type: application/javascript');
        $this->response->setOutput($data['callback'].'( '.json_encode($json).' )');
    }

    public function productReviewPageFetch()
    {
        $json = array();
        $data = $this->request->get;
        if ($data && isset($data['review_url']) && !empty($data['review_url'])) {
            $review_names = array();
            $review_ratings = array();
            $review_comments = array();

            parse_str(html_entity_decode(str_replace('//feedback.aliexpress.com/display/productEvaluation.htm?', '', $data['review_url']), ENT_QUOTES, 'UTF-8'), $output);

            $curl = curl_init();
            if ($data['page']) {
                $post_data = array(
                    'ownerMemberId' => $output['ownerMemberId'],
                    'productId' => $output['productId'],
                    'page' => $data['page'] + 1,
                    'currentPage' => $data['page'],
                );

                $curl = curl_init(html_entity_decode('https://feedback.aliexpress.com/display/productEvaluation.htm', ENT_QUOTES, 'UTF-8'));
                curl_setopt($curl, CURLOPT_HTTPHEADER, array('content-type:application/x-www-form-urlencoded'));
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post_data));
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_HEADER, false);

                $response_review = curl_exec($curl);
                curl_close($curl);

                $dom = new DOMDocument();
                libxml_use_internal_errors(true);
                $dom->loadHtml($response_review);

                $reviews_aliex_rating = $dom->getElementsByTagName('span');
                foreach ($reviews_aliex_rating as $key => $value) {
                    if ('user-name' == $value->getAttribute('class')) {
                        $review_names[] = trim($value->nodeValue);
                    }

                    if (('star-view' == $value->getAttribute('class')) && 'f-rate-info' == $value->parentNode->getAttribute('class')) {
                        $rating = str_replace('%', '', str_replace('width:', '', $value->firstChild->getAttribute('style')));
                        $review_ratings[] = (5 / (100 / $rating));
                    }
                }

                $reviews_aliex_comment = $dom->getElementsByTagName('dt');
                foreach ($reviews_aliex_comment as $key => $value) {
                    if (('buyer-feedback' == $value->getAttribute('class'))) {
                        $review_comments[] = trim($value->nodeValue);
                    }
                }

                $json['review_names'] = $review_names;
                $json['review_ratings'] = $review_ratings;
                $json['review_comments'] = $review_comments;
                $json['total_page'] = $data['total_page'];
                $json['page'] = $data['page'] + 1;
            } else {
                curl_setopt($curl, CURLOPT_URL, html_entity_decode('https:'.$data['review_url'], ENT_QUOTES, 'UTF-8'));
                curl_setopt($curl, CURLOPT_HEADER, 0);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

                $response_review = curl_exec($curl);

                curl_close($curl);

                $dom = new DOMDocument();
                libxml_use_internal_errors(true);
                $dom->loadHtml($response_review);

                $finder = new DomXPath($dom);
                $classname = 'ui-label';
                $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");

                $tmpDom = new DOMDocument();
                foreach ($nodes as $node) {
                    $tmpDom->appendChild($tmpDom->importNode($node, true));
                }

                $reviews_aliex_pages = 0;
                $review_page = explode('/', $tmpDom->textContent);
                $reviews_aliex_pages = end($review_page);

                $reviews_aliex_rating = $dom->getElementsByTagName('span');
                foreach ($reviews_aliex_rating as $key => $value) {
                    if ('user-name' == $value->getAttribute('class')) {
                        $review_names[] = trim($value->nodeValue);
                    }

                    if (('star-view' == $value->getAttribute('class')) && 'f-rate-info' == $value->parentNode->getAttribute('class')) {
                        $rating = str_replace('%', '', str_replace('width:', '', $value->firstChild->getAttribute('style')));
                        $review_ratings[] = (5 / (100 / $rating));
                    }
                }

                $reviews_aliex_comment = $dom->getElementsByTagName('dt');
                foreach ($reviews_aliex_comment as $key => $value) {
                    if (('buyer-feedback' == $value->getAttribute('class'))) {
                        $review_comments[] = trim($value->nodeValue);
                    }
                }

                $json['review_names'] = $review_names;
                $json['review_ratings'] = $review_ratings;
                $json['review_comments'] = $review_comments;
                $json['total_page'] = $reviews_aliex_pages;
                $json['page'] = 1;
            }
        } else {
            $json['error'] = true;
        }
        $this->response->addHeader('Content-Type: application/javascript');
        $this->response->setOutput($data['callback'].'( '.json_encode($json).' )');
    }

    public function getProductTotalReviews()
    {
        $json = array();
        $data = $this->request->get;
        if ($data && isset($data['review_url']) && !empty($data['review_url'])) {
            $data['review_url'] = urldecode($data['review_url']);
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, html_entity_decode('https:'.$data['review_url'], ENT_QUOTES, 'UTF-8'));
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

            $response_review = curl_exec($curl);

            curl_close($curl);

            $dom = new DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadHtml($response_review);

            $finder = new DomXPath($dom);
            $classname = 'fb-star-selector';
            $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");

            $tmpDom = new DOMDocument();
            foreach ($nodes as $node) {
                $tmpDom->appendChild($tmpDom->importNode($node, true));
            }

            $productTotalReviews = preg_replace('/[^0-9.]/', '', $tmpDom->textContent);
            $json['success'] = true;
            $json['productTotalReviews'] = $productTotalReviews;
        } else {
            $json['error'] = true;
        }
        $this->response->addHeader('Content-Type: application/javascript');
        $this->response->setOutput($data['callback'].'( '.json_encode($json).' )');
    }

    public function addProductWarehouse()
    {
        $json = array();
        $data = array();
        $data = $this->request->get;
        if ($data) {
            $this->load->model('checkout/warehouse');
            $return = $this->model_checkout_warehouse->addAliexpressProductWarehouse($data);
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

    public function updateProductPrice()
    {
        $data = $this->request->post;
        $options = array();
        $product_id = 0;
        $json = array();
        if ($data && isset($data['option']) && $data['option']) {
            $this->load->model('checkout/warehouse');
            foreach ($data['option'] as $key => $value) {
                $option = array();
                $option['product_option_value_id'] = $value;
                $option['product_option_id'] = $key;
                $result = $this->cart->getAlixOptionByProductOption($option);
                if ($result && isset($result['alix_option_value_id']) && $result['alix_option_value_id']) {
                    $options[] = $result['alix_option_value_id'];
                    $product_id = $result['product_id'];
                }
            }
            if ($options) {
                if (is_array($options) && count($options) > 1) {
                    $single = false;
                } else {
                    $single = true;
                }
                $options = implode(',', $options);
                $result = $this->cart->getOptionPrice($options, $product_id, $single);
                if ($result) {
                    $json['success'] = true;
                    $product_detail = $this->model_checkout_warehouse->getProductInfo($product_id);
                    if (isset($result['price_prefix']) && '+' == $result['price_prefix']) {
                        $product_detail['price'] = $product_detail['price'] + $result['price'];
                    } elseif (isset($result['price_prefix']) && '-' == $result['price_prefix']) {
                        $product_detail['price'] = $product_detail['price'] - $result['price'];
                        if ($product_detail['price'] < 0) {
                            $product_detail['price'] = 0;
                        }
                    }
                    // $json['price'] = $this->currency->format($product_detail['price'], $this->session->data['currency']);
                    // $json['price'] = $this->currency->format($this->tax->calculate($product_detail['price'], $product_detail['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                    if ($this->customer->isCustomerAllowedToViewPrice()) {
                        $json['price'] = $this->currency->format($this->tax->calculate($product_detail['price'], $product_detail['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                    }
                } else {
                    $json['success'] = false;
                    $json['msg'] = 'This combination is not available currently';
                }
            } else {
                $json['success'] = true;
            }
        } else {
            $json['success'] = false;
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function authenticateUser()
    {
        $data = array();
        $data = $this->request->get;
        $json = array();
        if ($data) {
            if ($this->config->get('module_wk_dropship_status')) {
                if (isset($data['username']) && $data['username']) {
                    $sentUsername = $data['username'];
                }

                if (isset($data['token']) && $data['token']) {
                    $sentToken = $data['token'];
                }

                $username = $this->config->get('wk_dropship_aliexpress_username');
                $token = $this->config->get('wk_dropship_aliexpress_token');

                if (isset($sentUsername) && isset($sentToken)) {
                    $defaultWeight = 0;
                    $defaultWeightClass = 1;
                    if ((float)$this->config->get('wk_dropship_aliexpress_default_weight') > 0) {
                        $defaultWeight = $this->config->get('wk_dropship_aliexpress_default_weight');
                    }

                    if ((int)$this->config->get('wk_dropship_aliexpress_default_weight_class') > 0) {
                        $defaultWeightClass = $this->config->get('wk_dropship_aliexpress_default_weight_class');
                    }

                    if ($sentUsername == $username && $sentToken == $token) {
                        $json['price_type'] = $this->config->get('wk_dropship_aliexpress_price_type');
                        $json['default_weight'] = $defaultWeight;
                        $json['default_weight_class'] = $defaultWeightClass;
                        $json['success'] = true;
                        $json['msg'] = 'Warning: Authentication failed, please provide valid username and token!';
                    } else {
                        $json['error'] = true;
                        $json['msg'] = 'Warning: Authentication failed, please provide valid username and token!';
                    }
                }
            } else {
                $json['error'] = true;
                $json['msg'] = 'AliExpress Integration App is not installed on this store!';
            }
        } else {
            $json['error'] = true;
            $json['msg'] = 'Not authenticated';
        }
        $this->response->addHeader('Content-Type: application/javascript');
        $this->response->setOutput($data['callback'].'( '.json_encode($json).' )');
    }

    public function getAliExpressProductsByOrderId()
    {
        $json = array();
        $data = array();
        $data = $this->request->get;
        if ($data && isset($data['order_id'])) {
            $this->load->model('checkout/warehouse');
            $result = $this->model_checkout_warehouse->getAliExpressProductsByOrderId($data['order_id']);
        }
        $this->response->addHeader('Content-Type: application/javascript');
        $this->response->setOutput($data['callback'].'( '.json_encode($result).' )');
    }

    public function getAliExpressOrderList()
    {
        $this->load->model('checkout/warehouse');
        $result = $this->model_checkout_warehouse->getAliExpressOrderList();
        $this->response->addHeader('Content-Type: application/javascript');
        $this->response->setOutput($this->request->get['callback'].'( '.json_encode($result).' )');
    }

    public function orderPlace()
    {
        $json = array();
        if (isset($this->request->get['order_id'])) {
            $this->load->model('checkout/warehouse');
            $this->model_checkout_warehouse->orderPlace($this->request->get['order_id']);
            $json['success'] = true;
        }
        $this->response->addHeader('Content-Type: application/javascript');
        $this->response->setOutput($this->request->get['callback'].'( '.json_encode($json).' )');
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
        $data = array();
        $data = $this->request->get;

        if ($data) {
            if ((utf8_strlen($data['product_name']) < 3) || (utf8_strlen($data['product_name']) > 255)) {
                $json['msg'] = 'Product Name must be greater than 3 and less than 255 characters!';
            }

            if ((utf8_strlen($data['meta_title']) < 3) || (utf8_strlen($data['meta_title']) > 255)) {
                $json['msg'] = 'Meta Title must be greater than 3 and less than 255 characters!';
            }

            if (!$json) {
                $this->load->model('checkout/warehouse');
                $return = $this->model_checkout_warehouse->updateProductGeneralInfo($data);
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
        $HttpOrigin = $_SERVER['HTTP_ORIGIN'];

        if(preg_match('/^(?:.+\.)?aliexpress\.com$/', $HttpOrigin))
        {  
            header("Access-Control-Allow-Origin: $HttpOrigin");
        }
        $json = array();
        $data = array();
        $data = $this->request->post;
        if ($data && !empty($data['productOptions'])) {
            $this->load->model('checkout/warehouse');
            $return = $this->model_checkout_warehouse->updateOptionsToCore($data['productOptions'], $data['product_id']);
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
        $this->response->addHeader('Content-Type: application/josn');
        $this->response->setOutput(json_encode($json));
    }

    public function UpdateProductOption()
    {
        $json = array();
        $data = array();
        $data = $this->request->get;
        if ($data) {
            $this->load->model('checkout/warehouse');
            $return = $this->model_checkout_warehouse->UpdateAliexpressProductOption($data);
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
        $data = array();
        $data = $this->request->get;

        if ($data) {
            $this->load->model('checkout/warehouse');
            $return = $this->model_checkout_warehouse->addAliexpressProductAttribute($data, true);
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

    public function deleteProductReview()
    {
        $json = array();
        $data = array();
        $data = $this->request->get;
        if ($data) {
            $this->load->model('checkout/warehouse');
            $return = $this->model_checkout_warehouse->deleteAliexpressProductReview($data);
            $json['success'] = true;
        } else {
            $json['error'] = true;
            $json['msg'] = "Warning: Product's attribute could not be updated successfully, Please try it again!";
        }
        $this->response->addHeader('Content-Type: application/javascript');
        $this->response->setOutput($data['callback'].'( '.json_encode($json).' )');
    }

    public function updateProductReview()
    {
        $json = array();
        $data = array();
        $data = $this->request->get;
        if ($data) {
            $this->load->model('checkout/warehouse');
            $return = $this->model_checkout_warehouse->addAliexpressProductReview($data);
            if ($return) {
                $json['success'] = true;
            } else {
                $json['error'] = true;
            }
        } else {
            $json['error'] = true;
            $json['msg'] = "Warning: Product's attribute could not be updated successfully, Please try it again!";
        }
        $this->response->addHeader('Content-Type: application/javascript');
        $this->response->setOutput($data['callback'].'( '.json_encode($json).' )');
    }

    //order update code starts here

    public function checkValidOrderId()
    {
        $json = array();
        $data = array();
        $data = $this->request->get;
        if ($data) {
            $this->load->model('checkout/warehouse');
            $return = $this->model_checkout_warehouse->checkValidOrderId($data);

            if (!empty($return)) {
                $json['success'] = true;
            } else {
                $json['error'] = true;
            }
        } else {
            $json['error'] = true;
            $json['msg'] = 'Warning: Not a Valid Order Id, Please try it again!';
        }
        $this->response->addHeader('Content-Type: application/javascript');
        $this->response->setOutput($data['callback'].'( '.json_encode($json).' )');
    }

    public function getOrderProductDetails()
    {
        $json = array();
        $data = array();
        $data = $this->request->get;
        if ($data) {
            $this->load->model('checkout/warehouse');
            $return = $this->model_checkout_warehouse->getOrderProductDetails($data);
            if (!empty($return)) {
                $json['details'] = $return;
                $json['success'] = true;
            } else {
                $json['error'] = true;
            }
        } else {
            $json['error'] = true;
            $json['msg'] = 'Warning: Not a Valid Order product, Please try it again!';
        }
        $this->response->addHeader('Content-Type: application/javascript');
        $this->response->setOutput($data['callback'].'( '.json_encode($json).' )');
    }
}
