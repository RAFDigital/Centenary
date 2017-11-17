<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class RafFormEntity
{
	protected $is_link = false;
	protected $form;

	public function __construct($field = false)
	{
		if(isset($field['sub_fields'])){
			unset($field['sub_fields']);
		}
		
		$this->field = $field;
	} 

	abstract function exportToJson($post_id, $value = false, $params = array());
	abstract function importFromJson($params = array()); 

	public function isLinkEntity(){
		return $this->is_link;
	} 

	protected function formatJsonValue($value, $params){
		if(!is_object($value) && !is_array($value)){
			$value = sanitize_text_field($value);
		}

		if(isset($params['as_array']) && $params['as_array']){
			$value = array($value);
		}

		/*
		if((isset($params['in_repeater']) && $params['in_repeater']) || (isset($params['only_value']) && $params['only_value'])){
			$json_value = $value;
		} else {
			$json_value = new StdClass();
			$name = $this->field['name'];
			$json_value->$name = $value;
		} */

		$json_value = $value;

		return $json_value;
	}

	public function setForm($form){
		$this->form = $form;
	}
}

