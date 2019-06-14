<?php

/**
 * Custom post type for Front Page cards.
 */
class MJKFP_CPT_FPCard extends JKNCPT {

	/*
	 * =========================================================================
	 * Identification
	 * =========================================================================
	 */
    
    /**
     * Return the name of this post type.
     *
     * @return string
     */
    static function name(): string { return 'Front Page card'; }
    
    /**
     * Return the ID of this post type.
     *
     * @return string
     */
    static function id(): string { return 'fpcard'; }
    
    /**
     * Return the description of this post type.
     *
     * @return string
     */
    static function description(): string {
        return 'An assortment of front page (spotlight + slides) content';
    }
    
    /**
     * Return true: this post type uses a settings page (edit screen).
     *
     * @return bool
     */
    static function has_edit_screen(): bool { return true; }


	/*
	 * =========================================================================
	 * Set up
	 * =========================================================================
	 */
    
    /**
     * Return the registration args with feed rewrites turned off.
     *
     * @return array
     */
    static function register_args(): array {
        return ['rewrite' => ['feeds' => false]];
    }


	/*
	 * =========================================================================
	 * Save
	 * =========================================================================
	 */

	/**
	 * Derive a title for this website.     *
	 * TODO Double-check. I don't know why this works for first-time post saves.
	 *
	 * @param WP_Post $p
	 * @return string
	 */
    static function derive_title(WP_Post $p): string {
        $date = get_the_time('Y-m-d \a\t H:i', $p->ID);
        return sprintf('Card starting on %s', $date);
    }

	/**
	 * Save the title, but not the sort number.
	 * Override because we don't need sort nums. The title inherently sorts.
	 *
	 * @param WP_Post $p
	 */
    static function do_save_actions(WP_Post $p): void { self::save_title($p); }

	/**
	 * Dummy function: Return a null sorting number.
	 *
	 * @param WP_post $p
	 * @return int|null
	 */
    static function derive_sort_num(WP_post $p): ?int { return null; }
}
