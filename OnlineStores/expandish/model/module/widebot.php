<?php

class ModelModuleWidebot extends Model {

	const ENDPOINT = 'https://bot.widebot.net/api/RestAPI';
	
	public function callAfterCheckoutAPI($flowname, $x_bot_token){
		$data = [
			'Attributes' => null,
			'FlowName'   => $flowname
		];
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => self::ENDPOINT,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => json_decode($data),
		  CURLOPT_HTTPHEADER => array(
		    "Content-Type: application/json",
		    "x-bot-token: $x_bot_token"
		  ),
		));

		$response = curl_exec($curl);
		curl_close($curl);
	}

}
