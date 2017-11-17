<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class RafAcfForm
{
	protected $admin_meta_keys = array(
		'id',
		'uid',
		'uuid'
	);

	protected $skip_field_types = array(
		'tab', 'message'
    );

	protected $meta_fields = array(); 
	protected $json_branches = array(); 
	protected $used_keywords = array(); 

 

	public function __construct($post_type, $params)
	{

		$this->loadEntityClasses();

		$this->post_type = $post_type;
		$this->fields = $this->getCustomFieldDefinitions(array('post_type' => $this->post_type));
		$this->params = $params;
	} 

	abstract function exportToJson($post_id);
	abstract function importFromJson($json);
 

	private function loadEntityClasses(){
		// base class first
		rafInclude('ciim/forms/entities/FormEntity');
		rafInclude('ciim/forms/entities/SimpleValue');
		rafInclude('ciim/forms/entities/Name');
		rafInclude('ciim/forms/entities/MainTitle');
		rafInclude('ciim/forms/entities/LinkLiteral');
		rafInclude('ciim/forms/entities/Date');
		rafInclude('ciim/forms/entities/Coverage');
		rafInclude('ciim/forms/entities/Keywords');
		rafInclude('ciim/forms/entities/Select');
		rafInclude('ciim/forms/entities/Checkbox');
		rafInclude('ciim/forms/entities/Descriptions');
		rafInclude('ciim/forms/entities/Repeater');
		rafInclude('ciim/forms/entities/Events'); 
	}

	protected function getTheTitle($post_id){
		$entity = new RafMainTitle($post_id);
		return $entity->exportToJson($post_id, false, array('main_title' => true));
	}

	public static function getJsonFieldName($entity, $params = array()){
		$p_link = '@link';
		if($entity->isLinkEntity()){
			return $p_link;
		} else {
			return self::getJsonFieldNameFromName($entity);
		}
	}

	public static function getJsonFieldNameFromName($entity, $params = array()){
		return $entity->field['name'];
	}


	protected function initializeFormEntiies($fields)
    { 
    	$entities = array();
    	foreach ($fields as $field) {
			if($field['type'] == 'fields_group'){
				return $this->initializeFormEntiies($field['sub_fields']); 
			}

			$entity = $this->createEntity($field); 
			$entity->setForm($this); 

			if(isset($field['sub_fields']) && count($field['sub_fields'])){
	    		$entity->children = $this->initializeFormEntiies($field['sub_fields']);
	    	}

			$entities[$field['key']] = $entity;
			
		}
		return $entities;
    }

    protected function createEntity($field){
    	$class_name = '';
		if(isset($field['wrapper']) && isset($field['wrapper']['class']) && strlen($field['wrapper']['class'])){
			$class = current(explode(' ', $field['wrapper']['class'] . ' ')); 
			if(strpos($class, 'raf_') === 0){
				if(!class_exists($class)){
					$class_name = $this->getCamelCaseName($class);
				}
			}
		} 

		if(!$class_name){
			$class_name = $this->getCamelCaseName('raf_' . $field['type']);
			if(!class_exists($class_name)){
				$class_name = 'RafSimpleValue';
			}
		} 
		return new $class_name($field);
    }


	public static function generateAdminId($title, $type){
		return $type . '-' .  self::slugify($title) . '-' .  time();
	}

	public static function generateAdminUid($id){
		return 'raf-wp-' . $id;
	}


	public static function slugify($text){
		// replace non letter or digits by -
		$text = preg_replace('~[^\pL\d]+~u', '-', $text);

		// transliterate
		$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

		// remove unwanted characters
		$text = preg_replace('~[^-\w]+~', '', $text);

		// trim
		$text = trim($text, '-');

		// remove duplicate -
		$text = preg_replace('~-+~', '-', $text);

		// lowercase
		$text = strtolower($text);

		if (empty($text)) {
			return 'n-a';
		}

		return $text;
	}

	private function getValueIfExists($root, $module, $value = false, $sub_value = false, $sub_sub_value = false){
		$val = '';

		// pr($root); die(); 

		if(isset($root->$module)){
			if($value !== false){
				$mod = $root->$module;
				$isset = false;
				if(is_array($mod)){
					if(isset($mod[$value])){
						$val = $mod[$value];
						$isset = true;
					}
				} else {
					if(isset($root->$module->$value)){
						$val = $mod->$value;
						$isset = true;
					}
				}

				if($sub_value !== false && $isset){
					if(is_array($val)){
						$val = $val[$sub_value];
					} else {
						if(isset($val->$sub_value)){
							$val = $val->$sub_value; 
						}
						
					}

					if($sub_sub_value !== false){
						if(is_array($val)){
							$val = current($val);
						}

						$val = $val->$sub_sub_value;
					}
				}

			} else {
				$val = $root->$module;
			}
		}
		return $val;
	}


	public function getAcfFieldKey($field_name){
		global $wpdb;
		$acf_fields = $wpdb->get_results( $wpdb->prepare( "SELECT `post_name` FROM $wpdb->posts WHERE post_excerpt=%s AND post_type=%s" , $field_name , 'acf-field' ) );
		
		switch ( count( $acf_fields ) ) {
			case 0: // no such field
				return false;
			default:
			case 1: // just one result. 
				return $acf_fields[0]->post_name;
		}
	}


	/**
	 * @return array multidimensional list of custom fields definitions
	 */
	protected function getCustomFieldDefinitions($args = array())
	{
		$fieldGroups = function_exists('acf_get_field_groups') ? acf_get_field_groups($args) : apply_filters('acf/get_field_groups', array());


	    $customFields = array();
	    foreach ($fieldGroups as $fieldGroup) {
	        $fields = acf_get_fields($fieldGroup); 
	        $customFields[] = array(
	        	'id' => $fieldGroup['key'], 
	        	'name' => $fieldGroup['title'], 
	        	'type' => 'fields_group', 
	        	'sub_fields' => $this->getRelevantFields(acf_get_fields($fieldGroup))
	        );
	    }

	    // 
	    return $customFields;
	}

	private function getRelevantFields($fields)
    {
        foreach ($fields as $i => $field) {
        	if(in_array($field['type'], $this->skip_field_types)){
        		unset($fields[$i]);
        		continue;
        	}

        	if(isset($field['sub_fields']) && count($field['sub_fields'])){
        		$fields[$i]['sub_fields'] = $this->getRelevantFields($field['sub_fields']);
        	}
 
        }
        return $fields;
    }

    /**
     * Removes - and _ and makes the next letter uppercase
     *
     */
    protected function getCamelCaseName($name)
    {
        return str_replace(
            ' ', '', ucwords(str_replace(array('_', '-'), ' ', $name))
        );
    }


    public function addJsonBranch($json){
    	if($json && count($json)){
    		$this->json_branches[] = $json;
    	}
    }

    protected function getJsonBranches(){
    	return $this->json_branches;
    }

    protected function getCompleteJson(){
    	$json_array = array();
    	foreach ($this->getJsonBranches() as $branch) {
    		$json_array[] = $branch;
    	}
    	return $json_array;
    }

}

