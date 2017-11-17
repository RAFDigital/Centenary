<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CIIMImportExportStory extends CIIMImportExport
{
	private $post_type = 'story';

	public static function getInstance() {
 
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}


	private function __construct() {
 
		$this->createPostType();

		add_action( 'acf/save_post', array($this, 'storyPublish'), 200);
		add_action('init', array($this, 'importFromJsonOnSearch')); 

		rafInclude('ciim/forms/Form');
		rafInclude('ciim/forms/StoryForm'); 
		
		//add_filter( 'manage_' . self::$post_type. '_posts_columns', array( $this, 'manage_posts_columns' ) );
		//add_action( 'manage_' . self::$post_type. '_posts_custom_column', array( $this, 'synonyms_column' ), 10, 2 ); 
		//add_filter( 'redirect_post_location', array( $this, 'update_post_redirect' ) );
		//add_action( 'admin_notices', array( $this, 'vocabulary_admin_notice' ) );
		
	}

	public function importFromJsonOnSearch(){
		$json = $this->getCIIMEntityById(false);
		if($json){  

			$fields = $this->getCustomFieldDefinitions(array('post_type' => $this->post_type));
			$this->importFieldsFromJson($fields);
			
			pr($fields); pr($detail_json->_source); 
			die();
		}
	}

    public function storyPublish( $post_id ) {
		$post = get_post($post_id); 
		if($post->post_status == 'publish'){ 
			if($post->post_type == $this->post_type){
				
				$params = array();
				$form = new RafAcfFormStory($this->post_type, $params);
				$json = $form->exportToJson($post_id);
				pr($form); die(); 
				
				die('??');

				/*
				$CIIM = new CIIM();

				list($story_admin_id, $story, $new_keywords) = $importExport->exportStory($post);
				// preflight
				$pre_flight_data = array();
				$pre_flight = new StdClass();
				$pre_flight_data[] = $story_admin_id;
				foreach($new_keywords as $kw){
					$pre_flight_data[] = $kw->admin->id;
				}
				$pre_flight->data = $pre_flight_data;
				$ret_data = array();
				$return = new StdClass();
				if($new_keywords){
					if(count($new_keywords)){
						foreach($new_keywords as $kw){
							$ret_data[] = $kw;
						}
					}
				}
				$ret_data[] = $story;
				$return->data = $ret_data;
				$return->process = 'RAF Stories';

				$json = json_encode($return);
				//pr($return); die();


				$status = $CIIM->export($json);// die();
				*/
				if($status){
					// remove post
					wp_delete_post($post_id, true);
					wp_redirect(  get_admin_url() . 'edit.php?post_type=story&success_ciim=1' ); exit;
				} else {
					wp_redirect(  get_admin_url() . 'post.php?post=' . $post_id . '&action=edit' ); exit;
				}
			} 
		}
	}


    private function createPostType(){
    	$args = array(
	       'public' => true,  
	       'label'  => 'Stories',
	        'labels' => array( 
	            'edit_item' => 'Edit',  
	            'new_item' => 'Add story',  
	            'name_admin_bar' => 'Add story', 
	            'add_new' => 'Add story',   
	            'add_new_item' => 'Add story' 
	        ),  
	        'has_archive' => false,  
	        'show_ui' => true, 
	        'query_var' => true, 
	        'supports' => array( 
	                'title',
	                'editor',  
	                'excerpt', 
	                'custom-fields',
	                'comments',
	                'revisions',
	                'thumbnail',
	                'author', 
	                'page-attributes' 
	            )
	    );   
	    register_post_type( 'story', $args );
    }


}

add_action( 'init', array( 'CIIMImportExportStory', 'getInstance' ));