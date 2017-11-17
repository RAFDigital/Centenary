<?php 

if ( ! defined( 'WPINC' ) ) {
	die;
}


error_reporting( E_ALL ); 
ini_set("display_errors", 1); 

add_theme_support('html5') ;
add_theme_support('post-thumbnails') ; 

define('BASE_PATH', get_site_url() . '/');

define( 'DBI_AWS_ACCESS_KEY_ID', 'AKIAJX6CWBULXZ3755QA' );
define( 'DBI_AWS_SECRET_ACCESS_KEY', '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' );

// helpers 
include_once locate_template('/inc/helpers.php'); 

// menus
include_once locate_template('/inc/menus.php');   


function rafInclude($class_path)
{ 
    $class = __DIR__."/inc/{$class_path}.php"; 
    include_once($class);
};



/*************************************************************/
/************************* WEB API ***************************/
/*************************************************************/

// REST - WEB
define('RAF_REST_BASE_URL', 'http://rafs.code8.cz/public/api');
define('RAF_REST_FORMAT', 'json'); 
require locate_template('/lib/php-restclient-master/restclient.php'); // https://github.com/tcdent/php-restclient
rafInclude('website-api/website-api');
  
// web users
rafInclude('website-api/website-users');
  

// export to WEB
rafInclude('website-api/page-to-json');
rafInclude('website-api/menus-to-json');
rafInclude('website-api/website-import-export');


/*************************************************************/
/**************************** CIIM ***************************/
/*************************************************************/

// call api CIIM
rafInclude('ciim/CIIM_api');
rafInclude('ciim/elastic-search');

// import export - CIIM
rafInclude('ciim/ciim-import-export');
rafInclude('ciim/ciim-import-export-story');

// vocabularies - raf dictionary
rafInclude('ciim/vocabularies');

// post types
rafInclude('interviews');
rafInclude('stories');

 
