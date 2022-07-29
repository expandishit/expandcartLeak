<?php
class ControllerApiReward extends Controller {
	public function index() {
		$this->load->language('api/reward');

		// Delete past reward in case there is an error
		unset($this->session->data['reward']);

		$json = array();

		if (!isset($this->session->data['api_id'])) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			$points = $this->customer->getRewardPoints();

			$points_total = 0;

			foreach ($this->cart->getProducts() as $product) {
				if ($product['points']) {
					$points_total += $product['points'];
				}
			}
			
			if (empty($this->request->post['reward'])) {
				$json['error'] = $this->language->get('error_reward');
			}

			if ($this->request->post['reward'] > $points) {
				$json['error'] = sprintf($this->language->get('error_points'), $this->request->post['reward']);
			}

			if ($this->request->post['reward'] > $points_total) {
				$json['error'] = sprintf($this->language->get('error_maximum'), $points_total);
			}

			if (!$json) {
				$this->session->data['reward'] = abs($this->request->post['reward']);

				$json['success'] = $this->language->get('text_success');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function maximum() {
		$this->load->language('api/reward');

		$json = array();

		if (!isset($this->session->data['api_id'])) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			$json['maximum'] = 0;

			foreach ($this->cart->getProducts() as $product) {
				if ($product['points']) {
					$json['maximum'] += $product['points'];
				}
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function available() {
		$this->load->language('api/reward');

		$json = array();

		if (!isset($this->session->data['api_id'])) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			$json['points'] = $this->customer->getRewardPoints();
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function details() {
		$this->load->language('api/reward');

		$json = array();

		if (!isset($this->session->data['api_id'])) {
			$json['error'] = $this->language->get('error_permission');
		} else {
            $params = json_decode(file_get_contents('php://input'));
	        $this->load->model('account/reward');
            $data['sort'] = $params->sort;
            $data['order'] = $params->order;
            $json['points'] = $this->model_account_reward->getRewards($data);
			$json['total'] = $this->customer->getRewardPoints();
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

}
