<?php

/**
 * Loads Front Page Cards, set a cron for purging, and add the admin notice.
 */
class MJKFPLoader {
    use JKNCron_OneHook_Static;

	/**
	 * Return the function to run on cron.
	 *
	 * @return callable
	 */
	static function get_cron_callback(): callable {
        return [__CLASS__, 'purge_fpcards'];
    }

	/**
	 * Add the various WP hooks for the various behaviours.
	 */
	static function add_hooks(): void {
        
        // Initial loading of fpcards
        add_action('init', [__CLASS__, 'load_fpcards']);
        
        // Admin message for FP Card archive screen
        add_action('admin_notices', [__CLASS__, 'admin_fpcard_edit_notice']);
        
        // Schedule the cron job
        self::schedule($overwrite=false, $rec='daily', $min=0, $hour=0);
    }    

	/**
	 * Load volumes into the module and return them.
	 *
	 * @return MJKFP_FPCard[]
	 */
	static function load_fpcards(): array {
		$module = JKNAPI::module();

        // Get fpcard custom post types
        if (!$module->fpcards_loaded()) {

        	// Get all posts of this post type
            $posts = MJKFP_CPT_FPCard::posts();

            // Turn them into Front Page Cards
	        $fpcards = array_map(function(WP_Post $p): MJKFP_FPCard {
		        return new MJKFP_FPCard($p->ID);
	        }, $posts);

	        // Sort and add to the module
	        foreach(MJKFP_API::sort_fpcards($fpcards) as $fpcard) {
		        $module->add_fpcard($fpcard);
	        }

	        // Set loaded
	        $module->fpcards_loaded = true;
        }

        return $module->fpcards();
    }

	/**
	 * Purge old front page cards on a cron job.
	 */
	static function purge_fpcards(): void {
		foreach(MJKFP_API::deletable_fpcards() as $card) {
			wp_delete_post($card->pid);
		}
    }

	/**
	 * Add the admin notice for FP Card edit screen.
	 */
	static function admin_fpcard_edit_notice(): void {
        
        if (function_exists('get_current_screen')) {
            $screen = get_current_screen();
            
            if ($screen->base == 'edit' &&
                JKNStrings::ends_with($screen->id, MJKFP_CPT_FPCard::qid())) {
        
                $cl = 'notice notice-info';
                $msg = __( 'Note that old Front Page cards are automatically'
                        . ' deleted on a daily basis. Future cards, the active card,'
                        . ' and up to one previous card are retained.', 'en-ca' );

                printf('<div class="%s"><p>%s</p></div>',
                        esc_attr($cl), esc_html($msg));
            }
        }
    }
}
