<?php

namespace ExpandCart\Foundation\Support;

class DataWarehouse
{
   
   
	//------- Store Events Tracking -----//
	/*
	 * dynamic method for tracking store events ex:onboarding_finish,first_order .. 
	 * the events names should added first at our UDP service
	 * to be mapped with specific place at dataware DB  
	 * 
	 * @parm String $eventName 
	 * @parm Array $properties 
	 *
	 * @return void 
	 */
    public static function tracking($eventName, $properties = []){
		try {
			
			$server = EC_MONITORING['server'];
			$port   = EC_MONITORING['port'];
			
			$soc = new \ExpandCart\Foundation\Network\UdpSocket;
			$soc->connect($server, $port);
			
			$data = [
				'storecode' => STORECODE,  //the main identifier 
				'client_id' => WHMCS_USER_ID, //additional info we can remove it 
				'eventName' => $eventName,
				'timestamp'	=> time()
			];
			
			

			if (count($properties) > 0) {
				$data['properties'] = $properties;
			}

			/*
			
			$soc->sendBody('tracking/store/events',$data);
			*/
			
			//------- tell finishing sendBody() ----//
			
			$body = bin2hex(gzencode(json_encode($data)));

			$soc->send([
				'headers' => [
					'auth' => time(),
					'method' => 'post',
					'action' => 'tracking/store/events',
					'content-encoding' => 'gzip',
					'udp-version' => '1.0'
				],
				'body' => $body
			]);

        $soc->close();

		} catch (Exception  $e){}		
	
	}
	

	/*
	 * 
	 * the events names should added first at our UDP service
	 * to be mapped with specific place at dataware DB  
	 * 
	 * @parm String $eventName 
	 * @parm Array $properties 
	 *
	 * @return void 
	 */
    public static function clientCreate( $data = []){
		
		try {
			
			$server = EC_MONITORING['server'];
			$port   = EC_MONITORING['port'];
			
			$soc = new \ExpandCart\Foundation\Network\UdpSocket;
			$soc->connect($server, $port);
			
			if(empty($data))
				return ;

			$body = bin2hex(gzencode(json_encode($data)));

			$soc->send([
				'headers' => [
					'auth' => time(),
					'method' => 'post',
					'action' => 'tracking/store/clientCreate',
					'content-encoding' => 'gzip',
					'udp-version' => '1.0'
				],
				'body' => $body
			]);

        $soc->close();
		
		} catch (Exception  $e){}
		
	}
	
	
    public static function moduleCreate($data = []){
		
		try {
			
			$server = EC_MONITORING['server'];
			$port   = EC_MONITORING['port'];
			
			$soc = new \ExpandCart\Foundation\Network\UdpSocket;
			$soc->connect($server, $port);
			
			if(empty($data))
				return ;

			$body = bin2hex(gzencode(json_encode($data)));

			$soc->send([
				'headers' => [
					'auth' => time(),
					'method' => 'post',
					'action' => 'tracking/store/moduleCreate',
					'content-encoding' => 'gzip',
					'udp-version' => '1.0'
				],
				'body' => $body
			]);

			$soc->close();
		
		} catch (Exception  $e){}
		
		
	}
	
	
	
	
			
}
