<?php
class ControllerCatalogReview extends Controller {
	private $error = array();

    public function __construct($registry) {
        parent::__construct($registry);

        if( !\Extension::isInstalled('product_review') ){
            exit();
        }
    }

	public function index() {
		$this->language->load('catalog/review');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/review');

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_module'),
            'href'      => $this->url->link('marketplace/home', '', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('catalog/review', 'token=' . $this->session->data['token'] . $url, 'SSL'),
            'separator' => ' :: '
        );

		$this->getList();
	}

	public function insert()
    {
		$this->language->load('catalog/review');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/review');
		
		$this->load->model('catalog/review_options');

        $this->load->model('catalog/product');

        $this->load->model('sale/customer');

        // $this->data['products'] = $this->model_catalog_product->getProducts();

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
//			$review_options_list = $this->model_catalog_review_options->getReviewsOptionList();
//			if($review_options_list){
//				// group all review options
//				foreach($review_options_list as $value){
//					$this->request->post['rate'][$value] = $this->request->post[$value];
//				}
//				if(isset($this->request->post['rating'])){
//					$this->request->post['rate']['rating'] = $this->request->post['rating'];
//				}
//			}


			$this->model_catalog_review->addReview($this->request->post);

            $json_data = ['success' => '1', 'success_msg' => $this->language->get('text_success') , 
                'redirect' => '1' ,'to'=> $this->url->link(
                    'catalog/review', '', 'SSL'
            )->format()];

            $this->response->setOutput(json_encode($json_data));
            return;
		}

        $this->data['links'] = [
            'submit' => $this->url->link(
                'catalog/review/insert',
                '',
                'SSL'
            )
        ];

		$this->getForm();
	}

	
    public function update()
    {
        
        $this->load->model('catalog/product');
		$this->load->model('catalog/review_options');

//        $this->data['products'] = $this->model_catalog_product->getProducts();

		$this->language->load('catalog/review');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/review');

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
//			$review_options_list = $this->model_catalog_review_options->getReviewsOptionList();
//			if($review_options_list){
//				// group all review options
//				foreach($review_options_list as $value){
//					$this->request->post['rate'][$value] = $this->request->post[$value];
//				}
//				if(isset($this->request->post['rating'])){
//					$this->request->post['rate']['rating'] = $this->request->post['rating'];
//				}
//			}
            $this->model_catalog_review->editReview($this->request->get['review_id'], $this->request->post);

            $json_data = [
                'success' => '1',
                'success_msg' => $this->language->get('text_success'),
            ];

            $this->response->setOutput(json_encode($json_data));
            return;
		}

		$this->data['links'] = [
		    'submit' => $this->url->link(
		        'catalog/review/update',
                'review_id=' . $this->request->get['review_id'],
                'SSL'
            )
        ];

		$this->getForm();
	}

	public function delete() {
		$this->language->load('catalog/review');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/review');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $review_id) {
                $queryRewardPointInstalled = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'reward_points_pro'");

                if($queryRewardPointInstalled->num_rows) {
                    $this->load->model('promotions/reward_points_observer');
                    $this->model_promotions_reward_points_observer->beforeUpdateReview($review_id, null, '3');
                }

				$this->model_catalog_review->deleteReview($review_id);
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

			$this->redirect(

			    $this->url->link('catalog/review', 'token=' . $this->session->data['token'] . $url, 'SSL')
            );
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

		$this->data['insert'] = $this->url->link('catalog/review/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('catalog/review/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['reviews'] = array();

		$data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_admin_limit'),
			'limit' => $this->config->get('config_admin_limit')
		);

		$review_total = $this->model_catalog_review->getTotalReviews();

		$results = $this->model_catalog_review->getReviews($data);

    	foreach ($results as $result) {
			$action = array();

			$action[] = array(
				'text' => $this->language->get('text_edit'),
				'href' => $this->url->link('catalog/review/update', 'token=' . $this->session->data['token'] . '&review_id=' . $result['review_id'] . $url, 'SSL')
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

		$this->data['sort_product'] = $this->url->link('catalog/review', 'token=' . $this->session->data['token'] . '&sort=pd.name' . $url, 'SSL');
		$this->data['sort_author'] = $this->url->link('catalog/review', 'token=' . $this->session->data['token'] . '&sort=r.author' . $url, 'SSL');
		$this->data['sort_rating'] = $this->url->link('catalog/review', 'token=' . $this->session->data['token'] . '&sort=r.rating' . $url, 'SSL');
		$this->data['sort_status'] = $this->url->link('catalog/review', 'token=' . $this->session->data['token'] . '&sort=r.status' . $url, 'SSL');
		$this->data['sort_date_added'] = $this->url->link('catalog/review', 'token=' . $this->session->data['token'] . '&sort=r.date_added' . $url, 'SSL');

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
		$pagination->url = $this->url->link('catalog/review', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		if($review_total == 0 ){
            $this->template = 'catalog/review/empty.expand';
        }else{
            $this->template = 'catalog/review_list.expand';
        }

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
			'href'      => $this->url->link('catalog/review', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);

        $this->data['breadcrumbs'][] = array(
            'text'      => !isset($this->request->get['review_id']) ? $this->language->get('breadcrumb_insert') : $this->language->get('breadcrumb_update'),
            'href'      => $this->url->link('catalog/review', 'token=' . $this->session->data['token'] . $url, 'SSL'),
            'separator' => ' :: '
        );

		if (!isset($this->request->get['review_id'])) {
			$this->data['action'] = $this->url->link('catalog/review/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('catalog/review/update', 'token=' . $this->session->data['token'] . '&review_id=' . $this->request->get['review_id'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link(
		    'catalog/review',
            'token=' . $this->session->data['token'] . $url, 'SSL');

		if (isset($this->request->get['review_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$review_info = $this->model_catalog_review->getReview($this->request->get['review_id']);
            $this->data['selectedProduct'] = ['product_id'=>$review_info['product_id'],'name'=>$review_info['product']];

		}

		$this->data['token'] = $this->session->data['token'];

		$this->load->model('catalog/product');

		if (isset($this->request->post['product_id'])) {
			$this->data['product_id'] = $this->request->post['product_id'];
		} elseif (!empty($review_info)) {
			$this->data['product_id'] = $review_info['product_id'];
		} else {
			$this->data['product_id'] = '';
		}

		if (isset($this->request->post['product'])) {
			$this->data['product'] = $this->request->post['product'];
		} elseif (!empty($review_info)) {
			$this->data['product'] = $review_info['product'];
		} else {
			$this->data['product'] = '';
		}

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
		$this->load->model('catalog/review_options');
		//$languageId = $this->config->get('config_language_id');
		//$this->data['review_options_text'] = $this->model_catalog_review_options->getReviewsOptionText($languageId);
		//$this->data['review_options_rate'] = $this->model_catalog_review_options->getReviewsOptionRate($languageId);
        $this->load->model('sale/customer');
        $this->data['customers'] = $this->model_sale_customer->getCustomers();
        $this->document->addScript('view/assets/js/core/libraries/jquery_ui/widgets.min.js');
        $this->document->addScript('view/javascript/pages/catalog/review.js');

		$this->template = 'catalog/review_form.expand';

		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}

	
    private function validateForm()
    {
		if ( !$this->user->hasPermission('modify', 'catalog/review') )
        {
			$this->error['error'] = $this->language->get('error_permission');
		}

		if ( ! $this->request->post['product_id'] )
        {
			$this->error['product_id'] = $this->language->get('error_product');
		}
        if ((utf8_strlen(ltrim($this->request->post['author']," "))< 2) || (utf8_strlen(ltrim($this->request->post['author'] ," ")) > 64))
        {
			$this->error['author'] = $this->language->get('error_author');
		}

//		if (utf8_strlen(ltrim($this->request->post['text']," "))< 1)
//        {
//			$this->error['text'] = $this->language->get('error_text');
//		}

//		$languageId = $this->config->get('config_language_id');
//		$review_options_rate = $this->model_catalog_review_options->getReviewsOptionRate($languageId);
//		if ( !isset($this->request->post['rating']) && !$review_options_rate )
//        {
//			$this->error['rating'] = $this->language->get('error_rating');
//		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'catalog/review')) {
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
        $this->load->model('catalog/review');
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

        $return = $this->model_catalog_review->dtHandler([
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
        $this->load->model('catalog/review');

        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            foreach ($this->request->post['selected'] as $review_id) {

                $queryRewardPointInstalled = $this->db->query(
                    "SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'reward_points_pro'"
                );

                if($queryRewardPointInstalled->num_rows) {
                    $this->load->model('promotions/reward_points_observer');
                    $this->model_promotions_reward_points_observer->beforeUpdateReview($review_id, null, '3');
                }

                $this->model_catalog_review->deleteReview($review_id);
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

    public function getProducts()
    {
        $this->load->model('catalog/product');

        $this->data['all_products'] = $this->model_catalog_product->getProductsFields(['name']);

        $this->response->setOutput(json_encode($this->data['all_products']));
        return;
    }

}
