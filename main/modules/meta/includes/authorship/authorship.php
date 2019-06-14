<?php

/**
 * Adds authorship fields to each post.
 */
final class MJKMeta_Authorship {

	/**
	 * Add the filters for the ACF group.
	 */
    static function set_up(): void {
        require_once 'acf_api/registry.php';
        MJKMeta_ACF_Auth::add_filters();
    }
}
