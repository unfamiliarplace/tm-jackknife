<?php

/**
 * Adds custom fonts.
 */
class MJKCommon_fonts {

	/**
	 * Enqueue the stylesheet listing our custom fonts.
	 */
    static function run(): void {
        $handle = JKNOpts::qualify('fonts');
        $url = sprintf('%s/assets/fonts/fonts.css', JKNAPI::murl());
        $url = JKNCDN::url($url);
        add_action('wp_enqueue_scripts', function() use ($handle, $url) {            
            wp_enqueue_style($handle, $url);
        });
    }
}
