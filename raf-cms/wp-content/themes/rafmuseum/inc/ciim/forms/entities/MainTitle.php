<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RafMainTitle extends RafName
{

	public function exportToJson($post_id, $value = false, $params = array()){
		$json = parent::exportToJson($post_id, get_the_title($post_id), $params);
		return $json;
	}

	public function importFromJson($params = array()){
		
	}
}