<?php 


class RafHelper {

    protected static $instance = null;

    public static function getInstance() {

        if ( null == self::$instance ) {
            self::$instance = new self;
        }
        return self::$instance;
    }
 
    public static function showAdminMessage( $message = '', $status = 'update' ) {
        switch($status){
            case 'update':
                echo '<div class="updated admin-message"><p>' . $message . '</p></div>';
                break;
            case 'error':
                echo '<div class="error"><p>' . $message . '</p></div>';
                break;
        }
    }
}
add_action( 'init', array( 'RafHelper', 'getInstance' ));

function pr($val){
	echo '<pre>'; 
	var_dump($val); 
	echo '</pre>';  
}


function wmp_getLink($post, $url){
    $link = '#';
    if($url){
        $link = $url;
    } elseif (is_object($post) && $post->ID) {
        $link = get_permalink($post->ID);
    }

    return $link;
}

function wmp_getLinkTarget($url){
    $target = '';  
    if(strpos($url, $_SERVER['HTTP_HOST']) === false){
        $target = 'target="_blank"';
    } 

    return $target;
}

function get_the_post_thumbnail_data($intID = 0) {
    if($intID == 0) {
        return $intID;
    } 
    $img = get_the_post_thumbnail($intID); 
    if(strlen($img)){
        $objDom = new SimpleXMLElement($img);
        $arrDom = (array)$objDom;
        return (array)$arrDom['@attributes'];
    } else {
        return false;
    }
}
 

function wmp_excerpts($post, $excerpt_length = 50) { 

    $content = $post->post_excerpt;

    if($content){
        $content = apply_filters('the_excerpt', $content);
    } else { 
        $content = strip_tags($post->post_content, '<br><strong>'); 
        if(!count($content) < 5){
            $content = get_field('left_column', $post->ID);
        }
        $words = explode(' ', $content, $excerpt_length + 1);
 
        if(count($words) > $excerpt_length){
            array_pop($words); 
            array_push($words, '...');
            $content = implode(' ', $words);
        }

        $content = '<p>' . $content . '</p>';

    } 
 
    return $content; 
}

function wmp_text_cut($text, $length = 200, $dots = true) {
    $text = trim(preg_replace('#[\s\n\r\t]{2,}#', ' ', $text));
    $text_temp = $text;
    while (substr($text, $length, 1) != " ") { $length++; if ($length > strlen($text)) { break; } }
    $text = substr($text, 0, $length);
    return $text . ( ( $dots == true && $text != '' && strlen($text_temp) > $length ) ? ' &hellip;' : ''); 
}