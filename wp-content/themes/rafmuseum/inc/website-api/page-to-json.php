<?php
class Page_To_Json {

	protected $post_type;

	public function __construct( $post ) {
		$this->post_type = $post->post_type; 
		$this->post = $post; 
		//$obj = get_post_type_object( $this->post_type );  
	}

	public function getJsonObject(){
		//pr($this->post);
		$page_json = $this->getPageInformation();
		// $page_json->meta = $this->getPageMeta();
		$page_json->extra_fields = $this->getPageAcfFields();
		//$page_json->extra_fields = 'ahoj';
		return json_encode($page_json);
	}

	protected function getPageAcfFields() {
		$acf = new StdClass();
		$skip = array();
		//$fields = get_fields($this->post->ID);
		$field_objects = get_field_objects($this->post->ID);
		
		// pr($field_objects); die(); 

		if(is_array($field_objects)){
			foreach( $field_objects as $name => $data ){
				if($data['type'] != 'clone'){
					$acf->$name = $data['value'];
				}				
			}
		}

		return $acf;
	}

	protected function getPageInformation() {
		$info = new StdClass();
		$skip = array(
			'comment_count',
			'comment_status',
			'ping_status',
			'post_password',
			'to_ping',
			'pinged',
			'guid',
			'post_author',
			'post_content_filtered',
			'post_date_gmt',
			'post_modified_gmt',
			'post_mime_type',
			'filter'
		);

		$fields_map = array(
			'ID' => 'id',
			'post_date' => 'issue_date',
			'post_modified' => 'modified',
			'post_status' => 'status',
			'post_type' => 'type',
			'post_name' => 'name',
			'post_title' => 'title',
			'post_excerpt' => 'excerpt',
			'post_content' => 'content',
			'post_parent' => 'parent_id',
			'page_template' => 'template', 


		);


		foreach ($this->post as $key => $value) { 
			if(!in_array($key, $skip)){
				$info->$key = $value;
			}

			if(array_key_exists($key, $fields_map)){
				$new_key = $fields_map[$key];
				$info->$new_key = $info->$key;

				unset($info->$key);
			}
		}

		/****   post_content ****/
		$info->content = apply_filters('the_content', $info->content);

		/****  additional data ****/
		// page template
		if($this->post_type == 'post'){
			$info->template = 'single-post';
		} else {
			$t = get_post_meta( $this->post->ID, '_wp_page_template', true);
			$dirs = explode('/', $t);
			$info->template = str_replace('.php', '', array_pop($dirs));
		}	

		// page template
		$info->url = WebsiteImportExport::getRelativeUrl(get_permalink($this->post->ID));


		return $info;
	}

	/*
	protected function getPageMeta() {
		$meta = new StdClass();
		$visible = array(
			'_wp_page_template',
		);
		//pr(get_post_meta ( $this->post->ID ));
		
		foreach ($visible as $meta_key) {
			$meta->$meta_key = get_post_meta( $this->post->ID, $meta_key, true );
		}

		return $meta;
	}
	*/

	protected function getFieldObjects() {
		$id = $this->post->ID;

		if ( empty( $id ) ) {
			return false;
		}

		$fields     = array();
		$fields_tmp = array();

		if ( function_exists( 'acf_get_field_groups' ) && function_exists( 'acf_get_fields' ) && function_exists( 'acf_extract_var' ) ) {				
			$field_groups = acf_get_field_groups( array( 'post_id' => $id ) );

			if ( is_array( $field_groups ) && ! empty( $field_groups ) ) {
				foreach ( $field_groups as $field_group ) {
					$field_group_fields = acf_get_fields( $field_group );
					if ( is_array( $field_group_fields ) && ! empty( $field_group_fields ) ) {
						foreach( array_keys( $field_group_fields ) as $i ) {
							$fields_tmp[] = acf_extract_var( $field_group_fields, $i );
						}
					}
				}
			}
		} else {
			if ( strpos( $id, 'user_' ) !== false ) {
				$filter = array( 'ef_user' => str_replace( 'user_', '', $id ) );
			} elseif ( strpos( $id, 'taxonomy_' ) !== false ) {
				$filter = array( 'ef_taxonomy' => str_replace( 'taxonomy_', '', $id ) );
			} else {
				$filter = array( 'post_id' => $id );
			}

			$field_groups = apply_filters( 'acf/location/match_field_groups', array(), $filter );
			$acfs = apply_filters( 'acf/get_field_groups', array() );

			if ( is_array( $acfs ) && ! empty( $acfs ) && is_array( $field_groups ) && ! empty( $field_groups ) ) {
				foreach( $acfs as $acf ) {
					if ( in_array( $acf['id'], $field_groups ) ) {
						$fields_tmp = array_merge( $fields_tmp, apply_filters( 'acf/field_group/get_fields', array(), $acf['id'] ) );
					}
				}
			}
		}

		if ( is_array( $fields_tmp ) && ! empty( $fields_tmp ) ) {
			foreach( $fields_tmp as $field ) {
				if ( is_array( $field ) && isset( $field['name'] ) ) {
					$fields[$field['name']] = $field;
				}
			}
		}

		return $fields;
	}
}

