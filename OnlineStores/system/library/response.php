<?php
class Response {
	private $headers = array(); 
	private $level = 0;
	private $output;

	/**
	 * @param int
	 */
	private $statusCode = 200;
	
	public function addHeader($header) {
		$this->headers[] = $header;
	}

	public function redirect($url) {
		header('Location: ' . $url);
		exit;
	}
	
	public function setCompression($level) {
		$this->level = $level;
	}
		
	public function setOutput($output, int $statusCode = 200)
	{
		$this->setStatusCode($statusCode);

		$this->output = $output;
	}

	/**
	 * Set the output to be a json response with the proper header
	 *
	 * @param mixed $output
	 * @param int $statusCode
	 *
	 * @return void
	 */
	public function json($output, int $statusCode = 200)
	{
		$this->addHeader('Content-Type: application/json');
		$this->setStatusCode($statusCode);
		$this->output = json_encode($output);
	}

	/**
	 * Set the response code
	 *
	 * @param int $statusCode
	 *
	 * @return self
	 */
	protected function setStatusCode(int $statusCode)
	{
		$this->status = $statusCode;

		http_response_code($this->status);

		return $this;
	}

	private function compress($data, $level = 0) {
		if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false)) {
			$encoding = 'gzip';
		} 

		if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip') !== false)) {
			$encoding = 'x-gzip';
		}

		if (!isset($encoding)) {
			return $data;
		}

		if (!extension_loaded('zlib') || ini_get('zlib.output_compression')) {
			return $data;
		}

		if (headers_sent()) {
			return $data;
		}

		if (connection_status()) { 
			return $data;
		}
		
		$this->addHeader('Content-Encoding: ' . $encoding);

		return gzencode($data, (int)$level);
	}

	public function output() {
		if ($this->output) {
			if ($this->level) {
				$ouput = $this->compress($this->output, $this->level);
			} else {
				$ouput = $this->output;
			}	
				
			if (!headers_sent()) {
				foreach ($this->headers as $header) {
					header($header, true);
				}
			}
			
			echo $ouput;
		}
	}
}
?>