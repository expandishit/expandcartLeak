<?php
class ControllerWkposExpense extends Controller {
	public function index() {
        $this->load->language('wkpos/wkpos');
		$json['expenses'] = array();
		$this->load->model('wkpos/expense');

        $user_id = $this->session->data['user_login_id'];
        $outlet_id = $this->session->data['wkpos_outlet'];

		$expenses = $this->model_wkpos_expense->getExpenses($user_id,$outlet_id);

		foreach ($expenses as $expense) {
			$json['expenses'][] = array(
				'date_added'     		=> $this->getDateByCurrentTimeZone($expense['date_added']),
				'title'     		=> $expense['title'] ,
				'amount'    => $this->currency->format($expense['amount'], $expense['currency_code'], $expense['currency_value']),
				'description'   		=> $expense['description'],
            );
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function addExpense() {
		$this->load->language('wkpos/wkpos');
		$this->load->model('wkpos/expense');

		$data = array();
        $json = array();

        if ((utf8_strlen(trim($this->request->post['title'])) < 1) || (utf8_strlen(trim($this->request->post['title'])) > 32)) {
            $json['title'] = $this->language->get('error_title');
        }

        if ((utf8_strlen(trim($this->request->post['amount'])) < 1) || (utf8_strlen(trim($this->request->post['amount'])) > 32)) {
            $json['amount'] = $this->language->get('error_amount');
        }

        if ($json) {
            $json['error'] = 1;
        }

        if (!$json) {
            $data['title'] = $this->request->post['title'];
            $data['description']  = $this->request->post['description'];
            $data['amount']  =  $this->request->post['amount'];
            $data['user_id'] = $this->session->data['user_login_id'];
            $data['outlet_id'] = $this->session->data['wkpos_outlet'];
            $data['currency_code'] = $this->session->data['currency'];
            $this->model_wkpos_expense->addExpense($data);
            $json['success'] = 'Expense added successfully';
        }
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
