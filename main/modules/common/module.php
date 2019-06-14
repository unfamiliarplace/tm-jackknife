<?php

/**
 * Miscellaneous functions used by other modules, e.g. category colours.
 */
final class MJKCommon extends JKNModule {

	/*
	 * =========================================================================
	 * Module registration
	 * =========================================================================
	 */

	/**
	 * @return string
	 */
    function id(): string { return 'common'; }

	/**
	 * @return string
	 */
    function name(): string { return 'Common Functions'; }

	/**
	 * @return string
	 */
    function description(): string {
        return 'Common settings and functions for The Medium website.'
            . ' Also adds the Online Administrator role.';
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

    		// Core
    		'MJKCommon_ACF'         => 'includes/acf_api/registry.php',
		    'MJKCommonTools'        => 'includes/tools/MJKCommonTools.php',

		    // Misc includes
		    'MJKCommon_cf7_disable'
		        => 'includes/misc/cf7_disable.php',
		    'MJKCommon_custom_roles'
		        => 'includes/misc/custom_roles.php',
		    'MJKCommon_cron_schedules'
		        => 'includes/misc/cron_schedules.php',
		    'MJKCommon_fonts'
		        => 'includes/misc/fonts.php',
		    'MJKCommon_disable_wpbeginner_widget'
		        => 'includes/misc/disable_wpbeginner_widget.php',
		    'MJKCommon_style_tags'
		        => 'includes/misc/style_tags.php'
	    ]);
    }

	/**
	 * Add the ACF filter and run all the miscellaneous includes.
	 */
    function run_on_startup(): void {

	    // Add ACF settings page
	    MJKCommon_ACF::add_filters();
        
        // Run includes
	    MJKCommon_cf7_disable::run();
        MJKCommon_cron_schedules::run();
        MJKCommon_custom_roles::run();
        MJKCommon_disable_wpbeginner_widget::run();
        MJKCommon_style_tags::run();
    }

    /**
     * Add the fonts (the stylesheet must be enqueued at init to be CDN'd).
     */
    function run_on_init(): void { MJKCommon_fonts::run(); }
}
