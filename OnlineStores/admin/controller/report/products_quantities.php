<?php

class ControllerReportProductsQuantities extends Controller
{
    private $plan_id = PRODUCTID;

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('plan/trial');
        $trial = $this->model_plan_trial->getLastTrial();
        if ($trial){
            $this->plan_id = $trial['plan_id'];
        }

        if($this->plan_id == 3){
            $this->redirect(
                $this->url->link('error/not_found', '', 'SSL')
            );
            exit();
        }
    }

    public function index()
    {
        $this->language->load('report/products_quantities');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['breadcrumbs'] = [];

        $this->data['breadcrumbs'][] = [
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        ];

        $this->data['breadcrumbs'][] = [
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('report/product_viewed', '', 'SSL'),
            'separator' => ' :: '
        ];

        $this->template = 'report/products_quantities.expand';
        $this->children = ['common/header', 'common/footer'];

        $this->response->setOutput($this->render());
    }

    public function list()
    {
        $this->initializer(['report/products_quantities']);

        $request = $_REQUEST;

        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        $start = $request['start'] ?: 0;
        $length = $request['length'] ?: 10;
        $product_id = !empty($request['product_id'])? $request['product_id'] : null;

        $return = $this->products_quantities->getTree([
            'start' => $start, 'length' => $length,'product_id'=>$product_id
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
    }
}
