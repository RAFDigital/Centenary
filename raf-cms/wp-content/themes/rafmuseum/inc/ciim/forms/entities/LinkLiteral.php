<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RafLinkLiteral extends RafFormEntity
{
	protected $is_link = true;

	public function exportToJson($post_id, $value = false, $params = array()){
		
		if(!$value){
			$value = get_field($this->field['key'], $post_id);
		}

		$link = new StdClass();
		$field_name = RafAcfForm::getJsonFieldNameFromName($this);
		$link->$field_name = new StdClass();
		$link->$field_name->value = sanitize_text_field($value);
		$link->type = 'literal';
 

		return $this->formatJsonValue($link, array_merge($params, array()));
	}

	public function importFromJson($params = array()){
		
	}
}