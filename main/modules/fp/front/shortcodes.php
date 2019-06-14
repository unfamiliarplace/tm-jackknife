<?php

/**
 * Adds a shortcode to display spotlight posts.
 */
class MJKFP_Shortcodes {
	
    // CSS variables
    const cl_panel  = 'mjk-fp-spotlight-panel';
    const cl_table  = 'mjk-fp-spotlight-table';
    const cl_tr     = 'mjk-fp-spotlight-tr';
    const cl_td     = 'mjk-fp-spotlight-td';
    const cl_header = 'mjk-fp-spotlight-header';
    const cl_body   = 'mjk-fp-spotlight-body';
    const cl_link   = 'mjk-fp-spotlight-link';

	/**
	 * Add the 'mjk_spotlight' shortcode.
	 */
    static function add_shortcodes(): void {
        add_shortcode('mjk_spotlight', [__CLASS__, 'render']);
    }

	/**
	 * Return the content of the shortcode.
	 *
	 * @param mixed $atts The shortcode attributes. Unused.
	 * @return string
	 */
    static function render($atts): string {
        $html = '';

        // Gather all spotlight objects
        $fpcard = MJKFP_API::current_fpcard();
        $lights = $fpcard->get_spotlights();

        // Short-circuit
        if (empty($lights)) return $html;

        // Otherwise start building
        $html .= self::insert_style(count($lights));
        $html .= sprintf('<div class="%s"><table class="%s"><tr class="%s">',
                        self::cl_panel, self::cl_table, self::cl_tr);

        // Render each spotlight object
        foreach($lights as $light) {
            $html .= sprintf('<td class="%s">%s</td>',
	            self::cl_td, $light->render());
        }

        // Close table
        $html .= '</tr></table></div>';

        // Insert style and return		
        return $html;
    }

	/**
	 * Return the formatted CSS.
	 *
	 * @param int $n_lights
	 * @return string
	 */
    static function insert_style(int $n_lights): string {

        // Determine td width as inverse of number of spotlight posts
        $td_width = 100 / $n_lights;

        return JKNCSS::tag('
            /* Spotlight posts */
            
            @media (max-width: 767px) {
                .'.self::cl_panel.' {
                    display: none;
                }
            
                .home .td-page-wrap .td-grid-wrap,
                single-format-video .td-page-wrap .td-grid-wrap {
                    padding-top: 10px !important;
                }
            }
            
            @media (min-width: 768px) {
                .home .td-page-wrap .td-grid-wrap,
                single-format-video .td-page-wrap .td-grid-wrap {
                    padding-top: 0 !important;
                }
            }
            
            .'.self::cl_panel.' {
                border-bottom: 1px solid #777;
                padding-bottom: 12px;
                margin-top: 10px;
                margin-bottom: 5px;
            }
            
            .'.self::cl_table.' {
                width: 100%;
                border-collapse: collapse;
                border-style: hidden;
            }
            
            .'.self::cl_td.' {
                border: 1px solid #777;
            }
            
            .'.self::cl_tr.' {
                vertical-align: top;
            }
            
            .'.self::cl_td.' {
                width: '.$td_width.'%;
                text-align: center;
                text-transform: uppercase;
                padding-left: 10px;
                padding-right: 10px;
            }
            
            .'.self::cl_header.'::before {
                content: "- ";
            }
            
            .'.self::cl_header.'::after {
                content: " -";
            }
            
            .'.self::cl_header.' {
                display: block;
                font-size: 11px;
                text-transform: uppercase;
                text-align: inherit;
                font-family: "Open Sans", sans-serif;
                line-height: 0;
                margin-bottom: 10px;
                margin-top: 10px;
            }
            
            .'.self::cl_body.', .'.self::cl_body.' a {
                color: #222;
                display: block;
                font-size: 14px;
                text-align: inherit;
                letter-spacing: 1.5px;
                font-style: italic;
                font-family: "Open Sans"; /* as opposed to Georgia */
                margin-bottom: 10px;
                line-height: 18px;
            }
            
            .'.self::cl_body.' a:hover, .'.self::cl_body.' a:active,
            .'.self::cl_body.' a:focus {
                text-decoration: underline;
            }
        ');
    }
}
