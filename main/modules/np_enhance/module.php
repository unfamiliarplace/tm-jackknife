<?php

/**
 * Works with the Newspaper theme, modifying templates to put our meta
 * and masthead data front and centre throughout the site, beside other
 * customizations.
 */
final class MJKNPEnhance extends JKNModule {

	const css_ver = 0.00029;

	/*
	 * =========================================================================
	 * Module registration
	 * =========================================================================
	 */

	/**
	 * @return string
	 */
    function id(): string { return 'np_enhance'; }

	/**
	 * @return string
	 */
    function name(): string { return 'Newspaper Enhancements'; }

	/**
	 * @return string
	 */
    function description(): string {
        return 'Adds Newspaper functions and improvements via the tagDiv api.';
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
    		'MJKNPEnhanceAPI'           => 'includes/api.php',
		    'MJKNPEnhanceAuthor'        => 'includes/author.php',
		    'MJKNPEnhanceAuthorBox'     => 'includes/author_box.php',
			'MJKNPE_TemplateSwitcher'   => 'includes/template_switcher.php',
			'MJKNPE_Actions'            => 'includes/actions.php',
			'MJKNPEnhance_TD'           => 'includes/td_api/registry.php',
		    'MJKNPE_ACF'                => 'includes/acf_api/registry_post.php',
		    'MJKNPE_ACF_Options'        => 'includes/acf_api/registry_options.php'
	    ]);
	}

	/**
	 * Add the ACf filters and the tagDiv registry hooks.
	 */
    function run_on_startup(): void {

    	// Add ACF filters
        MJKNPE_ACF::add_filters();
		MJKNPE_ACF_Options::add_filters();

		// Add tagDiv hooks
		MJKNPEnhance_TD::add_hooks();

		// Add theme customization actions
	    MJKNPE_Actions::add_hooks();
    }

    /**
     * Add the stylesheet.
     */
    function run_on_init(): void {

	    // Enqueue the stylesheet
	    $stylesheet_url = sprintf('%s/assets/css/style.css', JKNAPI::murl());
	    $stylesheet_url = JKNCDN::url($stylesheet_url);
	    add_action('wp_enqueue_scripts', function() use ($stylesheet_url) {
		    wp_enqueue_style('mjk_np_enhance_frontend', $stylesheet_url,
			    [], self::css_ver);
	    });
    }
}
