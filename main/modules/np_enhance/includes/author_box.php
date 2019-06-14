<?php

/**
 * Provides methods for the author page and author boxes.
 */
final class MJKNPEnhanceAuthorBox {

	/*
	 * =========================================================================
	 * Author boxes
	 * =========================================================================
	 */
    
    // Our constants
    const default_description = 'A contributor to <em>The Medium</em>.';
    
    // TODO Sort out the structure and CSS. This is older stuff.
    const cl_wrap = 'author-box-wrap';
    const cl_wrap_small = 'author-box-wrap_small';
    const cl_desc = 'desc_left';
    const cl_desc_small = 'desc_left_small';
    const cl_avatar = 'child_author_page_avatar';
    const cl_avatar_small = 'child_author_page_avatar_small';
    
    const cl_roles = 'mjk-npe-roles';
    const cl_role_highlight = 'mjk-npe-role-highlight';
    const cl_student_status = 'mjk-npe-student-status';


	/*
	 * =========================================================================
	 * General
	 * =========================================================================
	 */
    
    /**
     * Return true iff the user has a default avatar instead of a custom one.
     * This can be used to determine whether to use a small avatar.
     *
     * @param string $uid A user ID.
     * @return bool
     */
    static function has_default_avatar(string $uid): bool {
    	if (JKNAPI::plugin_dep_met('wpua')) {
		    return !WP_User_Avatar_Functions::has_wp_user_avatar($uid);

	    } else {
    		return get_avatar($uid) === FALSE;
	    }
    }
    
    /**
     * Return the formatted HTML for an author's avatar.
     *
     * @param string $uid A user ID.
     * @return string
     */
    static function format_avatar(string $uid): string {
        
        // Small size for default avatars
        if (self::has_default_avatar($uid)) {
            $width = 160;
            $class = self::cl_avatar_small;
        
        // Big size for custom avatars
        } else {
            $width = 350;
            $class = self::cl_avatar;
        }

        // Add the admin edit link when you hover over the image
        $edit_link = '';
        if (current_user_can('edit_users')) {
            $edit_link = sprintf('<a class="td-admin-edit" href="%s">edit</a>',
                get_edit_user_link($uid));
        }

        // Bundle it all up
        return sprintf('<div class="%s">%s%s</div>', $class, $edit_link,
                get_avatar($uid, $width));
    }
    
    /**
     * Return the formatted HTML for an author description.
     *
     * @param string $uid A user ID.
     * @return string
     */
    static function format_description(string $uid): string {

    	// Get the WP description
        $description = get_the_author_meta('description', $uid);

        // If there is one, allow line breaks and return
        if (!empty($description)) {
            return nl2br($description);

        // Otherwise return our default
        } else {
            return self::default_description;
        }
    }
    
    /**
     * Return the formatted <a> tag for a given academic year.
     *
     * @param JKNAcademicYear $ac_year
     * @return string
     */
    static function format_ac_year(JKNAcademicYear $ac_year): string {
        return sprintf('<a href="%1$s" title="Masthead %2$s">%2$s</a>',
            MJKMHAPI::url($ac_year), $ac_year->format());
    }

	/**
	 * Return the formatted HTML for a given list of roles.
	 *
	 * @param MJKMH_HeldRole[] $roles
	 * @return string
	 */
	static function format_roles(array $roles): string {

		$title_items = [];
		foreach($roles as $role) {
			$title_items[] = $role->title();
		}
		$titles = implode(', ', $title_items);
		$titles = JKNStrings::replace_last(',', ' &', $titles);

		$titles_html = sprintf('<span class="%s">%s</span>',
			self::cl_role_highlight, $titles);

		$link = self::format_ac_year(reset($roles)->ac_year());
		return sprintf('%s (%s)', $titles_html, $link);
	}
    
    /**
     * Return the formatted HTML for a given role.
     *
     * @param MJKMH_HeldRole $role
     * @return string
     */
    static function format_role(MJKMH_HeldRole $role): string {

        $title = sprintf('<span class="%s">%s</span>',
            self::cl_role_highlight,
	        $role->title());

        $link = self::format_ac_year($role->ac_year());
        return sprintf('%s (%s)', $title, $link);
    }


	/*
	 * =========================================================================
	 * Post author box
	 * =========================================================================
	 */

	/**
	 * Return the formatted HTML for a single author box for the given user ID.
	 *
	 * N.B. This adaptation of the tagDiv function should be kept up to date.
	 * It has one modification.
	 *
	 * @param string $pid The post ID.
	 * @param string $uid The user ID.
	 * @return string
	 */
    static function format_post_author_box(string $pid, string $uid): string {
        
        // This part is all tagDiv
        
        if (td_util::get_option('tds_show_author_box') == 'hide') {
            $html = '<div class="td-author-name vcard author" style="display: none"><span class="fn">';
            $html .= '<a href="' . get_author_posts_url($uid) . '">' . get_the_author_meta('display_name', $uid) . '</a>';
            $html .= '</span></div>';
            return $html;
        }
        
        $html = '';
        
        $hideAuthor = td_util::get_option('hide_author');

        if (empty($hideAuthor)) {

            $html .= '<div class="author-box-wrap">';
            if (current_user_can('edit_users')) {
                $html .= '<a class="td-admin-edit" target="_blank" href="'
                        . get_edit_user_link($uid) . '">edit</a>';
            }
            
            $html .= '<a itemprop="author" href="'
                    . get_author_posts_url($uid) . '">';
            
            $html .= get_avatar(get_the_author_meta('email', $uid), '96');
            $html .= '</a>';

            $html .= '<div class="desc">';
            $html .= '<div class="td-author-name vcard author"><span class="fn">';
            $html .= '<a itemprop="author" href="' . get_author_posts_url($uid)
                    . '">' . get_the_author_meta('display_name', $uid) . '</a>';
            $html .= '</span></div>';

            if (get_the_author_meta('user_url', $uid) != '') {
                $html .= '<div class="td-author-url">';
                $html .= '<a href="' . get_the_author_meta('user_url', $uid)
                        . '">' . get_the_author_meta('user_url', $uid)
                        . '</a></div>';
            }

            $html .= '<div class="td-author-description">';
            
            // MJK: 1/1: This is our addition
            
            $html .= self::format_post_roles($pid, $uid);
            
            // Back to tagDiv for the author social
            
            $html .= '<div class="td-author-social">';
            foreach (td_social_icons::$td_social_icons_array
                    as $td_social_id => $td_social_name) {
                
                //echo get_the_author_meta($td_social_id) . '<br>';
                $authorMeta = get_the_author_meta($td_social_id);
                if (!empty($authorMeta)) {

                    if ($td_social_id == 'twitter') {
                        if (filter_var($authorMeta, FILTER_VALIDATE_URL)) {
                            
                        } else {
                            $authorMeta = str_replace('@', '', $authorMeta);
                            $authorMeta = 'http://twitter.com/' . $authorMeta;
                        }
                    }
                    $html .= td_social_icons::get_icon($authorMeta,
                            $td_social_id, 4, 16);
                }
            }

	        $html .= '</div>';
            $html .= '</div>';
            $html .= '<div class="clearfix"></div>';
            $html .= '</div>'; // desc
            $html .= '</div>'; // author-box-wrap
        }
        
        return $html;
    }

	/**
	 * Return the formatted HTML of user roles for the given post and user.
	 *
	 * @param string $pid The post ID.
	 * @param string $uid The user ID.
	 * @return string
	 */
    static function format_post_roles(string $pid, string $uid): string {
	    $u = MJKMHAPI::user($uid);
	    $ytr = $u->years_to_roles();
	    $ays = array_keys($ytr);

	    // First try to get a preferred academic year
	    $pref_ay = $u->preferred_year();
	    if (!is_null($pref_ay)) {
	    	$ay = $pref_ay;

	    } else {

			// Get a candidate academic year
		    $rule = MJKNPE_ACF_Options::get(MJKNPE_ACF_Options::show_role_from);

		    if ($rule == 'current') {
			    $ay = reset($ays);

		    } else {
			    $dt = JKNTime::dt_timestamp(get_the_time('U', $pid));
			    $ay = JKNAcademicYear::make_from_dt($dt)->format();
		    }
	    }

	    // For some reason we only show the description if they have no roles
	    if (in_array($ay, $ays)) {
		    $roles = $ytr[$ay];
		    $content = self::format_roles($roles);
	    } else {
		    $content = self::format_description($uid);
	    }
        
        return sprintf('<div class="%s">%s</div>', self::cl_roles, $content);
    }


	/*
	 * =========================================================================
	 * Author page author box
	 * =========================================================================
	 */
    
    /**
     * Return the formatted HTML for the author page's two opening <div> tags.
     * The opening includes the avatar.
     * 
     * N.B. For the odd closing divs explanation, see the bottom of the
     * child theme's page-author-box.php.
     *
     * @param string $uid The user ID.
     * @return string
     */
    static function format_page_author_box(string $uid): string {
        
        // Smaller size for default avatars
        if (self::has_default_avatar($uid)) {
            $class_wrap = self::cl_wrap_small;
            $class_desc = self::cl_desc_small;
            
        // Bigger size for custom avatars
        } else {
            $class_wrap = self::cl_wrap;
            $class_desc = self::cl_desc;
        }

        // Avatar
        $avatar = self::format_avatar($uid);

        // Description area (includes roles)
        $desc = self::format_page_author_box_description($uid);
	    $desc = sprintf('<div class="%s">%s</div>', $class_desc, $desc);

	    // Clearfix
	    $clearfix = '<div class="clearfix"></div>';

	    // Bundle it up
        return sprintf(
        	'<div class="%s author-page">%s%s%s</div>',
            $class_wrap, $avatar, $desc, $clearfix);
    }
    
    /**
     * Return the formatted HTML for a page author box for the given user ID.
     *
     * N.B. This an adaptation of the tagDiv template should be kept up to date.
     *
     * @param string $uid The user ID.
     * @return string
     */
    static function format_page_author_box_description(string $uid): string {
        $css = self::format_page_css();

		// Extract parts of the user
	    $u = MJKMHAPI::user($uid);
	    $roles = $u->by_role();

	    // Get the roles/student status HTML and the description
        $roles_html = self::format_page_author_box_roles($u, $roles);
        $description = self::format_description($uid);

        // Slightly different layouts for roles / no roles
	    if (!empty($roles)) {

	    	// Nullify the description if it's the default one and we have roles
	    	if ($description == self::default_description) $description = '';
		    return sprintf('%s%s%s', $css, $roles_html, $description);

	    } else {
	    	return sprintf('%s%s%s', $css, $description, $roles_html);
	    }
    }
    
    /**
     * Return the formatted HTML for a page author box's roles section.
     * This consists of a Masthead title/link and a roles list including the
     * student status if there are roles, otherwise just the student status.
     *
     * @param MJKMH_User $u A masthead user.
     * @param MJKMH_HeldRole[] $roles The user's roles for this box.
     * @return string
     */
    static function format_page_author_box_roles(MJKMH_User $u,
	    array $roles): string {

	    $roles_html = '';
	    $mastheading = '';

    	// If they have roles
	    if (!empty($roles)) {

		    // Link to main page for the heading
		    $url = MJKMHAPI::url();
		    if (is_null($url)) {
			    $link = 'Masthead';
		    } else {
			    $link = sprintf(
				    '<a href="%1$s" title="%2$s">%2$s</a>', $url, 'Masthead');
		    }
		    $mastheading = sprintf('<h3 style="margin-top: 0;">%s</h3>', $link);

		    // Sort the roles by the one with the earliest year (hence reverse)
		    // Break ties using role priority
		    uksort($roles, function(string $a, string $b) use ($u, $roles): int {
			    $year_a = reset($roles[$a]);
			    $year_b = reset($roles[$b]);
			    if ($year_a != $year_b) return $year_b <=> $year_a;

			    $pri_a = $u->role_by_alias($a)->priority();
			    $pri_b = $u->role_by_alias($b)->priority();
			    return $pri_a <=> $pri_b;
		    });

		    // Turn into li items
		    foreach($roles as $title => $ac_years) {
			    $year_links = array_map([__CLASS__, 'format_ac_year'], $ac_years);
			    $roles_html .= sprintf('<li><span class="%s">%s</span> (%s)</li>',
				    self::cl_role_highlight, $title, implode(', ', $year_links));
		    }
	    }

        // Throw in the student status
        $student_status = $u->status();
        if (!empty($student_status)) {
            $roles_html .= sprintf('<li class="%s">%s</li>',
                self::cl_student_status, $student_status);
        }

        // Wrap it all up
        $roles_list = sprintf('<div class="%s"><ul>%s</ul></div>',
                self::cl_roles, $roles_html);
        
        return $mastheading . $roles_list;
    }
    
    /**
     * Return the formatted CSS for the author page box.
     *
     * @return string
     */
    static function format_page_css(): string {
        return JKNCSS::tag('
            
            .'.self::cl_desc.' a, .'.self::cl_desc_small.' a {
                color: #222 !important;
            }
            
            .'.self::cl_desc.' a:hover, .'.self::cl_desc_small.' a:hover {
                text-decoration: underline !important;
            }
            
            .'.self::cl_roles.' ul li {
                font-size: 12px;
                list-style: none;
                margin-left: 0;
            }

            li.'.self::cl_student_status.' {
                font-style: italic;
            }
        ');
    }
}
