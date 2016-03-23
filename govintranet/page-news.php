<?php
/* Template name: News  */

get_header(); 


?>

<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>


			<div class="col-lg-7 col-md-8 col-sm-12 white">
				<div class="row">
					<div class='breadcrumbs'>
						<?php if(function_exists('bcn_display') && !is_front_page()) {
							bcn_display();
							}?>
					</div>
				</div>
			<?php
				$thistitle = get_the_title();
				echo "<h1>".$thistitle."</h1>";
				the_content();

				$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
				$counter = 0;	
				$cquery = array(
					'orderby' => 'post_date',
				    'order' => 'DESC',
				    'post_type' => 'news',
				    'posts_per_page' => 10,
				    'paged' => $paged												
					);
						
				$newspost = new WP_Query($cquery);
				global $k; 
				$k = 0;
				while ($newspost->have_posts()) : $newspost->the_post();
					get_template_part( 'loop', 'newstwitter' );
				endwhile;

					if (  $newspost->max_num_pages > 1 ) : ?>
			<?php if (function_exists('wp_pagenavi')) : ?>
				<?php wp_pagenavi(array('query' => $newspost)); ?>
				<?php else : ?>
				<?php next_posts_link(__('&larr; Older items','govintranet'), $newspost->max_num_pages); ?>
				<?php previous_posts_link(__('Newer items &rarr;','govintranet'), $newspost->max_num_pages); ?>						
			<?php endif; 
			wp_reset_query();								
							
        endif;
    ?>							
	</div>

	<div class="col-lg-4 col-lg-offset-1 col-md-4 col-sm-12">
	<?php

		$taxonomies=array();
		$post_type = array();
		$taxonomies[] = 'news-type';
		$post_type[] = 'news';
		$post_cat = get_terms_by_post_type( $taxonomies, $post_type);
		if ($post_cat){
			echo "<div class='widget-box'><h3 class='widget-title'>" . __('Categories' , 'govintranet') . "</h3>";
			echo "<p class='taglisting " . $post->post_type . "'>";
			foreach($post_cat as $cat){
				if ($cat->name){
					$newname = str_replace(" ", "&nbsp;", $cat->name );
					echo "<span><a  class='wptag t".$cat->term_id."' href='".get_term_link($cat->slug,'news-type')."'>".$newname."</a></span> ";
				}
			}
			echo "</p></div>";
		}
		
		//$tagcloud = my_colorful_tag_cloud('', 'news-type' , 'news');
		$tagcloud = gi_howto_tag_cloud('news');

		if ( $tagcloud != '' ) :   
	
			echo "<div class='widget-box'>";
			echo "<h3 class='widget-title'>".__('Search by tag','govintranet')."</h3>";
			echo "<div class='tagcloud'>";
			echo $tagcloud; 
			echo "</div>";
			echo "<br>";
			echo "</div>";					

		endif;

		
		if (is_active_sidebar('newslanding-widget-area')) dynamic_sidebar('newslanding-widget-area'); 
	?>

	</div>

<?php endwhile; ?>

<?php get_footer(); ?>