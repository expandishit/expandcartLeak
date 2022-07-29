<?php
class Log {
	private $filename;
	
	public function __construct($filename) {
		$this->filename = $filename;
	}
	
	public function write($message) {
		$file = DIR_LOGS . $this->filename;
		
		$handle = fopen($file, 'a+'); 
		
		fwrite($handle, date('Y-m-d G:i:s') . ' - ' . $message . "\n");
			
		fclose($handle); 
	}


	public function forceWrite( $msg, $filename = null )
	{
		$ecdatetime = new ECDateTime;
		$time = $ecdatetime->get_current_date_time_in_mysql_format();

		$f = fopen($filename?? 'logs/logs.txt', 'w');

		fwrite($f, "{$time} => \n $msg");

		fclose($f);
	}


	public function ifStoreCodeIs( $storeCode, $data )
	{
		if ( STORECODE != $storeCode ) return false;

		try{
			$separator = DIRECTORY_SEPARATOR;
			$logsPath = __DIR__ . $separator . '..' . $separator . '..' . $separator . "logs";
			$logFile = $logsPath . $separator . $this->filename;

			$prefix = "\n\n" . date('Y-m-d G:i:s') . " => => ";
			$suffix = "\n\n";

			if ( ! file_exists($logFile) ) {
				$f = fopen($logFile, 'w');
				fclose($f);
			}

			if ( is_array($data) ) {
				ob_start();
				var_dump($data);
				$data = ob_get_clean();
			}

			file_put_contents( $logFile, $prefix.$data.$suffix, FILE_APPEND );

			return true;
		} catch ( \Exception $e ) {
			return false;
		}
	}
}
?>
