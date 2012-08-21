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
	$output .= '<div id="announcements"><ul>';

	//get announcement CPT of those in the future
	add_filter( 'posts_where', 'flh_announcements_filter_where' );
	$announce_query = new WP_Query( array( 'post_type' => 'announcement',
											'post_status' => 'future',  
											'orderby' => 'date',
											'order' => 'ASC'
								) );
	remove_filter( 'posts_where', 'flh_announcements_filter_where' );

	while ( $announce_query->have_posts() ) : $announce_query->the_post();
		$output .= '<li>';

		//cannot use the_content() directly as that echoes immediately, so the output is in the wrong place
		//get unfiltered content instead. have to filter and make it safe before display
		//code from http://codex.wordpress.org/Function_Reference/the_content
		$content = get_the_content();
		$content = apply_filters( 'the_content', $content );
		$content = str_replace( ']]>', ']]&gt;', $content );

		$output .= $content;
		$output .= '</li>';
	endwhile;

	wp_reset_postdata();

	$output .= '</ul></div>';
	return $output;
}


//filter function for WP_Query used in shortcode handler
function flh_announcements_filter_where( $where = '' ) {
	//future posts
	$where .= " AND post_date >= '" . date( 'Y-m-d H:i:s' ) . "'";

	return $where;
}


