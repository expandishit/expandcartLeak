<?php
class ControllerWkposReturns extends Controller {
  private $error = array();
  public function index() {
    $this->load->model('wkpos/wkpos');
    if(!$this->model_wkpos_wkpos->is_installed())
      $this->response->redirect($this->url->link('wkpos/main', '', true));
    
    $this->data = $this->load->language('wkpos/returns');
    $this->data = array_merge($this->data, $this->load->language('sale/return'));
    $this->document->setTitle($this->language->get('return_heading_title'));
    $this->data['breadcrumbs'] = array();
    $this->data['breadcrumbs'][] = array(
      'text' => $this->language->get('text_home'),
      'href' => $this->url->link('common/home', 'token='.$this->session->data['token'], true)
    );
    $this->data['breadcrumbs'][] = array(
      'text' => $this->language->get('text_heading_main'),
      'href' => $this->url->link('wkpos/main', 'token=' . $this->session->data['token'], true)
    );
    $this->load->model('localisation/return_status');

    $this->data['return_statuses'] = $this->model_localisation_return_status->getReturnStatuses();

    $this->data['breadcrumbs'][] = array(
      'text' => $this->language->get('return_heading_title'),
      'href' => $this->url->link('wkpos/returns', 'token='.$this->session->data['token'], true)
    );
    $this->data['limit'] = $this->config->get('config_limit_admin');
    $this->data['token'] = $this->session->data['token'];
    
    $this->children = array(
           'common/header',
           'common/footer',
        );
    $this->template = 'wkpos/returns.expand';
    $this->response->setOutput($this->render(TRUE));
    
    /*$this->data['header'] = $this->load->controller('common/header');
    $this->data['footer'] = $this->load->controller('common/footer');
    $this->data['column_left'] = $this->load->controller('common/column_left');
    $this->response->setOutput($this->load->view('wkpos/returns.tpl', $data));*/
  }
  public function loadReturns() {
    $this->load->language('wkpos/return');
    $this->load->model('wkpos/returns');
    $json = array();
    if (isset($this->request->get['filter_return_id'])) {
      $filter_return_id = $this->request->get['filter_return_id'];
    } else {
      $filter_return_id = '';
    }
    if (isset($this->request->get['filter_order_id'])) {
      $filter_order_id = $this->request->get['filter_order_id'];
    } else {
      $filter_order_id = '';
    }
    if (isset($this->request->get['filter_product'])) {
      $filter_product = $this->request->get['filter_product'];
    } else {
      $filter_product = '';
    }
    if (isset($this->request->get['filter_model'])) {
      $filter_model = $this->request->get['filter_model'];
    } else {
      $filter_model = '';
    }
    if (isset($this->request->get['filter_customer'])) {
      $filter_customer = $this->request->get['filter_customer'];
    } else {
      $filter_customer = '';
    }
    if (isset($this->request->get['filter_date_added'])) {
      $filter_date_added = $this->request->get['filter_date_added'];
    } else {
      $filter_date_added = '';
    }
    if (isset($this->request->get['filter_date_modified'])) {
      $filter_date_modified = $this->request->get['filter_date_modified'];
    } else {
      $filter_date_modified = '';
    }
    if (isset($this->request->get['filter_status'])) {
      $filter_status = $this->request->get['filter_status'];
    } else {
      $filter_status = '';
    }
    if (isset($this->request->get['sort'])) {
      $sort = $this->request->get['sort'];
    } else {
      $sort = 'r.return_id';
    }
    if (isset($this->request->get['order'])) {
      $order = $this->request->get['order'];
    } else {
      $order = 'DESC';
    }

    if (isset($this->request->post['filter_start'])) {
      $filter_start = $this->request->post['filter_start'];
    } else {
      $filter_start = '';
    }
    if (isset($this->request->post['limit'])) {
      $limit = $this->request->post['limit'];
    } else {
      $limit = $this->config->get('config_limit_admin');
    }
    $filter_data = array(
      'filter_return_id'    => $filter_return_id,
      'filter_order_id'     => $filter_order_id,
      'filter_customer'     => $filter_customer,
      'filter_product'      => $filter_product,
      'filter_model'        => $filter_model,
      'filter_date_added'   => $filter_date_added,
      'filter_date_modified'=> $filter_date_modified,
      'filter_status'       => $filter_status,
      'sort'                => $sort,
      'order'               => $order,
      'start'               => $filter_start,
      'limit'               => $limit
    );
    $results = $this->model_wkpos_returns->getPOSReturns($filter_data);
    
    $json['total'] = $this->model_wkpos_returns->getTotalPOSReturns($filter_data);
    $json['returns'] = array();
    $json['count'] = count($results);
    if ($results) {
      foreach ($results as $result) {
        $json['returns'][] = array(
  				'return_id'     => $result['return_id'],
  				'order_id'      => $result['order_id'],
  				'customer'      => $result['customer'],
  				'product'       => $result['product'],
  				'model'         => $result['model'],
  				'return_status' => $result['status'],
  				'date_added'    => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
  				'date_modified' => date($this->language->get('date_format_short'), strtotime($result['date_modified'])),
  				'edit'          => 'sale/return/update?return_id=' . $result['return_id']
  			);
      }
    }
    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
  }

  public function dtHandler() {
        $this->load->model('wkpos/returns');

        $request = $_REQUEST;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            parse_str(html_entity_decode($this->request->post['filter']), $filterData);
            $filterData = $filterData['filter'];
        }

        $filterData['search'] = null;
        if (isset($request['search']['value']) && strlen($request['search']['value']) > 0) {
            $filterData['search'] = $request['search']['value'];
        }

        $start = $request['start'];
        $length = $request['length'];

        $columns = array(
            0 => 'return_id',
            1 => 'order_id',
            2 => 'customer',
            3 => 'product',
            4 => 'model',
            5 => 'return_status',
            6 => 'date_added',
            7 => 'date_modified',
            8 => ''
        );

        $orderColumn = $columns[$request['order'][0]['column']];
        $orderType = $request['order'][0]['dir'];

        $data = array(
          'sort' => $orderColumn,
          'order' => $orderType,
          'start' => $request['start'],
          'limit' => $request['length']
        );
        $results = $this->model_wkpos_returns->getPOSReturnsDt($data, $filterData);
        $finalRecords = [];
        
        $records = $results['data'];
        $totalData = $results['total'];
        $totalFiltered = $results['totalFiltered'];

        foreach ($records as $result) {
          $finalRecords[] = array(
            'return_id'     => $result['return_id'],
            'order_id'      => $result['order_id'],
            'customer'      => $result['customer'],
            'product'       => $result['product'],
            'model'         => $result['model'],
            'return_status' => $result['status'],
            'date_added'    => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
            'date_modified' => date($this->language->get('date_format_short'), strtotime($result['date_modified'])),
            'edit'          => 'sale/return/update?return_id=' . $result['return_id']
          );
        }

        $json_data = array(
            "draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($totalData), // total number of records
            "recordsFiltered" => $totalFiltered, // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $finalRecords,   // total data array
        );
        $this->response->setOutput(json_encode($json_data));
        return;
    }

  protected function validate() {
    if (!$this->user->hasPermission('access', 'wkpos/returns')) {
      $this->error['warning'] = $this->language->get('error_permission');
    }
    return !$this->error;
  }
}
