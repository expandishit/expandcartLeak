<?php

class ModelModuleElModaqeqProduct extends Model
{
	private $token = '';

	public function __construct ($registry){
		parent::__construct($registry);
		
		// echo 'elmodaqeq expandish model __construct<br/>';
    	$this->load->model('module/elmodaqeq/authentication');
    	$response = $this->model_module_elmodaqeq_authentication->login();
    	if($response['StatusCode'] == 0)
    		$this->token = $response['ResponseKey'];
	}

	public function getProduct($expandcart_product_id)
	{
		return $this->db->query("SELECT * FROM elmodaqeq_product WHERE expandcart_product_id =".(int)$expandcart_product_id)->row;
	}

	public function updateElmodaqeqQuantity($barcode, $quantity)
	{
		if( !empty($this->token) ) {			
			$curl = curl_init();
			curl_setopt_array($curl, array(
			  CURLOPT_URL => 'http://bmfcarapi.auditorerp.cloud/SaveOrderFromBody',
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => '',
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 0,
			  CURLOPT_FOLLOWLOCATION => true,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => 'POST',
			  CURLOPT_POSTFIELDS => json_encode([
				  "NbrRef"        => $barcode,
				  "TotalTax"      => 0,
				  "TotalDiscount" => 0,
				  "TotalAmount"   => 0,
				  "GrossTotalAmount" => 0,
				  "Additionals"      => 0,
				  "lstORDetails"     => [
				    [
				      "Barcode"      => $barcode,
				      "Qty"          => $quantity,
				      "Price"        => 0,
				      "TotalItemTax" => 0
				    ]
				  ]
			  ]),
			  CURLOPT_HTTPHEADER => array(
			    'Authorization: Bearer ' . $this->token,
			    'Content-Type: application/json'
			  ),
			));
			$response = curl_exec($curl);
			curl_close($curl);

			$log = new Log('elmodaqeq-logsss'.time());
			$log->write(json_encode($response));
		}
		else {
			$log = new Log('elmodaqeq-logs'.time());
			$log->write('elmodaqeq auth failed');
		}
	}
}
