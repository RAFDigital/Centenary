<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RafRepeater extends RafSimpleValue
{

	public function exportToJson($post_id, $value = false, $params = array()){
		$json = array();

		if(!$value){
			$value = get_field($this->field['key'], $post_id);
		}
		//pr(' ----- ' . $this->field['name'] . ' -------'); pr($value);
		if(count($value) && is_array($value)){
			foreach ($value as $i => $row) {
				$row_json = new StdClass();
				if(count($this->children)){		
					foreach ($this->children as $children_entity) {
						$entity_name = RafAcfForm::getJsonFieldName($children_entity);

						$row_json->$entity_name = $children_entity->exportToJson(
							$post_id, 
							$row[$children_entity->field['name']], 
							array_merge($params, array('in_repeater' => true))
						);

					}
				}
				if(isset($params['in_repeater']) && $params['in_repeater']){
					$json[] = $row_json;
				} else {
					$entity_name = RafAcfForm::getJsonFieldName($this); 
					$json[$entity_name][] = $row_json;
				}
			}
		}
		
		return $json;
	}

	public function importFromJson($params = array()){
		
	}
}