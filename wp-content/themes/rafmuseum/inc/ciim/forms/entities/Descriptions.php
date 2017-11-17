<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RafDescriptions extends RafSimpleValue
{

	public function exportToJson($post_id, $value = false, $params = array()){ 
		$values = get_field($this->field['name'], $post_id);
		$descriptions = array();
		foreach ($values as $name => $value) {
			$d = new StdClass();
			$d->type = $name;
			$d->value = $value;
			if($name == 'main_description'){
				$d->primary = true;
			}
			$descriptions[] = $d;

		}

		return $this->formatJsonValue($descriptions, $params);
	}

	public function importFromJson($params = array()){

	}
 
}