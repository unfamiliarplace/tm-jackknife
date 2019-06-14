<?php

/**
 * Adds drop caps to articles.
 */
class MJKEnrich_DropCaps {

    const cl_dc = 'mjk-enrich-drop-cap';

	/**
	 * Add the ACF filtes and the content filter.
	 */
    static function set_up(): void {

	    // Add ACF
	    require_once 'acf_api/registry.php';
	    MJKEnrich_ACF_Drop_Caps::add_filters();

	    // Add content filter
	    add_filter('the_content', [ __CLASS__, 'add_class'], 10);
    }

	/**
	 * Filter the content, adding a drop cap class to the first <p> tag.
	 * Also insert the necessary CSS.
	 *
	 * @param string $content
	 * @return string
	 */
    static function add_class(string $content): string {
        global $post;

        // Short-circuit if this is not a post
        if (is_admin() || ($post->post_type != 'post')) return $content;

	    // End if drop caps are not on
	    $on = (bool) MJKEnrich_ACF::get(MJKEnrich_ACF::use_drop_cap);
	    if (empty($on)) return $content;

	    // End if any of this post's categories are drop cap exclusions
	    $excl = MJKEnrich_ACF::get(MJKEnrich_ACF::no_drop_cap_cats);
	    foreach(wp_get_post_categories($post->ID, ['fields' => 'ids']) as $cat) {
		    if (in_array($cat, $excl)) return $content;
	    }

	    // End if this post is set not to use drop caps
	    $post_dc_on = MJKEnrich_ACF_Drop_Caps::get(
		    MJKEnrich_ACF_Drop_Caps::use_drop_cap, $post->ID);
	    if (empty($post_dc_on)) return $content;

        // Otherwise, on we go with replacing the first
	    // <p>...</p> with <p class="med-drop-cap">...</p>

        // Pattern that matches a <p>...</p> tag with whatever attributes it has
        $first_p_pattern = '/<p( ?.*?)>(.*?)<\/p>/';

        // Find said first paragraph
        preg_match('/<p( ?.*?)>(.*?)<\/p>/', $content, $fp_matches);
        if (!isset($fp_matches[0])) return $content;
        $first_p = $fp_matches[0];

        // If it already has at least one class
        if (preg_match('/.*?class *?= *?".*?".*?/', $first_p)) {		
            $pattern = '/<p(.*?)class *?= *?"(.*?)"(.*?)>(.*?)<\/p>/';		
            $replace = '<p $1 class="$2 '.self::cl_dc.'" $3>$4</p>';

        // Otherwise
        } else {
            $pattern = $first_p_pattern;
            $replace = '<p $1 class="'.self::cl_dc.'">$2</p>';
        }

        // Do the regex replace using whichever pattern we decided on
        $content = preg_replace($pattern, $replace, $content, 1);

        // Insert styling
        self::insert_style();

        // Return after all that
        return $content;
    }

	/**
	 * Insert the formatted CSS.
	 */
    static function insert_style(): void {
        echo JKNCSS::tag('
        
            /* Drop caps */            
            p.'.self::cl_dc.':first-letter {
                float:left;
                font-size: 90.5px;
                margin-right:0.14em;
                margin-top: 12.25px;
                line-height: 55px;
            }
        ');
    }
}
