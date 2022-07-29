<?php

class ControllerAmazonAmazonMapExportProduct extends Controller {
	private $error = array();
	private $route = 'amazon/amazon_map/export_product';
	private $common = 'amazon/amazon_map/common';

	public function __construct($registory) {
		parent::__construct($registory);
		$this->load->model($this->route);
		$this->_amazonMapExportProduct = $this->model_amazon_amazon_map_export_product;
		$this->load->model('amazon/amazon_map/product');
		$this->_amazonMapProduct = $this->model_amazon_amazon_map_product;
		$this->load->language($this->route);
		$this->load->language($this->common);
    }

    public function index() {
		$this->document->setTitle($this->language->get('heading_title'));

		$data['account_id'] = $this->session->data['amazon_account_id'] =$this->request->get['account_id'];

		$this->document->addScript('view/javascript/amazon_connector/webkul_amazon_connector.js');
		$this->template = 'amazon/amazon_map/export_product.expand';
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
            0 => 'p_product_id',
            1 => 'name',
            2 => 'category',
			3 => 'price',
			4 => 'quantity',
        );
        $orderColumn = $columns[$request['order'][0]['column']];

		$results = $this->_amazonMapExportProduct->getAllUnmappedProducts($start, $length, $filterData, $orderColumn );

		$records = $results['data'];


		foreach ($records as $key => $record) {
			// Categories
			if (isset($record['p_product_id'])) {
				$categories = $this->model_catalog_product->getProductCategories($record['p_product_id']);
			} else {
				$categories = array();
			}

			$product_categories = array();

			foreach ($categories as $category_id) {
				$category_info = $this->model_catalog_category->getCategory($category_id);

				if ($category_info) {
					$product_categories[] = ($category_info['path']) ? $category_info['path'] . ' &gt; ' . $category_info['name'] : $category_info['name'];
				}
			}

			$records[$key]['category'] = implode(",", $product_categories);
		}

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

    public function ocExportProduct()
    {
        $this->load->language($this->route);
        $json = $export_products = $process_products = array();

        if (isset($this->request->post['account_id']) && $this->request->post['account_id'] && isset($this->request->post['selected']) && !empty($this->request->post['selected'])) {
            $account_id = $this->request->post['account_id'];
            $start_page = $this->request->get['page'];
            $export_products = $this->request->post['selected'];

            if (isset($start_page) && $start_page == 1) {
                unset($this->session->data['ocproduct_export_result']);

                $this->session->data['ocproduct_export_result'] = array();
            }
            $accountDetails = $this->Amazonconnector->getAccountDetails(array('account_id' => $account_id));

            if (isset($accountDetails['wk_amazon_connector_marketplace_id']) && $accountDetails['wk_amazon_connector_marketplace_id']) {
                if (count($export_products) <= 1) {
                    $totalProcessPages = 1;
                } else {
                    $totalProcessPages = ceil(count($export_products) / 1);
                }
                $filter_data = [
                    'product_ids' => implode(",", array_slice($export_products, ($start_page - 1) * 1, 1)),
                ];

                $process_products = $this->Amazonconnector->getOcProductWithCombination($filter_data);

                if (isset($process_products) && !empty($process_products)) {
                    $sync_result = $this->_amazonMapExportProduct->startExportProcess($process_products, $account_id);
                } else {
                    $sync_result['error'][]['message'] = sprintf($this->language->get('error_no_product_match_found'), implode(",", array_slice($export_products, ($start_page - 1) * 1, 1)));
                    $sync_result['error_count'] = COUNT(array_slice($export_products, ($start_page - 1) * 1, 1));
                }
            } else {
                $json['error_failed'] = $this->language->get('error_account_not_exist');
            }
        } else {
            $json['error_failed'] = [$this->language->get('error_no_product_selected')];
        }

        if (!isset($json['error_failed']) && !empty($sync_result)) {
            if (isset($sync_result['error']) && $sync_result['error']) {
                foreach ($sync_result['error'] as $key => $error) {
                    $this->session->data['ocproduct_export_result']['error'][] = $error;
                }

            }
            if (isset($sync_result['success']) && $sync_result['success']) {
                $this->session->data['success'] = $this->language->get('text_success_amazon_export');
                foreach ($sync_result['success'] as $key => $success) {
                    $this->session->data['ocproduct_export_result']['success'][] = $success;
                }
            }
            $json = array('data' => $sync_result, 'totalPage' => $totalProcessPages);
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
