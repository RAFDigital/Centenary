<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RafKeywords extends RafSimpleValue
{
	private $existing_keywords = false;
	private $new_keywords = array();
	public function exportToJson($post_id, $value = false, $params = array()){
		if(!$value){
			$value = get_field($this->field['key'], $post_id);
		}

		$keywords = array();

		$values = array_values($value);

		// existing ones
		$selected_keywords = $values[0]; 
		if($selected_keywords && count($selected_keywords)){
			foreach($selected_keywords as $kw_id){
				$keyword = $this->getKeywordJson($kw_id);
				$keywords[] = $keyword;
			}
		}

		// new ones
		if(isset($values[1])){
			$additional_keywords = trim(strip_tags($values[1])); 

			if(strlen($additional_keywords)){
				$additional_keywords = explode("\n", $additional_keywords);
				if(count($additional_keywords) > 0){ 
					foreach ($additional_keywords as $kw_txt) {
						$kw_txt = sanitize_text_field($kw_txt);
						if(strlen($kw_txt)){ 
							// already exists
							if($admin_id = $this->keywordExists($kw_txt)){
								$keyword = $this->getKeywordJson($admin_id);
								// add to KW but not to new kw json
								$keywords[] = $keyword; 
								continue;
							}

							$k = new StdClass();
							$admin = new StdClass();
							$admin->id = RafAcfForm::generateAdminId($kw_txt, 'term'); // $kw_txt;
							$admin->created = time();
							$admin->modified = time();
							$k->admin = $admin;
							$n = new StdClass(); 
							$n->value = $kw_txt;
							$k->name = array($n);
							$k->summary_title = $kw_txt; // = 'rafs-' . $this->slugify($kw_txt);
							$t = new StdClass();
							$t->base = 'term';
							$k->type = $t;

							// add to new ones alrady added
							$this->new_keywords[$admin->id] = $kw_txt;

							$keyword = $this->getKeywordJson($admin->id);
							$keywords[] = $keyword;  
							$this->form->addJsonBranch($k);
						}
					}
					//echo (json_encode($keywords_json)); die();
				}
			}
		}


		//pr($keywords); pr($keywords_json); //	die();
		

		return $this->formatJsonValue($keywords, $params);
	}


    public function keywordExists($keyword){
    	$exists = false;
    	if($this->existing_keywords === false){
    		$this->existing_keywords = ElasticSearch::getInstance()->getTermsAsSelect();
    	}

    	// exist in already saved ones
    	$exists = array_search($keyword, $this->existing_keywords);

    	if(!$exists){
    		// exists in new ones?
    		$exists = array_search($keyword, $this->new_keywords);
    	}
    	return $exists;
    }


	private function getKeywordJson($id){
		$p_link = '@link';
		$keyword = new StdClass();
		$keyword->$p_link = new StdClass();
		//$keyword->$p_link->cascade = true;
		$keyword->$p_link->type = 'reference';

		$keyword->admin = new StdClass();
		$keyword->admin->id = $id; 
		$t = new StdClass();
		$t->base = 'term';
		$keyword->type = $t; 
		return $keyword;
	}

	public function importFromJson($params = array()){

	}
 
}