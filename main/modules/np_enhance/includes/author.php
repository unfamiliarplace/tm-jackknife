<?php

/**
 * Provides methods for the credits on the author page.
 */
final class MJKNPEnhanceAuthor {

	const msg_no_roles = "None to display. This person's role may not involve"
	                     . " article contributions, or they may have been"
	                     . " active before contributions were tracked in our"
	                     . " database.";
    
    /**
     * Return formatted HTML for all the credits on an author page.
     *
     * @param string $uid  The user's ID.
     * @return string
     */
    static function format_all_credits(string $uid): string {
    
        $role_to_posts = self::credits_role_to_posts($uid);
        if (empty($role_to_posts)) return self::msg_no_roles;

        $html = '[vc_row][vc_column width="1/1"][vc_tabs]';

        foreach ($role_to_posts as $role => $posts) {
            if ($posts) {

                $title = sprintf('%s (%s)', $role, count($posts));
                $id = strtolower(str_replace(' ', '_', $role));
                $inner = self::format_credits_tab($posts);

                $tab = sprintf('[vc_tab title="%s" tab_id=%s]%s[/vc_tab]',
                        $title, $id, $inner);

                $html .= $tab;
            }
        }
        $html .= '[/vc_tabs][/vc_column][/vc_row]';

        // Clean up WP's removal of crucial VC syntax
        return wpb_js_remove_wpautop($html);
    }
    
    /**
     * Return an array of [role => posts] for the author page.
     *
     * @param string $uid A user ID.
     * @return WP_Post[]
     */
    static function credits_role_to_posts(string $uid): array {
        
        // Get the posts
        $role_to_posts = MJKMetaAPI::user_roles_to_posts($uid);    
        $all_role = MJKMetaAPI::all_role($role_to_posts);

        // Bail early if we have no posts
        if (empty($all_role)) return [];

        // Otherwise go on
        $role_to_posts = ['All' => $all_role] + $role_to_posts;

        // Unpack the post IDs to posts
        foreach($role_to_posts as $role => $post_ids) {
            $role_to_posts[$role] = JKNPosts::to_posts($post_ids);
        }
        
        return $role_to_posts;
    }
    
    /**
     * Return formatted HTML for one tab on the credits page.
     *
     * This is adapted from Newspaper's loop.php.
     *
     * @param WP_Post[] $posts
     * @return string
     */
    static function format_credits_tab(array $posts): string {

        global $loop_module_id, $loop_sidebar_position;

        $td_module_class = td_api_module::_helper_get_module_class_from_loop_id($loop_module_id);

        //disable the grid for some of the modules
        $td_module = td_api_module::get_by_id($td_module_class);
        if ($td_module['uses_columns'] === false) {
            $td_template_layout->disable_output();
        }

        td_global::$is_wordpress_loop = true;

        $td_template_layout = new td_template_layout($loop_sidebar_position);

        //disable the grid for some of the modules
        $td_module = td_api_module::get_by_id($td_module_class);
        if ($td_module['uses_columns'] === false) {
            $td_template_layout->disable_output();
        }

        // Go through posts and render

        $html = '';

        foreach ($posts as $post) {

            $html .= $td_template_layout->layout_open_element();

            if (class_exists($td_module_class)) {
                $td_mod = new $td_module_class($post);
                $html .= $td_mod->render();
            } else {
                td_util::error(__FILE__, 'Missing module: ' . $td_module_class);
            }

            $html .= $td_template_layout->layout_close_element();
            $td_template_layout->layout_next();
        }

        $html .= $td_template_layout->close_all_tags();

        return $html;
    }    
}
