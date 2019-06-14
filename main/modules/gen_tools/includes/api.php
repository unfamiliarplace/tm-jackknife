<?php

/**
 * Provides mechanisms for interacting with the Generation Tools system.
 * 
 * To register a page, supply:
 *      id          string: azAZ09_
 *      name        string
 *      source      string: fills {} in "The data is sourced from {}."
 *      renderer    JKNRenderer
 *      settings    (optional) array of JKNSettingsPages,
 *                  each of which will trigger generation when it is saved
 */
final class MJKGTAPI {

	/*
	 * =========================================================================
	 * Pages
	 * =========================================================================
	 */
    
    /**
     * Return a page given its ID.
     *
     * @param string $id
     * @return MJKGenToolsPage
     */
    static function page(string $id): MJKGenToolsPage {
        return JKNAPI::module()->page($id);
    }
    
    /**
     * Create a page, add it to the Gen Tools catalogue, and return it.
     * This should be done on the init action to ensure this exists.
     *
     * @param array $args
     * @return MJKGenToolsPage
     */
    static function add_page(array $args): MJKGenToolsPage {

    	// If it's a Switch renderer, we need to make it a Switch page
    	if (is_subclass_of($args['renderer'], 'JKNRendererSwitch')) {
    		$page = new MJKGenToolsPageSwitch($args);
	    } else {
    		$page = new MJKGenToolsPage($args);
	    }

	    JKNAPI::module()->add_page($page);
        return $page;
    }
    
    /**
     * Return all the pages currently in the Gen Tools catalogue.
     * This cannot reliably be run sooner than the init action.
     *
     * @return MJKGenToolsPage[]
     */
    static function pages(): array { return JKNAPI::module()->pages(); }


	/*
	 * =========================================================================
	 * Formatting
	 * =========================================================================
	 */

	/**
	 * Return a date in a standard format.
	 *
	 * @param int $ts The timestamp.
	 * @return string
	 */
	static function format_date(int $ts): string {
		$date_str = date('l, F j, Y \a\t g:i a', $ts);
		$date_str = substr($date_str, 0, -1) . '.m.'; // a.m., p.m.
		$date_str = str_replace(':00', '', $date_str); // no 12:00
		return $date_str;
	}

	/**
	 * Return a formatted revision date for the post with the given ID.
	 *
	 * @param string $pid
	 * @return string
	 */
	static function format_latest_post_revision(string $pid): string {

		// Get the latest revision
		$revs = wp_get_post_revisions(get_post($pid));
		$rev_gmt = reset($revs)->post_date_gmt;

		// determine its timestamp and format
		$rev_ts = strtotime($rev_gmt . ' GMT');
		return sprintf('%s — %s ago', self::format_date($rev_ts),
			human_time_diff($rev_ts));
	}
}
