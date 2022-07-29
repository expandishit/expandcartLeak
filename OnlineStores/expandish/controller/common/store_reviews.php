<?php
class ControllerCommonStoreReviews extends Controller{
    public function index()
    {
        $this->load->model('module/store_reviews');
		$this->data['all_reviews'] = $this->model_module_store_reviews->getReviews();

		if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/common/store_reviews_list.expand')) {
			$this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/common/store_reviews_list.expand';
		}
		else {
			$this->template = $this->config->get('config_template') . '/template/common/store_reviews_list.expand';
		}
		$this->response->setOutput($this->render_ecwig());
    }
}

?>