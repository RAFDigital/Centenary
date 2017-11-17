<?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CIIM
{
    const USERNAME = '';
    const PASSWORD = '';
    const URL = 'http://172.16.0.41/ciim/rafm/api/';

	public function export($json){

		// token test 
		//$result = $this->callAPI('', 'secure/process/token/new?processName=RAF+Stories');

		// register
		$proces_json = $this->registerProcess();
		$result = $this->callAPI($proces_json, 'secure/process/register');

		$result = $this->callAPI($json, 'secure/process/data/submit/complete');
		$response = $this->parseJson($result);
		if($response && $response->status == 'OK'){
			return $response->token;
		} else {
			return false;
		}
	}

	private function registerProcess(){
		$process = new StdClass();
		$process->descriptor = new StdClass();
		$process->descriptor->name = 'RAF Stories';
		$process->stream = new StdClass();
		$process->stream->code = 'rafs';
		$process->stream->name = 'RAF Stories Output stream';
		$process->source = new StdClass();
		$process->source->code = 'raf-wp';
		$process->source->name = 'RAF Stories Word Press';
		$process->source->type = 'PROJECT';
		return json_encode($process);
	}

	private function callAPI($json, $url){
		//echo $json; die(); 

		$process = curl_init(self::URL . $url);
		curl_setopt($process, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($json)));
		curl_setopt($process, CURLOPT_HEADER, 1);
		// curl_setopt($process, CURLOPT_USERPWD, self::USERNAME . ":" . self::PASSWORD);
		curl_setopt($process, CURLOPT_TIMEOUT, 5); 
		curl_setopt($process, CURLOPT_POST, 1);
		curl_setopt($process, CURLOPT_POSTFIELDS, $json);
		curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($process, CURLINFO_HEADER_OUT,true);

		$response = curl_exec($process);

		pr($response);

		curl_close($process);
		$start = stripos($response, "{");
		$body = substr($response,$start);
		
		return $body;
	}

	private function parseJson($result){
		$json = json_decode($result); 
		// pr($json); die(); 
		return $json;
	}
 
}