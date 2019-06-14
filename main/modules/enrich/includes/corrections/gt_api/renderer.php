<?php

/**
 * Renders the corrections page.
 */
class MJKEnrichCorrectionsRenderer extends JKNRendererSwitch {
    
    // This is the earliest year that we have corrections in the system
    const cutoff_ac_year = 2013;
    const msg_empty = '<h4>No corrections are recorded for this volume.</h4>';
    
    // CSS
    const cl_main = 'mjk-gt-corr-main';
    const cl_corr_div = 'mjk-gt-corr-corr-div';
    const cl_corr_date = 'mjk-gt-corr-corr-date';
    const cl_corr_post = 'mjk-gt-corr-corr-post';
    const cl_corr_post_date = 'mjk-gt-corr-corr-post-date';
    const cl_corr_nature = 'mjk-gt-corr-corr-nature';
    const cl_corr_notice = 'mjk-gt-corr-corr-notice';
    
    // Override from parent

	/**
	 * Return the formatted HTML for one academic year of corrections.
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
        
        // Limit it to the ones with corrections
        $posts = self::posts_with_corrections($posts);
        
        // Rotate so that corrections are the key
        $corrections = self::posts_to_corrections($posts);
        
        // If no corrections, render the empty message
        if (empty($corrections)) {
            $html .= self::msg_empty;
            
        // Otherwise render our corrections
        } else {
            $html .= self::render_corrections($corrections);
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

	        if (is_null($vol)) continue;

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
     * Return only the posts that have corrections among the ones passed.
     *
     * @param WP_Post[] $posts
     * @return WP_Post[]
     */
    static function posts_with_corrections(array $posts): array {
        $posts_with_corrections = [];
        
        // Only keep those that have corrections
        foreach($posts as $p) {
            if (MJKEnrich_ACF_Corrections::have_rows(
            	MJKEnrich_ACF_Corrections::corrections, $p->ID)) {
                $posts_with_corrections[] = $p;
            }
        }
        
        return $posts_with_corrections;
    }
    
    /**
     * Take an array of posts and return an array of [correction, post] pairs.
     *
     * @param WP_Post[] $posts
     * @return string[]
     */   
    static function posts_to_corrections(array $posts): array {
        $corrections = [];
        
        foreach($posts as $p) {
            $p_corrections = MJKEnrich_Corrections::gather_corrections($p->ID);
            foreach($p_corrections as $corr) {
                $corrections[] = ['correction' => $corr, 'post' => $p];
            }
        }
        
        // Sort corrections by timestamp, newest to oldest
        usort($corrections, function(array $a, array $b): int {
        	$pri_a = $a['correction']->get_dt();
        	$pri_b = $b['correction']->get_dt();
        	return $pri_b <=> $pri_a;
        });

        return $corrections;
    }

	/*
	 * =========================================================================
	 * Formatting
	 * =========================================================================
	 */
    
    /**
     * Return the HTML for an array of corrections.
     * The array is of the form [ ['correction' => c, 'post' => p] ]
     *
     * @param array $corrections
     * @return string
     */
    static function render_corrections(array $corrections): string {
        $html = '';
        
        // Concatenate each correction's rendering
        foreach($corrections as $corr_pair) {        
            $corr = $corr_pair['correction'];
            $p = $corr_pair['post'];
            $html .= sprintf('%s', self::render_correction($corr, $p));
        }
        
        // Wrap in a div
        $html = sprintf('<div class="%s">%s</div>', self::cl_main, $html);
        
        return $html;
    }

	/**
	 * Return the HTML for a single correction.
	 *
	 * @param MJKEnrich_Correction $corr
	 * @param WP_Post $p
	 * @return string
	 */
    static function render_correction(MJKEnrich_Correction $corr,
	        WP_POST $p): string {

        $html = '';
        
        // Article to which it applies (with title as link)
        $post_title_link = sprintf('<a href="%1$s" title="%2$s">%2$s</a>',
                get_permalink($p->ID), $p->post_title);
        
        $html .= sprintf('<div class="%s"><h3>%s</h3></div>',
                self::cl_corr_post, $post_title_link);
        
        // Nature of correction
        $html .= sprintf('<div class="%s"><span class="%s">Corrected %s:</span> %s</div>',
                self::cl_corr_nature, self::cl_corr_date,
                $corr->get_dt()->format('F j, Y'), $corr->get_nature());
        
        
        // Article's date
        $post_date = JKNTime::dt_post($p);
        $post_iss = MJKVIAPI::get_post_vi($p)[1];
        $html .= sprintf('<div class="%s">Originally published on %s (%s).</div>',
                self::cl_corr_post_date, $post_date->format('F j, Y'),
                $post_iss->format_vol_iss_a($short_vol_name=false, $cap=true));
        
        // Notice if there is one
        $notice_dt = $corr->get_notice_dt();
        if (!empty($notice_dt)) {          
            $not_iss = MJKVIAPI::get_issue_by_dt($notice_dt);
            $vi_html = $not_iss->format_vol_iss_a($short_vol_name=false, $cap=true);
            $html .= sprintf('<div class="%s">Notice to be printed on %s (%s).</div>',
                    self::cl_corr_notice, $notice_dt->format('F j, Y'), $vi_html);
        }
        
        // Wrapping div
        $html = sprintf('<div class="%s">%s</div>', self::cl_corr_div, $html);
        
        return $html;
    }
    
    /**
     * Return the CSS for this page.
     *
     * @return string
     */
    static function style(): string {
        return JKNCSS::tag('            
            .'.self::cl_corr_div.' {
                border-bottom: 1px dashed #555;
                padding-bottom: 15px;
                margin-bottom: 15px;                
            }
            
            .'.self::cl_corr_div.':last-child {
                border-bottom: none;
                padding-bottom: 0;
                margin-bottom: 0;
            }
            
            .'.self::cl_corr_date.' {                
                font-weight: 600;
            }

            .'.self::cl_corr_post.' h3 {
                margin-top: 0;
                margin-bottom: 3px;
            }

            .'.self::cl_corr_nature.' {
                margin-top: 8px;
                margin-bottom: 5px;
                font-size: 16px;                
                padding-left: 30px ;
                text-indent: -30px ;
            }            

            .'.self::cl_corr_post_date.', .'.self::cl_corr_notice.' {
                margin-left: 5px;
                font-size: 13px;
                line-height: 20px;
            }
            
            .'.self::cl_corr_post_date.'::before,
            .'.self::cl_corr_notice.'::before {
                content: "→ ";
            }
        ');
    }
}

