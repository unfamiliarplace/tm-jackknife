<?php

/**
 * Disables the WPBeginner dashboard widget.
 * (Not only does it suck, it also mixes in insecure HTTP requests.)
 */
class MJKCommon_disable_wpbeginner_widget {

	/**
	 * Disable the widget.
	 */
    static function run() {
        add_action('admin_menu', function(): void {
            remove_meta_box('wpbeginner', 'dashboard', 'core');
        });
    }
}
