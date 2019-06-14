<?php

/**
 * Disables Contact Form 7 loading on certain pages.
 */
class MJKCommon_cf7_disable {

	static $excl_pages;

	/**
	 * Add the CF7 load filters.
	 */
    static function run(): void {
        if (JKNAPI::plugin_dep_met('cf7') && !is_admin()) {

        	// Always disable, then add back later
	        add_filter('wpcf7_load_js', '__return_false');
	        add_filter('wpcf7_load_css', '__return_false');

	        add_action('init', [__CLASS__, 'conditionally_load_js']);
	        add_action('init', [__CLASS__, 'conditionally_load_css']);
        }
    }

	/**
	 * Conditionally load the JS based on whether disabling is set and whether
	 * this is an excluded page.
	 */
    static function conditionally_load_js(): void {
	    if (self::is_excl_page() ||
	        !MJKCommon_ACF::get(MJKCommon_ACF::cf7_disable_scripts)) {
	    	wpcf7_enqueue_scripts();
	    }
    }

	/**
	 * Conditionally load the CSS based on whether disabling is set and whether
	 * this is an excluded page.
	 */
	static function conditionally_load_css(): void {
		if (self::is_excl_page() ||
		    !MJKCommon_ACF::get(MJKCommon_ACF::cf7_disable_styles)) {
			wpcf7_enqueue_styles();
		}
	}

	/**
	 * Return true iff current page is among those where CF7 is always loaded.
	 *
	 * @return bool
	 */
    static function is_excl_page(): bool {
	    self::load_excl_pages();
	    $uri  = $_SERVER['REQUEST_URI'];
	    $page = get_page_by_path( $uri );
	    return !empty($page) && in_array($page->ID, self::$excl_pages);
    }

	/**
	 * Do a one-time load of the excluded pages where CF7 is always loaded.
	 */
    static function load_excl_pages(): void {
    	if (is_null(self::$excl_pages)) {
    		$excl = MJKCommon_ACF::get(MJKCommon_ACF::cf7_enable_pages);
    		self::$excl_pages = $excl ? $excl : [];
	    }
    }
}


