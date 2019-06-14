<?php

/**
 * Renders the updates page.
 */
class MJKEnrichUpdatesRenderer extends JKNRendererSwitch {
    
    // This is the earliest year that we have updates in the system
    const cutoff_ac_year = 2014;

    const msg_empty = '<h4>No updates are recorded for this volume.</h4>';
    
    // CSS
    const cl_main = 'mjk-gt-update-main';
    const cl_update_table = 'mjk-gt-update-table';
    const cl_update_post = 'mjk-gt-update-post';
    const cl_update_post_date = 'mjk-gt-update-post-date';
    const cl_update_date = 'mjk-gt-update-date';
    const cl_update_nature = 'mjk-gt-update-nature';

	/**
	 * Return the formatted HTML for one academic year of udpates.
	 *
	 * @param string $ac_year
	 * @return string
	 */
    static function content_option(string $ac_year): string {
        $html = '';
        
        // Get the posts from this volume
        $ay = JKNAcademicYear::make_from_format($ac_year);
        $vol = MJKVIAPI::get_volume_by_academic_year($ay);
        $posts = $vol->get_posts();
        
        // Limit it to the ones with updates
        $posts = self::posts_with_updates($posts);
        
        // Rotate so that updates are the key
        $updates = self::posts_to_updates($posts);
        
        // If no updates, render the empty message
        if (empty($updates)) {
            $html .= self::msg_empty;
            
        // Otherwise render our corrections
        } else {
            $html .= self::render_updates($updates);
        }
        
        return $html;
    }

	/**
	 * Return an array of academic years for the select field.
	 *
	 * @param array $args
	 * @return array
	 */
    static function switch_options(array $args=[]): array {
	    $options = [];

	    $ac_years = MJKCommonTools::academic_years($from=self::cutoff_ac_year);
	    foreach(array_keys($ac_years) as $ac_year) {
	    	$ay = JKNAcademicYear::make_from_format($ac_year);
		    $vol = MJKVIAPI::get_volume_by_academic_year($ay);

		    $options[] = [
			    'value'     => $ac_year,
			    'display'   => sprintf('%s (%s)', $vol->get_name(), $ac_year)
		    ];
	    }

	    return array_reverse($options);
    }

	/**
	 * Return a more user-friendly option key.
	 *
	 * @return string
	 */
	static function option_key(): string { return 'acyear'; }


	/*
	 * =========================================================================
	 * Specific to this page
	 * =========================================================================
	 */

	/*
	 * =========================================================================
	 * Data
	 * =========================================================================
	 */
    
    /**
     * Return only the posts that have updates.
     *
     * @param WP_Post[] $posts
     * @return WP_Post[]
     */
    static function posts_with_updates(array $posts): array {
        $posts_with_updates = [];
        
        // Only keep those that have updates
        foreach($posts as $p) {
            if (MJKEnrich_ACF_Updates::have_rows(MJKEnrich_ACF_Updates::updates, $p->ID)) {
                $posts_with_updates[] = $p;
            }
        }
        
        return $posts_with_updates;
    }
    
    /**
     * Take an array of posts and return an array of [update, post] pairs.
     *
     * @param WP_Post[] $posts
     * @return array
     */    
    static function posts_to_updates(array $posts): array {
        $updates = [];
        
        foreach($posts as $p) {
            $p_updates = MJKEnrich_Updates::gather_updates($p->ID);
            foreach($p_updates as $update) {
                $updates[] = ['update' => $update, 'post' => $p];
            }
        }
        
        // Sort updates by timestamp, newest to oldest
	    usort($updates, function(array $a, array $b): int {
		    $pri_a = $a['update']->dt();
		    $pri_b = $b['update']->dt();
		    return $pri_b <=> $pri_a;
	    });

        return $updates;
    }


	/*
	 * =========================================================================
	 * Formatting
	 * =========================================================================
	 */
    
    /**
     * Return the HTML for an array of updates.
     * The array is of the form [ ['update' => c, 'post' => p] ]
     *
     * @param MJKEnrich_Update[] $updates
     * @return string
     */
    static function render_updates(array $updates): string {
        $html = '';
        
        // Concatenate each update's rendering
        foreach($updates as $update_pair) {        
            $update = $update_pair['update'];
            $p = $update_pair['post'];
            $html .= sprintf('%s', self::render_update($update, $p));
        }
        
        // Wrap in a div
        $html = sprintf('<div class="%s">%s</div>', self::cl_main, $html);
        
        return $html;
    }
    
    /**
     * Return the HTML for a single update.
     *
     * @param MJKEnrich_Update $update
     * @param WP_Post $p
     * @return string
     */
    static function render_update(MJKEnrich_Update $update, WP_Post $p): string {
        $html = '';
        
        // Article to which it applies (with title as link)
        $post_title_link = sprintf('<a href="%1$s" title="%2$s">%2$s</a>',
                get_permalink($p->ID), $p->post_title);
        
        $html .= sprintf('<div class="%s"><h3>%s</h3></div>',
                self::cl_update_post, $post_title_link);
        
        // Article's date
        $post_date = JKNTime::dt_post($p);
        $post_iss = MJKVIAPI::get_post_vi($p)[1];
        $html .= sprintf('<div class="%s">Originally published on %s (%s).</div>',
                self::cl_update_post_date, $post_date->format('F j, Y'),
                $post_iss->format_vol_iss_a($short_vol_name=false, $cap=true));
        
        // Nature of update
        $dt = $update->dt();
        $nature = $update->nature();
        
        // Add the date line
        $date_str = $dt->format('F j, Y \@ g a');
        $date_str = substr($date_str, 0, -1) . '.m.'; // a.m. p.m.
        
        // Make a couple of table columns
        $cells = sprintf('<td class="%s">%s</td><td class="%s">%s</td>',
                self::cl_update_date, $date_str,
                self::cl_update_nature, $nature);
        
        // Put the cells in a table
        $html .= sprintf('<table class="%s"><tr>%s</tr></table>',
                self::cl_update_table, $cells);
        
        // Wrapping div
        $html = sprintf('<div class="%s">%s</div>', self::cl_main, $html);
        
        return $html;
    }
    
    /**
     * Return the CSS for this page.
     *
     * @return string
     */
    static function style(): string {
        return JKNCSS::tag('            
            .'.self::cl_main.' h3 {
                margin-bottom: 3px;
                margin-top: 3px;
            }
            
            .'.self::cl_update_table.' {
                margin-bottom: 0 !important;
            }
            
            .'.self::cl_main.' {
                margin-bottom: 18px;
                padding-bottom: 15px;
                border-bottom: 1px solid #555;
            }
            
            .'.self::cl_main.':last-child {
                border-bottom: 0;
            }

            .'.self::cl_main.' td {
                border: none;
                vertical-align: top;
                margin-bottom: 10px;
                padding-bottom: 10px;
                padding-top: 10px;
            }
            
            .'.self::cl_update_post_date.' {
                margin-bottom: 15px;
                font-size: 13px;
            }
            
            .'.self::cl_update_nature.' p {
                margin-bottom: 0;
                margin-top: 20px;
                padding-left: 10px;
            }
            
            .'.self::cl_update_nature.' p:first-child {
                margin-top: 0;
            }
            
            .'.self::cl_update_date.' {
                font-size: 17px;
                width: 275px;
                font-style: italic;
                background: rgba(17, 65, 111, .1);
            }
            
            @media (max-width: 479px) {
                .'.self::cl_update_date.' {
                    width: 135px;
                }
            }

        ');
    }
}
