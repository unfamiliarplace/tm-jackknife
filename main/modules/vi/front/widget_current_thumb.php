<?php

/**
 * The current issue thumbnail widget.
 */
class MJKVI_CurrentIssueThumbnailWidget extends WP_Widget {

    // Classes for current issue thumbnail layout
    const cl_iss_meta = 'mjk_vi_wg_iss_meta';
    const cl_iss_name = 'mjk_vi_wg_iss_name';
    const cl_iss_thumb = 'mjk_vi_wg_iss_thumb';
    const cl_iss_notes = 'mjk_vi_wg_iss_notes';
    const cl_iss_website = 'mjk_vi_wg_iss_website';
    const cl_iss_panel = 'mjk_vi_wg_iss_panel';
    
    // Class for the widget element
    const cl_widget = 'mjk_vi_current_issue_thumbnail_widget';

	/**
	 * Instantiate the parent object.
	 */
	function __construct() {
        parent::__construct(self::cl_widget, 'MJK Current Issue Thumbnail');
    }

	/**
	 * Output the widget.
	 *
	 * @param array $args
	 * @param array $instance
	 */
	function widget($args, $instance) {

        // Get title and dropdown configuration from db
        $title = (!empty($instance['title'])) ? $instance['title'] : '';

        $html = '';

        // Regular widget block title
        if (!empty($title)) {
            $html .= sprintf('<div class="block-title"><span>%s</span></div>',
                    apply_filters('widget_title', $title));
        }

        // Render the thumbnail
        $html .= $this->render_current_issue_thumb();

        // Add CSS, wrap in an aside class (standard for widgets)
        $css = $this->format_css();
        echo sprintf('<aside class="%s">%s%s</aside>',
                self::cl_widget, $css, $html);
    }

	/**
	 * Sanitize and save the widget options.
	 *
	 * @param array $new_instance
	 * @param array $old_instance
	 * @return array
	 */
	function update($new_instance, $old_instance) { return $new_instance; }

	/**
	 * Output the admin widget options form.
	 *
	 * @param array $instance
	 * @return string|void
	 */
	function form($instance) { echo ''; }

    // Format a thumbnail panel for the current issue

	/**
	 * Format and return a thumbnail panel for the current issue.
	 *
	 * @return string
	 */
	static function render_current_issue_thumb(): string {

        // The current issue must be one with content, otherwise keep searching backwards
        $issue = MJKVIAPI::current_issue($content_required = true);
        if (is_null($issue)) return '';

        $html = '';

        // Thumbnail
        $thumb = $issue->dynamic_thumbnail('medium');
        $img_tag = sprintf('<img src="%s" width="%s" height="%s" alt=%s" />',
                $thumb['src'], $thumb['width'], $thumb['height'], $issue->get_name());

        $html .= sprintf('<div class="%s" >%s</div>',
                self::cl_iss_thumb, $img_tag);

        // Meta info
        $name = sprintf('<span class="%s">%s, %s</span>',
                self::cl_iss_name, $issue->vol->get_name(), $issue->get_name());

        $html .= sprintf('<div class="%s">%s</div>',
                self::cl_iss_meta, $name);

        // Link
        if ($issue->has_content()) {
            $html = sprintf(
            	'<a href="%s" title="%s, %s">%s</a>',
                $issue->get_url(), $issue->vol->get_name(),
	            $issue->get_name(), $html);
        }

        // Wrap in a panel div
        return sprintf('<div class="%s">%s</div>',
                self::cl_iss_panel, $html);
    }

	/**
	 * Return the formatted CSS for current issue thumbnail widget.
	 *
	 * @return string
	 */
	private function format_css(): string {
        return JKNCSS::tag('
        
            div.'.self::cl_iss_meta.' span {
                display: block;
            }

            span.'.self::cl_iss_name.' {
                font-size: 14px;
            }

            div.'.self::cl_iss_thumb.' {
                padding-top: 5px;
            }

            div.'.self::cl_iss_panel.' {
                text-align: center;
            }

            div.'.self::cl_iss_panel.' a {
                display: block;
            }

            div.'.self::cl_iss_panel.' a:hover {
                background-color: rgba(17,65,111,0.15);
            }
            
        ');
    }
}
