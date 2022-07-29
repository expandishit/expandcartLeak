<?php

class ControllerMultisellerSellerReviews extends ControllerMultisellerBase
{
    private $error = array();

    public function index() {
        $this->language->load('multiseller/seller_reviews');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('multiseller/seller_reviews');

        $this->getList();
    }

    public function insert()
    {
        $this->language->load('multiseller/seller_reviews');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('multiseller/seller_reviews');

        $this->load->model('multiseller/seller');

        $this->load->model('sale/customer');

        $this->data['sellers'] = $this->model_multiseller_seller->getSellers();

        $this->data['customers'] = $this->model_sale_customer->getCustomers();

        if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {
            if ( !$this->validateForm() )
            {
                $json_data = [
                    'success' => '0',
                    'errors' => $this->error
                ];

                $this->response->setOutput(json_encode($json_data));

                return;
            }

            $this->model_multiseller_seller_reviews->addReview($this->request->post);

            $json_data = ['success' => '1', 'success_msg' => $this->language->get('text_success')];

            $this->response->setOutput(json_encode($json_data));
            return;
        }

        $this->data['links'] = [
            'submit' => $this->url->link(
                'multiseller/seller_reviews/insert',
                '',
                'SSL'
            )
        ];

        $this->getForm();
    }

    
    public function update()
    {
        
         $this->load->model('multiseller/seller');

        $this->data['sellers'] = $this->model_multiseller_seller->getSellers();

        $this->language->load('multiseller/seller_reviews');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('multiseller/seller_reviews');

        if ( $this->request->server['REQUEST_METHOD'] == 'POST' )
        {

            if ( ! $this->validateForm() )
            {
                $json_data = [
                    'success' => '0',
                    'errors' => $this->error
                ];

                $this->response->setOutput(json_encode($json_data));
                return;
            }

            $this->model_multiseller_seller_reviews->editReview($this->request->get['review_id'], $this->request->post);

            $json_data = [
                'success' => '1',
                'success_msg' => $this->language->get('text_success'),
            ];

            $this->response->setOutput(json_encode($json_data));
            return;
        }

        $this->data['links'] = [
            'submit' => $this->url->link(
                'multiseller/seller_reviews/update',
                'review_id=' . $this->request->get['review_id'],
                'SSL'
            )
        ];

        $this->getForm();
    }

    public function delete() {
        $this->language->load('multiseller/seller_reviews');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('multiseller/seller_reviews');

        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            foreach ($this->request->post['selected'] as $review_id) {
                $this->model_multiseller_seller_reviews->deleteReview($review_id);
            }

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->redirect($this->url->link('multiseller/seller_reviews', 'token=' . $this->session->data['token'] . $url, 'SSL'));
        }

        $this->getList();
    }

    protected function getList() {
        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'r.date_added';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'ASC';
        }

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('multiseller/seller_reviews', 'token=' . $this->session->data['token'] . $url, 'SSL'),
            'separator' => ' :: '
        );

        $this->data['insert'] = $this->url->link('multiseller/seller_reviews/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
        $this->data['delete'] = $this->url->link('multiseller/seller_reviews/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

        $this->data['reviews'] = array();

        $data = array(
            'sort'  => $sort,
            'order' => $order,
            'start' => ($page - 1) * $this->config->get('config_admin_limit'),
            'limit' => $this->config->get('config_admin_limit')
        );

        $review_total = $this->model_multiseller_seller_reviews->getTotalReviews();

        $results = $this->model_multiseller_seller_reviews->getReviews($data);

        foreach ($results as $result) {
            $action = array();

            $action[] = array(
                'text' => $this->language->get('text_edit'),
                'href' => $this->url->link('multiseller/seller_reviews/update', 'token=' . $this->session->data['token'] . '&review_id=' . $result['review_id'] . $url, 'SSL')
            );

            $this->data['reviews'][] = array(
                'review_id'  => $result['review_id'],
                'name'       => $result['name'],
                'author'     => $result['author'],
                'rating'     => $result['rating'],
                'status'     => ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
                'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
                'selected'   => isset($this->request->post['selected']) && in_array($result['review_id'], $this->request->post['selected']),
                'action'     => $action
            );
        }

        if (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else {
            $this->data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $this->data['success'] = '';
        }

        $url = '';

        if ($order == 'ASC') {
            $url .= '&order=DESC';
        } else {
            $url .= '&order=ASC';
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $this->data['sort_product'] = $this->url->link('multiseller/seller_reviews', 'token=' . $this->session->data['token'] . '&sort=pd.name' . $url, 'SSL');
        $this->data['sort_author'] = $this->url->link('multiseller/seller_reviews', 'token=' . $this->session->data['token'] . '&sort=r.author' . $url, 'SSL');
        $this->data['sort_rating'] = $this->url->link('multiseller/seller_reviews', 'token=' . $this->session->data['token'] . '&sort=r.rating' . $url, 'SSL');
        $this->data['sort_status'] = $this->url->link('multiseller/seller_reviews', 'token=' . $this->session->data['token'] . '&sort=r.status' . $url, 'SSL');
        $this->data['sort_date_added'] = $this->url->link('multiseller/seller_reviews', 'token=' . $this->session->data['token'] . '&sort=r.date_added' . $url, 'SSL');

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        $pagination = new Pagination();
        $pagination->total = $review_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_admin_limit');
        $pagination->text = $this->language->get('text_pagination');
        $pagination->url = $this->url->link('multiseller/seller_reviews', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

        $this->data['pagination'] = $pagination->render();

        $this->data['sort'] = $sort;
        $this->data['order'] = $order;

        $this->template = 'multiseller/seller_reviews/list.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }

    
    private function getForm()
    {

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('multiseller/seller_reviews', 'token=' . $this->session->data['token'] . $url, 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => !isset($this->request->get['review_id']) ? $this->language->get('breadcrumb_insert') : $this->language->get('breadcrumb_update'),
            'href'      => $this->url->link('multiseller/seller_reviews', 'token=' . $this->session->data['token'] . $url, 'SSL'),
            'separator' => ' :: '
        );

        if (!isset($this->request->get['review_id'])) {
            $this->data['action'] = $this->url->link('multiseller/seller_reviews/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
        } else {
            $this->data['action'] = $this->url->link('multiseller/seller_reviews/update', 'token=' . $this->session->data['token'] . '&review_id=' . $this->request->get['review_id'] . $url, 'SSL');
        }

        $this->data['cancel'] = $this->url->link('multiseller/seller_reviews', 'token=' . $this->session->data['token'] . $url, 'SSL');

        if (isset($this->request->get['review_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $review_info = $this->model_multiseller_seller_reviews->getReview($this->request->get['review_id']);
        }

        $this->data['token'] = $this->session->data['token'];

        $this->load->model('multiseller/seller');

        if (isset($this->request->post['seller_id'])) {
            $this->data['seller_id'] = $this->request->post['seller_id'];
        } elseif (!empty($review_info)) {
            $this->data['seller_id'] = $review_info['seller_id'];
        } else {
            $this->data['seller_id'] = '';
        }

        // if (isset($this->request->post['product'])) {
        //     $this->data['product'] = $this->request->post['product'];
        // } elseif (!empty($review_info)) {
        //     $this->data['product'] = $review_info['product'];
        // } else {
        //     $this->data['product'] = '';
        // }

        if (isset($this->request->post['author'])) {
            $this->data['author'] = $this->request->post['author'];
        } elseif (!empty($review_info)) {
            $this->data['author'] = $review_info['author'];
        } else {
            $this->data['author'] = '';
        }

        if (isset($this->request->post['text'])) {
            $this->data['text'] = $this->request->post['text'];
        } elseif (!empty($review_info)) {
            $this->data['text'] = $review_info['text'];
        } else {
            $this->data['text'] = '';
        }

        if (isset($this->request->post['rating'])) {
            $this->data['rating'] = $this->request->post['rating'];
        } elseif (!empty($review_info)) {
            $this->data['rating'] = $review_info['rating'];
        } else {
            $this->data['rating'] = 2;
        }

        if (isset($this->request->post['status'])) {
            $this->data['status'] = $this->request->post['status'];
        } elseif (!empty($review_info)) {
            $this->data['status'] = $review_info['status'];
        } else {
            $this->data['status'] = '';
        }

        $this->load->model('sale/customer');
        $this->data['customers'] = $this->model_sale_customer->getCustomers();

        $this->document->addScript('view/assets/js/core/libraries/jquery_ui/widgets.min.js');
        $this->document->addScript('view/javascript/pages/catalog/review.js');

        $this->template = 'multiseller/seller_reviews/form.expand';

        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }

    
    private function validateForm()
    {
        if ( !$this->user->hasPermission('modify', 'multiseller/seller_reviews') )
        {
            $this->error['error'] = $this->language->get('error_permission');
        }

        if ( ! $this->request->post['seller_id'] )
        {
            $this->error['seller_id'] = $this->language->get('error_product');
        }

        if ( (utf8_strlen($this->request->post['author']) < 3) || (utf8_strlen($this->request->post['author']) > 64) )
        {
            $this->error['author'] = $this->language->get('error_author');
        }

        if ( utf8_strlen($this->request->post['text']) < 1 )
        {
            $this->error['text'] = $this->language->get('error_text');
        }

        if ( !isset($this->request->post['rating']) )
        {
            $this->error['rating'] = $this->language->get('error_rating');
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    protected function validateDelete() {
        if (!$this->user->hasPermission('modify', 'multiseller/seller_reviews')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    public function dtHandler()
    {
        $this->load->model('multiseller/seller_reviews');
        $request = $_REQUEST;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        $start = $request['start'];
        $length = $request['length'];

        $columns = array(
            0 => 'review_id',
            1 => 'name',
            2 => 'author',
            3 => 'rating',
            4 => 'status',
            5 => 'date_added'
        );

        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];

        $return = $this->model_multiseller_seller_reviews->dtHandler([
            'sort' => $orderColumn,
            'order' => $orderType,
            'start' => $start,
            'length' => $length
        ]);

        $records = $return['data'];
        $totalData = $return['total'];
        $totalFiltered = $return['totalFiltered'];


        $json_data = array(
            "draw" => intval($request['draw']),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => $totalFiltered,
            "data" => $records
        );
        $this->response->setOutput(json_encode($json_data));
        return;
    }

    public function dtDelete()
    {
        $this->load->model('multiseller/seller_reviews');

        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            foreach ($this->request->post['selected'] as $review_id) {
                $this->model_multiseller_seller_reviews->deleteReview($review_id);
            }
            $result_json['success'] = '1';
            $result_json['success_msg'] = $this->language->get('text_bulkdelete_success');
        } else {
            $result_json['success'] = '0';
            $result_json['error'] = $this->language->get('text_bulkdelete_error');
        }

        $this->response->setOutput(json_encode($result_json));
        return;
    }
}
