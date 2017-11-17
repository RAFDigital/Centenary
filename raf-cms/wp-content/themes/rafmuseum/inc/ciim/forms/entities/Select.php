<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RafSelect extends RafFormEntity
{

	public function exportToJson($post_id, $value = false, $params = array()){
		if(!$value){
			$value = get_field($this->field['key'], $post_id);
		}

		return $this->formatJsonValue($value, $params);
	}

	public function importFromJson($params = array()){
		
	}
}