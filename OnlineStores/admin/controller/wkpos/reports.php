<?php
class ControllerWkposReports extends Controller {
  private $error = array();
  public function index() {
    $this->load->model('wkpos/wkpos');
    if(!$this->model_wkpos_wkpos->is_installed())
      $this->response->redirect($this->url->link('wkpos/main', '', true));
    
    $this->data = array();
    $this->data = array_merge($this->data, $this->load->language('wkpos/reports'));
    $this->document->setTitle($this->language->get('heading_title'));
    $this->data['breadcrumbs'] = array();
    $this->data['breadcrumbs'][] = array(
      'text' => $this->data['text_home'],
      'href' => $this->url->link('common/home', 'token='.$this->session->data['token'], true)
    );
    $this->data['breadcrumbs'][] = array(
      'text' => $this->language->get('text_heading_main'),
      'href' => $this->url->link('wkpos/main', 'token=' . $this->session->data['token'], true)
    );
    $this->data['breadcrumbs'][] = array(
      'text' => $this->data['heading_title'],
      'href' => $this->url->link('wkpos/reports', 'token='.$this->session->data['token'], true)
    );

    $this->data['payments'] = array();
    $this->data['payments'][] = array(
      'text' => $this->config->get('wkpos_card_title'.$this->config->get('config_language_id')),
      'code' => 'card'
    );
    $this->data['payments'][] = array(
      'text' => $this->config->get('wkpos_cash_title'.$this->config->get('config_language_id')),
      'code' => 'cash'
    );
    $this->data['payment'] = array();
    $this->data['outlets'] = array();
    $this->load->model('wkpos/outlet');
    $this->load->model('wkpos/supplier');
    $this->load->model('wkpos/user');
    $this->data['outlets'] = $this->model_wkpos_outlet->getOutlets();
    $this->data['users'] = $this->model_wkpos_user->getUsers();
    $this->data['suppliers'] = $this->model_wkpos_supplier->getSuppliers();
    $this->data['token'] = $this->session->data['token'];
    $this->data['limit'] = $this->config->get('config_limit_admin');
    
    $this->children = array(
           'common/header',
           'common/footer',
        );
    $this->template = 'wkpos/reports.expand';
    $this->response->setOutput($this->render(TRUE));
    
    /*$this->data['header'] = $this->load->controller('common/header');
    $this->data['column_left'] = $this->load->controller('common/column_left');
    $this->data['footer'] = $this->load->controller('common/footer');
    $this->response->setOutput($this->load->view('wkpos/reports.tpl', $data));*/
  }

  public function products() {
        $this->load->model('wkpos/wkpos');
        if(!$this->model_wkpos_wkpos->is_installed())
            $this->response->redirect($this->url->link('wkpos/main', '', true));

        $this->data = array();
        $this->data = array_merge($this->data, $this->load->language('wkpos/reports'));
        $this->document->setTitle($this->language->get('heading_title'));
        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'text' => $this->data['text_home'],
            'href' => $this->url->link('common/home', 'token='.$this->session->data['token'], true)
        );
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_heading_main'),
            'href' => $this->url->link('wkpos/main', 'token=' . $this->session->data['token'], true)
        );
        $this->data['breadcrumbs'][] = array(
            'text' => $this->data['heading_title'],
            'href' => $this->url->link('wkpos/reports', 'token='.$this->session->data['token'], true)
        );

        $this->data['payments'] = array();
        $this->data['payments'][] = array(
            'text' => $this->config->get('wkpos_card_title'.$this->config->get('config_language_id')),
            'code' => 'card'
        );
        $this->data['payments'][] = array(
            'text' => $this->config->get('wkpos_cash_title'.$this->config->get('config_language_id')),
            'code' => 'cash'
        );

        $this->data['outlets'] = array();
        $this->load->model('wkpos/outlet');
        $this->load->model('wkpos/supplier');
        $this->data['outlets'] = $this->model_wkpos_outlet->getOutlets();
        $this->data['suppliers'] = $this->model_wkpos_supplier->getSuppliers();
        $this->data['limit'] = $this->config->get('config_limit_admin');
        $this->data['tab'] = 'product';

        $this->children = array(
            'common/header',
            'common/footer',
        );
        $this->template = 'wkpos/reports/products.expand';
        $this->response->setOutput($this->render(TRUE));

        /*$this->data['header'] = $this->load->controller('common/header');
        $this->data['column_left'] = $this->load->controller('common/column_left');
        $this->data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view('wkpos/reports.tpl', $data));*/
    }

  public function sales() {
        $this->load->model('wkpos/wkpos');
        if(!$this->model_wkpos_wkpos->is_installed())
            $this->response->redirect($this->url->link('wkpos/main', '', true));

        $this->data = array();
        $this->data = array_merge($this->data, $this->load->language('wkpos/reports'));
        $this->document->setTitle($this->language->get('heading_title'));
        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'text' => $this->data['text_home'],
            'href' => $this->url->link('common/home', 'token='.$this->session->data['token'], true)
        );
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_heading_main'),
            'href' => $this->url->link('wkpos/main', 'token=' . $this->session->data['token'], true)
        );
        $this->data['breadcrumbs'][] = array(
            'text' => $this->data['heading_title'],
            'href' => $this->url->link('wkpos/reports', 'token='.$this->session->data['token'], true)
        );

        $this->data['payments'] = array();
        $this->data['payments'][] = array(
            'text' => $this->config->get('wkpos_card_title'.$this->config->get('config_language_id')),
            'code' => 'card'
        );
        $this->data['payments'][] = array(
            'text' => $this->config->get('wkpos_cash_title'.$this->config->get('config_language_id')),
            'code' => 'cash'
        );

          $this->data['groups'] = array();
          $this->data['groups'][] = array(
              'text' => $this->language->get('text_year'),
              'value' => 'year',
          );

          $this->data['groups'][] = array(
              'text' => $this->language->get('text_month'),
              'value' => 'month',
          );

          $this->data['groups'][] = array(
              'text' => $this->language->get('text_week'),
              'value' => 'week',
          );

          $this->data['groups'][] = array(
              'text' => $this->language->get('text_day'),
              'value' => 'day',
          );
         $this->data['filter_group'] = $this->request->get['filter_group'] ?? 'week';

        $this->data['payment'] = array();
        $this->data['outlets'] = array();
        $this->load->model('wkpos/outlet');
        $this->load->model('wkpos/supplier');
        $this->load->model('wkpos/user');
        $this->data['outlets'] = $this->model_wkpos_outlet->getOutlets();
        $this->data['users'] = $this->model_wkpos_user->getUsers();
        $this->data['suppliers'] = $this->model_wkpos_supplier->getSuppliers();
        $this->data['limit'] = $this->config->get('config_limit_admin');
        $this->data['tab'] = 'sale';

        $this->children = array(
            'common/header',
            'common/footer',
        );
        $this->template = 'wkpos/reports/sales.expand';
        $this->response->setOutput($this->render(TRUE));

        /*$this->data['header'] = $this->load->controller('common/header');
        $this->data['column_left'] = $this->load->controller('common/column_left');
        $this->data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view('wkpos/reports.tpl', $data));*/
    }

  public function user() {
        $this->load->model('wkpos/wkpos');
        if(!$this->model_wkpos_wkpos->is_installed())
            $this->response->redirect($this->url->link('wkpos/main', '', true));

        $this->data = array();
        $this->data = array_merge($this->data, $this->load->language('wkpos/reports'));
        $this->document->setTitle($this->language->get('heading_title'));
        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'text' => $this->data['text_home'],
            'href' => $this->url->link('common/home', 'token='.$this->session->data['token'], true)
        );
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_heading_main'),
            'href' => $this->url->link('wkpos/main', 'token=' . $this->session->data['token'], true)
        );
        $this->data['breadcrumbs'][] = array(
            'text' => $this->data['heading_title'],
            'href' => $this->url->link('wkpos/reports', 'token='.$this->session->data['token'], true)
        );

        $this->data['payments'] = array();
        $this->data['payments'][] = array(
            'text' => $this->config->get('wkpos_card_title'.$this->config->get('config_language_id')),
            'code' => 'card'
        );
        $this->data['payments'][] = array(
            'text' => $this->config->get('wkpos_cash_title'.$this->config->get('config_language_id')),
            'code' => 'cash'
        );
        $this->data['outlets'] = array();
        $this->load->model('wkpos/outlet');
        $this->load->model('wkpos/user');
        $this->data['outlets'] = $this->model_wkpos_outlet->getOutlets();
        $this->data['users'] = $this->model_wkpos_user->getUsers();
        $this->data['limit'] = $this->config->get('config_limit_admin');
        $this->data['tab'] = 'posusers';

        $this->children = array(
            'common/header',
            'common/footer',
        );
        $this->template = 'wkpos/reports/users.expand';
        $this->response->setOutput($this->render(TRUE));

        /*$this->data['header'] = $this->load->controller('common/header');
        $this->data['column_left'] = $this->load->controller('common/column_left');
        $this->data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view('wkpos/reports.tpl', $data));*/
    }

  public function expenses() {
        $this->load->model('wkpos/wkpos');
        if(!$this->model_wkpos_wkpos->is_installed()) $this->response->redirect($this->url->link('wkpos/main', '', true));

        $this->data = array();
        $this->data = array_merge($this->data, $this->load->language('wkpos/reports'));
        $this->document->setTitle($this->language->get('heading_title'));
        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'text' => $this->data['text_home'],
            'href' => $this->url->link('common/home', 'token='.$this->session->data['token'], true)
        );
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_heading_main'),
            'href' => $this->url->link('wkpos/main', 'token=' . $this->session->data['token'], true)
        );
        $this->data['breadcrumbs'][] = array(
            'text' => $this->data['heading_title'],
            'href' => $this->url->link('wkpos/reports', 'token='.$this->session->data['token'], true)
        );

        $this->data['payments'] = array();
        $this->data['payments'][] = array(
            'text' => $this->config->get('wkpos_card_title'.$this->config->get('config_language_id')),
            'code' => 'card'
        );
        $this->data['payments'][] = array(
            'text' => $this->config->get('wkpos_cash_title'.$this->config->get('config_language_id')),
            'code' => 'cash'
        );
        $this->data['outlets'] = array();
        $this->load->model('wkpos/outlet');
        $this->load->model('wkpos/user');
        $this->data['outlets'] = $this->model_wkpos_outlet->getOutlets();
        $this->data['users'] = $this->model_wkpos_user->getUsers();
        $this->data['limit'] = $this->config->get('config_limit_admin');
        $this->data['tab'] = 'expenses';

        $this->children = array(
            'common/header',
            'common/footer',
        );
        $this->template = 'wkpos/reports/expenses.expand';
        $this->response->setOutput($this->render(TRUE));

  }

  public function expensesReport() {
        $json = array();
        if ($this->validateAccess()) {
            $get = $this->request->get;
            $page = isset($get['page']) ? $get['page'] : 1;
            $this->load->model('wkpos/reports');
            $filter = array(
                'user_id'     => isset($get['filter_user']) ? $get['filter_user'] : '',
                'outlet_id'   => isset($get['filter_outlet']) ? $get['filter_outlet'] : '',
                'date_from'   => isset($get['filter_date_from']) ? $get['filter_date_from'] : '',
                'date_to'     => isset($get['filter_date_to']) ? $get['filter_date_to'] : '',
                'start'       => ($page - 1) * $this->config->get('config_limit_admin'),
                'limit'       => $this->config->get('config_limit_admin')
            );
        }
        $json['expenses'] = $this->model_wkpos_reports->getExpenses($filter);
        $json['total'] = $this->model_wkpos_reports->getTotalExpenses($filter);
        foreach ($json['expenses'] as $key => $value) {
            $json['expenses'][$key]['title'] = $value['title'];
            $json['expenses'][$key]['description'] = $value['title'];
            $json['expenses'][$key]['amount'] = $this->currency->format($value['amount'], $this->config->get('config_currency'));
            $json['expenses'][$key]['date_added'] = $value['date_added'];
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

  public function userOrders() {
        $this->load->model('wkpos/wkpos');
        if(!$this->model_wkpos_wkpos->is_installed())
            $this->response->redirect($this->url->link('wkpos/main', '', true));

        $this->data = array();
        $this->data = array_merge($this->data, $this->load->language('wkpos/reports'));
        $this->document->setTitle($this->language->get('heading_title'));
        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'text' => $this->data['text_home'],
            'href' => $this->url->link('common/home', 'token='.$this->session->data['token'], true)
        );
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_heading_main'),
            'href' => $this->url->link('wkpos/main', 'token=' . $this->session->data['token'], true)
        );
        $this->data['breadcrumbs'][] = array(
            'text' => $this->data['heading_title'],
            'href' => $this->url->link('wkpos/reports', 'token='.$this->session->data['token'], true)
        );

        $this->data['payments'] = array();
        $this->data['payments'][] = array(
            'text' => $this->config->get('wkpos_card_title'.$this->config->get('config_language_id')),
            'code' => 'card'
        );
        $this->data['payments'][] = array(
            'text' => $this->config->get('wkpos_cash_title'.$this->config->get('config_language_id')),
            'code' => 'cash'
        );
        $this->load->model('wkpos/user');
        $this->data['users'] = $this->model_wkpos_user->getUsers();
        $this->data['limit'] = $this->config->get('config_limit_admin');
        $this->data['user_id'] = isset($this->request->get['user_id']) ? $this->request->get['user_id'] : 1;
        $this->data['tab'] = 'posuserorders';

        $this->children = array(
            'common/header',
            'common/footer',
        );
        $this->template = 'wkpos/reports/user_orders.expand';
        $this->response->setOutput($this->render(TRUE));
    }

  public function productReport() {
    $json = array();
    if ($this->validateAccess()) {
        $get = $this->request->get;
        $page = isset($get['page']) ? $get['page'] : 1;
        $this->load->model('wkpos/reports');
        $filter = array(
          'supplier_id' => isset($get['filter_supplier']) ? $get['filter_supplier'] : '',
          'outlet_id'   => isset($get['filter_outlet']) ? $get['filter_outlet'] : '',
          'product_id'   => (isset($get['filter_product']) && $get['filter_product'] !== 'null') ? $get['filter_product'] : '',
          'start'       => ($page - 1) * 20,
          'limit'       => 20
        );
        $json['total'] = $this->model_wkpos_reports->getTotalProducts($filter);
        $json['products'] = $this->model_wkpos_reports->getProducts($filter);
        foreach ($json['products'] as $key => $product) {
            $json['products'][$key]['total'] = $this->currency->format($product['total'], $this->config->get('config_currency'));
            $json['products'][$key]['cost'] = $this->currency->format($product['cost'], $this->config->get('config_currency'));
            $json['products'][$key]['profit'] = $this->currency->format($product['profit'], $this->config->get('config_currency'));

          if ($product['sold']) {
            $json['products'][$key]['remaining'] = ((int)$product['quantity'] - (int)$product['sold']) > 0 ? (int)$product['quantity'] - (int)$product['sold'] : 0;
          } else {
            $json['products'][$key]['remaining'] = $json['products'][$key]['quantity'];
            $json['products'][$key]['sold'] = 0;
          }
        }
        $json['success'] = true;
        $json['error_permission'] = false;
    } else if (isset($this->error['permission'])){
      $json['error_permission'] = $this->error['permission'];
    }
    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
  }

  public function saleReport() {
    $json = array();
    if ($this->validateAccess()) {
      $get = $this->request->get;
      $page = isset($get['page']) ? $get['page'] : 1;
      $this->load->model('wkpos/reports');
      $filter = array(
        'user_id'     => isset($get['filter_user']) ? $get['filter_user'] : '',
        'outlet_id'   => isset($get['filter_outlet']) ? $get['filter_outlet'] : '',
        'payment'     => isset($get['filter_payment']) ? $get['filter_payment'] : '',
        'order_mode'  => isset($get['filter_mode']) ? $get['filter_mode'] : '',
        'date_from'   => isset($get['filter_date_from']) ? $get['filter_date_from'] : '',
        'date_to'     => isset($get['filter_date_to']) ? $get['filter_date_to'] : '',
        'customer_id' => isset($get['filter_customer']) ? $get['filter_customer'] : '',
        'group'       => isset($get['filter_group']) ? $get['filter_group'] : '',
        'start'       => ($page - 1) * $this->config->get('config_limit_admin'),
        'limit'       => $this->config->get('config_limit_admin')
      );
    }
    $json['sales'] = $this->model_wkpos_reports->getSales($filter);
    foreach ($json['sales'] as $key => $total) {
      $json['sales'][$key]['total'] = $this->currency->format($total['total'], $this->config->get('config_currency'));
      $json['sales'][$key]['cost'] = $this->currency->format($total['cost'], $this->config->get('config_currency'));
      $json['sales'][$key]['expenses'] = $this->currency->format($total['expenses'], $this->config->get('config_currency'));
      $json['sales'][$key]['profit'] = $this->currency->format($total['total'] - $total['cost']- $total['expenses'], $this->config->get('config_currency'));
    }
    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
  }

  public function userReport() {
        $json = array();
        if ($this->validateAccess()) {
            $get = $this->request->get;
            $page = isset($get['page']) ? $get['page'] : 1;
            $this->load->model('wkpos/reports');
            $filter = array(
                'user_id'     => isset($get['filter_user']) ? $get['filter_user'] : '',
                'outlet_id'   => isset($get['filter_outlet']) ? $get['filter_outlet'] : '',
                'date_from'   => isset($get['filter_date_from']) ? $get['filter_date_from'] : '',
                'date_to'     => isset($get['filter_date_to']) ? $get['filter_date_to'] : '',
                'start'       => ($page - 1) * $this->config->get('config_limit_admin'),
                'limit'       => $this->config->get('config_limit_admin')
            );
        }
        $json['users'] = $this->model_wkpos_reports->getUsers($filter);
        $json['total'] = $this->model_wkpos_reports->getTotalUsers($filter);
        foreach ($json['users'] as $key => $total) {
            $json['users'][$key]['total_cash'] = $this->currency->format($total['total_cash'], $this->config->get('config_currency'));
            $json['users'][$key]['total_card'] = $this->currency->format($total['total_card'], $this->config->get('config_currency'));
            $json['users'][$key]['expenses'] = $this->currency->format($total['expenses'], $this->config->get('config_currency'));
            $json['users'][$key]['total'] = $this->currency->format($total['total_cash']+$total['total_card']-$total['expenses'], $this->config->get('config_currency'));
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

  public function userOrdersReport() {
        $json = array();
        if ($this->validateAccess()) {
            $get = $this->request->get;
            $page = isset($get['page']) ? $get['page'] : 1;
            $this->load->model('wkpos/reports');
            $filter = array(
                'user_id'     => isset($get['filter_user']) ? $get['filter_user'] : '',
                'date_from'   => isset($get['filter_date_from']) ? $get['filter_date_from'] : '',
                'date_to'     => isset($get['filter_date_to']) ? $get['filter_date_to'] : '',
                'start'       => ($page - 1) * $this->config->get('config_limit_admin'),
                'limit'       => $this->config->get('config_limit_admin')
            );
        }
        $json['orders'] = $this->model_wkpos_reports->getOrders($filter, true);
        $json['total'] = $this->model_wkpos_reports->getTotalOrders($filter, true);
        foreach ($json['orders'] as $key => $total) {
            $json['orders'][$key]['total'] = $this->currency->format($total['total'], $this->config->get('config_currency'));
            $json['orders'][$key]['cost'] = $this->currency->format($total['cost'], $this->config->get('config_currency'));
            $json['orders'][$key]['profit'] = $this->currency->format($total['total'] - $total['cost'], $this->config->get('config_currency'));
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

  public function customerAutocomplete() {
    $json = array();
    if ($this->validateAccess() && isset($this->request->get['filter_customer']) && strlen($this->request->get['filter_customer']) >= 2) {
      $this->load->model('wkpos/reports');
      $filter = array(
        'filter_customer' => $this->request->get['filter_customer'],
      );
      $json= $this->model_wkpos_reports->getOrderCustomers($filter);
    }
    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
  }
  protected function validateAccess() {
    $this->load->language('wkpos/reports');
    if (!$this->user->hasPermission('access', 'wkpos/reports')) {
      $this->error['permission'] = $this->language->get('error_permission');
    }
    return !$this->error;
  }
}
