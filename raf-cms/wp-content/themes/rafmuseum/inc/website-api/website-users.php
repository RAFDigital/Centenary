<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WebsiteUsers
{
	private static $user_fields = array(
		'id' => 'ID',
		'name' => 'Name',
		'email' => 'Email',
		'role' => 'Role',
		'valid' => 'Valid',
		'created_at' => 'Registered',
		'updated_at' => 'Last modif.'
	);
	private static $instance = false;

	private function __construct()
	{
		add_action( 'admin_menu', array($this, 'webusers_menu'));
	} 
	
	public static function getInstance()
	{ 
		if(self::$instance === false){ 
			self::$instance = new WebsiteUsers;
		} 
		return self::$instance; 
	} 

	public static function getUserFields()
	{
		return self::$user_fields;
	}

	public function getUsers($order = 'name', $direction = 'asc', $page = 1, $per_page = 10){
		$web_api = WebsiteApi::getInstance(); 
 		
		$result = $web_api->rest->get("users", ['order' => $order, 'direction' => $direction, 'page' => $page]); //
		//pr($result); die();

		
		if($result->info->http_code == 200){
		    return $result->decode_response();
		} else {
			return array();
		}
	}

	public function getUser($user_id){
		$web_api = WebsiteApi::getInstance(); 
 
		$result = $web_api->rest->get("users/" . intval($user_id)); //
		
		if($result->info->http_code == 200){
		    return $result->decode_response();
		} else {
			return false;
		}
	}

	public function updateUser($user_id, $user_data){
		$web_api = WebsiteApi::getInstance(); 
 		
 		//pr($user_data); pr($user_id); die();

		$result = $web_api->rest->put("users/" . intval($user_id), $user_data); //
		//pr($result); die();

		if($result->info->http_code == 200){
		    return $result->decode_response();
		} else {
			return false;
		}
	}

	public function addUser($user_data){
		$web_api = WebsiteApi::getInstance(); 
 		
 		// pr($user_data); pr($user_id); die();

		$result = $web_api->rest->post("users", $user_data); //
		//pr($result); die();

		if($result->info->http_code == 200){
		    return $result->decode_response();
		} else {
			return false;
		}
	}

	public function deleteUser($user_id){
		$web_api = WebsiteApi::getInstance(); 
 		
 		// pr($user_data); pr($user_id); die(); 

		$result = $web_api->rest->delete("users/" . intval($user_id)); //
		//  pr($result->info); die();

		if($result->info->http_code == 200){
		    return true; 
		} else {
			return false;
		}
	}


	/*************************************************************/
	/********************** ADMIN PAGE ***************************/
	/*************************************************************/

	public function webusers_menu()
	{
		add_menu_page( 'Website Users Administration', 'Website Users', 'manage_options', 'raf-webusers', 'raf_web_users_admin', 'dashicons-groups' );
	}

}

add_action( 'init', array( 'WebsiteUsers', 'getInstance' ));

function raf_web_users_admin() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	$webusers_message = '';
	$webusers_message_type = 'update';

	$page = isset($_GET['wpage']) ? $_GET['wpage']: 'list';
	$websiteUsers = WebsiteUsers::getInstance();

	global $webusers_admin_page_url;
	$webusers_admin_page_url = get_admin_url(null, 'admin.php?page=raf-webusers');

	if(isset($_POST['edit_webuser']) && $_POST['user'] && $_POST['user']['id'] && $_POST['user']['email'] && $_POST['user']['name']){
		$user_update_id = $_POST['user']['id']; 
		unset($_POST['user']['id']);
		$user_update_data = $_POST['user'];
		$state = $websiteUsers->updateUser($user_update_id, $user_update_data);
		if($state){
			$webusers_message = 'Web user successfully changed.';
		} else {
			$webusers_message = 'Unable to change web user, please try again later.';
			$webusers_message_type = 'error';
		}
	} 
	

	if(isset($_POST['create_webuser']) && $_POST['user'] && $_POST['user']['email'] && $_POST['user']['name']){
		$user_create_data = $_POST['user'];
		$state = $websiteUsers->addUser($user_create_data);
		if($state){
			$webusers_message = 'Web user successfully created.';
		} else {
			$webusers_message = 'Unable to create web user, please try again later.';
			$webusers_message_type = 'error';
		}
	}

	if(isset($_GET['wudelete']) && $_GET['wudelete']){
		$state = $websiteUsers->deleteUser($_GET['wudelete']);
		if($state){
			$webusers_message = 'Web user successfully deleted.';
		} else {
			$webusers_message = 'Unable to delete web user, please try again later.';
			$webusers_message_type = 'error';
		}
	}

	if($webusers_message){
		RafHelper::showAdminMessage( __($webusers_message), $webusers_message_type );
	}

	?> 
	<div class="wrap">
		
		<?php
		switch ($page) {
			case 'edit': 
				include_once locate_template('/inc/website-api/admin-parts/webuser_edit.php');  
				break;
			case 'add-new': 
				include_once locate_template('/inc/website-api/admin-parts/webuser_new.php');  
				break;
			
			case 'list';
			default:
				include_once locate_template('/inc/website-api/admin-parts/webusers_list.php');  
				break;
		}		
		?>
	</div>
	<?php
}