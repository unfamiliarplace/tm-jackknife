<?php

/**
 * Add the online administrator role.
 */
class MJKCommon_custom_roles {

	/**
	 * ADd the role.
	 */
    static function run() {

        // Define the role
        $onlineadmin_caps = [
            'delete_others_pages' => true,
            'delete_others_posts' => true,
            'delete_pages' => true,
            'delete_posts' => true,
            'delete_private_pages' => true,
            'delete_private_posts' => true,
            'delete_published_pages' => true,
            'delete_published_posts' => true,
            'edit_dashboard' => true,
            'edit_others_pages' => true,
            'edit_others_posts' => true,
            'edit_pages' => true,
            'edit_posts' => true,
            'edit_private_pages' => true,
            'edit_private_posts' => true,
            'edit_published_pages' => true,
            'edit_published_posts' => true,
            'edit_theme_options' => true,
            'export' => true,
            'import' => true,
            'list_users' => true,
            'manage_categories' => true,
            'manage_links' => true,
            'manage_options' => true,
            'moderate_comments' => true,
            'publish_pages' => true,
            'publish_posts' => true,
            'read_private_pages' => true,
            'read_private_posts' => true,
            'read' => true,
            'upload_files' => true,
            'install_themes' => true,
            'create_users' => true
        ];

        // Remove to refresh / delete conflicts
        remove_role('onlineadmin');

        // Readd
        add_role('onlineadmin', __('Online Administrator'), $onlineadmin_caps );
    }
}
