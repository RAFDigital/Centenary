<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RafVocabularies {

	protected static $instance = null;
	
	public static $post_type = 'vocabulary';
	public static $taxonomy = 'vocabulary_dictionary';

	public static $plugin_slug = 'vocabulary-dictionary';
	public static $plugin_slug_safe = 'vocabulary_dictionary';

	const SYNONYMS_ERROR_IN_TITLE = 20;
	const SYNONYMS_ERROR_IN_SYNONYM = 21;

	public static function getInstance() {

		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	private function __construct() {
 
		$this->create_vocabulary_post_type();
		$this->create_vocabulary_taxonomy(); 

		add_action( 'save_post', array($this, 'vocabulary_save_custom_meta') ); // Save custom meta boxes
		
		add_filter( 'manage_' . self::$post_type. '_posts_columns', array( $this, 'manage_posts_columns' ) );
		add_action( 'manage_' . self::$post_type. '_posts_custom_column', array( $this, 'synonyms_column' ), 10, 2 );
		
		add_filter( 'redirect_post_location', array( $this, 'update_post_redirect' ) );

		add_action( 'admin_notices', array( $this, 'vocabulary_admin_notice' ) );
		
	}
	

	public function create_vocabulary_post_type() {

		$icon = 'dashicons-book';
		
		// Create posttype
		$labels = array(
			'name'                => _x( 'RAF Dictionaries', 'vocabulary' ),
			'singular_name'       => _x( 'RAF Dictionaries', 'vocabulary' ),
			'menu_name'           => _x( 'RAF Dictionaries', 'vocabulary' ),
			'name_admin_bar'      => _x( 'Term', 'add new on admin bar', 'vocabulary' ),
			'add_new'             => _x( 'Add New Term', 'vocabulary' ),
			'add_new_item'        => __( 'Add New Term', 'vocabulary' ),
			'edit_item'           => __( 'Edit Term', 'vocabulary' ),
			'new_item'            => __( 'New Term', 'vocabulary' ),
			'view_item'           => __( 'View Term', 'vocabulary' ),
			'search_items'        => __( 'Search Terms', 'vocabulary' ),
			'not_found'           => __( 'No Terms found', 'vocabulary' ),
			'not_found_in_trash'  => __( 'No Terms found in Trash', 'vocabulary' ),
			'parent_item_colon'   => __( 'Parent Term:', 'vocabulary' ),
		);
		
		$args = array(
			'labels'              => $labels,
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => null,
			'menu_icon'           => $icon,
			'show_in_nav_menus'   => false,
			'publicly_queryable'  => true,
			'exclude_from_search' => true,
			'has_archive'         => true,
			'query_var'           => true,
			'can_export'          => true,
			'rewrite'             => true,
			'capability_type'     => 'post',
			'register_meta_box_cb'=> array( $this, 'add_vocabulary_meta_boxes' ),
			'supports'            => array( 'title', 'editor' ),
		);

		register_post_type( self::$post_type, $args );
	}
	
	public function create_vocabulary_taxonomy() {
		
		// Add new taxonomy, make it hierarchical (like categories)
		$labels = array(
			'name'              => _x( 'Dictionaries', 'vocabulary' ),
			'singular_name'     => _x( 'Dictionary', 'vocabulary' ),
			'search_items'      => __( 'Search Dictionaries', 'vocabulary' ),
			'all_items'         => __( 'All Dictionaries', 'vocabulary' ),
			'parent_item'       => __( 'Parent Dictionary', 'vocabulary' ),
			'parent_item_colon' => __( 'Parent Dictionary:', 'vocabulary' ),
			'edit_item'         => __( 'Edit Dictionary', 'vocabulary' ),
			'update_item'       => __( 'Update Dictionary', 'vocabulary' ),
			'add_new_item'      => __( 'Add New Dictionary', 'vocabulary' ),
			'new_item_name'     => __( 'New Dictionary Name', 'vocabulary' ),
			'menu_name'         => __( 'Dictionaries', 'vocabulary' ),
		);
	
		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'vocabulary' ),
		);
	
		register_taxonomy( self::$taxonomy, array( self::$post_type ), $args );
		
		// see http://codex.wordpress.org/Function_Reference/register_taxonomy#Usage why we added this rule
		register_taxonomy_for_object_type( self::$taxonomy, self::$post_type );
	}

	public function add_vocabulary_meta_boxes() {
		add_meta_box( 
			self::$plugin_slug_safe . '_synonyms', 
			__( 'Synonyms', 'vocabulary' ), 
			array( $this, 'synonym_meta_box' ),
			self::$post_type, 
			'normal', 
			'default', 
			null
		);
	}

	public static function get_synonyms_post_meta( $post_id ) {
		return get_post_meta( $post_id, self::$plugin_slug_safe . '_synonyms', true );
	}

	private function get_synonyms_as_array( $synonyms ) {
		if( strpos( $synonyms, ',' ) > 0 ) {
			$synonyms = explode(',', $synonyms);
			$new_list = array();
			foreach($synonyms as $synonym) {
				$new_list[] = sanitize_text_field($synonym);	
			}
			$synonyms = $new_list;
		} else {
			$synonyms = array( sanitize_text_field($synonyms));
		}
		return $synonyms;
	}

	

	public function synonym_meta_box( $post ) {
		$synonyms = self::get_synonyms_post_meta( $post->ID );
		include_once locate_template('/inc/ciim/admin-parts/synonyms.php');
	}

	public function vocabulary_save_custom_meta( $post_id ) {

		if ( ! isset( $_POST ) || empty( $_POST ) || ! isset( $_POST['post_type'] ) ) {
			return $post_id;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
      		return $post_id;
		}

      	if ( self::$post_type != $_POST['post_type'] ) {
      		return $post_id;
      	}

      	if ( self::$post_type == $_POST['post_type'] ) {
      		if ( ! current_user_can( 'edit_post', $post_id ) )
        		return $post_id;
      	} else {
      		if ( ! current_user_can( 'edit_post', $post_id ) )
        		return $post_id;
      	}
      	
      	$term_synonyms = sanitize_text_field( $_POST['term-synonyms'] );
      	
      	if( empty( $term_synonyms ) ) {
      		delete_post_meta( $post_id, self::$plugin_slug_safe . '_synonyms' );
      		
      		return $post_id;
      	}
      	
      	if( strpos( $term_synonyms, ',' ) ) {
      		$synonyms = explode( ',', $term_synonyms );
      		foreach( $synonyms as $key => $value ) {
      			$synonyms[$key] = sanitize_text_field($value);
      		}
      	} else {
      		$synonyms = array( $term_synonyms );
      	}
      	// We also want to queue the current post because we need to check the title
      	$args = array(
      		'post_type' => self::$post_type,
      		'post_per_page' => -1,
      	);
      	
      	$posts = get_posts( $args );
      	
      	foreach( $posts as $single_post ) {
      		$meta = self::get_synonyms_post_meta( $single_post->ID );
      		$key = self::$plugin_slug_safe . '_synonyms';
      		$single_post->$key = $meta;
      		
      		if( $single_post->ID != $post_id) {
      			if( in_array( $single_post->post_title, $synonyms ) ) {
      				// The title exists in the synonyms so we can't update it
      				$_POST['synonyms_error'] = self::SYNONYMS_ERROR_IN_TITLE;
      				return false;
      			}
      			
      			// Check if the post has multipe synonyms
      			if( strpos( $single_post->$key, ',' ) ) {
		      		$single_post_synonyms = explode( $single_post->$key, ',' );
		      		foreach( $single_post_synonyms as $single_post_synonym ) {
		      			$single_post_synonym = trim($single_post_synonym);
		      			
		      			// Check if one of the synonyms is in the array of synonyms the user want's to add 
		      			if( in_array( $single_post_synonym, $synonyms ) ) {
	      					// The synonym exists in the synonyms so we can't update it
      						$_POST['synonyms_error'] = self::SYNONYMS_ERROR_IN_SYNONYM;
	      					return false;
	      				}
		      		}
      			} else {
      				
      				// Check if one of the synonyms is in the array of synonyms the user want's to add 
	      			if( in_array( $single_post->$key, $synonyms ) ) {
      					// The synonym exists in the synonyms so we can't update it
      					$_POST['synonyms_error'] = self::SYNONYMS_ERROR_IN_SYNONYM;
      					return false;
      				}
      			}
      			
      		} else {
      			// This is the current post and we only need to check the title
      			
      			if( in_array( $single_post->post_title, $synonyms ) ) {
      				// The title exists in the synonyms so we can't update it
      				$_POST['synonyms_error'] = self::SYNONYMS_ERROR_IN_TITLE;
      				return false;
      			}
      		}
      	}
      	
      	update_post_meta( $post_id, self::$plugin_slug_safe . '_synonyms', $term_synonyms );
   	}
   	
   	public function manage_posts_columns( $columns ) {
   		$insert_at = 2;
   		// With this contraption we can add the column anywhere we want
		return array_merge( array_slice( $columns, 0, $insert_at ), array( 'synonyms' => __( 'Synonyms', 'vocabulary' ) ), array_slice( $columns, $insert_at ) );
   	}
   	
	public function synonyms_column( $column_name, $post_id ) {
	    if ( 'synonyms' == $column_name) {
	        echo self::get_synonyms_post_meta( $post_id );
	    }
	}
	
	public function update_post_redirect( $location ){
	    if( isset( $_POST['synonyms_error'] ) ) {
	    	$location = add_query_arg( array( 'synonyms_error' => (int)$_POST['synonyms_error'] ), $location );
	    }
	    
	    return $location;
	}
	
	public function vocabulary_admin_notice() {
		if( isset ($_GET['synonyms_error'] ) ) {
			self::get_message( $_GET['synonyms_error'] );
		}
	}
	
	public static function get_post_type() {
		return self::$post_type;
	}

	public static function admin_message( $message = '', $status ) {
		switch($status){
			case 'update':
				echo '<div class="updated admin-message"><p>' . $message . '</p></div>';
				break;
			case 'error':
				echo '<div class="error"><p>' . $message . '</p></div>';
				break;
		}
	} 

	public static function get_message( $id ) {
		
		switch ( $id ) { 
			case self::SYNONYMS_ERROR_IN_TITLE:
				RafHelper::showAdminMessage( __( 'Error updating synonyms, one or more already exists as a term', 'vocabulary'), 'error' );
				break;
			case self::SYNONYMS_ERROR_IN_SYNONYM:
				RafHelper::showAdminMessage( __( 'Error updating synonyms, one or more already exists as another synonym', 'vocabulary'), 'error' );
				break;
		}
	}


	public function get_terms( $atts ) {
		global $wpdb;
				
		$has_single_dictionary = isset( $atts['dictionary'] );
		$single_dictionary = $has_single_dictionary ? $atts['dictionary'] : '';
		
		$shortcode_has_letter = isset( $atts['letter'] );
		$shortcode_letter = $shortcode_has_letter ? $atts['letter'] : '';
		 
		$args = array(
			'post_type' => self::$post_type,
			'posts_per_page' => -1,
			'order'=> 'ASC', 
			'orderby' => 'title'
		);
		
		// Add the shortcode dictionary to the arguments
		if( $has_single_dictionary ) {
			$args[self::$taxonomy] = $single_dictionary;
		}
		
		return get_posts( $args );
	}

	public function get_terms_as_array( $atts ) {
		$terms = $this->get_terms( $atts );
		$terms_array = array();

		if($terms && count($terms)){
			foreach ($terms as $term) {
				$term_title = get_the_title($term);
				$synonyms = $this->get_synonyms_post_meta($term->ID);
				$terms_array = array_merge($terms_array, array($term_title), $this->get_synonyms_as_array($synonyms));
			} 
		}

		$return = array();
		foreach ($terms_array as $key => $term) {
			$return[$term] = $term;
		}

		natsort($return);
		return $return;
	}

	public function importFromCSV(){
		$row = 0;

		$dir = get_home_path() . 'import-folder';
		@mkdir($dir, 0777);
		$files = array_diff(scandir($dir), array('..', '.'));

		if(count($files)){
			foreach ($files as $file) {
				$path = $dir . '/' . $file;
				if(strpos($file, '.csv')){
					if (($handle = fopen($path, "r")) !== FALSE) {
						$contents = file_get_contents($path);
						$del = ";";
						if(substr_count($contents, ',') > 5){
							$del = ",";   
						}
						$encoding = mb_detect_encoding($contents); 

						$tag_id = current(explode('.', $file));

					    while (($data = fgetcsv($handle, 1000, $del)) !== FALSE) {
					    	if(count($data) && $data[0]){
								$this->importTerm($data, $tag_id);
							}			        
					    }
					    fclose($handle);       
					    // unlink($file); 
					} 
				}
			}
			
		}
	}

	private function importTerm($data, $tag_id){
		$data = array_filter($data);
		$title = sanitize_text_field(array_shift($data));
		$term_synonyms = implode(',', $data);

		$post_id = wp_insert_post(array (
			'post_type' => self::$post_type,
			'post_title' => $title,
			'post_content' => '',
			'post_status' => 'publish',
			'post_author' => 1,
		));

		wp_set_post_terms( $post_id, $tag_id, self::$taxonomy );
		update_post_meta( $post_id, self::$plugin_slug_safe . '_synonyms', $term_synonyms );
		return $post_id; 
	}
}


add_action( 'init', array( 'RafVocabularies', 'getInstance' ));


if(isset($_GET['import_raf_vocabularies'])){
	add_action('wp_loaded', 'import_raf_vocabularies'); 
}   

function import_raf_vocabularies() {
	if ( !function_exists( 'get_home_path' )){
		require_once( dirname(__FILE__) . '/../../../../wp-admin/includes/file.php' );
	}
    RafVocabularies::getInstance()->importFromCSV(); 
    die('imported');
}