<?php

/**
 * Creates a settings page where you enter elections-related info (times,
 * candidates, results) from which a frontend page is generated.
 */
final class MJKElections extends JKNModule {

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
    function id(): string { return 'elections'; }
    
    /**
     * Return the name.
     *
     * @return string
     */
    function name(): string { return 'Elections'; }
    
    /**
     * Return the description.
     *
     * @return string
     */
    function description(): string {
        return 'Provides an interface and page for organizing elections.';
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
			'MJKElections_ACF'    => 'includes/acf_api/registry.php'
		]);
	}
    
    /**
     * Add the Gen Tools page.
     */
    function run_on_startup(): void {

    	// Add ACF
	    MJKElections_ACF::add_filters();

        // Load the generator
        require_once 'includes/gt_api/renderer.php';
        
        // Add the page to GT, to which we'll attach the settings page
        MJKGTAPI::add_page([
            'id' => 'elections',
            'name' => 'Elections',
            'source' => 'the Elections page',
            'renderer' => 'MJKElectionsRenderer',
            'settings' => [JKNAPI::settings_page()]
        ]);
    }
}
