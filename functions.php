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

//wp_headに追加
function add_preload() {
  echo '<link rel="preload" href="/wp-content/themes/jin/font/jin-icons/fonts/jin-icons.ttf?c16tcv" as="font" type="font/ttf" crossorigin>'."\n";
}
add_action('wp_head', 'add_preload');

function dequeue_plugins_style() {
    wp_dequeue_style('wp-block-library');
}
add_action( 'wp_enqueue_scripts', 'dequeue_plugins_style', 9999);

if(!(is_admin())) {
  function add_noscript_to_jin( $tag, $handle ) {
    $targets = [ 'theme-style', 'fontawesome-style','swiper-style', 'crayon' ];

  	if (is_mobile() && (is_single() || is_page())){
  		array_unshift($targets, 'parent-style');
  	}

    if ( !in_array( $handle , $targets, true ) ) {
        return $tag;
    }
    $tag = str_replace( '<link', '<noscript class="deferred-jin"><link', $tag );
    return str_replace( '/>', '/></noscript>', $tag );
  }
  add_filter( 'style_loader_tag', 'add_noscript_to_jin', 10, 2 );

  function jin_script() {
		if (is_mobile() && (is_single() || is_page())){
			$target_id = "jin-inline-css";
		}else{
			$target_id = "parent-style-css";
		}
    echo <<< EOM
<script>
 var loadDeferredStylesJin = function() {
   var addStylesNodes = document.getElementsByClassName("deferred-jin");
   var target = document.getElementById("{$target_id}");
   var place = target.nextElementSibling;

   addStylesNodes = Array.prototype.slice.call(addStylesNodes);
   addStylesNodes.forEach(function(elm) {
		 var parent = document.createElement("div");
		 parent.innerHTML = elm.textContent;
		 place.insertAdjacentElement('beforebegin', parent.firstChild );
   });
	 addStylesNodes.forEach(function(elm) {elm.parentElement.removeChild(elm);});
 };
 var raf = window.requestAnimationFrame || window.mozRequestAnimationFrame ||
     window.webkitRequestAnimationFrame || window.msRequestAnimationFrame;
 if (raf) raf(function() { window.setTimeout(loadDeferredStylesJin, 0); });
 else window.addEventListener('load', loadDeferredStylesJin);
</script>
EOM;
  }
  add_action( 'shutdown', 'jin_script' );

	function remove_loading_img() {
		echo <<< EOM
<script>
var imgs = document.querySelectorAll( 'img' );
imgs.forEach(function(img) {
  if(img.complete){
		img.setAttribute('style', 'background-image: none !important');
  }else{
		img.onload = function() {
      img.setAttribute('style', 'background-image: none !important');
	  };
	}
});
</script>
EOM;
	}
	add_action( 'shutdown', 'remove_loading_img' );
}

function output_inline_style() {
	wp_register_style( 'jin', false );
	wp_enqueue_style( 'jin' , 0);
	$theme_color = get_theme_mod( 'theme_color', '#a9a9a9');
	$svg = file_get_contents( get_stylesheet_directory_uri().'/loading.svg');
	$svg = base64_encode(str_replace("#a9a9a9", $theme_color, $svg));

	$css = "
    img, iframe {
      background-image: url('data:image/svg+xml;base64,".$svg."') !important;
      background-repeat: no-repeat !important;
      background-position: center !important;
      background-size:30% auto !important;
	  }
		.adsbygoogle iframe{
      background-size:20% auto !important;
      background-position: 50% 20% !important;
    }
		";
	if (is_mobile() && (is_single() || is_page())){
    $css .= file_get_contents( get_stylesheet_directory_uri().'/inline.css');
		$css .= "
		.my-profile{
		  padding-bottom: 105px !important;
		}";
  }

	wp_add_inline_style( 'jin', $css );
}
add_action( 'wp_enqueue_scripts', 'output_inline_style', -99);

add_action( 'kattene', 'kattene_prepare_lazyloading');

function my_remove_enqueue_style() {
    wp_dequeue_style('swiper-style');
    wp_dequeue_script('cps-swiper');
    wp_dequeue_style('crayon');
}
add_action( 'wp_enqueue_scripts', 'my_remove_enqueue_style', 11);

function crayon_enqueue_styles() {
  wp_enqueue_style('crayon');
}
add_action( 'wp_enqueue_scripts', 'crayon_enqueue_styles', 12 );

function my_deregister_scripts(){
  wp_deregister_script( 'wp-embed' );
}
add_action( 'wp_footer', 'my_deregister_scripts' );

if(!(is_admin())) {
 function add_async_to_enqueue_script($url) {
  if(FALSE === strpos($url, '.js')) return $url;
  return "$url' defer charset='UTF-8";
 }
 add_filter('clean_url', 'add_async_to_enqueue_script', 11, 1);
}

remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
define( 'EWWW_IMAGE_OPTIMIZER_DISABLE_AUTOCONVERT', true );
