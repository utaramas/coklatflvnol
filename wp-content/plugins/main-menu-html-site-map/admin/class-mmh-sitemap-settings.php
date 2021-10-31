<?php

/**
 * The settings of the plugin.
 *
 * @link       http://devinvinson.com
 * @since      1.0.0
 *
 * @package    Wppb_Demo_Plugin
 * @subpackage Wppb_Demo_Plugin/admin
 */

/**
 * Class WordPress_Plugin_Template_Settings
 *
 */
class Mmh_Sitemap_Settings {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * This function introduces the theme options into the 'Appearance' menu and into a top-level
	 * 'WPPB Demo' menu.
	 */
	public function setup_plugin_options_menu() {

		//Add the menu to the Plugins set of menu items
		add_submenu_page(
			"options-general.php",
			'Main menu Sitemap', 					// The title to be displayed in the browser window for this page.
			'Main menu Sitemap',					// The text to be displayed for this menu item
			'manage_options',					// Which type of users can see this menu item
			'mmh_sitemap',			// The unique ID - that is, the slug - for this menu item
			array( $this, 'render_settings_page_content')					// The name of the function to call when rendering this menu's page
		);

	}

	/**
	 * Provides default values for the Options.
	 *
	 * @return array
	 */
	public function default_display_options() {

		$defaults = array(
			'show_header'		=>	'',
			'show_blog'		=>	'',
			'enable_blog'		=>	'',
		);

		return $defaults;

	}

	/**
	 * Provide default values for the Social Options.
	 *
	 * @return array
	 */
	public function default_help_options() {

		$defaults = array(
			'twitter'		=>	'twitter',
			'facebook'		=>	'',
			'googleplus'	=>	'',
		);

		return  $defaults;

	}

	/**
	 * Provides default values for the Input Options.
	 *
	 * @return array
	 */
	public function default_input_options() {

		$defaults = array(
			'input_example'		=>	'default input example',
			'textarea_example'	=>	'',
			'checkbox_example'	=>	'',
			'radio_example'		=>	'2',
			'time_options'		=>	'default'
		);

		return $defaults;

	}

	/**
	 * Renders a simple page to display for the theme menu defined above.
	 */
	public function render_settings_page_content( $active_tab = '' ) {
		?>
		<!-- Create a header in the default WordPress 'wrap' container -->
		<div class="wrap">

			<h2><?php _e( 'Main Menu HTML Sitemap', 'mmh-sitemap' ); ?></h2>
			<?php //settings_errors(); ?>

			<?php if( isset( $_GET[ 'tab' ] ) ) {
				$active_tab = $_GET[ 'tab' ];
			} else if( $active_tab == 'help_options' ) {
				$active_tab = 'help_options';
			} else if( $active_tab == 'input_examples' ) {
				$active_tab = 'input_examples';
			} else {
				$active_tab = 'display_options';
			} // end if/else ?>

			<h2 class="nav-tab-wrapper">
				<a href="?page=mmh_sitemap&tab=display_options" class="nav-tab <?php echo $active_tab == 'display_options' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Options', 'mmh-sitemap' ); ?></a>
				<a href="?page=mmh_sitemap&tab=help_options" class="nav-tab <?php echo $active_tab == 'help_options' ? 'nav-tab-active' : ''; ?>"><?php _e( 'How to use', 'mmh-sitemap' ); ?></a>
				<!-- <a href="?page=mmh_sitemap&tab=input_examples" class="nav-tab <?php echo $active_tab == 'input_examples' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Input Examples', 'mmh-sitemap' ); ?></a> -->
			</h2>

			<form method="post" action="options.php">
				<?php

				if( $active_tab == 'display_options' ) {

					settings_fields( 'mmh_sitemap_display_options' );
					do_settings_sections( 'mmh_sitemap_display_options' );
					submit_button();

				} elseif( $active_tab == 'help_options' ) {

					settings_fields( 'mmh_sitemap_help_options' );
					do_settings_sections( 'mmh_sitemap_help_options' );

				} else {

					// settings_fields( 'wppb_demo_input_examples' );
					// do_settings_sections( 'wppb_demo_input_examples' );

				} // end if/else

				

				?>
			</form>

		</div><!-- /.wrap -->
		<?php
	}


	/**
	 * This function provides a simple description for the General Options page.
	 *
	 * It's called from the 'wppb-demo_initialize_theme_options' function by being passed as a parameter
	 * in the add_settings_section function.
	 */
	public function general_options_callback() {
		$options = get_option('mmh_sitemap_display_options');
		// var_dump($options);
		echo '<p>' . __( 'Select which menu to display in sitemap. use short code: <pre>[mainmenu-sitemap]</pre>', 'mmh-sitemap' ) . '</p>';
	} // end general_options_callback

	/**
	 * This function provides a simple description for the Social Options page.
	 *
	 * It's called from the 'wppb-demo_theme_initialize_help_options' function by being passed as a parameter
	 * in the add_settings_section function.
	 */
	public function help_options_callback() {
		$options = get_option('mmh_sitemap_help_options');
		// var_dump($options);
		echo '<p>' . __( 'Provide the URL to the social networks you\'d like to display.', 'mmh-sitemap' ) . '</p>';
	} // end general_options_callback

	/**
	 * This function provides a simple description for the Input Examples page.
	 *
	 * It's called from the 'wppb-demo_theme_initialize_input_examples_options' function by being passed as a parameter
	 * in the add_settings_section function.
	 */
	public function input_examples_callback() {
		$options = get_option('wppb_demo_input_examples');
		// var_dump($options);
		echo '<p>' . __( 'Provides examples of the five basic element types.', 'mmh-sitemap' ) . '</p>';
	} // end general_options_callback


	/**
	 * Initializes the theme's display options page by registering the Sections,
	 * Fields, and Settings.
	 *
	 * This function is registered with the 'admin_init' hook.
	 */
	public function init_mmhsitemap_display_options() {

		// If the theme options don't exist, create them.
		if( false == get_option( 'mmh_sitemap_display_options' ) ) {
			$default_array = $this->default_display_options();
			add_option( 'mmh_sitemap_display_options', $default_array );
		}


		add_settings_section(
			'general_settings_section',			            // ID used to identify this section and with which to register options
			__( 'Options', 'mmh-sitemap' ),		        // Title to be displayed on the administration page
			array( $this, 'general_options_callback'),	    // Callback used to render the description of the section
			'mmh_sitemap_display_options'		                // Page on which to add this section of options
		);

		add_settings_field(
			'enable_menu',
			__( 'Display Menu', 'mmh-sitemap' ),
			array( $this, 'enable_menu_callback'),
			'mmh_sitemap_display_options',
			'general_settings_section',
			array(
				__( 'Enable Menu showing in sitemap', 'mmh-sitemap' ),
			)
		);

		add_settings_field(
			'show_header',						        // ID used to identify the field throughout the theme
			__( 'Select menu', 'mmh-sitemap' ),					// The label to the left of the option interface element
			array( $this, 'toggle_header_callback'),	// The name of the function responsible for rendering the option interface
			'mmh_sitemap_display_options',	            // The page on which this option will be displayed
			'general_settings_section',
			array('class'=>'mmhs-selectmenu')		        // The name of the section to which this field belongs

		);

		add_settings_field(
			'enable_blog',
			__( 'Blog posts', 'mmh-sitemap' ),
			array( $this, 'enable_blog_callback'),
			'mmh_sitemap_display_options',
			'general_settings_section',
			array(
				__( 'Enable blog post showing in sitemap', 'mmh-sitemap' ),
			)
		);

		add_settings_field(
			'show_blog',
			__( 'Select Blog category', 'mmh-sitemap' ),
			array( $this, 'toggle_content_callback'),
			'mmh_sitemap_display_options',
			'general_settings_section',
			array('class'=>'mmhs-selectblogcat')
		);

		add_settings_field(
			'enable_pages',
			__( 'Display Pages', 'mmh-sitemap' ),
			array( $this, 'enable_pages_callback'),
			'mmh_sitemap_display_options',
			'general_settings_section',
			array(
				__( 'Enable Pages showing in sitemap', 'mmh-sitemap' ),
			)
		);

		add_settings_field(
			'exclude_menu',
			__( 'Exclude page ID(s)', 'mmh-sitemap' ),
			array( $this, 'menu_exclude_callback'),
			'mmh_sitemap_display_options',
			'general_settings_section',
			array('class'=>'mmhs-excludepage')
		);

		add_settings_field(
			'enable_allpost',
			__( 'Show all posts', 'mmh-sitemap' ),
			array( $this, 'enable_allpost_callback'),
			'mmh_sitemap_display_options',
			'general_settings_section',
			array(
				__( 'Enable All posts showing in sitemap', 'mmh-sitemap' ),
			)
		);

		add_settings_field(
			'exclude_post',
			__( 'Exclude post category ID(s)', 'mmh-sitemap' ),
			array( $this, 'post_cat_exclude_callback'),
			'mmh_sitemap_display_options',
			'general_settings_section',
			array('class'=>'mmhs-excludeppostcat')
		);

		add_settings_field(
			'enable_cpt',
			__( 'Show custom post type', 'mmh-sitemap' ),
			array( $this, 'enable_cpt_callback'),
			'mmh_sitemap_display_options',
			'general_settings_section',
			array(
				__( 'Enable Custom post type showing in sitemap', 'mmh-sitemap' ),
			)
		);

		// Finally, we register the fields with WordPress
		register_setting(
			'mmh_sitemap_display_options',
			'mmh_sitemap_display_options',
			array( $this, 'validate_input_examples')
		);

	} // end wppb-demo_initialize_theme_options


	/**
	 * This function renders the interface elements for toggling the visibility of the header element.
	 *
	 * It accepts an array or arguments and expects the first element in the array to be the description
	 * to be displayed next to the checkbox.
	 */
	public function toggle_header_callback($args) {
		// $menus = get_terms('nav_menu');
		$theme_locations = get_nav_menu_locations();
		// var_dump($menus);
		// foreach($menus as $menu){
		// 	echo $menu->name . " n";
		// } 
		// First, we read the options collection
		$options = get_option('mmh_sitemap_display_options');

		$html = '<select id="sitemap-menu" name="mmh_sitemap_display_options[sitemap-menu]">';
		$html .= '<option value="default">' . __( 'None', 'mmh-sitemap' ) . '</option>';
		foreach($theme_locations as $menukey => $menu){
			// $html .= '<option value="'.$menu->name.'"' . selected( $options['sitemap-menu'], $menu->name, false) . '>' . __( ''.$menu->name.'', 'mmh-sitemap' ) . '</option>';
			$html .= '<option value="'.$menukey.'"' . selected( $options['sitemap-menu'], $menukey, false) . '>' . __( ''.$menukey.'', 'mmh-sitemap' ) . '</option>';
		}
		$html .= '</select>';

		// echo $html;
		// Next, we update the name attribute to access this element's ID in the context of the options array
		// We also access the show_header element of the options collection in the call to the checked() helper function
		// $html = '<input type="checkbox" id="show_header" name="mmh_sitemap_display_options[show_header]" value="1" ' . checked( 1, isset( $options['show_header'] ) ? $options['show_header'] : 0, false ) . '/>';

		// Here, we'll take the first argument of the array and add it to a label next to the checkbox
		// $html .= '<label for="show_header">&nbsp;'  . $args[0] . '</label>';

		echo $html;

	} // end toggle_header_callback

	public function toggle_content_callback($args) {

		$options = get_option('mmh_sitemap_display_options');
		$cats = get_categories();
		// var_dump(get_categories());

		$html = '<select id="sitemap-menu" name="mmh_sitemap_display_options[sitemap-blog]">';
		foreach($cats as $cat){
			$html .= '<option value="'.$cat->slug.'"' . selected( $options['sitemap-blog'], $cat->slug, false) . '>' . __( ''.$cat->name.'', 'mmh-sitemap' ) . '</option>';
		}
		$html .= '</select>';

		echo $html;

	} // end toggle_content_callback

	public function enable_menu_callback($args) {

		$options = get_option('mmh_sitemap_display_options');

		$html = '<input type="checkbox" id="enable_menu" name="mmh_sitemap_display_options[enable_menu]" value="1" ' . checked( 1, isset( $options['enable_menu'] ) ? $options['enable_menu'] : 0, false ) . '/>';
		$html .= '<label for="enable_menu">&nbsp;'  . $args[0] . '</label>';

		echo $html;

	} // end enable_blog_callback

	public function enable_blog_callback($args) {

		$options = get_option('mmh_sitemap_display_options');

		$html = '<input type="checkbox" id="enable_blog" name="mmh_sitemap_display_options[enable_blog]" value="1" ' . checked( 1, isset( $options['enable_blog'] ) ? $options['enable_blog'] : 0, false ) . '/>';
		$html .= '<label for="enable_blog">&nbsp;'  . $args[0] . '</label>';

		echo $html;

	} // end enable_blog_callback

	public function enable_pages_callback($args) {

		$options = get_option('mmh_sitemap_display_options');

		$html = '<input type="checkbox" id="enable_pages" name="mmh_sitemap_display_options[enable_pages]" value="1" ' . checked( 1, isset( $options['enable_pages'] ) ? $options['enable_pages'] : 0, false ) . '/>';
		$html .= '<label for="enable_pages">&nbsp;'  . $args[0] . '</label>';

		echo $html;

	} // end enable_blog_callback

	public function enable_cpt_callback($args) {

		$options = get_option('mmh_sitemap_display_options');

		$html = '<input type="checkbox" id="enable_cpt" name="mmh_sitemap_display_options[enable_cpt]" value="1" ' . checked( 1, isset( $options['enable_cpt'] ) ? $options['enable_cpt'] : 0, false ) . '/>';
		$html .= '<label for="enable_cpt">&nbsp;'  . $args[0] . '</label>';

		echo $html;

	} // end enable_blog_callback

	public function enable_allpost_callback($args) {

		$options = get_option('mmh_sitemap_display_options');

		$html = '<input type="checkbox" id="enable_allpost" name="mmh_sitemap_display_options[enable_allpost]" value="1" ' . checked( 1, isset( $options['enable_allpost'] ) ? $options['enable_allpost'] : 0, false ) . '/>';
		$html .= '<label for="enable_allpost">&nbsp;'  . $args[0] . '</label>';

		echo $html;

	} // end enable_blog_callback

	public function initialize_about_options() {
		add_settings_section(
			'social_settings_section',			// ID used to identify this section and with which to register options
			__( 'Usage of plugin', 'mmh-sitemap' ),		// Title to be displayed on the administration page
			array( $this, 'about_options_callback'),	// Callback used to render the description of the section
			'mmh_sitemap_help_options'		// Page on which to add this section of options
		);
	}


	public function about_options_callback() {
		echo '
		<p>Use this shortcode in you sitemap page to show your selected menu as sitemap <pre>[mainmenu-sitemap]</pre></p>
		<p>Once you added this ahort code you can manage it on plugin settings.</p>
		<br>
		<h2>You may also like this plugin [Free Plugins!]</h2>
		<p> <a href="https://wordpress.org/plugins/widget-youtube-subscribtion/" target="_blank">Easy Youtube Subscribe Button Widget >></a></p>
		<p> <a href="https://wordpress.org/plugins/popup-notification-news-alert/" target="_blank">Toast Popup Notification News Alert >></a></p>
		<p> <a href="https://wordpress.org/plugins/embed-page-facebook/" target="_blank">Easy Facebook Embed Page Widget >></a></p>

		';
	} 

	public function menu_exclude_callback($args) {

		$options = get_option('mmh_sitemap_display_options');

		echo '<input type="text" id="exclude_menu_items" name="mmh_sitemap_display_options[exclude_menu_items]" value="' . $options['exclude_menu_items'] . '" />
		<p><small>Use comma "," separate by IDs</small></p>';

		// $html = '<input type="checkbox" id="enable_blog" name="mmh_sitemap_display_options[enable_blog]" value="1" ' . checked( 1, isset( $options['enable_blog'] ) ? $options['enable_blog'] : 0, false ) . '/>';
		// $html .= '<label for="enable_blog">&nbsp;'  . $args[0] . '</label>';

		// echo $html;

	} // end enable_blog_callback	
	public function post_cat_exclude_callback($args) {

		$options = get_option('mmh_sitemap_display_options');

		echo '<input type="text" id="exclude_post_cat" name="mmh_sitemap_display_options[exclude_post_cat]" value="' . $options['exclude_post_cat'] . '" />
		<p><small>Use comma "," separate by IDs</small></p>';

		// $html = '<input type="checkbox" id="enable_blog" name="mmh_sitemap_display_options[enable_blog]" value="1" ' . checked( 1, isset( $options['enable_blog'] ) ? $options['enable_blog'] : 0, false ) . '/>';
		// $html .= '<label for="enable_blog">&nbsp;'  . $args[0] . '</label>';

		// echo $html;

	} // end enable_blog_callback


	/**
	 * Sanitization callback for the social options. Since each of the social options are text inputs,
	 * this function loops through the incoming option and strips all tags and slashes from the value
	 * before serializing it.
	 *
	 * @params	$input	The unsanitized collection of options.
	 *
	 * @returns			The collection of sanitized values.
	 */
	public function sanitize_help_options( $input ) {

		// Define the array for the updated options
		$output = array();

		// Loop through each of the options sanitizing the data
		foreach( $input as $key => $val ) {

			if( isset ( $input[$key] ) ) {
				$output[$key] = esc_url_raw( strip_tags( stripslashes( $input[$key] ) ) );
			} // end if

		} // end foreach

		// Return the new collection
		return apply_filters( 'sanitize_help_options', $output, $input );

	} // end sanitize_help_options

	public function validate_input_examples( $input ) {

		// Create our array for storing the validated options
		$output = array();

		// Loop through each of the incoming options
		foreach( $input as $key => $value ) {

			// Check to see if the current option has a value. If so, process it.
			if( isset( $input[$key] ) ) {

				// Strip all HTML and PHP tags and properly handle quoted strings
				$output[$key] = strip_tags( stripslashes( $input[ $key ] ) );

			} // end if

		} // end foreach

		// Return the array processing any additional functions filtered by this action
		return apply_filters( 'validate_input_examples', $output, $input );

	} // end validate_input_examples


	public function print_menu_shortcode($atts, $content = null) {
		$options = get_option('mmh_sitemap_display_options');

		extract(shortcode_atts(array( 'name' => null, 'class' => null ), $atts));
		// $exitem = $options['exclude_menu_items'];
		// var_dump($exitem );
		// $menuites = wp_get_nav_menu_items( $options["sitemap-menu"], array( 'exclude'=>'2' ));
		// foreach ($menuites as $key => $menuite) {
		// 	unset($menuites[0]);
		// 	// var_dump($menuites[0]);
		// 	echo "<pre>".var_export($menuite->title, true)."</pre>";
		// }

		
		

		if ($options['enable_menu'] ==true) {
			if ($options['sitemap-menu'] !== 'default') {
				echo '<h2 id="pages">Main Menu</h2>';
				echo wp_nav_menu( 
					array( 
						'theme_location' => 'primary', 
					// 'theme_location' => $options["sitemap-menu"], 
					// 'menu' => $options["sitemap-menu"], 
						'menu_class' => 'sitemap-page', 
						'container_class' => 'sitemap-container',
					)
				);
			}
		}

		if ($options['enable_pages'] ==true) {
			?>
			<h2 id="pages">Pages</h2>
			<ul class="sitemap-page">
				<?php 
				wp_list_pages( array( 
					'exclude' => $options["exclude_menu_items"],
					'title_li' => '',
				)
			);
			?>
		</ul>
		<?php
	}

	if ($options['enable_blog'] ==true) {
		$category_query_args = array(
			'category_name' => $options['sitemap-blog'],
			'posts_per_page' => -1,
		);

		if ($options["enable_blog"]==1) {
			$category_query = new WP_Query( $category_query_args );
			echo '<h2 id="pages">Blog</h2>';
			echo "<div class='sitemap-container'><ul class='sitemap-page'>";
			foreach ($category_query->posts as $key => $blogvalue){
				echo '<li><a href="'.get_the_permalink($blogvalue->ID).'">'.$blogvalue->post_title.'</a></li>';
			};
			echo "</ul></div>";
		}
	}


	if ($options['enable_allpost'] ==true) {
		?>
		<h2 id="posts">Posts</h2>
		<ul>
			<?php
			// var_dump(get_terms( 'category', array( 'search' => $options['sitemap-blog'] ) ));
			$expost = $options['exclude_post_cat'];
			if ($options["enable_blog"]==1) {
				$blogid = get_category_by_slug($options['sitemap-blog'] );
				$blogidmain = $blogid->term_id;
			}else{
				$blogidmain = '';
			}
			$allpostex = $blogidmain.','.$expost;
			// var_dump($expost);
			$cats = get_categories(array('exclude' => $allpostex));
			foreach ($cats as $cat) {
				echo "<li><h3>".$cat->cat_name."</h3>";
				echo "<ul>";

				$args = array(
					'post__not_in' => array(),
					'cat'=>$cat->cat_ID,
					'posts_per_page' => -1
				);
				$query_all_posts = new WP_Query($args);

				while( $query_all_posts->have_posts() ) {
					$query_all_posts->the_post();
					echo '<li><a title="'.get_the_title().'" href="'.get_permalink().'">'.get_the_title().'</a></li>';
				}
				echo "</ul></li><br>";
			}
			?>
		</ul>
		<?php
	}


	if ($options['enable_cpt'] ==true) {

		if ($options['enable_allpost'] !=true) {
			?>
			<h2 id="posts">Posts</h2>
		<?php }

		foreach( get_post_types( array('public' => true, '_builtin' => false) ) as $post_type ) {

			$cpt = get_post_type_object( $post_type );

			echo '<h3>'.$cpt->labels->name.'</h3>';
			echo '<ul class="sitemap-page">'; 

			$args = array(
				'post_type' => $post_type,
				'post__not_in' => array(),
				'posts_per_page' => -1
			);
			$query_cpt = new WP_Query($args);

			while( $query_cpt->have_posts() ) {
				$query_cpt->the_post();
				echo '<li><a title="'.get_the_title().'" href="'.get_permalink().'">'.get_the_title().'</a></li>';
			}
			echo '</ul>';
		} 
	}


}


function get_blogpost_stitemap(){

		// $options = get_option('mmh_sitemap_display_options');

		// $category_query_args = array(
		// 	'category_name' => $options['sitemap-blog'],
		// 	'posts_per_page' => -1,
		// );

		// if ($options["enable_blog"]==1) {
		// 	$category_query = new WP_Query( $category_query_args );
		// 	echo "<div class='sitemap-container'><ul class='sitemap-page'><li> <a href='/blog'>Blog</a><ul class='sub-menu'>";
		// 	foreach ($category_query->posts as $key => $blogvalue){
		// 		echo '<li><a href="'.get_the_permalink($blogvalue->ID).'">'.$blogvalue->post_title.'</a></li>';
		// 	};
		// 	echo "</ul></li></ul></div>";
		// }


}

public function register_shortcodes() {
	add_shortcode( 'mainmenu-sitemap', array( $this, 'print_menu_shortcode') );
		// add_shortcode( 'blog-sitemap', array( $this, 'get_blogpost_stitemap') );
		// add_shortcode( 'anothershortcode', array( $this, 'another_shortcode_function') );
}
}

