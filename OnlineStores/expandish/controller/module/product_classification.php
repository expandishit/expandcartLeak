<?php  
class ControllerModuleProductClassification extends Controller {


	public function get_models_by_brand()
	{
		if ($this->request->server['REQUEST_METHOD'] != 'POST') {

			$this->response->setOutput(json_encode(['status' => 'fail']));
			return;

		}
		$brand = (int)$this->request->post['brand'];

		$this->load->model('module/product_classification/brand');


		$models = $this->model_module_product_classification_brand->getModelsByBrandId($brand);
		$response['status'] = 'success';
		$response['models'] = $models;

		$this->response->setOutput(json_encode($response));
		return;
	}
	public function get_years_by_model()
	{
		if ($this->request->server['REQUEST_METHOD'] != 'POST') {

			$this->response->setOutput(json_encode(['status' => 'fail']));
			return;

		}
		$model = (int)$this->request->post['model'];

		$this->load->model('module/product_classification/model');


		$years = $this->model_module_product_classification_model->getYearsByModelId($model);

		$response['status'] = 'success';
		$response['years'] = $years;

		$this->response->setOutput(json_encode($response));
		return;
	}
	

}
?>
