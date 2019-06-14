<?php

/**
 * Adds correction notices at the bottom of articles and a page listing them.
 */
class MJKEnrich_Corrections {
    
    const cl_div = 'mjk-enrich-corrections';
    const cl_date = 'mjk-enrich-correction-date';
    const cl_forecast = 'mjk-enrich-correction-forecast';

	/**
	 * Add the ACF filter, the content filter, and the Gen Tools page.
	 */
    static function set_up(): void {
        
        // ACF
        require_once 'acf_api/registry.php';
        MJKEnrich_ACF_Corrections::add_filters();
        
        // Page content
        add_filter('the_content', [__CLASS__, 'add_notice'], 0);

	    // Gen Tools
	    require_once 'gt_api/renderer.php';
	    MJKGTAPI::add_page([
		    'id' => 'corrections',
		    'name' => 'Correction notices',
		    'source' => 'posts marked as corrected on the post editing screen',
		    'renderer' => 'MJKEnrichCorrectionsRenderer'
	    ]);
    }


	/*
	 * =========================================================================
	 * Shortcode
	 * =========================================================================
	 */

	/**
	 * Return the content with a correction notice appended.
	 *
	 * @param string $content
	 * @return string
	 */
	static function add_notice(string $content): string {
        global $post;
        
        // Short-circuit if this is admin or not a regular post
        if (is_admin() || ($post->post_type != 'post')) return $content;
        
        // See if there are any corrections
        $corrections = self::gather_corrections($post->ID);
        
        // If so, format, add to content, and insert style
        if (!empty($corrections)) {
            $content .= self::format_notice($corrections);
            self::insert_style();
        }
        
        // This is a filter, so always return content
        return $content;
    }

	/**
	 * Return the corrections for the given post ID.
	 *
	 * @param string $pid
	 * @return MJKEnrich_Correction[]
	 */
	static function gather_corrections(string $pid): array {
        $corrections = [];
        
        // Go through each correction
        if (MJKEnrich_ACF_Corrections::have_rows(MJKEnrich_ACF_Corrections::corrections, $pid)) {
            while (MJKEnrich_ACF_Corrections::have_rows(MJKEnrich_ACF_Corrections::corrections, $pid)) {
                the_row();
                
                // Grab subfields
                $args = [];
                $args['pid'] = $pid;
                $args['corr_date'] = MJKEnrich_ACF_Corrections::sub(MJKEnrich_ACF_Corrections::corr_date);
                $args['corr_nature'] = MJKEnrich_ACF_Corrections::sub(MJKEnrich_ACF_Corrections::corr_nature);
                $args['corr_will_print'] = MJKEnrich_ACF_Corrections::sub(MJKEnrich_ACF_Corrections::corr_will_print);
                $args['corr_print_next_iss'] = MJKEnrich_ACF_Corrections::sub(MJKEnrich_ACF_Corrections::corr_print_next_iss);
                $args['corr_notice_date'] = MJKEnrich_ACF_Corrections::sub(MJKEnrich_ACF_Corrections::corr_notice_date);

	            // I really, really hate that we have to do this.
	            // But there seems to be no other way. Something is bugged.
	            // There are posts with a single 'corrections' row marked
	            // that nevertheless have NO corrections and cause fatal errors.
	            // If you remove that row in the database...
	            // it spontaneously appears on another post. O_O
	            if (empty($args['corr_date'])) continue;

                // Instantiate a correction and index it
                $corr = new MJKEnrich_Correction($args);
                $corrections[] = $corr;
            }
        }
        
        // Sort by date made and return
        usort($corrections,
	        function (MJKEnrich_Correction $a, MJKEnrich_Correction $b): int {
                return $a->get_dt() <=> $b->get_dt();
        });
        
        return $corrections;
    }

	/**
	 * Return the formatted HTML for a correction notice.
	 *
	 * @param MJKEnrich_Correction[] $corrections
	 * @return string
	 */
	static function format_notice(array $corrections): string {
        $html = '';
        
        $heading = '<h6>This article has been corrected.</h6>';
        $corrections_html = self::format_corrections($corrections);
        
        $html .= sprintf('<hr/><div class="%s">%s%s</div>',
                self::cl_div, $heading, $corrections_html);
        
        return $html;
    }

	/**
	 * Return the formatted HTML for a list of corrections.
	 *
	 * @param MJKEnrich_Correction[] $corrections
	 * @return string
	 */
	static function format_corrections(array $corrections): string {
        
        // Add each correction as a <li>
        $html_li_items = '';
        foreach($corrections as $correction) {
            $html_li_items .= sprintf('<li>%s</li>',
                    self::format_correction($correction));
        }        
        
        // Wrap all the <li>s in an <ol>
        $html = sprintf('<ol>%s</ol>', $html_li_items);
        return $html;
    }

	/**
	 * Return the formatted HTML for a single correction.
	 *
	 * @param MJKEnrich_Correction $correction
	 * @return string
	 */
	static function format_correction(MJKEnrich_Correction $correction): string {
        $html = '';
        
        // Extract features
        $nature = $correction->get_nature();
        $corr_dt = $correction->get_dt();
        $notice_dt = $correction->get_notice_dt();
        
        // Add the first line
        $date_made_str = $corr_dt->format('F j, Y \a\t g a');
        $date_made_str = substr($date_made_str, 0, -1) . '.m.'; // a.m. p.m.
        $html .= sprintf('<span class="%s">%s:</span> %s',
                self::cl_date, $date_made_str, $nature);
        
        // Add the second line if a notice will be printed
        if (!empty($notice_dt)) {
            
            // Get a (Vol, iss) bit in
            $issue = MJKVIAPI::get_issue_by_dt($notice_dt);
            $vi_html = $issue->format_vol_iss_a($short_vol_name=false, $cap=true);
            
            // Add a second line
            $html .= sprintf('<br><span class="%s">Notice to be printed on %s (%s).</span>',
                self::cl_forecast, $notice_dt->format('F j, Y'), $vi_html);
        }
        
        return $html;
    }

	/**
	 * Insert the formatted CSS.
	 */
	static function insert_style(): void {
        echo JKNCSS::tag('
            /* Correction notices (post) */
            .'.self::cl_div.' h6 {
                font-size: 17.25px;
            }
            
            .'.self::cl_div.' ol {
               margin-bottom: 0;
            }

            .'.self::cl_div.' ol li {
                font-size: 15.25px;
                margin-bottom: 5px;
            }

            .'.self::cl_date.' {
                font-weight: bold;
            }

            .'.self::cl_forecast.' {
                font-size: 13px;
            }
            
            .'.self::cl_forecast.'::before {
                content: "→ ";
            }
        ');
    }
}


/*
 * =========================================================================
 * Correction
 * =========================================================================
 */

/**
 * Represents a correction.
 */
class MJKEnrich_Correction {
    
    // Properties (TODO Make private with getters -- some may already exist)
	public $pid;
	public $dt;
	public $nature;
	public $notice_dt;

	/**
	 * Establish this correction's datetime, nature, and notice datetime if any
	 *
	 * @param array $args
	 */
	function __construct(array $args) {
        $this->pid = $args['pid'];
        $this->dt = JKNTime::dt($args['corr_date']);
        $this->nature = $args['corr_nature'];        
        $this->notice_dt = $this->determine_notice_dt(
                $args['corr_will_print'],
                $args['corr_print_next_iss'],
                $args['corr_notice_date']
        );
    }

	/**
	 * Return the notice DateTime if there is one, otherwise null.
	 *
	 * @param bool $will_print
	 * @param bool $print_next_iss
	 * @param string|null $notice_date A datestring.
	 * @return DateTime|null
	 */
	function determine_notice_dt(bool $will_print, bool $print_next_iss,
			string $notice_date=null): ?DateTime {
        
        // Short-circuit if this notice will not be printed
        if (empty($will_print)) { return null; }
        
        // If printing next issue, return that issue's dt
        if (!empty($print_next_iss)) {
            $origin_issue = MJKVIAPI::get_issue_by_dt($this->dt);
            $next_issue = MJKVIAPI::next_issue($origin_issue, true);
            
            // Avoid summer issues
            if (!empty($next_issue) && $next_issue->is_summer) {
                $next_issue = MJKVIAPI::next_issue($next_issue, true);
            }
            
            // return the next issue if there is one
            if (!empty($next_issue)) return $next_issue->get_first_day();
            
        // Otherwise get the dt of the selected date
        } else {
            $notice_dt = JKNTime::dt($notice_date);
            
            // Try to get an issue
            $issue = MJKVIAPI::get_issue_by_dt($notice_dt, $allow_summer=false);
            if (!empty($issue)) {
                
                // If there is one, make sure this is actually that issue's day
                if ($issue->lands_on_dt($notice_dt)) {
                    return $issue->get_first_day();
                }
            }
        }

        return null;
    }

	/**
	 * Return this correction's nature.
	 *
	 * @return mixed
	 */
	function get_nature(): string {
        return $this->nature;
    }

	/**
	 * Return a clone of this correction's datetime.
	 *
	 * @return DateTime
	 */
	function get_dt(): DateTime {
        return clone $this->dt;
    }

	/**
	 * Return a clone of this correction's notice DateTime.
	 *
	 * @return DateTime|null
	 */
	function get_notice_dt(): ?DateTime {
        return empty($this->notice_dt) ? null : clone $this->notice_dt;
    }
}