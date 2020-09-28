<?php if( ! get_option( 'related_entries_delete' ) == '記事下の関連記事を非表示にする' ): ?>
<?php if( ! get_post_meta( $post->ID, "related_off", true) == 'この記事で関連記事を表示しない' ) : ?>
<?php if( get_the_category() != false ): ?>
<?php
  $categories = get_the_category($post->ID);
	$category_ID = array();
  $category_ids = "";
  foreach($categories as $category):
		array_push( $category_ID, $category -> cat_ID);
  	$category_ids.=$category -> cat_ID.",";
  endforeach ;

	$tags = wp_get_post_tags($post->ID);
	$tag_ids = array();
	foreach($tags as $tag):
	  array_push( $tag_ids, $tag -> term_id);
	endforeach ;

  $cat_num = 4;
	$query_opt = array(
		'post__not_in' => array($post -> ID),
		'posts_per_page'=> is_related_num(),//カスタマイザで指定した値を取得
	);

	if(empty($tag_ids)){
		$query_opt += array('category__in' => $category_ID);
	}else{
		$query_opt += array('tag__in' => $tag_ids);

		add_filter('posts_fields', function( $fields ) {
			return $fields . ' ,count(*) as count';
		});

		add_filter( 'posts_orderby', function( $orderby, $query ) {
			return 'count DESC, ' . $orderby;
		}, 10, 2);

		add_filter( 'posts_where', function( $where = '' ) use ( $category_ids ){
			return str_replace(
				"term_taxonomy_id IN (",
				"term_taxonomy_id IN (".$category_ids,
				$where
			);
		});
	}
	$query = new WP_Query($query_opt);
	$cat_num = $query->post_count;
?>

<?php if( $query -> have_posts() ): ?>
<div class="toppost-list-box-simple">
<section class="related-entry-section toppost-list-box-inner">
	<?php if( ! is_related_title() == "" ) :?>
	<div class="related-entry-headline">
		<div class="related-entry-headline-text ef"><span class="fa-headline"><i class="jic jin-ifont-post" aria-hidden="true"></i><?php echo is_related_title(); ?></span></div>
	</div>
	<?php endif; ?>
	<?php if( is_related_type() == "basic1" ) : ?>
		<div class="post-list basicstyle">
		<?php while ($query -> have_posts()) : $query -> the_post(); ?>
			<?php get_template_part('include/liststyle/parts/post-list-parts'); ?>
		<?php endwhile;?>
		</div>
	<?php elseif( is_related_type() == "magazine1" ) : ?>
		<div class="post-list-mag">
		<?php while ($query -> have_posts()) : $query -> the_post(); ?>
			<?php get_template_part('include/liststyle/parts/post-list-mag-parts'); ?>
		<?php endwhile;?>
		</div>
	<?php elseif( is_related_type() == "magazine2" ) : ?>
		<div class="post-list-mag3col">
		<?php while ($query -> have_posts()) : $query -> the_post(); ?>
			<?php get_template_part('include/liststyle/parts/post-list-mag-parts'); ?>
		<?php endwhile;?>
		</div>
	<?php elseif( is_related_type() == "slide" ) : ?>
		<?php if( $cat_num < 4 ) : ?>
		<div class="post-list-mag3col">
		<?php while ($query -> have_posts()) : $query -> the_post(); ?>
			<?php get_template_part('include/liststyle/parts/post-list-mag-parts'); ?>
		<?php endwhile;?>
		</div>
		<?php else: ?>
		<div class="post-list-mag3col-slide related-slide">
			<div class="swiper-container2">
				<ul class="swiper-wrapper">
		<?php while ($query -> have_posts()) : $query -> the_post(); ?>
			<?php get_template_part('include/liststyle/parts/post-list-mag-parts-slide'); ?>
		<?php endwhile;?>
				</ul>
				<div class="swiper-pagination"></div>
				<div class="swiper-button-prev"></div>
				<div class="swiper-button-next"></div>
			</div>
		</div>
		<?php endif; ?>
	<?php endif; ?>
</section>
</div>
<div class="clearfix"></div>
<?php endif; wp_reset_postdata(); ?>
<?php endif; ?>


<?php if( ! get_post_meta( $post->ID, "custom_ad_off", true) == 'この記事で広告を表示しない' ) : ?>
	<?php if( ! get_option('ad_related_pc_left') == null || ! get_option('ad_related_pc_right') == null || ! get_option('ad_related_sp') == null ) : ?>
		<?php if ( is_mobile() ) :?>
			<?php if( ! get_option('ad_related_sp') == null ) : ?>
			<div class="related-ad-area">
				<div class="sponsor"><?php echo get_option('ad_setting_text'); ?></div>
				<section class="ad-single">
					<div class="sp-bottom-rectangle">
						<?php echo get_option('ad_related_sp'); ?>
					</div>
				</section>
			</div>
			<?php endif; ?>
		<?php else: ?>
			<?php if( ! get_option('ad_related_pc_left') == null && get_option('ad_related_pc_right') == null ) : ?>
			<div class="related-ad-area">
				<section class="ad-single">

					<div class="center-rectangle">
						<div class="sponsor-center"><?php echo get_option('ad_setting_text'); ?></div>
						<?php echo get_option('ad_related_pc_left'); ?>
					</div>
				</section>
			</div>
			<?php elseif( get_option('ad_related_pc_left') == null && ! get_option('ad_related_pc_right') == null ) : ?>
			<div class="related-ad-area">
				<section class="ad-single">

					<div class="center-rectangle">
						<div class="sponsor-center"><?php echo get_option('ad_setting_text'); ?></div>
						<?php echo get_option('ad_related_pc_right'); ?>
					</div>
				</section>
			</div>
			<?php elseif( ! get_option('ad_related_pc_left') == null && ! get_option('ad_related_pc_right') == null ) : ?>
			<div class="related-ad-area">
				<section class="ad-single">

					<div class="left-rectangle">
						<div class="sponsor-center"><?php echo get_option('ad_setting_text'); ?></div>
						<?php echo get_option('ad_related_pc_left'); ?>
					</div>
					<div class="right-rectangle">
						<div class="sponsor-center"><?php echo get_option('ad_setting_text'); ?></div>
						<?php echo get_option('ad_related_pc_right'); ?>
					</div>
					<div class="clearfix"></div>
				</section>
			</div>
			<?php endif; ?>
		<?php endif; ?>
	<?php endif; ?>
<?php endif; ?>
<?php endif; ?>
<?php endif; ?>
