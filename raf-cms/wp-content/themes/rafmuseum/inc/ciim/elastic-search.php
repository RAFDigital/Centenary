<?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ElasticSearch
{

	protected static $instance = null;

    const USERNAME = 'raf';
    const PASSWORD = 'R4f!Mu$';
    const URL = 'http://172.16.0.41/es/_search';

    public static function getInstance() {

		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function search($search_term, $type){
		$result = $this->getSearchResults($search_term, $type); 
		return $this->parseJson($result);
	}

	public function getSearchResults($search_term, $type){

		$query = '{"query":{"bool":{"must":[
			{"term":{"type.base":"' . $type . '"}},
			{"query_string":{"default_field":"_all","query":"' . $search_term . '"}}],
			"must_not":[],
			"should":[]}},
			"from":0,
			"size":100,
			"sort":[],
			"aggs":{}}
		'; 
 
		return $this->getJson($query);
	}

	public function getObjectById($id, $type){

		$query = '
			{"query":{"bool":{"must":[{"term":{"type.base":"' . $type . '"}},
			{"term":{"admin.id":"' . $id . '"}}],"must_not":[],"should":[]}},"from":0,"size":10,"sort":[],"aggs":{}}
			 
		';  
 
		$result = $this->getJson($query);
		return $this->parseJson($result);
	}

	public function getInterviews(){
		$query = '
			{"query":{"bool":{"must":[],"must_not":[],"should":[{"match_all":{}}]}},"from":0,"size":5000,"sort":[],"aggs":{},"version":true}
		';  
		$result = $this->getJson($query, 'http://172.16.0.41/es/_search?q=type.base:interview');
		return $this->parseJson($result);
	}


	public function getTerms(){
		$query = '
			{"query":{"bool":{"must":[],"must_not":[],"should":[{"match_all":{}}]}},"from":0,"size":5000,"sort":[],"aggs":{},"version":true}
		';  
		$query = '';
		$result = $this->getJson($query, 'http://172.16.0.41/es/_search?q=type.base:term');
		return $this->parseJson($result);
	}

	public function getIterviewStories($interview_id){
		$query = '
			{"query":{"bool":{"must":[{"term":{"interviews.admin.uid.keyword":"' . $interview_id . '"}}],"must_not":[],"should":[]}},"from":0,"size":10,"sort":[],"aggs":{}}
		'; 
		$result = $this->getJson($query);
		return $this->parseJson($result);
	}

	public function getTermsAsSelect(){
		$terms = $this->getTerms();
		$select = array();
		if(isset($terms->hits)){
			foreach($terms->hits->hits as $hit){
				$select[$hit->_source->admin->id] = $hit->_source->summary_title;
			} 
		}
		return $select;
	}

	public function getInterviewsAsSelect(){
		$interviews = $this->getInterviews(); 
		$select = array();
		if(isset($interviews->hits)){
			foreach($interviews->hits->hits as $hit){
				$select[$hit->_source->admin->id] = $hit->_source->summary_title;
			} 
		}
		return $select;
	}

	

	private function getJson($query, $url = 'http://172.16.0.41/es/_search'){
		$process = curl_init($url);
		curl_setopt($process, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($process, CURLOPT_HEADER, 1);
		curl_setopt($process, CURLOPT_USERPWD, self::USERNAME . ":" . self::PASSWORD);
		curl_setopt($process, CURLOPT_TIMEOUT, 5); 
		curl_setopt($process, CURLOPT_POST, 1);
		curl_setopt($process, CURLOPT_POSTFIELDS, $query);
		
		curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($process, CURLINFO_HEADER_OUT,true); 

		$response = curl_exec($process);
		curl_close($process); 

		//pr($response); die();
		$start = stripos($response, "{"); 
		$body = substr($response,$start);
		
		return $body;
	}

	private function parseJson($result){
		$json = json_decode($result);

		// pr($json); die(); 
		return $json;
	}

	public function generateHtml($json){
		$html = '';
		$add_new_url = get_admin_url() . 'post-new.php?post_type=story'; 

		//pr($json);

		if($json->hits->total > 0){
			$html .= '
				<table id="elastic_results">
				<tr>
					<th>name</th>
					<th>created</th>
					<th>modified</th>
					<th>parent</th>
					<th>summary_title</th>
					<th>type</th>
				</tr>
			';
			foreach($json->hits->hits as $hit){
				// var_dump($hit);
				$info = $hit->_source;
				$name = $info->name[0];
				if(isset($info->parent)){
					$parent = $info->parent[0];
					$parent = $parent->summary_title;
				} else {
					$parent = '';
				}
				
				$html .= '
					<tr>
						<td><a href="' . $add_new_url . '" title="Edit">' . $name->value . '</a></td>
						<td>' . date('m/d/Y H:i', $info->admin->created) . '</td> 
						<td>' . date('m/d/Y H:i', $info->admin->modified) . '</td>
						<td>' . $parent . '</td>
						<td>' . $info->summary_title . '</td>
						<td>' . $info->type->base . '</td>
					</tr>
				';
			}  
			$html .= '
				</table>
			';
		}

		
		//echo $html; die();
		return $html;
	}
	
}

add_action( 'init', array( 'ElasticSearch', 'getInstance' ));


function c8_admin_scripts() {   
	wp_enqueue_script( 'c8_raf', get_template_directory_uri() . '/admin/wp_admin.js', array(), '1.0.0', true );
	wp_enqueue_style( 'c8_admin_css', get_template_directory_uri() . '/admin/wp_admin.css', array(), '1.0.0', false );
} 
add_action( 'admin_init', 'c8_admin_scripts' );    