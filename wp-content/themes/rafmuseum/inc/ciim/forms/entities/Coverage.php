<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RafCoverage extends RafFormEntity
{

	public function exportToJson($post_id, $value = false, $params = array()){
		if(!$value){
			$value = get_field($this->field['key'], $post_id);
		}

		$values = array_values($value);

		$coverage = array();
		$coverage_1 = new StdClass();
		$coverage_1->earliest = $values[0];
		$coverage_1->latest = $values[1];
		$coverage_1->primary = true;
		$coverage_1->range = ($coverage_1->earliest != $coverage_1->latest);
		$coverage_1->value = $coverage_1->earliest . '-' . $coverage_1->latest;
		$coverage[] = $coverage_1;

		return $this->formatJsonValue($coverage, $params);
	}

	public function importFromJson($params = array()){
		
	}
}