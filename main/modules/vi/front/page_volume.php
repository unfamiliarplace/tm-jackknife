<?php

/**
 * A page template for a volume.
 */
class MJKVI_PageVolume extends MJKVI_Page {

	private $vol;

	/*
	 * =========================================================================
	 * Constants
	 * =========================================================================
	 */

    // "Advertise in this issue" deal
    const adv_url = '/advertise';
    const adv_text = 'Advertise in this issue';
    
    // Columns for the grid of issues
    const issues_per_row = 4;
    
    // Classes for the various elements
    const cl_vol_notes = 'mjk_vi_vol_notes';
    const cl_vol_website = 'mjk_vi_vol_website';
    const cl_issues = 'mjk_vi_vol_issues';
    const cl_iss_meta = 'mjk_vi_iss_meta';
    const cl_iss_meta_nc = 'mjk_vi_iss_meta_nc';
    const cl_iss_name = 'mjk_vi_iss_name';
    const cl_iss_date = 'mjk_vi_iss_date';
    const cl_iss_thumb = 'mjk_vi_iss_thumb';
    const cl_iss_panel = 'mjk_vi_iss_panel';
    const cl_iss_panel_a_content = 'mjk_vi_iss_panel_a_content';
    const cl_iss_panel_a_empty = 'mjk_vi_iss_panel_a_empty';


	/*
	 * =========================================================================
	 * Set up
	 * =========================================================================
	 */

	/**
	 * Parse the vol number and load the volume, or die if invalid.
	 *
	 * @param int|string $n_vol
	 */
	function __construct($n_vol) {
        $this->vol = MJKVIAPI::get_vol_by_num($n_vol);
        if (empty($this->vol))
            wp_die($this->die_vol_fail($n_vol));
    }

	/**
	 * Return the title for this page.
	 *
	 * @return string
	 */
	function get_page_title(): string {
        return $this->vol->get_name();
    }

	/**
	 * Return the formatted html for this page.
	 *
	 * @param bool $breadcrumbs
	 * @return string
	 */
	function format(bool $breadcrumbs=true): string {
        $html = '';
        $html .= $this->format_header();
        $html .= $this->format_body($breadcrumbs);
        $html .= $this->format_footer();
        return (!empty($html)) ? do_shortcode($html) : '';
    }

	/**
	 * Return the formatted body (no header or footer) for this page.
	 * Breadcrumbs are optional because this page can also be loaded from
	 * Archive/Current Volume.
	 *
	 * @param bool $breadcrumbs
	 * @return string
	 */
	function format_body(bool $breadcrumbs=true): string {
        $html = '';
        $html .= $this->format_css();
        $html .= $this->format_title();
        if ($breadcrumbs) $html .= $this->format_breadcrumbs($this->vol);
        $html .= $this->format_website();
        $html .= $this->format_notes();
        $html .= $this->format_issues();
        return $html;
    }


	/*
	 * =========================================================================
	 * Top portion
	 * =========================================================================
	 */

	/**
	 * Return the formatted html for the visible title.
	 *
	 * @return string
	 */
	private function format_title(): string {
        return sprintf('<h1>%s (%s)</h1>',
            $this->vol->get_name(), $this->vol->format_academic_year());
    }

	/**
	 * Return the formatted html for any notes.
	 *
	 * @return string
	 */
	private function format_notes(): string {
        if (!empty($this->vol->notes))
            return sprintf('<p class="%s">%s</p>',
	            self::cl_vol_notes, $this->vol->notes);

        return '';
    }


	/*
	 * =========================================================================
	 * Website
	 * =========================================================================
	 */

	/**
	 * Return formatted html for the website.
	 *
	 * @return string
	 */
	private function format_website(): string {
        $ws = $this->vol->get_website();

        // Only list if it's not the main one
        if (!empty($ws) && !$ws->main) {
            $link = sprintf('<a href="%1$s" title="%2$s website">%2$s website</a>',
                    $ws->link, $ws->name);
            return sprintf('<p class="%s">Articles from this volume can be found on the %s.</p>',
                    self::cl_vol_website, $link);
        }

        return '';
    }


	/*
	 * =========================================================================
	 * Issues
	 * =========================================================================
	 */

	/**
	 * Remove summer issues that have no content (there should only be one if
	 * it had content).
	 *
	 * @param array $issues
	 * @return array
	 */
	private function filter_out_summer(array $issues): array {
        return array_filter($issues, function(MJKVI_Issue $issue): bool {
            return (!($issue->is_summer) or $issue->has_content());
        });
    }

	/**
	 * Return the formatted issues grid.
	 *
	 * @return string
	 */
	private function format_issues(): string {

        // Filter out summer issue if it has no content
        $issues = $this->filter_out_summer($this->vol->issues);

        // Short-circuit for no issues
        if (empty($issues)) {
            return '<h4>No published issues in this volume are available'
	        . ' online.</h4>';
        }

        // Get a grid from MJKCommon
        $html = JKNLayouts::grid($issues,
                self::issues_per_row, [__CLASS__, 'format_issue']);
        
        return sprintf('<div class="%s">%s</div>',
                self::cl_issues, $html);
    }

	/**
	 * Format and return an individual issue.
	 *
	 * @param MJKVI_Issue $issue
	 * @return string
	 */
	static function format_issue(MJKVI_Issue $issue): string {

        $html = '';

        // Thumbnail
        $thumb = $issue->dynamic_thumbnail();
        $img_tag = sprintf('<img src="%s" width="%s" height="%s" alt=%s" />',
                $thumb['src'], $thumb['width'], $thumb['height'],
                $issue->get_name());

        $html .= sprintf('<div class="%s">%s</div>',
                self::cl_iss_thumb, $img_tag);

        // Meta info
        $name = sprintf('<span class="%s">%s</span>',
                self::cl_iss_name, $issue->get_name());
        $date = sprintf('<span class="%s">%s</span>',
                self::cl_iss_date, $issue->format_date());

        $meta_class = ($issue->has_content()) ? self::cl_iss_meta : self::cl_iss_meta_nc;
        $html .= sprintf('<div class="%s">%s%s</div>',
                $meta_class, $name, $date);

        // Elements of the link
        // Case where the issue has content (usually = was published)
        if ($issue->has_content()) {
            $link = $issue->get_url();
            $title = $issue->get_name();
            $class = self::cl_iss_panel_a_content;

            $html = sprintf('<a href="%s" title="%s" class="%s">%s</a>',
                    $link, $title, $class, $html);

        // Case where it has none, but could be an advertising link
        } elseif ($issue->in_future()) {

        	$advertise = MJKVI_ACF_Options::get(MJKVI_ACF_Options::advertise);

        	if ($advertise) {
		        $link = self::adv_url;
		        $title = self::adv_text;
		        $class = self::cl_iss_panel_a_empty;

		        $html = sprintf('<a href="%s" title="%s" class="%s">%s</a>',
			        $link, $title, $class, $html);
	        }

        }

        // Wrap in panel class
        return sprintf('<div class="%s">%s</div>', self::cl_iss_panel, $html);
    }


	/*
	 * =========================================================================
	 * CSS
	 * =========================================================================
	 */

	/**
	 * Return the formatted CSS.
	 *
	 * @return string
	 */
	private function format_css(): string {

        // Apparently this gets overridden...
        $td_pb_name = sprintf("td-pb-span%s", 12 / self::issues_per_row);
        $td_pb_pcnt = 100 * (1 / self::issues_per_row);

        return JKNCSS::tag('		
            div.'.self::cl_issues.' div.vc_column-inner {
                text-align: center;
            }

            div.'.self::cl_issues.' div.vc_row {
                 margin-bottom: 25px;
            }

            div.'.self::cl_iss_meta.', div.'.self::cl_iss_meta_nc.' {
                  font-family: "Roboto", sans-serif;
                  color: #222;
            }

            div.'.self::cl_iss_meta_nc.' {
                color: #555;
            }

            span.'.self::cl_iss_name.' {
                font-weight: bold;
            }

            div.'.self::cl_iss_meta.' span, div.'.self::cl_iss_meta_nc.' span {
                display: block;
            }

            div.'.self::cl_iss_thumb.' {
                padding-top: 5px;
            }
            
            div.'.self::cl_iss_panel.' {                
			    border-radius: 10px;
			    overflow: hidden;
            }

            div.'.self::cl_iss_panel.' a {
                display: block;
            }

            div.'.self::cl_iss_panel.' a.'.self::cl_iss_panel_a_content.':hover {
                background-color: rgba(17,65,111,0.15);
            }

            div.'.self::cl_iss_panel.' a.'.self::cl_iss_panel_a_empty.':hover {
                background-color: rgba(0,0,0,0.07);
            }

            @media (max-width: 767px) {
                div.'.self::cl_iss_panel.' {
                    margin-bottom: 10px;
                    padding-bottom: 5px;
                    border-bottom: 1px dashed #777;
                }
            }

            p.'.self::cl_back.' {
                  display: block;
                  font-size: small;
                  font-family: "Roboto", sans-serif;
            }

            p.'.self::cl_back.' a:hover {
                text-decoration: underline;
            }

            p.'.self::cl_vol_notes.' {
                font-family: "Roboto", sans-serif;
                margin-top: 25px;
            }

            p.'.self::cl_vol_website.' {
                font-style: italic;
            }
            
            @media (min-width: 768px) {
                .vc_column_container.'.$td_pb_name.' {
                    width: '.$td_pb_pcnt.'%;
                }
            }

            @media (max-width: 767px) {
                .vc_column_container.'.$td_pb_name.' {
                    width: 100%;
                }
            }
        ');
    }
}
