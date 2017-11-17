<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RafName extends RafFormEntity
{

	public function exportToJson($post_id, $value = false, $params = array()){
		if(!$value){
			$value = get_field($this->field['key'], $post_id);
		}

		$name_value = new StdClass();
		$name_value->value = sanitize_text_field($value);
		$name_value->primary = true;

		return $this->formatJsonValue($name_value, array_merge($params, array('as_array' => true)));
	}

	public function importFromJson($params = array()){
		
	}
}