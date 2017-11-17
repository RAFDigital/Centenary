<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class CIIMImportExport
{
	protected static $instance = null;

	
	abstract function importFromJsonOnSearch();

	private function getCIIMEntityById($id = false){
		$id = $id ? $id : $_GET['import_id'];
		$json = false;
		if(isset($_GET['c8load']) && $_GET['c8load'] == self::$post_type){  
			if($id){
				$json = ElasticSearch::getObjectById($id, self::$post_type); 
				if($json->hits->total > 0 && $json->hits->hits[0]){
					$json = $json->hits->hits[0];  
				}
			}	 
		}
		return $json;
	}

}

