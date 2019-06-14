<?php

/**
 * A spotlight based on a WordPress post.
 */
class MJKFP_SpotlightArticle extends MJKFP_Spotlight {

	/**
	 * Return the header for a post (usually its category).
	 *
	 * @param WP_Post $p
	 * @return string
	 */
    private function get_header(WP_Post $p): string {

        // Use our most specific category getter if NP is not active
        if (!JKNAPI::theme_dep_met('newspaper')) {
            $h = JKNTaxonomies::get_most_specific_category($p);
            $h = $h->name;

        // Otherwise use theirs
        } else {
            $cat_finder = new td_module_slide($p);
            $h = $cat_finder->get_category();
        }

        // Default "category" text if none was assigned to a post
        if (empty($h)) $h = 'Spotlight';

        // The Newspaper function returns a link we don't need. Remove all tags.
        $h = preg_replace('/<.*?>/', '', $h);

        // Return
        return $h;
    }

	/**
	 * Extract the post's header (category), title, and permalink and construct.
	 *
	 * @param WP_Post $p
	 */
    function __construct(WP_Post $p) {
        $header = $this->get_header($p);
        $body = $p->post_title;
        $link = get_permalink($p->ID);
        parent::__construct($header, $body, $link);
    }
}
