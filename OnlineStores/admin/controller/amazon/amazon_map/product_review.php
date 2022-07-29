<?php

class ControllerAmazonAmazonMapProductReview extends Controller {
  private $route = 'amazon/amazon_map/product_review';
  private $common = 'amazon/amazon_map/common';

  public function __construct($registory) {
		parent::__construct($registory);
		$this->load->model($this->route);
		$this->_amazonProductReview = $this->model_amazon_amazon_map_product_review;
		$this->load->language($this->route);
		$this->load->language($this->common);
  }
  public function index() {
    
    $this->document->setTitle($this->language->get('text_title'));

    $data['account_id'] = $this->session->data['amazon_account_id'] =$this->request->get['account_id'];
		
    $data['delete_action']    = $this->url->link('amazon/amazon_map/product_review/delete', '' , true);
    
    $this->template = 'amazon/amazon_map/product_review.expand';
    $this->children = array(
        'common/header',
        'common/footer'
    );
    
    $this->data=$data;
		$this->response->setOutput($this->render());

  }


  public function dtHandler()
  {
      $this->load->model('catalog/product');
      $this->load->model('catalog/category');

      $request = $_REQUEST;
      
      if ($this->request->server['REQUEST_METHOD'] == 'POST') {
          parse_str(html_entity_decode($request['filter']), $filterData);
          $filterData = $filterData['filter'];
      }
      
      $filterData['search'] = null;
      if (isset($request['search']['value']) && strlen($request['search']['value']) > 0) {
          $filterData['search'] = $request['search']['value'];
      }

      $start = $request['start'];
      $length = $request['length'];

      $columns = array(
          0 => 'product_id',
          1 => 'name',
          2 => 'price',
          3 => 'feed_id',
      );
      $orderColumn = $columns[$request['order'][0]['column']];

      $results = $this->_amazonProductReview->getAllProductReview($start, $length, $filterData, $orderColumn );
      
      $records = $results['data'];
      
      $totalData = $results['total'];
      $totalFiltered = $results['totalFiltered'];

      $json_data = array(
          "draw" => intval($request['draw']),
          "recordsTotal" => intval($totalData),
          "recordsFiltered" => $totalFiltered,
          "data" => $records
      );
      $this->response->setOutput(json_encode($json_data));
      return;
  }

  public function checkFeedStatus() {
		$json = array();
    if(isset($this->request->post['feed_id']) && isset($this->request->post['account_id'])){
      $result = $this->model_amazon_amazon_map_product_review->getFeedSubmissionResult($this->request->post['feed_id'], $this->request->post['account_id'], $this->request->post['product_id']);

		  $json = $result;
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
  }

  public function delete() {
		
		if(isset($this->request->post['selected_feed']) && isset($this->request->post['account_id'])){
    
			if(count($this->request->post['selected_feed'])>0) {
				$result_json['success'] = '0';
				$result_json['errors'] = $this->language->get('text_delete_atleast');
				$this->response->setOutput(json_encode($result_json));
				return;	
      }
      $this->model_amazon_amazon_map_product_review->deleteProduct($this->request->post['account_id'], $this->request->post['selected_feed']);

			$result_json['success'] = '1';
			$result_json['success_msg']  = $this->language->get('text_success_product_delete');
			$this->response->setOutput(json_encode($result_json));
      return;
		}

  }
  
 
}  

?>
