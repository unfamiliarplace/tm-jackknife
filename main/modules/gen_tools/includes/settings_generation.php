<?php

/**
 * Provides the ability to generate a page when a settings page is saved.
 */
final class MJKGTSettingsGeneration {
    
    const alert_success = 'The %s page was successfully generated.';
    
    /**
     * Add a callback to generate the given settings page on the saving of a
     * Generation Tools page.
     *
     * @param JKNSettingsPage $spage
     * @param string $gen_id The ID of the Gen Tools page to regenerate.
     */
    static function add_gen(JKNSettingsPage $spage, string $gen_id): void {

        // Add the generate on save
        $save_post_cb = function(array $args): void {

            // Get the page to generate
            $gen_page = MJKGTAPI::page($args['gen_id']);
                
            // Generate the page
            $gen_result = $gen_page->generate();

            // Alert
            if (empty($gen_result)) {
	            $gen_result = sprintf(self::alert_success, $gen_page->name());
            }

            echo JKNJavascript::tag(sprintf('alert("%s");', $gen_result));
        };

        // Add the above function to the settings page's update context
        $spage->add_action_on_update($save_post_cb, ['gen_id' => $gen_id]);
    }
}
