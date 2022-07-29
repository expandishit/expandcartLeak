<?php

class ModelModuleElModaqeqAuthentication extends Model
{
	const BASE_API_URL = 'http://bmfcarapi.auditorerp.cloud/LoginPost';

	public function login()
	{
		$settings = $this->config->get('elmodaqeq');
		$credentials = [
			'UserName' => $settings['username'],
			'Password' => $settings['password']
		];

		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => self::BASE_API_URL,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'POST',
		  CURLOPT_POSTFIELDS => json_encode($credentials),
		  CURLOPT_HTTPHEADER => array(
		    'Accept: application/json',
		    'Content-Type: application/json'
		  ),
		));

		$response = curl_exec($curl);
		curl_close($curl);
		return json_decode($response, true); //0 or -1
	}
}
