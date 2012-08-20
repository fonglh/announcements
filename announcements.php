<?php
/*
Plugin Name: Announcements plugin
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

