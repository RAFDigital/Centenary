<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RafEvents extends RafRepeater
{

	public function exportToJson($post_id, $value = false, $params = array()){
		//pr($this); die();
		//pr(get_field($this->field['key'], $post_id)); die();
		$params['in_events'] = true;
		$json = parent::exportToJson($post_id, get_field($this->field['key'], $post_id), $params);
		return $json;
	}

	public function importFromJson($params = array()){
		
	}


}