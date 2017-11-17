<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WebsiteImportExport
{
	protected static $instance = null;
	const ERROR_DURING_PUBISHING = 11;
	const SUCCESS_PUBLISH = 21;


	public static function getInstance() {
 
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	private function __construct() {
 	
 		// after save and ACF save
		add_action( 'acf/save_post', array($this, 'exportPageToWebsite'), 999, 2);

		//after post status change, bin enchant_dict_check(dict, word)
		add_action( 'transition_post_status', array($this, 'pageStatusChange'), 999, 3); 

		add_action('wp_update_nav_menu', array($this, 'exportMenusToWebsite'), 210); 

		add_filter( 'redirect_post_location', array( $this, 'updatePostRedirect' ) );
		add_action( 'admin_notices', array( $this, 'websiteAdminNotice' ) );

	}

	public static function getRelativeUrl($path)
	{
		return str_replace(BASE_PATH, '/', $path);
	}

	public function pageStatusChange($new_status, $old_status, $post){
		if($new_status == $old_status || ($old_status == 'new' && $new_status == 'publish')){
			// acf/save_post
		} else {
			$this->exportPageToWebsite($post->ID);
		}
	}


	public function exportPageToWebsite($post_id)
	{
		$post = get_post($post_id);
		if($post->post_type == 'page' || $post->post_type == 'post'){ 
			$pageToJson = new Page_To_Json($post); 
			$page_json = $pageToJson->getJsonObject(); 
			
 			$web_api = WebsiteApi::getInstance();   
			$result = $web_api->rest->post("posts", $page_json); //
		 	
		 	//pr($web_api);pr($result); echo $page_json; die();  die();
			if($result->info->http_code == 200){
			    // var_dump($result->decode_response());die(); 
			    $_POST['api_update'] = self::SUCCESS_PUBLISH; 
			} else {
				$_POST['api_error'] = self::ERROR_DURING_PUBISHING;
			}
 
			// echo $page_json; die(); 
		}
	}

	public function exportMenusToWebsite($nav_menu_selected_id, $menu_data = false) 
	{		
		if($menu_data === false){
			$menuToJson = new Menu_To_Json();
			$menu_json = $menuToJson->getJsonObject(); 
		}
		
		echo $menu_json; die();
	}	


	/****************************************************/
	/********************   NOTICES   *******************/
	/****************************************************/


	public function updatePostRedirect( $location ){
	    if( isset( $_POST['api_error'] ) ) {
	    	$location = add_query_arg( array( 'api_error' => (int)$_POST['api_error'] ), $location );
	    }

	    if( isset( $_POST['api_update'] ) ) {
	    	$location = add_query_arg( array( 'api_update' => (int)$_POST['api_update'] ), $location );
	    }
	    
	    return $location;
	}

	public function websiteAdminNotice() {
		if( isset ($_GET['api_error'] ) ) {
			self::get_message( $_GET['api_error']);
		}
		if( isset ($_GET['api_update'] ) ) {
			self::get_message( $_GET['api_update']);
		}
	}

	public static function get_message( $id ) {
		
		switch ( $id ) { 
			case self::ERROR_DURING_PUBISHING:
				RafHelper::showAdminMessage( __( 'Error updating website, please try again later.', 'api'), 'error' );
				break;
			case self::SUCCESS_PUBLISH:
				RafHelper::showAdminMessage( __( 'Page successfully published to the website.', 'api'), 'update' );
				break;
		}
	}
}

add_action( 'init', array( 'WebsiteImportExport', 'getInstance' ));