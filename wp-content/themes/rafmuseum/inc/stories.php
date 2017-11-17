<?php 


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if(isset($_GET['elastic_search_story'])){
	add_action('init', 'c8_search_story'); 
}  
if(isset($_GET['c8load']) && $_GET['c8load'] == 'story'){
	add_action('init', 'c8_load_post_story'); 
}  

function c8_load_post_story(){
	$id = $_GET['story_id'];
	$search = new ElasticSearch();
	$importExport = new CIIMImportExportStory();

	// $terms = $search->getTermsAsSelect(); pr($terms); die();


	$json = $search->getObjectById($id, 'story'); 
	if($id){
		if($json->hits->total > 0 && $json->hits->hits[0]){
			$detail = $json->hits->hits[0]; 
			$post_id = $importExport->importFromJson($detail);
		}
	}	 
	if($post_id){
		wp_redirect(  get_admin_url() . 'post.php?post=' . $post_id . '&action=edit' );
	} else {
		wp_redirect(  get_admin_url() . 'edit.php?post_type=story&err=notfound' );
	} 
	exit;
	
} 

function c8_search_story(){
	$return = array(
		'results' => array(),
		'html' => ''
	);
	if(strlen($_GET['elastic_search_story'])){
		$search = new ElasticSearch();
		$json = $search->search($_GET['elastic_search_story'], 'story');
		
		$html = ''; 
		$add_new_url = get_admin_url() . 'post-new.php?post_type=story&c8load=story&story_id='; 

		if($json->hits->total > 0){
			$html .= '
				<table id="elastic_results">
				<tr>
					<th>Title</th>
					<th>Event</th>
					<th>Agents</th>
					<th>Type</th>
				</tr>
			';
			foreach($json->hits->hits as $hit){
				
				$info = $hit->_source;
				$id = $info->admin->id;
				if(isset($info->title)){
					$name = $info->title[0];
					$name = $name->value;
				} else {
					$name = $info->summary_title;
				}
				$event_name = '';
				if(isset($info->events)){
					$event = $info->events[0];
					$event_name = $event->event[0];
					$event_name = $event_name->summary_title;
				}

				$agents = array();
				if(isset($info->events) && $info->events[0]){
					foreach($event->agents as $a){
						$n = $a->name[0];
						$agents[] = $n->value;
					}
				}
				
				$html .= '
					<tr>
						<td><a href="' . $add_new_url . $id . '" title="Edit">' . $name . '</a></td> 
						<td>' . $event_name . '</td> 
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
 

add_filter( 'views_edit-story', function( $views )
{
    ?> 
	<div id="raf-elastic"> 
	<?php if(isset($_GET['success_ciim'])){ echo '<h3 style="color: #009819;">Expored to CIIM</h3>';}; ?>
	<table>
		<tr>
			<td><strong>Search database</strong></td>
			<td><input type="text" name="elastic" id="elastic_search_story_input"></td>
			<td><input type="submit" id="elastic_search_story" class="button action" value="Search stories"></td>
		</tr>
	</table> 
	<div id="elastic_results"></div>
	</div>
	<?php

    return $views;
} ); 

function acf_load_interviews_field_choices( $field ) {
    
	$search = new ElasticSearch();
    $field['choices'] = $search->getInterviewsAsSelect(); 
	
    // return the field
    return $field;
}

add_filter('acf/load_field/name=parent', 'acf_load_interviews_field_choices');


function acf_load_keywords_field_choices( $field ) {
    
    // $terms = RafVocabularies::getInstance()->get_terms_as_array(array()); 

    $field['choices'] = ElasticSearch::getInstance()->getTermsAsSelect(); 
	
	//$field['choices'] = $terms;
    // return the field
    return $field;
    
}

add_filter('acf/load_field/name=keywords', 'acf_load_keywords_field_choices');

function acf_load_coverage_field_choices( $field ) {
    $field['choices'] = array();
    $years = range(1900, date('Y')); 
	foreach($years as $y){ 
		$field['choices'][$y] = $y;
	}
    // return the field
    return $field;
    
}

add_filter('acf/load_field/name=coverage_earliest', 'acf_load_coverage_field_choices');
add_filter('acf/load_field/name=coverage_latest', 'acf_load_coverage_field_choices');

