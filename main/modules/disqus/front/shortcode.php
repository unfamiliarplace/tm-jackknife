<?php

/**
 * Provides a shortcode that displays the latest comments.
 */
class MJKDisqus_Shortcode {
    
    const shortcode = 'mjk_disqus_latest_comments';
    
    /**
     * Register the latest comments shortcode.
     */
    static function add_shortcode(): void {
        add_shortcode(self::shortcode, [__CLASS__, 'render']);
    }
    
    /**
     * Return the content for the shortcode.
     *
     * @return string
     */
    static function render(): string {
        return MJKDisqusTools::content();
    }
}
