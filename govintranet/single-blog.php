<?php
/**
 * The Template for displaying all single blogposts.
 *
 * @package WordPress
 */

get_header(); ?>

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
				$video=null;
				//check if a video thumbnail exists, if so we won't use it to display as a headline image
				if (function_exists('get_video_thumbnail')){
					$video = get_video_thumbnail(); 
				}

				if (!$video){
				
					$ts = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'newshead' ); 
					$tt = get_the_title();
					$tn = "<img src='".$ts[0]."' width='".$ts[1]."' height='".$ts[2]."' class='img img-responsive' alt='".$tt."' />";
					if ($ts){
						echo $tn;
						echo wpautop( "<p class='news_date'>".get_post_thumbnail_caption()."</p>" );
					}
				}
				?>
				<h1><?php the_title(); ?></h1>
				<?php
				$article_date=get_the_date();
				$mainid=$post->ID;
				$article_date = date(get_option('date_format'),strtotime($article_date));	?>
				<?php echo the_date(get_option('date_format'), '<p class=news_date>', '</p>') ?>
				<?php the_content(); ?>
				<?php get_template_part("part", "downloads"); ?>			
				<?php
				if ('open' == $post->comment_status) {
					 comments_template( '', true ); 
				}
			 ?>

		</div> <!--end of first column-->
		<div class="col-lg-4 col-lg-offset-1 col-md-4 col-sm-12">
			<?php
            $user = get_userdata($post->post_author);
            
            echo "<div class='widget-box'><h3>" . __('Author' , 'govintranet') . "</h3><div class='well'><div class='media'>";
            
            $gis = "options_module_staff_directory";
			$forumsupport = get_option($gis); 
			if ($forumsupport){
                echo "<a class='pull-left' href='".site_url()."/staff/" . $user->user_nicename . "/'>";
            } else {
                echo "<a class='pull-left' href='".site_url()."/author/" . $user->user_nicename . "/'>";	                        
            }
			$user_info = get_userdata($post->post_author);
			$userurl = site_url().'/staff/'.$user_info->user_nicename;
			$displayname = get_user_meta($post->post_author ,'first_name',true )." ".get_user_meta($post->post_author ,'last_name',true );		
			$directorystyle = get_option('options_staff_directory_style'); // 0 = squares, 1 = circles
			$avstyle="";
			if ( $directorystyle==1 ) $avstyle = " img-circle";
			$image_url = get_avatar($post->post_author , 150, "", $user_info->display_name);
			$image_url = str_replace(" photo", " photo alignleft".$avstyle, $image_url);
			$image_url = str_replace('"150"', '"96"', $image_url);
			$image_url = str_replace("'150'", "'96'", $image_url);
            echo $image_url;
            echo "</a>";
            echo "<div class='media-body'><p class='media-heading'>";
            echo "<strong>".$user->display_name."</strong><br>";                        
            $jobtitle = get_user_meta($user->ID, 'user_job_title',true);
            $bio = get_user_meta($user->ID,'description',true);                        
			echo "<strong>".$jobtitle."</strong><br class='blog-staff-profile-link'>";
            if ($forumsupport){
                echo "<a class='blog-staff-profile-link' href='".site_url()."/staff/";
				echo $user->user_nicename . "/' title='{$user->display_name}'>Staff profile</a><br>";
            }
            echo "<a class='blog-author-link'  href='".site_url()."/author/";
			echo $user->user_nicename . "/' title='{$user->display_name}'>Blog posts</a><br class='blog-author-link'>";
			echo "</div></div></div></div>";
			
			get_template_part("part", "sidebar");

		 	dynamic_sidebar('blog-widget-area'); 

			get_template_part("part", "related");

			$post_cat = get_the_terms($post->ID,'blog-category');
			if ($post_cat){
				$html='';
				$catTitlePrinted=false;
				foreach($post_cat as $cat){
				if ( $cat->term_id > 0 ){
					if ( !$catTitlePrinted ){
						$catTitlePrinted = true;
					}
					$html.= "<span><a class='wptag t".$cat->term_id."' href='".get_term_link($cat->slug , 'blog-category') . "'>".str_replace(" ","&nbsp;",$cat->name)."</a></span> ";
					}
				}	
				if ( $html ){
					echo "<div class='widget-box'><h3>" . __('Categories' , 'govintranet') . "</h3>".$html."</div>";
				}
			}
		 	
			$posttags = get_the_tags();
			if ($posttags) {
				$foundtags=false;	
				$tagstr="";
			  	foreach($posttags as $tag) {
			  		if (substr($tag->name,0,9)!="carousel:"){
			  			$foundtags=true;
			  			$tagurl = $tag->term_id;
				    	$tagstr=$tagstr."<span><a class='label label-default' href='".get_tag_link($tagurl) . "?type=blog'>" . str_replace(' ', '&nbsp' , $tag->name) . '</a></span> '; 
			    	}
			  	}
			  	if ($foundtags){
				  	echo "<div class='widget-box'><h3>" . __('Tags','govintranet') . "</h3><p> "; 
				  	echo $tagstr;
				  	echo "</p></div>";
			  	}
			}
		 	
		//if we're looking at a blog post, show recently published 
			echo "<div class='widget-box nobottom'>";
			if ( $post_cat ):
				$blog_categories = array();
				foreach ( $post_cat as $cat){
					$blog_categories[] = $cat->term_id;
				}
				$recentitems = new WP_Query(array(
					'post_type'=>'blog',
					'posts_per_page'=>5,
					'post__not_in' => array($mainid),
					'tax_query' => array(array(
				    'taxonomy' => 'blog-category',
				    'field' => 'id',
				    'terms' => (array)$blog_categories,
				    'compare' => "IN",
					))
					));			
				$recent_title = __('Recent in this category' , 'govintranet');
				if ( !$recentitems->have_posts()):
					$recentitems = new WP_Query(array(
					'post_type'=>'blog',
					'posts_per_page'=>5,
					'post__not_in' => array($mainid),
					));	
					$recent_title = __('Recent in other categories' , 'govintranet');
				endif;
			else:
				$recentitems = new WP_Query(array(
				'post_type'=>'blog',
				'posts_per_page'=>5,
				'post__not_in' => array($mainid),
				));	
				$recent_title = __('Recent posts' , 'govintranet');
			endif;
			echo "<h3>" . $recent_title . "</h3>";
			if (!$recentitems->have_posts()){
				echo "<p>" . __('Nothing to show yet' , 'govintranet') . ".</p>";
			}
			if ( $recentitems->have_posts() ) while ( $recentitems->have_posts() ) : $recentitems->the_post(); 
				if ($mainid!=$post->ID) {
					$thistitle = get_the_title($id);
					$thisURL=get_permalink($id);
					echo "<div class='widgetnewsitem'>";
					$image_url = get_the_post_thumbnail($id, 'thumbnail', array('class' => 'alignright'));
					echo "<h3><a href='{$thisURL}'>".$thistitle."</a></h3>";
					$thisdate= $post->post_date;
					$thisdate=date(get_option('date_format'),strtotime($thisdate));
					echo "<span class='news_date'>".$thisdate."</span>&nbsp;";
					
					$user = get_userdata($post->post_author);
					$gis = "options_forum_support";
					$staffdirectory = get_option('options_module_staff_directory');
					$user_info = get_userdata($post->post_author);
					$displayname = get_user_meta($post->post_author ,'first_name',true )." ".get_user_meta($post->post_author ,'last_name',true );		

				   $forumsupport = get_option($gis);
				   if ($forumsupport){	
					   		$authorlink = "<a href='".site_url()."/author/" . $user->user_nicename . "/'>";
							if (function_exists('bp_activity_screen_index')){ // if using BuddyPress - link to the members page
								$authorlink = "<a href='".site_url()."/members/" . $user->user_nicename . "/'>";
								} 
							elseif (function_exists('bbp_get_displayed_user_field') && $staffdirectory ){ // if using bbPress - link to the staff page
								$authorlink = "<a href='".site_url()."/staff/" . $user->user_nicename . "/'>";
								}
							echo $authorlink;
							$user_info = get_userdata($post->post_author);
							$userurl = site_url().'/staff/'.$user_info->user_nicename;
							$displayname = get_user_meta($post->post_author ,'first_name',true )." ".get_user_meta($post->post_author ,'last_name',true );		
							$directorystyle = get_option('options_staff_directory_style'); // 0 = squares, 1 = circles
							$avstyle="";
							if ( $directorystyle==1 ) $avstyle = " img-circle";
							$image_url = get_avatar($post->post_author , 32);
							$image_url = str_replace(" photo", " photo ".$avstyle, $image_url);
							echo $image_url;
							echo "</a>&nbsp;";
							echo $authorlink;
							$auth = get_the_author();
							echo "<span class='listglyph'>".$auth."</span>";
							echo "</a> ";
		           } else {
		                echo " <a href='".site_url()."/author/" . $user->user_nicename . "/'>" . $user->display_name . "</a>";			   
				   }
		
					
					echo "<br>".get_the_excerpt()."<br><span class='news_date'><a class='more' href='{$thisURL}' title='{$thistitle}'>" . __('Read more' , 'govintranet') . "</a></span></div><div class='clearfix'></div><hr class='light' />";
				}
			endwhile; 
			echo "</div>";
			wp_reset_query();
				?>
		</div> <!--end of second column-->
			
<?php endwhile; // end of the loop. ?>

<?php get_footer(); ?>