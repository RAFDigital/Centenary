<?php
class Menu_To_Json {

	protected $post_type;

	public function __construct() {
	}

	public function getJsonObject()
	{
		$info = new StdClass();
		// we need all menus
		$menus = array(); 

		foreach ( get_registered_nav_menus() as $menu_ident => $menu_name ) {
			$menus[$menu_ident] = $this->navMenu2Tree($menu_name);
		}

		
		return json_encode($menus);
	}

	function simplifyItems( $items )
	{
		$simple = array();
		if(count($items) && $items){
			foreach ($items as $post_object) {
				//pr($post_object); die();	
				$simplified = new StdClass();
				$simplified->ID = $post_object->ID;
				$simplified->title = $post_object->title; 
				$simplified->menu_item_parent = $post_object->menu_item_parent;
				$simplified->page_id = $post_object->object_id;
				$simplified->type = $post_object->type;
				$simplified->url = WebsiteImportExport::getRelativeUrl(get_permalink($post_object->object_id));
				$simplified->target = $post_object->target;
				$simplified->attr_title = $post_object->attr_title;
				$simplified->description = $post_object->description;
				$simplified->classes = $post_object->classes;
				$simplified->xfn = $post_object->xfn;

				$simple[] = $simplified;
			}
		} 
		return $simple;
	}

	function navMenu2Tree( $menu_ident )
	{
	    $items = $this->simplifyItems(wp_get_nav_menu_items( $menu_ident ));  

	    return (array) $items ? $this->buildTree( $items, 0 ) : null;
	}

	function buildTree( array &$elements, $parentId = 0 )
	{
	    $branch = array();
	    foreach ( $elements as &$element )
	    {
	        if ( $element->menu_item_parent == $parentId )
	        {
	            $children = $this->buildTree( $elements, $element->ID );
	            if ( $children )
	                $element->children = $children;

	            $branch[] = $element;
	            unset( $element );
	        }
	    }
	    return $branch;
	}
	
}

