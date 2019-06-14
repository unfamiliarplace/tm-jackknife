<?php

/**
 * Adds a subtitle field to each post.
 */
class MJKMeta_Subtitles {

	/**
	 * Add the ACF group filters.
	 */
    static function set_up(): void {
        require_once 'acf_api/registry.php';
        MJKMeta_ACF_Subtitle::add_filters();
    }
}
