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

	function pegasus_postgrid_admin_table_css() {
		if ( postgrid_check_main_theme_name() == 'Pegasus' || postgrid_check_main_theme_name() == 'Pegasus Child' ) {
			//do nothing
		} else {
			//wp_register_style('postgrid-admin-table-css', trailingslashit(plugin_dir_url(__FILE__)) . 'css/pegasus-postgrid-admin-table.css', array(), null, 'all');
			ob_start();
			?>
				pre {
					background-color: #f9f9f9;
					border: 1px solid #aaa;
					page-break-inside: avoid;
					font-family: monospace;
					font-size: 15px;
					line-height: 1.6;
					margin-bottom: 1.6em;
					max-width: 100%;
					overflow: auto;
					padding: 1em 1.5em;
					display: block;
					word-wrap: break-word;
				}
				input[type="text"].code {
					width: 100%;
				}
				table.pegasus-table {
					width: 100%;
					border-collapse: collapse;
					border-color: #777 !important;
				}
				table.pegasus-table th {
					background-color: #f1f1f1;
					text-align: left;
				}
				table.pegasus-table th,
				table.pegasus-table td {
					border: 1px solid #ddd;
					padding: 8px;
				}
				table.pegasus-table tr:nth-child(even) {
					background-color: #f2f2f2;
				}
				table.pegasus-table thead tr { background-color: #282828; }
				table.pegasus-table thead tr td { padding: 10px; }
				table.pegasus-table thead tr td strong { color: white; }
				table.pegasus-table tbody tr:nth-child(0) { background-color: #cccccc; }
				table.pegasus-table tbody tr td { padding: 10px; }
				table.pegasus-table code { color: #d63384; }

			<?php
			// Get the buffered content
			$inline_css = ob_get_clean();

			wp_register_style('postgrid-admin-table-css', false);
			wp_enqueue_style('postgrid-admin-table-css');

			wp_add_inline_style('postgrid-admin-table-css', $inline_css);
		}
	}

	add_action('admin_enqueue_scripts', 'pegasus_postgrid_admin_table_css');

	function postgrid_check_main_theme_name() {
		$current_theme_slug = get_option('stylesheet'); // Slug of the current theme (child theme if used)
		$parent_theme_slug = get_option('template');    // Slug of the parent theme (if a child theme is used)

		//error_log( "current theme slug: " . $current_theme_slug );
		//error_log( "parent theme slug: " . $parent_theme_slug );

		if ( $current_theme_slug == 'pegasus' ) {
			return 'Pegasus';
		} elseif ( $current_theme_slug == 'pegasus-child' ) {
			return 'Pegasus Child';
		} else {
			return 'Not Pegasus';
		}
	}

	function pegasus_postgrid_menu_item() {
		if ( postgrid_check_main_theme_name() == 'Pegasus' || postgrid_check_main_theme_name() == 'Pegasus Child' ) {
			//do nothing
		} else {
			//echo 'This is NOT the Pegasus theme';
			add_menu_page(
				"Post Grid", // Page title
				"Post Grid", // Menu title
				"manage_options", // Capability
				"pegasus_postgrid_plugin_options", // Menu slug
				"pegasus_postgrid_plugin_settings_page", // Callback function
				null, // Icon
				90 // Position in menu
			);
		}
	}
	add_action("admin_menu", "pegasus_postgrid_menu_item");

	function pegasus_postgrid_plugin_settings_page() { ?>
		<div class="wrap pegasus-wrap">
			<h1>Post Grid Usage</h1>

			<div>
				<h3>Loop Usage 1:</h3>

				<pre >[loop the_query="post_type=post&showposts=100" bkg_color="#dedede" ]</pre>

				<input
					type="text"
					readonly
					value="<?php echo esc_html('[loop the_query="post_type=post&showposts=100" bkg_color="#dedede" ]'); ?>"
					class="regular-text code"
					id="my-shortcode"
					onClick="this.select();"
				>
			</div>

			<div>
				<h3>Loop Posts Usage 1:</h3>

				<pre >[loop-posts the_query="post_type=post&showposts=100&ord=ASC&order_by=date" bkg_color="#dedede"]</pre>

				<input
					type="text"
					readonly
					value="<?php echo esc_html('[loop-posts the_query="post_type=post&showposts=100&ord=ASC&order_by=date" bkg_color="#dedede"]'); ?>"
					class="regular-text code"
					id="my-shortcode"
					onClick="this.select();"
				>
			</div>

			<div>
				<h3>Loop Grid Usage 1:</h3>

				<pre >[loop-grid the_query="post_type=post&showposts=100&ord=ASC&order_by=date" bkg_color="#dedede" pagination="yes"]</pre>

				<input
					type="text"
					readonly
					value="<?php echo esc_html('[loop-grid the_query="post_type=post&showposts=100&ord=ASC&order_by=date" bkg_color="#dedede" pagination="yes"]'); ?>"
					class="regular-text code"
					id="my-shortcode"
					onClick="this.select();"
				>
			</div>

			<p style="color:red;">MAKE SURE YOU DO NOT HAVE ANY RETURNS OR <?php echo htmlspecialchars('<br>'); ?>'s IN YOUR SHORTCODES, OTHERWISE IT WILL NOT WORK CORRECTLY</p>

			<div>
				<?php echo pegasus_postgrid_settings_table(); ?>
			</div>
		</div>
	<?php
	}

	function pegasus_postgrid_settings_table() {

		$data = json_decode( file_get_contents( plugin_dir_path( __FILE__ ) . 'settings.json' ), true );

		if (json_last_error() !== JSON_ERROR_NONE) {
			return '<p style="color: red;">Error: Invalid JSON provided.</p>';
		}

		// Start building the HTML
		$html = '<table border="0" cellpadding="1" class="table pegasus-table" align="left">
		<thead>
		<tr style="background-color: #282828;">
		<td <span><strong>Name</strong></span></td>
		<td <span><strong>Attribute</strong></span></td>
		<td <span><strong>Options</strong></span></td>
		<td <span><strong>Description</strong></span></td>
		<td <span><strong>Example</strong></span></td>
		</tr>
		</thead>
		<tbody>';

		// Iterate over the data to populate rows
		if (!empty($data['rows'])) {
			foreach ($data['rows'] as $section) {
				foreach ($section as $key => $settings) {
					if ($key !== 'section_name') {
						// Add group header
						$html .= '<tr>';
						$html .= '<td colspan="5">';
						$html .= '<span>';
						$html .= '<strong>' . htmlspecialchars(ucwords(str_replace('_', ' ', $key))) . '</strong>';
						$html .= '</span>';
						$html .= '</td>';
						$html .= '</tr>';

						// Add rows in the group
						foreach ($settings as $row) {
							$html .= '<tr>
								<td>' . htmlspecialchars($row['name']) . '</td>
								<td>' . htmlspecialchars($row['attribute']) . '</td>
								<td>' . nl2br(htmlspecialchars($row['options'])) . '</td>
								<td>' . nl2br(htmlspecialchars($row['description'])) . '</td>
								<td><code>' . htmlspecialchars($row['example']) . '</code></td>
							</tr>';
						}
					}
				} //end foreach
			}
		}

		$html .= '</tbody></table>';

		// Return the generated HTML
		return $html;
	}

	/*
	function pegasus_posts_plugin_settings_page() { ?>
	    <div class="wrap pegasus-wrap">
	    <h1>Posts Grid</h1>

		<form method="post" action="options.php">
	        <?php
	            settings_fields("section");
	            do_settings_sections("theme-options");
	            submit_button();
	        ?>
	    </form>

		<p>Usage: <pre>[loop the_query="showposts=100&post_type=page&post_parent=453" bkg_color="#dedede" ]</pre> </p>
		<p>Usage: <pre>[loop-posts the_query="post_type=post&showposts=100&ord=ASC&order_by=date" bkg_color="#dedede"]</pre> </p>
		<p>Usage: <pre>[loop-grid the_query="post_type=post&showposts=100&ord=ASC&order_by=date" bkg_color="#dedede" pagination="yes"]</pre> </p>
		</div>
	<?php
	}
	*/
	function pegasus_posts_plugin_styles() {

		wp_register_style( 'post-grid-css', trailingslashit( plugin_dir_url( __FILE__ ) ) . 'css/postgrid.css', array(), null, 'all' );

	}
	add_action( 'wp_enqueue_scripts', 'pegasus_posts_plugin_styles' );

	/**
	* Proper way to enqueue JS
	*/
	function pegasus_posts_grid_plugin_js() {

		wp_register_script( 'match-height-js', trailingslashit( plugin_dir_url( __FILE__ ) ) . 'js/matchHeight.js', array( 'jquery' ), null, 'all' );
		wp_register_script( 'pegasus-posts-plugin-js', trailingslashit( plugin_dir_url( __FILE__ ) ) . 'js/plugin.js', array( 'jquery' ), null, 'all' );

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
		//$the_query = preg_replace('~&#x0*([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $the_query);
		//$the_query = preg_replace('~&#0*([0-9]+);~e', 'chr(\\1)', $the_query);

		$the_query = preg_replace_callback('~&#x0*([0-9a-f]+);~', function($matches){
			return chr( dechex( $matches[1] ) );
		}, $the_query);

		$the_query = preg_replace_callback('~&#0*([0-9]+);~', function($matches){
			return chr( $matches[1] );
		}, $the_query);


		$query_args = array(
			'post_type' => 'post', // Ensure you are querying the correct post type
			'posts_per_page' => -1, // Set the number of posts to retrieve
			//'post_status' => 'publish', // Ensure only published posts are retrieved
			//'category_name' => 'your-category-slug', // Optional: Filter by category
			//'orderby' => 'date', // Optional: Order by date
			//'order' => 'DESC' // Optional: Order descending
		);

		// echo '<pre>';
		// var_dump( $the_query );
		// echo '</pre>';
		// echo '<pre>';
		// var_dump( $query_args );
		// echo '</pre>';
		// Convert query string into array for WP_Query
		parse_str( $the_query, $query_args );
		// echo '<pre>';
		// var_dump( $query_args );
		// echo '</pre>';
		// Create a new WP_Query instance
		$query = new WP_Query( $query_args );

		// query is made
		//query_posts($the_query);

		// Reset and setup variables
		$output = '';
		$temp_title = '';
		$temp_link = '';
		$temp_date = '';
		$temp_pic = '';
		$temp_excerpt = '';
		$temp_content = '';
		$the_id = '';

		global $post;

		// the loop
		if ($query->have_posts()) {
			while ($query->have_posts()) {
				$query->the_post();

				$the_id = get_the_ID();
				$temp_title = get_the_title($post->ID);
				$temp_link = get_permalink($post->ID);
				$temp_date = get_the_date($post->ID);
				$temp_pic = wp_get_attachment_url( get_post_thumbnail_id($post->ID) );
				$post_thumb = has_post_thumbnail() ? get_the_post_thumbnail_url($the_id, 'medium') : plugin_dir_url(__FILE__) . '/images/not-available.png';
				$temp_excerpt = wp_trim_words( get_the_excerpt(), 150 );
				$temp_content = wp_trim_words( get_the_content(), 300 );



				// output all findings - CUSTOMIZE TO YOUR LIKING
				$color_chk = "{$a['bkg_color']}";

				if ($color_chk) {
					$output .= "<li style='background: {$a['bkg_color']}; '>";
				} else {
					$output .= "<li>";
				}

					$output .= "<div class='post-$the_id'>";

						if($temp_pic) {
							$output .= "<a href='$temp_link'>";
							$output .= "<img class='post-img-feat img-fluid' src='$temp_pic'>";
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

						$output .= "<a class='read-more-link ' href='$temp_link'>Read More</a>";

					$output .= "</div>";
				$output .= "</li>";
			}//end while
			wp_reset_postdata();
		} else {
			echo '<p>No posts found.</p>';
		}

		//wp_reset_postdata();
		wp_reset_query();

		wp_enqueue_style( 'post-grid-css' );
		wp_enqueue_script( 'match-height-js' );
		wp_enqueue_script( 'pegasus-posts-plugin-js' );

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
		//$the_query = preg_replace('~&#x0*([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $the_query);
		//$the_query = preg_replace('~&#0*([0-9]+);~e', 'chr(\\1)', $the_query);

		$the_query = preg_replace_callback('~&#x0*([0-9a-f]+);~', function($matches){
			return chr( dechex( $matches[1] ) );
		}, $the_query);

		$the_query = preg_replace_callback('~&#0*([0-9]+);~', function($matches){
			return chr( $matches[1] );
		}, $the_query);

		$query_args = array(
			'post_type' => 'post', // Ensure you are querying the correct post type
			'posts_per_page' => -1, // Set the number of posts to retrieve
			//'post_status' => 'publish', // Ensure only published posts are retrieved
			//'category_name' => 'your-category-slug', // Optional: Filter by category
			//'orderby' => 'date', // Optional: Order by date
			//'order' => 'DESC' // Optional: Order descending
		);

		// echo '<pre>';
		// var_dump( $the_query );
		// echo '</pre>';
		// echo '<pre>';
		// var_dump( $query_args );
		// echo '</pre>';
		// Convert query string into array for WP_Query
		parse_str( $the_query, $query_args );
		// echo '<pre>';
		// var_dump( $query_args );
		// echo '</pre>';
		// Create a new WP_Query instance
		$query = new WP_Query( $query_args );

		// query is made
		//query_posts($the_query);

		// Reset and setup variables
		$output = '';
		$temp_title = '';
		$temp_link = '';
		$temp_date = '';
		$temp_pic = '';
		$temp_content = '';
		$the_id = '';

		global $post;

		// the loop
		if ($query->have_posts()) {
			while ($query->have_posts()) {
				$query->the_post();

				$the_id = get_the_ID();
				$temp_title = get_the_title($post->ID);
				$temp_link = get_permalink($post->ID);
				$temp_date = get_the_date($post->ID);
				$temp_pic = wp_get_attachment_url( get_post_thumbnail_id($post->ID) );
				$post_thumb = has_post_thumbnail() ? get_the_post_thumbnail_url($the_id, 'medium') : plugin_dir_url(__FILE__) . '/images/not-available.png';
				$temp_excerpt = wp_trim_words( get_the_excerpt(), 150 );
				$temp_content = wp_trim_words( get_the_content(), 300 );



				// output all findings - CUSTOMIZE TO YOUR LIKING
				$color_chk = "{$a['bkg_color']}";
				if ($color_chk) { $output .= "<li class='' style='background: {$a['bkg_color']}; '>"; }else{ $output .= "<li class=' ' >"; }
					$output .= "<article class='post-$the_id'>";

						$output .= "<ul class='blog-container '>";

							if($temp_pic) {
							$output .= "<li class='img-container '>";
								$output .= "<a href='$temp_link'>";
								$output .= "<img class='blog-img-feat' src='$temp_pic'>";
								$output .= "</a>";
							$output .= "</li>";
							}else{
								$output .= "<li class='no-img-container '></li>";
							}

							$output .= "<li class='content-container '>";
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
								$output .= "<a class='read-more-link ' href='$temp_link'>Read More</a>";
							$output .= "</li>";

						$output .= "</ul>";

					$output .= "</article>";
				$output .= "</li>";
			}//end while
			//wp_reset_postdata();
		} else {
			echo '<p>No posts found.</p>';
		}


		wp_reset_query();

		wp_enqueue_style( 'post-grid-css' );
		wp_enqueue_script( 'match-height-js' );
		wp_enqueue_script( 'pegasus-posts-plugin-js' );

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
		//$the_query = preg_replace('~&#x0*([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $the_query);
		//$the_query = preg_replace('~&#0*([0-9]+);~e', 'chr(\\1)', $the_query);

		$the_query = preg_replace_callback('~&#x0*([0-9a-f]+);~', function($matches){
			return chr( dechex( $matches[1] ) );
		}, $the_query);

		$the_query = preg_replace_callback('~&#0*([0-9]+);~', function($matches){
			return chr( $matches[1] );
		}, $the_query);

		$query_args = array(
			'post_type' => 'post', // Ensure you are querying the correct post type
			'posts_per_page' => -1, // Set the number of posts to retrieve
			//'post_status' => 'publish', // Ensure only published posts are retrieved
			//'category_name' => 'your-category-slug', // Optional: Filter by category
			//'orderby' => 'date', // Optional: Order by date
			//'order' => 'DESC' // Optional: Order descending
		);

		// echo '<pre>';
		// var_dump( $the_query );
		// echo '</pre>';
		// echo '<pre>';
		// var_dump( $query_args );
		// echo '</pre>';
		// Convert query string into array for WP_Query
		parse_str( $the_query, $query_args );
		// echo '<pre>';
		// var_dump( $query_args );
		// echo '</pre>';
		// Create a new WP_Query instance
		$query = new WP_Query( $query_args );

		// query is made
		//query_posts($the_query);

		// Reset and setup variables
		$output = '';
		$temp_title = '';
		$temp_link = '';
		$temp_date = '';
		$temp_pic = '';
		$temp_content = '';
		$the_id = '';

		global $post;

		// the loop
		if ($query->have_posts()) {
			while ($query->have_posts()) {
				$query->the_post();

				$the_id = get_the_ID();
				$temp_title = get_the_title($post->ID);
				$temp_link = get_permalink($post->ID);
				$temp_date = get_the_date($post->ID);
				$temp_pic = wp_get_attachment_url( get_post_thumbnail_id($post->ID) );
				$post_thumb = has_post_thumbnail() ? get_the_post_thumbnail_url($the_id, 'medium') : plugin_dir_url(__FILE__) . '/images/not-available.png';
				$temp_excerpt = wp_trim_words( get_the_excerpt(), 150 );
				$temp_content = wp_trim_words( get_the_content(), 300 );


				// output all findings - CUSTOMIZE TO YOUR LIKING
				$color_chk = "{$a['bkg_color']}";
				if ($color_chk) { $output .= "<li class='' style='background: {$a['bkg_color']}; '>"; }else{ $output .= "<li class=' ' >"; }
					$output .= "<article class='post-$the_id'>";

						$output .= "<div class='blog-container '>";

							if($temp_pic) {
							$output .= "<div class='img-container '>";
								$output .= "<a href='$temp_link'>";
								$output .= "<img class='blog-img-feat' src='$temp_pic'>";
								$output .= "</a>";
							$output .= "</div>";
							}else{
								$output .= "<div class='no-img-container '></div>";
							}

							$output .= "<div class='content-container '>";
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
								$output .= "<a class='read-more-link ' href='$temp_link'>Read More</a>";
							$output .= "</div>";

						$output .= "</div>";

					$output .= "</article>";
				$output .= "</li>";
			}//end while
			//wp_reset_postdata();
		} else {
			echo '<p>No posts found.</p>';
		}

		wp_reset_postdata();
		//wp_reset_query();

		wp_enqueue_style( 'post-grid-css' );
		wp_enqueue_script( 'match-height-js' );
		wp_enqueue_script( 'pegasus-posts-plugin-js' );

		return '<ul class="post-grid ">' . $output . '</ul>';

	}
	add_shortcode("loop-grid", "loop_grid_query_shortcode");

?>
