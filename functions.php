<?php

add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );
function theme_enqueue_styles() {
	wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
}

add_editor_style('editor-style.css');

function my_mce4_options( $init ) {
    $custom_colors = '
        "e36b6d", "Color 1",
        "62cc99", "Color 2",
        "6b9de3", "Color 3",
        "7d6be3", "Color 4",
        "e3836b", "Color 5",
        "de81ce", "Color 6",
        "00aae7", "Color 7"
        ';
    $init['textcolor_map'] = '['.$custom_colors.']';
    $init['textcolor_rows'] = 1;
    return $init;
}
add_filter( 'tiny_mce_before_init', 'my_mce4_options' );

function comment_shortcode( $atts, $content = null ) {
    return null;
}
add_shortcode('comment', 'comment_shortcode');

function custom_excerpt_length($length){
	return 160;
}
add_filter('excerpt_length','custom_excerpt_length',999);

function jin_auto_desc_func_custom() {
	$post_id = get_the_ID();
	$post = get_post( $post_id );
	$auto_desc	= wp_html_excerpt( strip_shortcodes($post->post_content), 160, '' );
	$auto_desc  = preg_replace("/( |　)/", "", $auto_desc );

	return esc_attr(  $auto_desc  );
}

function custom_youtube_oembed($code){
  if(strpos($code, 'youtu.be') !== false || strpos($code, 'youtube.com') !== false){
    $html = preg_replace("@src=(['\"])?([^'\">\s]*)@", "src=$1$2&showinfo=0&rel=0", $code);
    $html = preg_replace('/ width="\d+"/', '', $html);
    $html = preg_replace('/ height="\d+"/', '', $html);
    $html = '<p class="video">' . $html . '</p>';

    return $html;
  }
  return $code;
}

add_filter('embed_handler_html', 'custom_youtube_oembed');
add_filter('embed_oembed_html', 'custom_youtube_oembed');

function remove_parent_theme_wrap_iframe_in_div_hook(){
remove_filter('the_content','wrap_iframe_in_div');
}
add_action('after_setup_theme','remove_parent_theme_wrap_iframe_in_div_hook');

add_action( 'kattene', 'kattene_prepare_lazyloading');

define( 'EWWW_IMAGE_OPTIMIZER_DISABLE_AUTOCONVERT', true );
