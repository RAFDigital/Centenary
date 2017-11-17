<?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if(isset($_GET['elastic_search_interview'])){
	add_action('init', 'c8_search_interview');
	// header('Content-Type: text/html; charset=utf-8');
}  

if(isset($_GET['c8load']) && $_GET['c8load'] == 'interview'){
	add_action('init', 'c8_load_post_interview'); 
}  


function c8_load_post_interview(){
	$id = $_GET['interview_id'];
	$search = new ElasticSearch();
	$importExport = new RafImportExport();

	//$terms = $search->getTermsAsSelect(); pr($terms); die();


	$json = $search->getObjectById($id, 'interview'); 
	//pr($json); die();


	if($id){
		if($json->hits->total > 0 && $json->hits->hits[0]){
			$detail = $json->hits->hits[0];
			$post_id = $importExport->addInterviewPost($detail);
		}
	}	  
	if($post_id){
		wp_redirect(  get_admin_url() . 'post.php?post=' . $post_id . '&action=edit' );
	} else {
		wp_redirect(  get_admin_url() . 'edit.php?post_type=interview&err=notfound' );
	} 
	exit;
	
} 

function c8_search_interview(){
	$return = array(
		'results' => array(), 
		'html' => ''
	);
	if(strlen($_GET['elastic_search_interview'])){
		$search = new ElasticSearch();
	
		$json = $search->search($_GET['elastic_search_interview'], 'interview');
		
		$html = '';  
		$add_new_url = get_admin_url() . 'post-new.php?post_type=interview&c8load=interview&interview_id='; 


		//pr($json);

		if($json->hits->total > 0){ 
			$html .= '
				<table id="elastic_results">
				<tr>
					<th>Title</th>
					<th>Agents</th>
					<th>Type</th>
				</tr>
			';
			foreach($json->hits->hits as $hit){
				// var_dump($hit);
				$info = $hit->_source;
				$id = $info->admin->id;
				//$id = $hit->_id; 
				if(isset($info->title)){
					$name = $info->title[0];
					$name = $name->value;
				} else {
					$name = $info->summary_title;
				}

				$agents = array();
				if(isset($info->agents)){
					foreach($info->agents as $a){  
						$n = $a->name[0];
						$agents[] = $n->value;
					}
				}  
				
				$html .= '
					<tr>
						<td><a href="' . $add_new_url . $id . '" title="Edit">' . $name . '</a></td> 
						<td>' . implode(', ', $agents) . '</td> 
						<td>' . $info->type->base . '</td>
					</tr>
				';
			}  
			$html .= '
				</table>
			';
		} else {
			$html = '<h4>No matches</h4>';
		}

		$return = array();
		$return['results'] = $json; 
		$return['html'] = $html;
	}

	echo json_encode($return); 
	die();
}

// custon post types
function c8_interview() {
    
    $args = array(
       'public' => true,  
       'label'  => 'Interviews',
        'labels' => array( 
            'edit_item' => 'Edit',  
            'new_item' => 'Add interview',  
            'name_admin_bar' => 'Add interview', 
            'add_new' => 'Add interview',   
            'add_new_item' => 'Add interview' 
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
    register_post_type( 'interview', $args ); 

}  

add_action( 'init', 'c8_interview' );


add_filter( 'views_edit-interview', function( $views )
{
    ?>
	<div id="raf-elastic">
	<?php if(isset($_GET['success_ciim'])){ echo '<h3 style="color: #009819;">Expored to CIIM</h3>';}; ?>
	<table>
		<tr>
			<td><strong>Search database</strong></td>
			<td><input type="text" name="elastic" id="elastic_search_interview_input"></td>
			<td><input type="submit" id="elastic_search_interview" class="button action" value="Search interviews"></td>
		</tr>
	</table> 
	<div id="elastic_results"></div>
	</div>
	<?php

    return $views;
} ); 


function post_published_export(  $post_id ) {
	$post = get_post($post_id);
	
	if($post->post_status == 'publish'){

		if($post->post_type == 'interview'){

			$importExport = new CIIMImportExport();
			$CIIM = new CIIM();
			
			//list($json, $pre_flight) = $importExport->exportInterview($post);
			$json = $importExport->exportInterview($post);
			
			//echo $json; die(); 
			$status = $CIIM->export($json);// die(); 

			if($status){ 
				// remove post
				wp_delete_post($post_id, true);
				wp_redirect(  get_admin_url() . 'edit.php?post_type=interview&success_ciim=1' ); exit;

			} else {
				wp_redirect(  get_admin_url() . 'post.php?post=' . $post_id . '&action=edit' ); exit;
			}
		}
	}
}
add_action( 'acf/save_post','post_published_export', 200);