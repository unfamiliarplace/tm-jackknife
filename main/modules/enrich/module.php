<?php

/**
 * Enriches articles with drop caps, corrections, updates, and attached files.
 */
final class MJKEnrich extends JKNModule {

	/*
	 * =========================================================================
	 * Module registration
	 * =========================================================================
	 */

	/**
	 * @return string
	 */
    function id(): string { return 'enrich'; }

	/**
	 * @return string
	 */
    function name(): string { return 'Article Enrichment'; }

	/**
	 * @return string
	 */
    function description(): string {
        return 'Adds drop caps, correction notices, files, and updates to'
            . ' articles.';
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
			// General ACF page
			'MJKEnrich_ACF'     => 'includes/acf_api/registry.php',

			// Submodules
			'MJKEnrich_Below_WYSIWYG'           =>
				'includes/below_wysiwyg/below_wysiwyg.php',

			'MJKEnrich_Files'                   =>
				'includes/files/files.php',

			'MJKEnrich_Corrections'             =>
				'includes/corrections/corrections.php',

			'MJKEnrich_DropCaps'                =>
				'includes/drop_caps/drop_caps.php',

			'MJKEnrich_Updates'                 =>
				'includes/updates/updates.php'
		]);
    }

	/**
	 * Add ACF and run the submodules. Order matters for the_content filters.
	 */
    function run_on_startup(): void {

    	// Add ACF filter
	    MJKEnrich_ACF::add_filters();

        // Below WYSIWYG group of ACF things
        MJKEnrich_Below_WYSIWYG::set_up();

        // Files
        MJKEnrich_Files::set_up();

        // Corrections
        MJKEnrich_Corrections::set_up();

        // Drop caps
	    MJKEnrich_DropCaps::set_up();

        // Updates
        MJKEnrich_Updates::set_up();
    }
}
