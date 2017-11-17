<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RafDate extends RafFormEntity
{

	public function exportToJson($post_id, $value = false, $params = array()){
		if(!$value){
			$value = get_field($this->field['key'], $post_id);
		}

		$t = strtotime($value);
		$date = new StdClass();
		$date->earliest = date('Y', $t);
		$date->latest = date('Y', $t);
		$date->primary = true;
		$date->value  = date('d.m.Y', $t);

		return $this->formatJsonValue($date, $params);
	}

	public function importFromJson($params = array()){
		
	}
}