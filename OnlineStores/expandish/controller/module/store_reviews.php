<?php
class ControllerModuleStoreReviews extends Controller { 
	
	public function postStoreReview() {

		$this->load->model('module/store_reviews');
		$this->language->load_json('module/store_review');

		$data = [
			'customer_id' => $this->customer->getId(),
			'ip_address' => $this->customer->getClientRealIP(),
			'rate' =>$this->request->post['rate'] ,
			'name' => $this->request->post['name'] ? $this->request->post['name'] : '',
			'rate_description' =>  $this->request->post['text'] ? $this->request->post['text'] : '',
		];

		if (! $this->model_module_store_reviews->getCustomerReview($data)) {	
			$this->model_module_store_reviews->saveStoreReviews($data);
			$this->response->setOutput(json_encode([
				'status' => $this->language->get('store_reviews_success'),
				'status_code' => 1
			]));
		}else{
			$this->response->setOutput(json_encode([
				'status' => $this->language->get('store_reviews_exists'),
				'status_code' => 0
			]));
		}

	}

}
