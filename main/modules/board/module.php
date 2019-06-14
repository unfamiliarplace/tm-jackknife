<?php

/**
 * Creates a settings page where you enter board-related info (minutes, meeting
 * dates, audits, etc.) from which a frontend page is generated.
 */
final class MJKBoard extends JKNModule {

	/*
	 * =========================================================================
	 * Module registration
	 * =========================================================================
	 */

    /**
     * Return the ID.
     *
     * @return string
     */
    function id(): string { return 'board'; }
    
    /**
     * Return the name.
     *
     * @return string
     */
    function name(): string { return 'Board of Directors'; }
    
    /**
     * Return the description.
     *
     * @return string
     */
    function description(): string {
        return 'Provides an interface and page for organizing board activity.';        
    }


	/*
	 * =========================================================================
	 * Actions
	 * =========================================================================
	 */

	/**
	 * Autoload the classes.
	 */
	function run_on_load(): void {
		JKNClasses::autoload([
			'MJKBod_ACF'    => 'includes/acf_api/registry.php'
		]);
	}
    
    /**
     * Add the Gen Tools page.
     */
    function run_on_startup(): void {

    	// Add ACF
	    MJKBod_ACF::add_filters();

        // Load the generator
        require_once 'includes/gt_api/renderer.php';
        
        // Add the page to GT, to which we'll attach the settings page
        MJKGTAPI::add_page([
            'id' => 'board',
            'name' => 'Board of Directors',
            'source' => 'the Board of Directors page',
            'renderer' => 'MJKBodRenderer',
            'settings' => [JKNAPI::settings_page()]
        ]);
    }
}
