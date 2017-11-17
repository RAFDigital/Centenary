<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RafAcfFormStory extends RafAcfForm
{

	public function exportToJson($post_id){
		$entities = $this->initializeFormEntiies($this->fields);

		$json_branch_story = new StdClass();

		//admin 
		$json_branch_story->admin = $this->loadAdminDataFromForm($post_id); 

		// title
		$json_branch_story->title = $this->getTheTitle($post_id); 

		// acf fields
		$json_branch_story = $this->exportFormEntitiesToJson($json_branch_story, $entities, $post_id);

		// base type 
		$t = new StdClass();
		$t->base = $this->post_type;
		$json_branch_story->type = $t;

		$this->addJsonBranch($json_branch_story);


		echo json_encode($this->getCompleteJson());
		die(); 
	}

	protected function loadAdminDataFromJson($data_json){
		foreach ($this->admin_meta_keys as $key) {
			$this->meta_fields['admin_' . $key] = $this->getValueIfExists($data_json, 'admin', $key);
		}
	}

	protected function loadAdminDataFromForm($post_id){ 
		
		$admin_id = $this->generateAdminUid($post_id);

		$admin = new StdClass();
		// test
		$admin->id = $admin_id;

		return $admin;

	}

	private function getStoryMeta($post_id, $ident, $key){
		return get_post_meta($post_id, 'story_' . $ident . '_' . $key, true); 
	}

	private function exportFormEntitiesToJson($json, $entities, $post_id){ 
    	foreach ($entities as $field_key => $entity){
    		//pr($entity->field['name']); 
    		$entity_name = RafAcfForm::getJsonFieldName($entity);
			$json->$entity_name = $entity->exportToJson($post_id); 
			/*
			if(isset($entity_data['children']) && count($entity_data['children'])){
	    		$entities[$field_key]['children'] = $this->setFormEntitiesValuesFromPost($entity_data['children'], $post_id);
	    	}*/
		}
		return $json;
	}

	public function importFromJson($json){

	}
}

