<?php

Class StoreStatistics {

	private $user;

	public function __construct($user = null){
		$this->user = $user;
	}

	public function store_statistcs_push($field, $action, $attributes=[]) 
	{

		$request = array(
		    'field' 		=> $field,
		    'action' 		=> $action,
		    'store_code' 	=> STORECODE,
		    'attributes' 	=> $attributes,
		    'meta' => [
		    	'HTTP_SEC_CH_UA' 			=> $_SERVER['HTTP_SEC_CH_UA'],
		    	'HTTP_SEC_CH_UA_MOBILE' 	=> $_SERVER['HTTP_SEC_CH_UA_MOBILE'],
		    	'HTTP_SEC_CH_UA_PLATFORM' 	=> $_SERVER['HTTP_SEC_CH_UA_PLATFORM'],
		    	'REMOTE_ADDR' 				=> $_SERVER['REMOTE_ADDR']
		    ]
		);

		if($this->user && $user_id = $this->user->isLogged()){
			$request['meta']['admin_id'] = $user_id;
			$request['meta']['admin_email'] = $this->user->getEmail();
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://ectools.expandcart.com/api/statistics/push');
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Verification-Key: expand_stats_jkgomn2306']);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS,
		    http_build_query(
		        $request
		    )
		);
		curl_setopt($ch, CURLOPT_TIMEOUT, 1);
		curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
		$response = curl_exec($ch);
		if ($cer = curl_error($ch)) {
		    $response->error =$cer;
		    return $response;
		}
		
		curl_close($ch);

		$response = json_decode($response);

		return $response;
	}
}
?>