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

function to_blog_card($the_content) {
	if ( is_singular() || is_category() || is_front_page() ) {


  $res = preg_match_all("/\[card name=.*\]/" , $the_content, $m);
		foreach ($m[0] as $match) {
			$temp = '';
      $temp = preg_replace("/^\[card name=/", "" , $match);
			$temp = preg_replace("/\]$/", "" , $temp);
			$temp = str_replace('"', '', $temp);
			$url = '/'.$temp.'/';
			$id = url_to_postid( $url );
			if ( ! $id ) continue;//IDを取得できない場合はループを飛ばす
				$post = get_post($id);
				$title = $post->post_title;
				if( ! get_post_meta($post->ID, 'post_desc',true) == null ){
					$excerpt = get_post_meta($post->ID, 'post_desc',true);
				}else{
					$excerpt = cps_excerpt($post->post_content,68);
				}
				$logo = esc_url( get_site_icon_url( 32 ) ) ;
				$sitetitle = get_bloginfo('name');
				$thumbnail = get_the_post_thumbnail($id, 'cps_thumbnails', array('class' => 'blog-card-thumb-image'));
				if ( !$thumbnail ) {
					$thumbnail = '<img src="'.get_template_directory_uri().'/img/noimg320.png" />';
				}

			$tag = '<a href="'.$url.'" class="blog-card"><div class="blog-card-hl-box"><i class="jic jin-ifont-post"></i><span class="blog-card-hl"></span></div><div class="blog-card-box"><div class="blog-card-thumbnail">'.$thumbnail.'</div><div class="blog-card-content"><span class="blog-card-title">'.$title.'</span><span class="blog-card-excerpt">'.$excerpt.'...</span></div></div></a>';

      $the_content = str_replace('<p>'.$match.'</p>', $tag , $the_content);

		}
	}
	return $the_content;
}

add_filter('the_content','to_blog_card');
function remove_parent_theme_hook_func(){
  remove_filter('the_content','url_to_blog_card');
}
add_action('after_setup_theme','remove_parent_theme_hook_func');

function jin_auto_desc_func_custom() {
	$post_id = get_the_ID();
	$post = get_post( $post_id );
	$auto_desc	= wp_html_excerpt( strip_shortcodes($post->post_content), 160, '' );
	$auto_desc  = preg_replace("/( |　)/", "", $auto_desc );

	return esc_attr(  $auto_desc  );
}

// 画像の添付ファイルページのリダイレクト
add_action( 'template_redirect', 'attachment404' );

function attachment404() {
  // attachmentページだった場合
  if ( is_attachment() ) {
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
  }
}

function my_add_noindex_attachment(){
    if (is_attachment()) {
        echo '<meta name="robots" content="noindex,follow" />';
    }
}
add_action('wp_head', 'my_add_noindex_attachment');

add_filter( 'post_thumbnail_html', 'remove_width_attribute', 10 );
add_filter( 'image_send_to_editor', 'remove_width_attribute', 10 );

function remove_width_attribute( $html ) {
$html = preg_replace( '/(width|height)="\d*"\s/', "", $html );
return $html;
}

// メディア追加時のwidth/height自動追加を削除
function my_remove_width_attribute( $options ) {
    if ( $options['tinymce'] ) {
        wp_enqueue_script( 'remove_width_attribute', get_stylesheet_directory_uri() . '/js/remove_width_attribute.js', array( 'jquery' ), '1.0.0', true);
    }
}
add_action( 'wp_enqueue_editor', 'my_remove_width_attribute', 10, 1 );

function multiple_tags() {
//複数タグのアーカイブでURLからスラッグを拾ってID・タグ名を取得。
$tagVar = get_query_var('tag');
if ( !empty($tagVar) ) {
    if ( strpos($tagVar, '+') || strpos($tagVar, ' ') )
        $separator = " + ";
    else if ( strpos($tagVar, ',') )
        $separator = " , ";
    $tagSlugs = $currentTerms = array();
    $tagSlugs = preg_split('(\+|,| )', $tagVar);
    foreach ($tagSlugs as $tagSlug)
        $currentTerms[] = get_term_by('slug', $tagSlug, 'post_tag');
}
//出力
if ( !empty($currentTerms) ) {
    $tagCount = count($currentTerms);
    $i = 0;
    foreach ($currentTerms as $currentTerm) {
        $currentTagName .= $currentTerm->name;
        $i++;
        if($i != $tagCount){
          $currentTagName .= $separator;
        }
    }
}
return $currentTagName;
}

// titleタグ変更　Cf. https://teratail.com/questions/168613
function change_document_title( $title ) {
  if ( is_tag() ) {
    $title = multiple_tags().'の記事';
  }
  return $title;
}

add_filter( 'pre_get_document_title', 'change_document_title' , 100);

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


function tinymce_custom($settings) {

    $invalid_style = array(
        'table' => 'width height',
        'th' => 'width height',
        'td' => 'width height'
    );
    $settings['invalid_styles'] = json_encode($invalid_style);
    $settings['table_resize_bars'] = false;
    $settings['object_resizing'] = "img";

    return $settings;
}
add_filter('tiny_mce_before_init', 'tinymce_custom', 0);

//wp_headに追加
function add_preload() {
  echo '<link rel="preload" href="/wp-content/themes/jin/font/jin-icons/fonts/jin-icons.ttf?c16tcv" as="font" type="font/ttf" crossorigin>'."\n";
}
add_action('wp_head', 'add_preload');

function set_width_height( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
  $img_attr = wp_get_attachment_image_src($post_thumbnail_id, $size);
  $html = str_replace('<img', '<img width="'.$img_attr[1].'" height="'.$img_attr[2].'"', $html);
  return $html;
};

add_filter( 'post_thumbnail_html', 'set_width_height', 99, 5 );

function dequeue_plugins_style() {
    wp_dequeue_style('wp-block-library');
}
add_action( 'wp_enqueue_scripts', 'dequeue_plugins_style', 9999);

function add_noscript_to_jin( $tag, $handle ) {
  if ( !in_array( $handle , [ 'theme-style', 'fontawesome-style','swiper-style', ], true ) ) {
      return $tag;
  }
  $tag = str_replace( '<link', '<noscript class="deferred-jin"><link', $tag );
  return str_replace( '/>', '/></noscript>', $tag );
}
add_filter( 'style_loader_tag', 'add_noscript_to_jin', 10, 2 );


function jin_script() {
  echo <<< EOM
<script>
 var loadDeferredStylesJin = function() {
   var addStylesNodes = document.getElementsByClassName("deferred-jin");
   var replacement = document.createElement("div");

   addStylesNodes = Array.prototype.slice.call(addStylesNodes);
   addStylesNodes.forEach(function(elm) {
     replacement.innerHTML += elm.textContent;
     elm.parentElement.removeChild(elm);
   });
   document.body.appendChild(replacement);
 };
 var raf = window.requestAnimationFrame || window.mozRequestAnimationFrame ||
     window.webkitRequestAnimationFrame || window.msRequestAnimationFrame;
 if (raf) raf(function() { window.setTimeout(loadDeferredStylesJin, 0); });
 else window.addEventListener('load', loadDeferredStylesJin);
</script>
EOM;
}

add_action( 'shutdown', 'jin_script' );

function output_inline_style() {
	wp_register_style( 'inline-jin', false );
	wp_enqueue_style( 'inline-jin' , 0);
	$svg = base64_encode(file_get_contents( get_stylesheet_directory_uri().'/loading.svg'));

	$css = "
    img, iframe {
      background-image: url('data:image/svg+xml;base64,".$svg."') !important;
      background-repeat: no-repeat !important;
      background-position: center !important;
      background-size:20% auto !important;
	  }";
	wp_add_inline_style( 'inline-jin', $css );
}
add_action( 'wp_enqueue_scripts', 'output_inline_style' );

add_action( 'kattene', 'kattene_prepare_lazyloading');

add_filter('the_content','prepare_lazyloading_to_balloon_icon');
function prepare_lazyloading_to_balloon_icon($the_content){
	return str_replace(
		'<div class="balloon-icon "><img src',
	  '<div class="balloon-icon "><img width="60" height="60" loading="lazy" src',
		$the_content);
}

?>
