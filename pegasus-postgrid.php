<?php 
/*
Plugin Name: Pegasus Post Grid Plugin
Plugin URI:  https://developer.wordpress.org/plugins/the-basics/
Description: This allows you to create post grids on your website with just a shortcode.
Version:     1.0
Author:      Jim O'Brien
Author URI:  https://visionquestdevelopment.com/
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wporg
Domain Path: /languages
*/

	/**
	 * Silence is golden; exit if accessed directly
	 */
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	function pegasus_post_grid_menu_item() {
		add_menu_page("Posts Grid", "Posts Grid", "manage_options", "pegasus_posts_plugin_options", "pegasus_posts_plugin_settings_page", null, 99);
	}
	add_action("admin_menu", "pegasus_post_grid_menu_item");
	
	
	function pegasus_posts_plugin_settings_page() { ?>
	    <div class="wrap pegasus-wrap">
	    <h1>Posts Grid</h1>
	    <?php /* ?>
		<form method="post" action="options.php">
	        <?php
	            settings_fields("section");
	            do_settings_sections("theme-options");      
	            submit_button(); 
	        ?>          
	    </form>
		<?php */ ?>
		<p>Usage: <pre>[loop the_query="showposts=100&post_type=page&post_parent=453" bkg_color="#dedede" ]</pre> </p>
		<p>Usage: <pre>[loop-posts the_query="post_type=post&showposts=100&ord=ASC&order_by=date" bkg_color="#dedede"]</pre> </p>
		<p>Usage: <pre>[loop-grid the_query="post_type=post&showposts=100&ord=ASC&order_by=date" bkg_color="#dedede" pagination="yes"]</pre> </p>
		</div>
	<?php
	}
	
	function pegasus_posts_plugin_styles() {
		
		wp_enqueue_style( 'post-grid-css', trailingslashit( plugin_dir_url( __FILE__ ) ) . 'css/postgrid.css', array(), null, 'all' );
		
	}
	add_action( 'wp_enqueue_scripts', 'pegasus_posts_plugin_styles' );
	
	/**
	* Proper way to enqueue JS 
	*/
	function pegasus_posts_grid_plugin_js() {
		
		wp_enqueue_script( 'match-height-js', trailingslashit( plugin_dir_url( __FILE__ ) ) . 'js/matchHeight.js', array( 'jquery' ), null, true );
		wp_enqueue_script( 'pegasus-posts-plugin-js', trailingslashit( plugin_dir_url( __FILE__ ) ) . 'js/plugin.js', array( 'jquery' ), null, true );
		
	} //end function
	add_action( 'wp_enqueue_scripts', 'pegasus_posts_grid_plugin_js' );
	
	
	/*~~~~~~~~~~~~~~~~~~~~
		POSTS LOOP - list style
	~~~~~~~~~~~~~~~~~~~~~*/
	// [loop the_query="showposts=100&post_type=page&post_parent=453" bkg_color="#dedede" ]	
	function loop_query_shortcode($atts) {

		$a = shortcode_atts( array(
			"bkg_color" => ''
		), $atts );
		
		// Defaults
		extract(shortcode_atts(array(
			"the_query" => ''
		), $atts));

		// de-funkify query
		$the_query = preg_replace('~&#x0*([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $the_query);
		$the_query = preg_replace('~&#0*([0-9]+);~e', 'chr(\\1)', $the_query);

		// query is made               
		query_posts($the_query);

		// Reset and setup variables
		$output = '';
		$temp_title = '';
		$temp_link = '';
		$temp_date = '';
		$temp_pic = '';
		$temp_content = '';
		$the_id = '';

		// the loop
		if (have_posts()) : while (have_posts()) : the_post();

			$temp_title = get_the_title($post->ID);
			$temp_link = get_permalink($post->ID);
			$temp_date = get_the_date($post->ID);
			$temp_pic = wp_get_attachment_url( get_post_thumbnail_id($post->ID) );
			$temp_excerpt = wp_trim_words( get_the_excerpt(), 150 );
			$temp_content = wp_trim_words( get_the_content(), 300 );
			$the_id = get_the_ID();
			

			// output all findings - CUSTOMIZE TO YOUR LIKING
			$color_chk = "{$a['bkg_color']}";
			if ($color_chk) { $output .= "<li style='background: {$a['bkg_color']}; '>"; }else{ $output .= "<li>"; }
			
				$output .= "<div class='post-$the_id'>";
				
					if($temp_pic) { 
						$output .= "<a href='$temp_link'>";
						$output .= "<img class='post-img-feat' src='$temp_pic'>"; 
						$output .= "</a>";
					} 
					
					$output .= "<a href='$temp_link'><h2>$temp_title</h2></a>";
					$output .= "<i>$temp_date</i>";
					$output .= "<br>";
					$output .= "<p class='post-content'>";
					
					
					if(isset($temp_excerpt)) {
						$temporary_excerpt = substr(strip_tags($temp_excerpt), 0, 150);
						if($temporary_excerpt){
								$output .= $temporary_excerpt; 
								$output .= '...';
						}
					}else{  
						$more = 0; 
						$temporary = substr(strip_tags($temp_content), 0, 300); 
						if($temporary){ $output .= $temporary; $output .= '...'; } 
					}
					$output .= "</p>";
					
					$output .= "<a class='read-more-link clearfix' href='$temp_link'>Read More</a>";

				$output .= "</div>";
			$output .= "</li>";
		endwhile; else:
			$output .= "nothing found.";
		endif;

		wp_reset_query();
		return '<ul class="post-listing">' . $output . '</ul>';
	   
	}
	add_shortcode("loop", "loop_query_shortcode");
	
	/*~~~~~~~~~~~~~~~~~~~~
		POSTS LOOP - left and right hand style
	~~~~~~~~~~~~~~~~~~~~~*/
	// [loop-posts the_query="post_type=post&showposts=100&ord=ASC&order_by=date" bkg_color="#dedede"]
	function blog_loop_query_shortcode($atts) {
		
		$a = shortcode_atts( array(
			"bkg_color" => ''
		), $atts );
		
		// Defaults
		extract(shortcode_atts(array(
			"the_query" => ''
		), $atts));

		// de-funkify query
		$the_query = preg_replace('~&#x0*([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $the_query);
		$the_query = preg_replace('~&#0*([0-9]+);~e', 'chr(\\1)', $the_query);

		// query is made               
		query_posts($the_query);

		// Reset and setup variables
		$output = '';
		$temp_title = '';
		$temp_link = '';
		$temp_date = '';
		$temp_pic = '';
		$temp_content = '';
		$the_id = '';

		// the loop
		if (have_posts()) : while (have_posts()) : the_post();

			$temp_title = get_the_title($post->ID);
			$temp_link = get_permalink($post->ID);
			$temp_date = get_the_date($post->ID);
			$temp_pic = wp_get_attachment_url( get_post_thumbnail_id($post->ID) );
			$temp_excerpt = wp_trim_words( get_the_excerpt(), 300 );
			$temp_content = wp_trim_words( get_the_content(), 500 );
			$the_id = get_the_ID();
			

			// output all findings - CUSTOMIZE TO YOUR LIKING
			$color_chk = "{$a['bkg_color']}";
			if ($color_chk) { $output .= "<li class='clearfix' style='background: {$a['bkg_color']}; '>"; }else{ $output .= "<li class='clearfix' >"; }
				$output .= "<article class='post-$the_id'>";
				
					$output .= "<ul class='blog-container clearfix'>";
						
						if($temp_pic) {
						$output .= "<li class='img-container clearfix'>";
							$output .= "<a href='$temp_link'>";
							$output .= "<img class='blog-img-feat' src='$temp_pic'>"; 
							$output .= "</a>";
						$output .= "</li>";
						}else{
							$output .= "<li class='no-img-container clearfix'></li>";
						}						
						
						$output .= "<li class='content-container clearfix'>";
							$output .= "<a href='$temp_link'><h2>$temp_title</h2></a>";
							$output .= "<i>$temp_date</i>";
							$output .= "<p class='post-content'>";
							if(isset($temp_excerpt)) {
								$temporary_excerpt = substr(strip_tags($temp_excerpt), 0, 300);
								if($temporary_excerpt){ $output .= $temporary_excerpt; $output .= '...'; }
							}else{  
								$more = 0; 
								$temporary = substr(strip_tags($temp_content), 0, 500); 
								if($temporary){ $output .= $temporary; $output .= '...'; } 
							}
							$output .= "</p>";
							$output .= "<a class='read-more-link clearfix' href='$temp_link'>Read More</a>";
						$output .= "</li>";
						
					$output .= "</ul>";
					
				$output .= "</article>";
			$output .= "</li>";
		endwhile; else:
			$output .= "nothing found.";
		endif;
		
		
		wp_reset_query();
		return '<ul class="post-listing">' . $output . '</ul>';
		
	}
	add_shortcode("loop-posts", "blog_loop_query_shortcode");
	
	
	/*~~~~~~~~~~~~~~~~~~~~
		POSTS LOOP - Grid style
	~~~~~~~~~~~~~~~~~~~~~*/
	// [loop-grid the_query="post_type=post&showposts=100&ord=ASC&order_by=date" bkg_color="#dedede" pagination="yes"]
	function loop_grid_query_shortcode($atts) {

		$a = shortcode_atts( array(
			"bkg_color" => '',
			"pagination" => ''
		), $atts );
		
		// Defaults
		extract(shortcode_atts(array(
			"the_query" => ''
			
		), $atts));

		// de-funkify query
		$the_query = preg_replace('~&#x0*([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $the_query);
		$the_query = preg_replace('~&#0*([0-9]+);~e', 'chr(\\1)', $the_query);

		// query is made               
		query_posts($the_query);

		// Reset and setup variables
		$output = '';
		$temp_title = '';
		$temp_link = '';
		$temp_date = '';
		$temp_pic = '';
		$temp_content = '';
		$the_id = '';

		// the loop
		if (have_posts()) : while (have_posts()) : the_post();

			$temp_title = get_the_title($post->ID);
			$temp_link = get_permalink($post->ID);
			$temp_date = get_the_date($post->ID);
			$temp_pic = wp_get_attachment_url( get_post_thumbnail_id($post->ID) );
			$temp_excerpt = wp_trim_words( get_the_excerpt(), 300 );
			$temp_content = wp_trim_words( get_the_content(), 500 );
			$the_id = get_the_ID();
			

			// output all findings - CUSTOMIZE TO YOUR LIKING
			$color_chk = "{$a['bkg_color']}";
			if ($color_chk) { $output .= "<li class='clearfix' style='background: {$a['bkg_color']}; '>"; }else{ $output .= "<li class='clearfix' >"; }
				$output .= "<article class='post-$the_id'>";
				
					$output .= "<div class='blog-container clearfix'>";
						
						if($temp_pic) {
						$output .= "<div class='img-container clearfix'>";
							$output .= "<a href='$temp_link'>";
							$output .= "<img class='blog-img-feat' src='$temp_pic'>"; 
							$output .= "</a>";
						$output .= "</div>";
						}else{
							$output .= "<div class='no-img-container clearfix'></div>";
						}						
						
						$output .= "<div class='content-container clearfix'>";
							$output .= "<a href='$temp_link'><h2>$temp_title</h2></a>";
							$output .= "<i>$temp_date</i>";
							$output .= "<p class='post-content'>";
							if(isset($temp_excerpt)) {
								$temporary_excerpt = substr(strip_tags($temp_excerpt), 0, 300);
								if($temporary_excerpt){ $output .= $temporary_excerpt; $output .= '...'; }
							}else{  
								$more = 0; 
								$temporary = substr(strip_tags($temp_content), 0, 500); 
								if($temporary){ $output .= $temporary; $output .= '...'; } 
							}
							$output .= "</p>";
							$output .= "<a class='read-more-link clearfix' href='$temp_link'>Read More</a>";
						$output .= "</div>";
						
					$output .= "</div>";
					 
				$output .= "</article>";
			$output .= "</li>";
		endwhile; else:
			$output .= "nothing found.";
		endif;
		
		
		wp_reset_query();
		return '<ul class="post-grid clearfix">' . $output . '</ul>';
	   
	}
	add_shortcode("loop-grid", "loop_grid_query_shortcode");
	
	