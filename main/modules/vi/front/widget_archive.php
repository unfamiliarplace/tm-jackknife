<?php

/**
 * The archive dropdown widget.
 */
class MJKVI_ArchiveWidget extends WP_Widget {

    // Keys for which dropdown(s) to load
    const both = 'Both';
    const current = 'Current volume';
    const past = 'Past volumes';
    
    // Class for the widget element
    const cl_widget = 'mjk_vi_archive_widget';

	/**
	 * Instantiate the parent object.
	 */
	function __construct() {
        parent::__construct(self::cl_widget, 'MJK Vol & Issue Archives');
    }

	/**
	 * Output the widget.
	 *
	 * @param array $args
	 * @param array $instance
	 */
	function widget($args, $instance) {

        // Get title and dropdown configuration from db
        $title = (!empty($instance['title'])) ? $instance['title'] : 'Archives';
        $config = (!empty($instance['config'])) ? $instance['config'] : self::both;

        $html = '';

        // Regular widget block title
        $html .= sprintf('<div class="block-title"><span>%s</span></div>',
                apply_filters('widget_title', $title));

        // Render both dropdowns
        if ($config == self::both) {
            $html .= $this->format_current();
            $html .= '<br>';
            $html .= $this->format_past();

        // Just render the current issues dropdown
        } elseif ($config == self::current) {
            $html .= $this->format_current();

        // Just render the past volumes dropdown
        } else {
            $html .= $this->format_past();
        }

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
	function update($new_instance, $old_instance) {
        $new_instance['title'] = trim($new_instance['title']);
        return $new_instance;
    }

	/**
	 * Output the admin widget options form.
	 *
	 * @param array $instance
	 * @return string|void
	 */
	function form($instance) {

        // Gather title keys
        $title = (!empty($instance['title'])) ? $instance['title'] : 'Archives';
        $title_id = $this->get_field_id('title');
        $title_name = $this->get_field_name('title');

        // Gather dropdown configuration keys
        $config = (!empty($instance['config'])) ? $instance['config'] : self::both;
        $config_id = $this->get_field_id('config');
        $config_name = $this->get_field_name('config');

        $html = '';

        // Title text input
        $html .= sprintf('<br><label for="%s">Title:</label>', $title_id);
        $html .= sprintf('<br><input id="%s" name="%s" type="text" value="%s">',
                $title_id, $title_name, $title);

        // Label for dropdown config radio input
        $html .= sprintf('<br><br><label for="%s">Show issues from the current volume, show past volumes, or both?</label>',
                $config_id);

        // Dropdown configuration radio buttons
        $html .= sprintf('<br><br><input type="radio" style="margin-bottom: 2px;" name="%1$s" id="%2$s" value="%2$s" %3$s />%2$s',
                $config_name, self::both, checked($config, self::both, false));
        $html .= sprintf('<br><input type="radio" style="margin-bottom: 2px;" name="%1$s" id="%2$s" value="%2$s" %3$s />%2$s',
                $config_name, self::current, checked($config, self::current, false));
        $html .= sprintf('<br><input type="radio" style="margin-bottom: 2px;" name="%1$s" id="%2$s" value="%2$s" %3$s />%2$s',
                $config_name, self::past, checked($config, self::past, false));

        $html .= '<br>';
        echo $html;
    }

	/**
	 * Format and return the current volume dropdown.
	 *
	 * @return string
	 */
	private function format_current(): string {

        // We want the past ones, but also the current one on top
        $current = MJKVIAPI::current_issue(true);
        if (!is_null($current)) {
	        $issues = MJKVIAPI::past_issues($current);
	        if (!is_null($current)) $issues[] = $current;
        }

        // Options for the select
        $options_html = '<option value>Select issue</option>';
        if (!empty($issues)) {
	        foreach( $issues as $issue ) {
		        $options_html .= sprintf('<option value="%s">%s (%s)</option>',
			        $issue->get_url(), $issue->get_name(),
			        $issue->format_date());
	        }
        }

        // Other data for the select
        $id = 'mjk_vi_current_vol_dropdown';
        $action = 'document.location.href=this.options[this.selectedIndex].value;';
        $label = '<label for="archives-mjk_vi_past_vols"><i>Issues published this year</i></label>';

        // Format
        return sprintf('%1$s<br><select id="%2$s" name="%2$s" class="%2$s" onchange="%3$s">%4$s</select>',
                $label, $id, $action, $options_html);
    }

	/**
	 * Format and return the past volumes dropdown.
	 *
	 * @return string
	 */
	private function format_past(): string {
        $vols = MJKVIAPI::past_volumes();

        if (empty($vols)) return '';

        $vols = array_reverse($vols);

        // Options for the select
        $options_html = '<option value>Select volume</option>';
        foreach ($vols as $vol) {
            $options_html .= sprintf('<option value="%s">%s (%s)</option>',
                $vol->get_url(), $vol->get_name(), $vol->format_academic_year());
        }

        // Other data for the select
        $id = 'mjk_vi_past_vols_dropdown';
        $action = 'document.location.href=this.options[this.selectedIndex].value;';
        $label = '<label for="archives-mjk_vi_past_vols"><i>Past volumes</i></label>';

        // Format
        return sprintf('%1$s<br><select id="%2$s" name="%2$s" class="%2$s" onchange="%3$s">%4$s</select>',
                $label, $id, $action, $options_html);
    }

	/**
	 * Format and return the CSS for the widget.
	 *
	 * @return string
	 */
	private function format_css(): string {
        return JKNCSS::tag('
        
            .'.self::cl_widget.' select{
                margin-top: 15px;
                margin-bottom: 20px;                
                border: none;
                border-bottom: 1px solid #555;
            }   
            
	    ');
    }
}