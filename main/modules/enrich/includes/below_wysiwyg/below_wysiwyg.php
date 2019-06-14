<?php

/**
 * Adds an ACF group below the WYSIWYG editor that corrections, files, and
 * updates will use.
 */
class MJKEnrich_Below_WYSIWYG {

	/**
	 * Add the filters for the group.
	 */
    static function set_up() {
        require_once 'acf_api/registry.php';
        MJKEnrich_ACF_Below_WYSIWYG::add_filters();
    }
}
