<?php

class ControllerReportProductsLive extends Controller
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
        $this->language->load('report/products_live');

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

        $this->template = 'report/products_live.expand';
        $this->children = ['common/header', 'common/footer'];

        $this->response->setOutput($this->render());
    }

    public function list()
    {
        $this->initializer(['report/products_live']);

        $request = $_REQUEST;
        $search = !empty($request['search']['value']) ? $request['search']['value'] : null;

        $start = $request['start'] ?: 0;
        $length = $request['length'] ?: 10;

        $return = $this->products_live->getTree([
            'start' => $start, 'length' => $length
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
}
