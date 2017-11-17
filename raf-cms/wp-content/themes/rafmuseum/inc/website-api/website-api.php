<?php 
class WebsiteApi { 
	private static $instance = false;
	public $rest;

	private function __construct()
	{

	} 

	public static function getInstance()
	{ 
		if(self::$instance === false){ 
			self::$instance = new WebsiteApi; 
			self::$instance->initApi();
		} 
		return self::$instance; 
	} 

	private function initApi(){
		$this->rest = new RestClient([
		    'base_url' => RAF_REST_BASE_URL,
		    // 'format' => RAF_REST_FORMAT,  
		    'headers' => [
		    	'Accept' => 'application/json',
		    	'Content-Type' => 'application/json'
		    ],// ['Authorization' => 'xy'],  
		]);
	}

} 
