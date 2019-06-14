<?php

/**
 * Creates a Disqus recent comments widget.
 */
final class MJKDisqus_Widget extends WP_Widget {
    
    const cl_widget = 'mjk-disqus-widget';
    const default_title = 'Recent comments';
    
    /**
     * Set up the widget with WP.
     */
    function __construct() {        
        parent::__construct(self::cl_widget, static::name());
    }
    
    /**
     * Return the name of this widget.
     *
     * @return string
     */
    static function name(): string {
        return sprintf('%s Disqus Widget', JKNAPI::space()->name());
    }

	/**
	 * Return the title the user gave to this instance.
	 *
	 * @param array $instance
	 * @return string
	 */
    function title(array $instance): string {
        if (isset($instance['title'])) {
            return $instance['title'];
        } else {
            return self::default_title;
        }
    }

	/**
	 * Output the widget HTML.
	 *
	 * @param array $args
	 * @param array $instance
	 */
    function widget($args, $instance) {
        $html = '';
        
        // Regular widget block title
        $html .= sprintf('<div class="block-title"><span>%s</span></div>',
                apply_filters('widget_title', $this->title($instance)));
        
        // Add the shortcode
        $html .= do_shortcode(sprintf('[%s]', MJKDisqus_Shortcode::shortcode));
        
        // Add CSS, wrap in an aside class (standard for widgets)
        echo sprintf('<aside class="widget %s">%s</aside>',
                self::cl_widget, $html);
    }

	/**
	 * Sanitize and save widget options.
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
	 * Output the form that allows you to pick a title.
	 *
	 * @param array $instance
	 * @return string|void
	 */
    function form($instance) {
        
        // Gather title keys
        $title = $this->title($instance);
        $title_id = $this->get_field_id('title');
        $title_name = $this->get_field_name('title');

        $html = '';

        // Title text input
        $html .= sprintf('<br><label for="%s">Title:</label>', $title_id);
        $html .= sprintf('<br><input id="%s" name="%s" type="text" value="%s">',
                $title_id, $title_name, $title);

        // Instructions for the rest
        $spage = JKNAPI::settings_page();
        $link = sprintf('<a href="%s" title="%s">here</a>', $spage->url(),
                $spage->page_title());
        
        $html .= sprintf('<p><em>Settings for this widget can be altered'
                . ' %s.</em></p>', $link);
        
        echo $html;
    }
}
