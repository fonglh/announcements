<?php
/*
Plugin Name: Announcements
Plugin URI: http://example.com/wordpress-plugins/my-plugin
Description: Provides a custom post type for easy administration of announcements. Displays announcements using a jQuery news ticker. Shortcode allows them to be easily added to page or post content
Version: 1.0
Author: fonglh
Author URI: https://wpadventures.wordpress.com
License: GPLv2
*/

register_activation_hook( WP_PLUGIN_DIR . '/announcements/announcements.php', 'flh_announcements_install' );

/* Start by giving the administrator role access to the CPT
 */
function flh_announcements_install() {
	/* Get the administrator role. */
	$role =& get_role( 'administrator' );

	/* If the administrator role exists, add required capabilities for the plugin. */
	if ( !empty( $role ) ) {
		/* CPT management capabilities. */
		$role->add_cap( 'publish_announcements' );
		$role->add_cap( 'create_announcements' );
		$role->add_cap( 'delete_announcements' );
		$role->add_cap( 'delete_published_announcements' );
		$role->add_cap( 'edit_announcements' );
		$role->add_cap( 'edit_published_announcements' );
	}

	flush_rewrite_rules();

}

register_deactivation_hook( WP_PLUGIN_DIR . '/announcements/announcements.php', 'flh_announcements_deactivate' );

/* Remove custom capabilities when plugin is deactivated
 *
 */
function flh_announcements_deactivate() {
	/* Get the administrator role. */
	$role =& get_role( 'administrator' );

	/* If the administrator role exists, add required capabilities for the plugin. */
	if ( !empty( $role ) ) {
		/* CPT management capabilities. */
		$role->remove_cap( 'publish_announcements' );
		$role->remove_cap( 'create_announcements' );
		$role->remove_cap( 'delete_announcements' );
		$role->remove_cap( 'delete_published_announcements' );
		$role->remove_cap( 'edit_announcements' );
		$role->remove_cap( 'edit_published_announcements' );
	}

	flush_rewrite_rules();
}



add_action( 'init', 'flh_add_announcement_cpt' );

/*  Register the custom post type for Announcement
 *
 */
function flh_add_announcement_cpt() {
	 /* Set up the arguments for the 'Announcement' post type. */
    $announcement_args = array(
       'labels'=>array(
					'name'=>__('Announcements'),
					'singular_name'=>__('Announcement'),
					'add_new'=>__('Add New'),
					'add_new_item'=>__('Add New Announcement'),
					'edit'=>__('Edit'),
					'edit_item'=>__('Edit Announcement'),
					'new_item'=>__('New Announcement'),
					'view_item'=>__('View Announcement'),
					'search_items'=>__('Search Announcements'),
					'not_found'=>__('No announcements found'),
					'not_found_in_trash'=>__('No announcements found in trash')),    
	   'description'=>__('A short announcement you want to put on your site with different styling'),
        'public' => true,
		'menu_position' => 9,
		'has_archive' => false,
		'capability_type' => 'announcement',
		'map_meta_cap' => true,
        'rewrite' => false
		);

    /* Register the announcement post type. */
    register_post_type( 'announcement', $announcement_args );
}

//use the shortcode [announcements] in page or post content to display the announcements
add_shortcode( 'announcements', 'flh_announcements_handler' );

//shortcode handler which queries for the announcements and displays them
function flh_announcements_handler() {
	$output = '';

	//get announcement CPT of those in the future
	//it's not enough to just get post_status=future as missed schedule posts will also show up
	add_filter( 'posts_where', 'flh_announcements_filter_where' );
	$announce_query = new WP_Query( array( 'post_type' => 'announcement',
											'post_status' => 'future',  
											'orderby' => 'date',
											'order' => 'ASC'
								) );
	remove_filter( 'posts_where', 'flh_announcements_filter_where' );

	// don't output anything if there are no posts
	if ( $announce_query->have_posts() ) {
		$output .= '<ul id="js-news" class="js-hidden">';
		$options = flh_announcements_get_options();
		$max_chars = $options[ 'max-chars' ];
	}
	else
		return $output;		// empty string

	while ( $announce_query->have_posts() ) : $announce_query->the_post();
		$output .= '<li class="news-item">';

		//cannot use the_content() directly as that echoes immediately, so the output is in the wrong place
		//get unfiltered content instead. have to filter and make it safe before display
		//code from http://codex.wordpress.org/Function_Reference/the_content
		//$content = get_the_content();
		//$content = apply_filters( 'the_content', $content );
		//$content = str_replace( ']]>', ']]&gt;', $content );

		//going to use my custom excerpt function instead
		$content = flh_announcements_excerpt_max_charlength( get_the_excerpt(), get_permalink(), $max_chars );

		$output .= $content;
		$output .= '</li>';
	endwhile;

	wp_reset_postdata();

	$output .= '</ul>';
	return $output;
}

// get the excerpt with a maximum of $charlength characters
// code is adapted from http://codex.wordpress.org/Function_Reference/get_the_excerpt
function flh_announcements_excerpt_max_charlength( $text, $permalink, $charlength ) {
	$excerpt = $text;
	$charlength++;

	if( mb_strlen( $excerpt ) > $charlength ) {
		//the -5 seems to be to make up for the [...] at the end
		$subex = mb_substr( $excerpt, 0, $charlength - 10 );
		$exwords = explode(' ', $subex );
		//figure out how long the last (possibly) partial word is
		$excut = - ( mb_strlen( $exwords[ count( $exwords ) - 1 ] ) );
		if( $excut < 0 ) {
			//remove the last (partial) word
			$excerpt = mb_substr( $subex, 0, $excut );
		}
		else
			$excerpt = $subex;
		$excerpt .= '<a href="' . $permalink . '">&hellip; [Read All]</a>';
	}
	return $excerpt;
}


//filter function for WP_Query used in shortcode handler
function flh_announcements_filter_where( $where = '' ) {
	//future posts
	$where .= " AND post_date >= '" . date( 'Y-m-d H:i:s' ) . "'";

	return $where;
}

//enqueue scripts and styles for news ticker
add_action( 'wp_enqueue_scripts', 'flh_announcements_ticker_enqueue' );

function flh_announcements_ticker_enqueue() {
	wp_enqueue_script(
			'news-ticker',
			plugins_url( 'announcements/js/jquery.ticker.js' ),		//passing __FILE__ as the 2nd param doesn't work for symlinks
			array( 'jquery' )
		);
	wp_enqueue_script(
			'start-news-ticker',
			plugins_url( 'announcements/js/ticker-init.js' ),
			array( 'news-ticker' )
		);
	wp_enqueue_style(
			'news-ticker-style',
			plugins_url( 'announcements/css/ticker-style.css' )
		);
}

//enqueue js scripts and styles only for my options page so it doesn't screw up other settings pages
add_action( 'admin_print_styles-settings_page_announcements_options', 'flh_announcements_admin_enqueue' );

function flh_announcements_admin_enqueue() {
	//this changes the colour in the colour samples when the text changes
	wp_enqueue_script(
			'flh-announcements-options',
			plugins_url( 'announcements/js/announcements-options.js' ),
			array( 'farbtastic' )
		);

	// this is for the colour samples
	wp_enqueue_style(
			'flh-announcements-options-style',
			plugins_url( 'announcements/css/announcements-options.css' ),	
			false
		);

	//need this to display the colour picker
	wp_enqueue_style( 'farbtastic' );

	//to display the sample ticker
	wp_enqueue_style(
			'news-ticker-style',
			plugins_url( 'announcements/css/ticker-style.css' )
		);
}


//add submenu to Settings menu
add_action( 'admin_menu', 'flh_announcements_create_menu' );

//Add an entry to the Settings menu named 'Announcements'
function flh_announcements_create_menu() {
	add_options_page( 'Announcements Options', 'Announcements', 'manage_options', 'announcements_options', 'flh_announcements_settings_render_page' );
}

//Render the settings page
function flh_announcements_settings_render_page() {
	$lorem_ipsum = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer tristique gravida eleifend. Nam sit amet est nunc. Nam id purus felis, quis gravida ipsum. Integer eu lectus tellus, non consequat risus. Duis ultrices nibh non neque mattis tincidunt. Suspendisse potenti. Phasellus ac nulla sit amet turpis condimentum rutrum nec a est. Morbi dictum urna nec orci suscipit ornare. Etiam ut nisi arcu, vitae semper urna. Donec fermentum ornare ipsum, ut suscipit nisl suscipit ac.';
	$options = flh_announcements_get_options();
	?>
	<div class="wrap">
		<?php screen_icon(); ?>
		<h2>Announcements Options</h2>
		<?php //settings_errors(); ?>
		<div id="ticker-wrapper-sample" class="ticker-wrapper has-js left" style="width:620px">
			<div id="ticker-sample" class="ticker">
				<p id="ticker-content-sample" class="ticker-content" style="display: block; opacity: 1; left: 20px; font-size: <?php echo $options['text-size']; ?>;">
				<?php echo flh_announcements_excerpt_max_charlength( $lorem_ipsum, '#', $options['max-chars'] ); ?>
				</p>
			</div>
			<ul id="ticker-controls-sample" class="ticker-controls">
				<li id="play-pause-sample" class="jnt-play-pause controls"></li>
				<li id="prev-sample" class="jnt-prev controls"></li>
				<li id="next-sample" class="jnt-next controls"></li>
			</ul>
		</div>
		<br />

		<form method="post" action="options.php">
			<?php
				settings_fields( 'flh_announcements_options' );
				do_settings_sections( 'announcements_options' );
				submit_button();
			?>
		</form>
		
		</div>
	<?php
;
}

/* Return an array of default options.
 * Provide a filter so other functions can change the defaults if necessary
 */
function flh_announcements_get_default_options() {
	$defaults = array(
		'ticker-color' => '#21759b',
		'text-color' => '#ffffff',
		'ticker-height' => '58px',
		'max-chars' => 140,
		'text-size' => '16px'
	);

	return apply_filters( 'flh_announcements_default_options', $defaults );
}

/* Get options from the database and merge with defaults
 */
function flh_announcements_get_options() {
	$defaults = flh_announcements_get_default_options();
	$options = get_option( 'flh_announcements_options', $defaults );
	$options = wp_parse_args( $options, $defaults );

	return $options;
}


add_action( 'admin_init', 'flh_announcements_options_init' );

function flh_announcements_options_init() {
	register_setting( 
		'flh_announcements_options',
		'flh_announcements_options',
		'flh_announcements_validate_options'
	);

	add_settings_section(
		'ticker-appearance',
		'Ticker Appearance',
		'flh_announcements_appearance_section',
		'announcements_options'
	);

	add_settings_section(
		'ticker-behavior-section',
		'Ticker Behavior',
		'flh_announcements_behavior_section',
		'announcements_options'
	);

	add_settings_field( 'ticker-color', 'Ticker Color', 'flh_announcements_options_field_ticker_color', 'announcements_options', 'ticker-appearance' );
	add_settings_field( 'text-color', 'Text Color', 'flh_announcements_options_field_text_color', 'announcements_options', 'ticker-appearance' );
	add_settings_field( 'text-size', 'Text Size', 'flh_announcements_options_field_sample_text_size', 'announcements_options', 'ticker-appearance' );
	add_settings_field( 'ticker-height', 'Ticker Height (px)', 'flh_announcements_options_field_ticker_height', 'announcements_options', 'ticker-appearance' );
	add_settings_field( 'max-chars', 'Maximum number of characters', 'flh_announcements_options_field_max_chars', 'announcements_options', 'ticker-behavior-section' );
}

function flh_announcements_behavior_section() {
	?>
	<p>Control elements of the ticker's behavior here. Save the changes to see them take effect</p>
	<?php
}

function flh_announcements_appearance_section() {
	?>
	<p>Control what your ticker will look like. Changes here can be immediately previewed in the sample ticker.</p>
	<p>Save the changes to see them take effect on your site</p>
	<?php
	
}

function flh_announcements_validate_options( $input ) {
	$output = flh_announcements_get_default_options();

	// Ticker color must be 3 or 6 hexadecimal characters
	if ( isset( $input['ticker-color'] ) && preg_match( '/^#?([a-f0-9]{3}){1,2}$/i', $input['ticker-color'] ) )
		$output['ticker-color'] = '#' . strtolower( ltrim( $input['ticker-color'], '#' ) );

	// Text color must be 3 or 6 hexadecimal characters
	if ( isset( $input['text-color'] ) && preg_match( '/^#?([a-f0-9]{3}){1,2}$/i', $input['text-color'] ) )
		$output['text-color'] = '#' . strtolower( ltrim( $input['text-color'], '#' ) );

	// ticker height must in in px. if just a number is given, add px to it
	if ( isset( $input['ticker-height'] ) ) {
		if ( preg_match( '/^[0-9]+px$/', $input['ticker-height'] ) )	//if number followed by px
			$output['ticker-height'] = strtolower( $input['ticker-height'] );
		elseif ( preg_match( '/^[0-9]+$/', $input['ticker-height'] ) )	//just a number, append px to it
			$output['ticker-height'] = $input['ticker-height'] . 'px';
	}

	// max chars must be a decimal number
	if( isset( $input['max-chars'] ) ) {
		if ( preg_match( '/^[0-9]+$/', $input['max-chars'] ) )		//numbers
			$output['max-chars'] = $input['max-chars'];
	}

	// sample text size must in in px. if just a number is given, add px to it
	if ( isset( $input['text-size'] ) ) {
		if ( preg_match( '/^[0-9]+px$/', $input['text-size'] ) )	//if number followed by px
			$output['text-size'] = strtolower( $input['text-size'] );
		elseif ( preg_match( '/^[0-9]+$/', $input['text-size'] ) )	//just a number, append px to it
			$output['text-size'] = $input['text-size'] . 'px';
	}


	return $output;
}

function flh_announcements_options_field_ticker_color() {
	$defaults = flh_announcements_get_default_options();
	$options = flh_announcements_get_options();
	?>
	<input type="text" name="flh_announcements_options[ticker-color]" id="ticker-color" value="<?php echo esc_attr( $options['ticker-color'] ); ?>" />
	<a href="#" class="tickerpickcolor hide-if-no-js" id="ticker-color-example"></a>
	<input type="button" class="tickerpickcolor button hide-if-no-js" id="ticker-pick-color" value="Select a Color" />
	<div id="tickerColorPickerDiv" style="z-index: 100; background:#eee; border:1px solid #ccc; position:absolute; display:none;"></div>
	<br />
	<span><?php printf( __( 'Default color: %s', 'flh_announcements' ), '<span id="ticker-default-color">' . $defaults['ticker-color'] . '</span>' ); ?></span>
	<?php

}

function flh_announcements_options_field_text_color() {
	$defaults = flh_announcements_get_default_options();
	$options = flh_announcements_get_options();
	?>
	<input type="text" name="flh_announcements_options[text-color]" id="text-color" value="<?php echo esc_attr( $options['text-color'] ); ?>" />
	<a href="#" class="textpickcolor hide-if-no-js" id="text-color-example"></a>
	<input type="button" class="textpickcolor button hide-if-no-js" value="Select a Color" />
	<div id="textColorPickerDiv" style="z-index: 100; background:#eee; border:1px solid #ccc; position:absolute; display:none;"></div>
	<br />
	<span><?php printf( __( 'Default color: %s', 'flh_announcements' ), '<span id="text-default-color">' . $defaults['text-color'] . '</span>' ); ?></span>
	<?php
}

function flh_announcements_options_field_ticker_height() {
	$defaults = flh_announcements_get_default_options();
	$options = flh_announcements_get_options();
	$ticker_height = $options['ticker-height'];
	$ticker_height = substr( $ticker_height, 0, -2 );	//strip out 'px' from the option value
	?>
	<input type="range" name="flh_announcements_options[ticker-height]" id="ticker-height" value="<?php echo esc_attr( $ticker_height ); ?>" min="32" max="400" />
	<br />
	<span><?php printf( __( 'Default height: %s', 'flh_announcements' ), '<span id="default-height">' . $defaults['ticker-height'] . '</span>' ); ?></span>
	<?php
}

function flh_announcements_options_field_max_chars() {
	$defaults = flh_announcements_get_default_options();
	$options = flh_announcements_get_options();
	?>
	<input type="text" name="flh_announcements_options[max-chars]" id="max-chars" value="<?php echo esc_attr( $options['max-chars'] ); ?>" />
	<br />
	<span><?php printf( __( 'Default maximum: %s', 'flh_announcements' ), '<span id="default-max-chars">' . $defaults['max-chars'] . '</span>' ); ?></span>
	<?php
}

function flh_announcements_options_field_sample_text_size() {
	$defaults = flh_announcements_get_default_options();
	$options = flh_announcements_get_options();
	$text_size = $options['text-size'];
	$text_size = substr( $text_size, 0, -2 );		//strip out 'px' from the option value
	?>
	<input type="range" name="flh_announcements_options[text-size]" id="text-size" value="<?php echo esc_attr( $text_size ); ?>" min="8" max="32" />
	<br />
	<span><?php printf( __( 'Default sample text size: %s', 'flh_announcements' ), '<span id="default-text-size">' . $defaults['text-size'] . '</span>' ); ?></span>
	<?php
}

// output Announcements options style settings in page header
add_action( 'wp_head', 'flh_announcements_print_ticker_color_style' );

function flh_announcements_print_ticker_color_style() {
	$defaults = flh_announcements_get_default_options();
	$options = flh_announcements_get_options();
	?>
	<style>
	<?php

	// for simplicity, just output both colour options if either of them do not match the default
	if ( $options['ticker-color'] !== $defaults['ticker-color'] || $options['text-color'] !== $defaults['text-color'] ) {
		?>
		.ticker, 
		.ticker-wrapper.has-js,
		.ticker-content,
		.ticker-content a {
			background-color: <?php echo $options['ticker-color']; ?>;
			color: <?php echo $options['text-color']; ?>;
			font-size: <?php echo $options['text-size']; ?>;	
		}
		<?php
	}
	
	if ( $options['ticker-height'] !== $defaults['ticker-height'] ) {
		?>
		.ticker-wrapper.has-js {
			height: <?php echo $options['ticker-height']; ?>;
		}
		<?php
	}
	?>
	</style>
	<?php
}

