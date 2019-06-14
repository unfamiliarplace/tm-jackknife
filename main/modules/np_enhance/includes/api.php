<?php

/**
 * Provides an interface between tagDiv templates and meta & masthead data.
 */
final class MJKNPEnhanceAPI {

	/*
	 * =========================================================================
	 * Constants
	 * =========================================================================
	 */

    // Our constants
    const cred_sprite_fname         = 'cred_sprite.png';
    const trans_fname               = 'trans.png';
    
    const icon_width                = 12;
    const icon_height               = 13;
    
    const cl_vi                     = 'mjk-npe-vi';
    const cl_feat_caption           = 'mjk-npe-feat-caption';
    
    const cl_date                   = 'mjk-npe-date';
    const cl_a_white                = 'mjk-npe-a-white';
    const cl_a_dark                 = 'mjk-npe-a-dark';
    
    const default_feat_title        = 'Click to view the image directly.';
    
    // tagDiv-related constants. These mirror TD classes; do not change
    const td_cl_author              = 'td-post-author-name';
    const td_cl_author_box          = 'td-author-box';
    const td_cl_date                = 'td-post-date';
    const td_cl_subtitle            = 'td-post-sub-title';
    const td_cl_white               = '-white';
    const td_cl_visibility_hidden   = 'td-visibility-hidden';

    // Categories that are mainly photos or photo galleries
	static $mainly_photo_cats;


	/*
	 * =========================================================================
	 * Directories
	 * =========================================================================
	 */
    
    /**
     * Return the URL of the no-thumb directory.
     *
     * @return string
     */
    static function no_thumb_dir(): string {
        $url = sprintf('%s/assets/images/no-thumb', JKNAPI::murl());
	    return JKNCDN::url($url);
    }

    /**
     * Return the URL of the credits sprite.
     *
     * @return string
     */
    static function cred_sprite(): string {
        $url = sprintf('%s/assets/images/cred/%s', JKNAPI::murl(),
                self::cred_sprite_fname);
        return JKNCDN::url($url);
    }

    /**
     * Return the URL of the transparent png.
     *
     * @return string
     */
    static function trans(): string {
        $url = sprintf('%s/assets/images/cred/%s', JKNAPI::murl(),
                self::trans_fname);
	    return JKNCDN::url($url);
    }


	/*
	 * =========================================================================
	 * Image and video wrappers
	 * =========================================================================
	 */
    
    /**
     * Return NP's image HTML with our no-thumb placeholders instead of theirs.
     * TODO make more future-proof using regex
     *
     * @param string $thumbType
     * @param td_module $mod A tagDiv module.
     * @return string
     */
    static function image(string $thumbType, td_module $mod): string {
        $html = $mod->get_image($thumbType);
        $old_url = get_template_directory_uri() . '/images/no-thumb';
        $rep = str_replace($old_url, self::no_thumb_dir(), $html);
        return JKNCDN::images($rep);
    }

    /**
     * Return NP's video HTML with custom YouTube tags.
     * TODO make more future-proof using regex
     *
     * @param string $thumbType
     * @param td_module $mod A tagDiv module.
     * @return string
     */
    static function video_tags(string $thumbType, td_module $mod): string {
        $html = $mod->get_image($thumbType);
        if (empty($html)) return '';
        
        // Bail if this is not a YouTube video
        if (strpos($html, 'td_youtube_player') === false) return $html;
        
        // Otherwise use our tags
        $child_settings = 'vq=hd720&rel=0&modestbranding=1&autoplay=1';
        return str_replace('vq=hd720', $child_settings, $html);
    }


	/*
	 * =========================================================================
	 * Icons and classes
	 * =========================================================================
	 */
    
    /**
     * Return a class modified after applying the white class to it.
     * This is for use on dark backgrounds.
     *
     * @param string $cl The existing class.
     * @param bool $white Whether to apply the white class.
     * @return string
     */
    static function white_class(string $cl, bool $white): string {
        $cl_white = ($white) ? self::td_cl_white : '';
        return $cl . $cl_white;
    }
    
    /**
     * Return the original class plus the white class in a single string.
     * This is for use on dark backgrounds.
     *
     * @param string $cl The existing class.
     * @param bool $white Whether to add the white class.
     * @return string
     */
    static function add_white_class(string $cl, bool $white): string {
        $cl_white = self::white_class($cl, $white);
        return ($white) ? $cl = sprintf('%s %s', $cl, $cl_white) : $cl;
    }

    /**
     * Return an <img> tag with an appropriate meta icon.
     *
     * @param string $cl The class to apply. This determines the sprite too.
     * @param string $alt The alt text to apply.
     * @param bool $white Whether to apply the white class.
     * @return string
     */
    static function icon(string $cl, string $alt, bool $white=false): string {
        
        // The fixed attributes
        $src = self::trans();
        $title = $alt;
        $cl_general = 'mne-icon';
        
        // The dependent attributes
        $cl = self::white_class($cl, $white);
        
        // Throw it all together
        $tag = sprintf('<img src="%s" alt="%s" title="%s" class="%s %s"'
                . ' width="%s" height="%s" />', $src, $alt, $title,
                $cl_general, $cl, self::icon_width, self::icon_height);
        
        return $tag;
    }


	/*
	 * =========================================================================
	 * Subtitle
	 * =========================================================================
	 */

	/**
	 * Return the subtitle of the given post.
	 * If $use_excerpt, fall back on the post excerpt.
	 *
	 * @param WP_Post $post
	 * @param bool $use_excerpt Whether to use an excerpt if no subtitle exists.
	 * @return string
	 */
    static function post_subtitle(WP_Post $post,
            bool $use_excerpt=false): string {

    	// Try for a subtitle
        $subtitle = MJKMeta_ACF_Subtitle::get(
                MJKMeta_ACF_Subtitle::subtitle, $post->ID);

        // If there's no subtitle and we're allowed to use an excerpt, do so
        if (empty($subtitle)){
            $subtitle = ($use_excerpt) ? get_the_excerpt($post->ID) : '';
        }
        
        return $subtitle;
    }

	/**
	 * Return the subtitle of the given tagDiv module.
	 * This is in order to use the tagDiv version of get_excerpt.
	 * If $use_excerpt, fall back on the module excerpt.
	 *
	 * @param td_module $mod
	 * @param bool $use_excerpt Whether to use an excerpt if no subtitle exists.
	 * @return string
	 */
    static function module_subtitle(td_module $mod,
            bool $use_excerpt=false): string {

	    // Try for a subtitle
        $subtitle = MJKMeta_ACF_Subtitle::get(
                MJKMeta_ACF_Subtitle::subtitle, $mod->post->ID);

	    // If there's no subtitle and we're allowed to use an excerpt, do so
        if (empty($subtitle)){
            $subtitle = ($use_excerpt) ? $mod->get_excerpt() : '';
        }
        
        return $subtitle;
    }

	/**
	 * Return formatted HTML for the subtitle of the given module or post.
	 * If $use_excerpt, fall back on the excerpt.
	 *
	 * This lets templates be agnostic about which subtitle function to call.
	 *
	 * @param WP_Post|td_module $mod_or_post Either a post or a tagDiv module.
	 * @param bool $use_excerpt Whether to use an excerpt if no subtitle exists.
	 * @return string
	 */
    static function subtitle($mod_or_post, bool $use_excerpt=false): string {
        
        $subtitle = '';
        $class = get_class($mod_or_post);

        // Determine which kind we have and call the appropriate function
        if ($class == 'WP_Post') {
            $subtitle = self::post_subtitle($mod_or_post, $use_excerpt);
        } elseif (JKNStrings::starts_with ($class, 'td_module')) {
            $subtitle = self::module_subtitle($mod_or_post, $use_excerpt);
        }

        if (empty($subtitle)) return '';
        return sprintf('<p class="%s">%s</p>', self::td_cl_subtitle, $subtitle);
    }


	/*
	 * =========================================================================
	 * Credit
	 * =========================================================================
	 */

	/**
	 * Return formatted HTML for the given post's date.
	 * $white is whether to use white classes (for dark backgrounds)
	 * $show_vi is whether to add the volume & issue info
	 *
	 * TODO Make white class a second class, not different, for better CSS.
	 * TODO CSS prefixes in general should be more regular.
	 *
	 * @param WP_Post $p
	 * @param bool $white Whether to apply the white class.
	 * @param bool $white Whether to show the Volume & Issue info explicity.
	 * @return string
	 */
    static function date(WP_Post $p, bool $white=false,
            bool $show_vi=false): string {
        
        $html = '';

        // From Newspaper
        $visibility_class = '';
        if (td_util::get_option('tds_p_show_date') == 'hide') {
            $visibility_class = ' ' . self::td_cl_visibility_hidden;
        }

        $udate = get_the_time('U', $p->ID);

        // Compile the img tag for the icon
        $img_tag = self::icon('mne-icon-date', 'Date published', $white);

        // Compile the visible date text
        $date_text = get_the_time(get_option('date_format'), $p->ID);
        $date_class = self::add_white_class(self::cl_date, $white);
        
        if (!$show_vi) {
            $vi_url = MJKVIAPI::get_post_vi_url($p);
            $vi_title = MJKVIAPI::get_post_vi_title($p);
            
            $date_whole = sprintf('<a class="%s" href="%s" title="%s">%s</a>',
                    $date_class, $vi_url, $vi_title, $date_text);
            
        } else {
            $vi_text = ($show_vi) ? self::get_vi_text($p) : '';
            $vi_format = self::format_vi_text($vi_text, $white);
            
            $date_whole = sprintf('%s%s', $date_text, $vi_format);
        }

        // Compile the time tag
        $time_tag = sprintf('<time itemprop="dateCreated" class="entry-date'
                . ' updated %s" datetime="%s">%s%s</time>',
                $visibility_class, date(DATE_W3C, $udate),
                $img_tag, $date_whole);

        // Compile the meta tag
        $meta_tag = sprintf('<meta itemprop="interactionCount"'
                . ' content="UserComments:%s" />',
                get_comments_number($p->ID));

        // Compile the date div
        $div_cls = self::add_white_class(self::td_cl_date, $white);
        $html .= sprintf('<div class="%s">%s%s</div>',
                $div_cls, $time_tag, $meta_tag);

        return $html;
    }

    /**
     * Return formatted HTML for a given user.
     * Add the given item property if there is one.
     *
     * @param array $user An array of WP userdata.
     * @param string|null $itemprop The itemprop to add, if any.
     * @return string
     */
    static function format_user(array $user, string $itemprop=null): string {
        $src = sprintf('/author/%s', $user['user_nicename']);
        $name = $user['display_name'];
        return sprintf('<a href="%1$s" itemprop="%2$s" title="%3$s">%3$s</a>',
                $src, $itemprop, $name);
    }
    
    /**
     * Return formatted HTML for a given contributor.
     * N.B. This is format_user but with the 'author' itemprop specified.
     *
     * @param array $user An array of WP userdata.
     * @return string
     */
    static function format_author(array $user): string {
        return self::format_user($user, 'author');
    }
    
    /**
     * Return the given <a> tag with the given itemprop inserted.
     *
     * @param string $tag The <a> tag.
     * @param string $prop The itemprop to insert.
     * @return string
     */
    static function add_itemprop(string $tag, string $prop): string {
        $itemprop = sprintf('itemprop="%s"', $prop);
        return str_replace('<a', '<a' . $itemprop, $tag);
    }
    
    /**
     * Return formatted HTML for the given list of strings and the given icon.
     * N.B. TD author name divs use both a normal class and a white class.
     *
     * @param array $contribs An array of WP userdata subarrays.
     * @param string $icon The icon tag to insert.
     * @param bool $white Whether to apply the white class.
     * @return string
     */
    static function contributors(array $contribs, string $icon,
            bool $white=false): string {
        
        $inner = implode(', ', $contribs);
        $inner = JKNStrings::replace_last(',', ' &', $inner);
        $cl_white = self::white_class(self::td_cl_author, $white);
        return sprintf('<div class="%s %s">%s%s</div>',
                self::td_cl_author, $cl_white, $icon, $inner);
    }

    /**
     * Return formatted HTML for the list of photographers.
     *
     * @param WP_Post $post
     * @param bool $white Whether to apply the white class.
     * @param bool $include_outside Whether to include outside photo sources.
     * @param bool $only_outside Whether to only show outside photo sources.
     * @return string
     */
    static function photographers(WP_Post $post, bool $white=false,
			bool $include_outside=true, bool $only_outside=false): string {

    	// Set up
    	$pid = $post->ID;
    	$users = [];
    	$outside = [];

    	// Get all the sources we're using
    	if (!$only_outside) $users = MJKMetaAPI::photographers($pid);
	    if ($include_outside) $outside = MJKMetaAPI::outside_photo_sources($pid);
        if (empty($users) && empty($outside)) return '';

        // Format and merge them into comparability
        $user_html_pieces = array_map([__CLASS__, 'format_author'], $users);
        $all_pieces = array_merge($user_html_pieces, $outside);

        // Add icon and do overall format
        $icon = self::icon('mne-icon-photo', 'Photographer', $white);
        return self::contributors($all_pieces, $icon, $white);
    }
    
    /**
     * Return formatted HTML for the list of videographers.
     *
     * @param WP_Post $post
     * @param bool $white Whether to apply the white class.
     * @return string
     */
    static function videographers(WP_Post $post, bool $white=false): string {
        $users = MJKMetaAPI::videographers($post->ID);
        if (empty($users)) return '';
        $user_html_pieces = array_map([__CLASS__, 'format_author'], $users);
        $icon = self::icon('mne-icon-video', 'Videographer', $white);
        return self::contributors($user_html_pieces, $icon, $white);
    }

    /**
     * Return formatted HTML for the list of authors.
     *
     * @param WP_Post $post
     * @param bool $white Whether to apply the white class.
     * @return string
     */
    static function authors(WP_Post $post, bool $white=false): string {
        $users = MJKMetaAPI::authors($post->ID);
        if (empty($users)) return '';
        $user_html_pieces = array_map([__CLASS__, 'format_author'], $users);
        $icon = self::icon('mne-icon-author', 'Author', $white);
        return self::contributors($user_html_pieces, $icon, $white);     
    }
    
    /**
     * Return formatted HTML for the list of notes contributors.
     *
     * @param WP_Post $post
     * @param bool $white Whether to apply the white class.
     * @return string
     */
    static function notes_contributors(WP_Post $post,
            bool $white=false): string {
        
        $users = MJKMetaAPI::notes_contributors($post->ID);
        if (empty($users)) return '';
        $user_html_pieces = array_map([__CLASS__, 'format_author'], $users);
        $icon = ' notes from ';
        return self::contributors($user_html_pieces, $icon, $white);
    }

	/**
	 * Return formatted HTML for all credits (e.g. on a single article).
	 *
	 * @param WP_Post $p The current post.
	 * @param bool $white Whether to use the white class (on a dark background).
	 * @param string $glue An optional separator.
	 * @return string
	 */
	static function all_credits(WP_Post $p, bool $white=false,
			string $glue=''): string {

		$credits = [
			self::authors($p, $white),
			self::notes_contributors($p), $white,
			self::photographers($p, $white),
			self::videographers($p, $white)
		];

		return implode($glue, $credits);
	}

	/**
	 * Return formatted HTML for succinct credits (e.g. in a category listing).
	 * This consists of the main authors and, if the post is a video, the
	 * videographers.
	 *
	 * @param WP_Post $p The current post.
	 * @param bool $white Whether to use the white class (on a dark background).
	 * @param string $glue An optional separator.
	 * @return string
	 */
	static function succinct_credits(WP_Post $p, bool $white=false,
			string $glue=''): string {

		// Always show main authors (or notes contributors if no authors)
		$authors = self::authors($p, $white);
		if (empty($authors)) $authors = self::notes_contributors($p, $white);
		$credits = [$authors];

		// Add photographers if it's a "mainly photos" post
		$photos = false;

		// Get the mainly photos categories if not yet gotten
		if (is_null(static::$mainly_photo_cats)) {

			$cats = MJKNPE_ACF_Options::get(MJKNPE_ACF_Options::mainly_photo_cats);
			static::$mainly_photo_cats = $cats;
		}

		// Show photos if individual post is set to mainly show photos
		if (MJKVI_ACF_P::get(MJKNPE_ACF::mainly_photo, $p->ID)) $photos = true;

		// Dhow photos if category is set to mainly show photos
		foreach(wp_get_post_categories($p->ID, ['fields' => 'ids']) as $cat) {
			if (in_array($cat, static::$mainly_photo_cats)) $photos = true;
			break;
		}

		if ($photos) $credits[] = self::photographers($p, $white);

		// Add videographers if it's a video post
		if (get_post_format($p->ID) == 'video') {
			$credits[] = self::videographers($p, $white);
		}

		return implode($glue, $credits);
	}


	/*
	 * =========================================================================
	 * Author boxes
	 * =========================================================================
	 */
    
    /**
     * Return the formatted HTML for all the author boxes for a post.
     *
     * @param WP_Post $post
     * @return string
     */
    static function all_author_boxes(WP_Post $post): string {
        $html = '';
        
        $pid = $post->ID;
        
        // Get all types of contributors
        $authors            = MJKMetaAPI::authors($pid);
        $notes_contributors = MJKMetaAPI::notes_contributors($pid);
        $photographers      = MJKMetaAPI::photographers($pid);
        $videographers      = MJKMetaAPI::videographers($pid);
        
        // Merge into one
        $all_users = array_merge($authors, $notes_contributors,
                $photographers, $videographers);
        
        // Unique
        $all_user_IDs = array_unique(array_map(
        	function(array $u): string { return $u['ID']; },
	        $all_users));
        
        // Concatenate the single box for each one
        foreach($all_user_IDs as $uid) {
            $html .= MJKNPEnhanceAuthorBox::format_post_author_box($pid, $uid);
        }
        
        return $html;
    }


	/*
	 * =========================================================================
	 * Featured images
	 * =========================================================================
	 */
    
    /**
     * Return the caption for the featured image on a single post template.
     * Credit: stackoverflow.com/questions/13850313
     *
     * @param WP_Post $post
     * @return string
     */
    static function featured_image_caption(WP_Post $post): string {
        
        // Identify the image
        $thumb_id = get_post_thumbnail_id($post->ID);
        $thumb = get_posts(['p' => $thumb_id, 'post_type' => 'attachment']);

        // Bail if no image or no caption
        if (!($thumb && isset($thumb[0]) && $thumb[0]->post_excerpt)) return '';
        
        // Put together an icon and the caption
        $icon = self::icon('mne-icon-photo', 'Caption');
        $caption = $thumb[0]->post_excerpt;
        return sprintf('<div class="%s">%s%s</div>',
                self::cl_feat_caption, $icon, $caption);
    }

    /**
     * Return the given <img> tag with a generic featured image title.
     * This overrides a default title (which is set to the filename on upload).
     *
     * @param string $img_tag
     * @return string The image
     */
    static function generic_featured_image_title(string $img_tag): string {
        $re = '/title ?= ?".*?"/';
        $new = sprintf('title="%s"', self::default_feat_title);
        return preg_replace($re, $new, $img_tag);
    }


	/*
	 * =========================================================================
	 * Volume & Issue
	 * =========================================================================
	 */

	/**
	 * Return plain text given post's volume and issue.
	 *
	 * @param WP_Post $post
	 * @param bool $white Whether to apply the white class.
	 * @return string
	 */
    static function get_vi_text(WP_Post $post, bool $white=false): string {

        // Bail if VI is not active
        if (!JKNAPI::module_dep_met('vi')) return null;

        $text = '';

        // Fetch volume and issue
        list($vol, $iss) = MJKVIAPI::get_post_vi($post);

        // Only need to return something if a volume is found
        if (!empty($vol)) {

            // If we have an issue, gfo with Vol, iss
            if (!empty($iss)) {
                $text .= $iss->format_vol_iss_a($short_vol_name = true);

                // Otherwise, just Vol
            } else {
                $text .= $vol->format_a($short_name = true);
            }
        }

        return $text;
    }

	/**
	 * Return formatted HTML for the given post's volume and issue.
	 *
	 * @param string $vi_text The Volume & Issue text.
	 * @param bool $white Whether to apply the white class.
	 * @return string
	 */
    static function format_vi_text(string $vi_text, bool $white=false): string {
        $class = self::white_class(self::cl_vi, $white);
        return sprintf('<span class="%s">(%s)</span>', $class, $vi_text);
    }
}
