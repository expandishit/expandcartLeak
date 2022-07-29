<?php

class ControllerMultisellerBase extends Controller
{
    private $error = array();

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->registry = $registry;
        $parts = explode('/', $this->request->request['route']);
        if (!isset($parts[2]) || !in_array($parts[2], array('install', 'uninstall'))) {
        }
         if(\Extension::isInstalled('trips')&&$this->config->get('trips')['status']==1){
            $this->data = array_merge($this->data, $this->load->language('module/trips'));  
         }
         else{$this->data = array_merge($this->data, $this->load->language('multiseller/multiseller'));}
        
        $this->data['token'] = $this->session->data['token'];
//		$this->document->addStyle('view/stylesheet/multimerch/multiseller.css');
//		$this->document->addStyle('view/javascript/multimerch/datatables/css/jquery.dataTables.css');
//		$this->document->addScript('view/javascript/multimerch/datatables/js/jquery.dataTables.min.js');
        $this->document->addScript('view/javascript/multimerch/common.js');
    }

    // @todo: validation
    public function validate($action = '', $level = 'access')
    {
        return true;
        //var_dump($this->user->hasPermission($level, 'module/multiseller'));
//        if (
//            in_array(
//                strtolower($action),
//                [
//                    'sellers',
//                    'install',
//                    'uninstall',
//                    'jxsavesellerinfo',
//                    'savesettings',
//                    'jxconfirmpayment',
//                    'jxcompletepayment',
//                    'jxproductstatus'
//                ]
//            )
//        )
        if (!$this->user->hasPermission($level, 'module/multiseller')) {
            return $this->forward('error/permission');
        }
    }

    public function getFilterParams($filters, $colMap)
    {
        $searchVal = $this->request->post['search']['value'];
        $filterParams = array();
        $columns = $this->request->post['columns'];
        foreach ($columns as $key => $column) {
            if (isset($column['search']) && $column['searchable'] == true) {
                $colName = (strlen($column['name']) > 0 ? $column['name'] : $column['data']);
                $filterVal = $searchVal;
                if (!empty($filterVal) && in_array($colName, $filters)) {
                    $colName = isset($colMap[$colName]) ? $colMap[$colName] : $colName;
                    $filterParams[$colName] = $searchVal;
                }
            }
        }

        return $filterParams;
    }

    public function getSortParams($sorts, $colMap, $defCol = false, $defWay = false)
    {
        if (isset($this->request->post['order'][0]['column'])) {
            $column = $this->request->post['columns'][$this->request->post['order'][0]['column']];
            $orderBy = strlen($column['name']) > 0 ? $column['name'] : $column['data'];
            if (isset($orderBy)) {
                $sortCol = $orderBy;
            } else {
                $sortCol = $defCol ? $defCol : $sorts[0];
            }
        } else {
            $sortCol = $defCol ? $defCol : $sorts[0];
        }

        if (!in_array($sortCol, $sorts)) {
            $sortCol = $defCol ? $defCol : $sorts[0];
        }

        $sortCol = isset($colMap[$sortCol]) ? $colMap[$sortCol] : $sortCol;

        if (isset($this->request->post['order'][0]['dir'])) {
            $sortDir = $this->request->post['order'][0]['dir'] == 'desc' ? "DESC" : "ASC";
        } else {
            $sortDir = $defWay ? $defWay : "ASC";
        }

        return array($sortCol, $sortDir);
    }
}
