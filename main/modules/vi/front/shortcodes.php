<?php

/**
 * Adds the various shortcodes.
 */
final class MJKVI_Shortcodes {

	/*
	 * =========================================================================
	 * Constants
	 * =========================================================================
	 */

    // Volumes and sites per row in the respective grds
    const vols_per_row = 3;
    const sites_per_row = 3;

    // Classes for volume layout
    const cl_vol_thumb = 'mjk_vi_vol_thumb';
    const cl_vol_name = 'mjk_vi_vol_name';
    const cl_vol_year = 'mjk_vi_vol_year';
    const cl_vol_meta = 'mjk_vi_vol_meta';
    const cl_vol_panel = 'mjk_vi_vol_panel';
    const cl_vols = 'mjk_vi_vols';

    // Classes for website layout
    const cl_ws_name = 'mjk_vi_ws_name';
    const cl_ws_date = 'mjk_vi_ws_date';
    const cl_ws_meta = 'mjk_vi_ws_meta';
    const cl_ws_thumb = 'mjk_vi_ws_thumb';
    const cl_ws_notes = 'mjk_vi_ws_notes';
    const cl_ws_panel = 'mjk_vi_ws_panel';
    const cl_websites = 'mjk_vi_websites';

	/*
	 * =========================================================================
	 * Shortcode adder
	 * =========================================================================
	 */

    // The issues for the current volume shortcode are all handled by page_volume's formatting    // 
    // Register the shortcodes with WP (don't forget to do so with VC too if wanted in page builder)
	/**
	 *
	 */
	static function add_shortcodes(): void {
        add_shortcode('mjk_current_volume_archive',
	        [__CLASS__, 'render_current_volume_archives']);

        add_shortcode('mjk_past_volume_archive',
	        [__CLASS__, 'render_past_volume_archives']);

        add_shortcode('mjk_erindalian_archive',
	        [__CLASS__, 'render_erindalian_archives']);

        add_shortcode('mjk_all_volume_archive',
	        [__CLASS__, 'render_all_volume_archives']);

        add_shortcode('mjk_website_archive',
	        [__CLASS__, 'render_website_archive']);
    }

    // LOL. Since we reuse page_volume's formatting but its headings are meant for one page...

	/**
	 * @param string $html
	 * @return string
	 */
	private static function downgrade_headings(string $html): string {

        // h5 -> h6, h4 -> h5 etc.
        for ($i = 5; $i > 0; $i--) {
            $html = str_replace(sprintf('h%s', $i), sprintf('h%s', $i + 1), $html);
        }

        return $html;
    }


	/*
	 * =========================================================================
	 * Rendering volume shortcodes
	 * =========================================================================
	 */

	/**
	 * Format and return the current volume (a grid of issues).
	 *
	 * @return string
	 */
	static function render_current_volume_archives(): string {

        // Just borrow the page_volume template
        $vol = MJKVIAPI::current_volume();
        $template = new MJKVI_PageVolume($vol->get_num());

        // Only format the body, don't remake header and footer
        $html = $template->format_body($breadcrumbs = false);
        $html = self::downgrade_headings($html);
        return (!empty($html)) ? do_shortcode($html) : '';
    }

	/**
	 * Format and return the past volume archives.
	 *
	 * @return string
	 */
	static function render_past_volume_archives(): string {
        $vols = MJKVIAPI::past_volumes(null, false);
        $vols = array_reverse($vols);

        $html = '';
        $html .= self::format_volumes_css();
        $html .= '<h2>Past volumes</h2>';
        $html .= self::format_volumes($vols);
        return $html;
    }

	/**
	 * Format and return the Erindalian archives.
	 *
	 * @return string
	 */
	static function render_erindalian_archives(): string {
        $vols = MJKVIAPI::erindalian_volumes();
        $vols = array_reverse($vols);

        $html = '';
        self::format_volumes_css();
        $html .= '<h2>The Erindalian</h2>';
        $html .= self::format_volumes($vols);
        return $html;
    }

	/**
	 * Format and return the all-volume archives.
	 *
	 * @return string
	 */
	static function render_all_volume_archives(): string {
        $vols = MJKVIAPI::volumes();
        $vols = array_reverse($vols);

        $html = '';
        $html .= self::format_volumes_css();
        $html .= '<h2>All volumes</h2>';
        $html .= self::format_volumes($vols);
        return $html;
    }

	/**
	 * Format and return the volume grid for all/past volume archives.
	 *
	 * @param MJKVI_Volume[] $vols
	 * @return string
	 */
	private static function format_volumes(array $vols): string {
        $grid = JKNLayouts::grid($vols, self::vols_per_row, [__CLASS__, 'format_volume']);
        return sprintf('<div class="%s">%s</div>', self::cl_vols, $grid);
    }

	/**
	 * Format and return an individual volume panel.
	 *
	 * @param MJKVI_Volume $vol
	 * @return string
	 */
	static function format_volume(MJKVI_Volume $vol): string {
    	$html = '';

        // Thumbnail
        $thumb = $vol->thumbnail();
        $img_tag = sprintf('<img src="%s" alt="%s" />', $thumb['src'], $vol->get_name());

        $html .= sprintf('<div class="%s">%s</div>', self::cl_vol_thumb, $img_tag);

        // Meta info
        $name = sprintf('<span class="%s">%s</span>', self::cl_vol_name, $vol->get_name());
        $year = sprintf('<span class="%s">%s</span>', self::cl_vol_year, $vol->format_academic_year());

        $html .= sprintf('<div class="%s">%s%s</div>', self::cl_vol_meta, $name, $year);

        // Link
        $html = sprintf('<a href="%s" title="%s">%s</a>', $vol->get_url(), $vol->get_name(), $html);

        // Wrap in a panel div
        return sprintf('<div class="%s">%s</div>', self::cl_vol_panel, MJKCommonTools::cdn_images($html));
    }

	/**
	 * Format and return the CSS for the all/past volumes archives.
	 *
	 * @return string
	 */
	private static function format_volumes_css(): string {
        return JKNCSS::tag('
            div.'.self::cl_vols.' div.vc_column-inner {
                text-align: center;
            }

            div.'.self::cl_vols.' div.vc_row {
                margin-bottom: 25px;
            }

            div.'.self::cl_vol_meta.' {
                font-family: "Roboto", sans-serif;
                color: #222;
            }

            div.'.self::cl_vol_meta.' span {
                display: block;
            }

            span.'.self::cl_vol_name.' {
                font-weight: bold
            }

            div.'.self::cl_vol_thumb.' {
                padding: 10px 5px 0px 5px;
            }

            div.'.self::cl_vol_panel.' {
                margin: 5px;                
				border-radius: 10px;
				overflow: hidden;
            }

            div.'.self::cl_vol_panel.' a {
                display: block;
                padding-bottom: 5px;
            }

            div.'.self::cl_vol_panel.' a:hover {
                background-color: rgba(17,65,111,0.15);
            }
        ');
    }


	/*
	 * =========================================================================
	 * Rendering websites
	 * =========================================================================
	 */

	/**
	 * Format and return the archival websites.
	 *
	 * @return string
	 */
	static function render_website_archive() {
        $websites = MJKVIAPI::archival_websites();

        $html = '';

        self::format_websites_css();
        $html .= '<h2>Current & Archival Websites</h2>';
        $html .= self::format_websites($websites);

        return $html;
    }

	/**
	 * Format the list of archival websites.
	 *
	 * @param MJKVI_ArchivalWebsite[] $websites
	 * @return string
	 */
	private static function format_websites(array $websites): string {
        $grid = JKNLayouts::grid($websites, self::sites_per_row, [__CLASS__, 'format_website']);
        return sprintf('<div class="%s">%s</div>', self::cl_websites, $grid);
    }

	/**
	 * Format an individual website.
	 *
	 * @param MJKVI_ArchivalWebsite $ws
	 * @return string
	 */
	static function format_website(MJKVI_ArchivalWebsite $ws): string {
        $html = '';

        // Meta info
        $name = sprintf('<span class="%s">%s</span>', self::cl_ws_name, $ws->format_name());
        $date = sprintf('<span class="%s">%s</span>', self::cl_ws_date, $ws->format_date());
        $html .= sprintf('<div class="%s">%s%s</div>', self::cl_ws_meta, $name, $date);

        // Thumbnail
        $thumb_id = $ws->thumb_id;
        $img = sprintf('<img src="%s" alt="%s" title="%s" />', wp_get_attachment_url($thumb_id), $ws->format_name(), $ws->format_name());
        $html .= sprintf('<div class="%s">%s</div>', self::cl_ws_thumb, $img);

        // Notes
        $html .= sprintf('<div class="%s">%s</div>', self::cl_ws_notes, $ws->notes);

        // Link
        $html = sprintf('<a href="%s"  title="%s website">%s</a>', $ws->link, $ws->name, $html);

        // Format in a panel div
        return sprintf('<div class="%s">%s</div>', self::cl_ws_panel, MJKCommonTools::cdn_images($html));
    }

	/**
	 * Format and return the CSS for the archival websites shortcode.
	 */
	private static function format_websites_css() {
        echo JKNCSS::tag('
            div.'.self::cl_ws_meta.' {
                font-family: "Roboto", sans-serif;
            }

            span.'.self::cl_ws_name.' {
                font-weight: bold;
            }

            span.'.self::cl_ws_date.' {
                display: block;
                font-size: small;
            }

            div.'.self::cl_ws_thumb.' {
                border: 1px solid #555;
                margin: 5px;
                padding: 5px;
                background-color: #fff !important;
            }

            div.'.self::cl_ws_notes.' {
                text-align: left;
                padding: 5px;
            }

            div.'.self::cl_ws_panel.' a {
                color: #222;
                text-decoration: initial !important;
                display: block;
            }

            div.'.self::cl_ws_panel.' a:hover {
                background-color: rgba(17,65,111,0.15);
            }

            div.'.self::cl_websites.' .vc_column-inner {
                margin-bottom: 25px;
                text-align: center;
            }
        ');
    }
}
