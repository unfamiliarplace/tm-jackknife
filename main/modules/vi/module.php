<?php

/**
 * Creates volumes (one publishing year) and issues (one week), associating
 * them with posts and PDFs, and generates archives for browsing them.
 */
final class MJKVI extends JKNModule {

	/*
	 * =========================================================================
	 * Storage
	 * =========================================================================
	 */
    
    public $vols = [];
	public $websites = [];

	public $vols_loaded = false;
	public $websites_loaded = false;

	public $cpt_volume;
	public $cpt_website;

	public $cache;


	/*
	 * =========================================================================
	 * Module registration
	 * =========================================================================
	 */

	/**
	 * @return string
	 */
    function id(): string { return 'vi'; }

	/**
	 * @return string
	 */
    function name(): string { return 'Volume & Issue'; }

	/**
	 * @return string
	 */
    function description(): string {
        return 'Represents volumes, issues, and archival websites.' .
            '<br>Visual Composer is not required but is strongly recommended.' .
	        '<br>Newspaper is not required but makes for better issue pages.';
    }


	/*
	 * =========================================================================
	 * Actions
	 * =========================================================================
	 */

	/**
	 * Autoload all classes.
	 */
	function run_on_load(): void {
    	JKNClasses::autoload([

    		// Core
		    'MJKVI_Loader'                  => 'includes/load.php',
			'MJKVI_CPT_Volume'              => 'includes/cpt_volume.php',
			'MJKVI_CPT_ArchivalWebsite'     => 'includes/cpt_website.php',
			'MJKVI_Volume'                  => 'includes/volume.php',
			'MJKVI_Issue'                   => 'includes/issue.php',
			'MJKVI_ArchivalWebsite'         => 'includes/archival_website.php',
			'MJKVIAPI'                      => 'includes/api.php',

			// Cache
			'MJKVICacheDir_Volume'              => 'includes/cache.php',
		    'MJKVICacheDir_Issue'               => 'includes/cache.php',
		    'MJKVICacheDir_VolGeneral'          => 'includes/cache.php',
		    'MJKVICacheObject_IssuuData'        => 'includes/cache.php',
		    'MJKVICacheObject_ArchiveorgThumb'  => 'includes/cache.php',
		    'MJKVICacheObject_VolThumb'         => 'includes/cache.php',

    		// ACF
    		'MJKVI_ACF_Options'     => 'includes/acf_api/registry_options.php',
			'MJKVI_ACF_VOL'         => 'includes/acf_api/registry_vol.php',
			'MJKVI_ACF_AW'          => 'includes/acf_api/registry_aw.php',
			'MJKVI_ACF_P'           => 'includes/acf_api/registry_p.php',

		    // tagDiv
		    'MJKVI_TD'              => 'includes/td_api/registry.php',

		    // Frontend
		    'MJKVI_Page'            => 'front/page.php',
			'MJKVI_PageVolume'      => 'front/page_volume.php',
			'MJKVI_PageIssue'       => 'front/page_issue.php',
			'MJKVI_Shortcodes'      => 'front/shortcodes.php',
			'MJKVI_ArchiveWidget'   => 'front/widget_archive.php',

		    'MJKVI_CurrentIssueThumbnailWidget' =>
			    'front/widget_current_thumb.php',
	    ]);
	}

    /**
     * Add post types and their options, hooks for templates, etc., shortcodes,
     * and ACF filters.
     */
    function run_on_startup(): void {

        // Templates and query var constants
        define('MJKVI_TEMPLATES', JKNAPI::mpath() . '/templates/');
        define('MJKVI_VOL_QVAR', 'mjk_vi_vol');
        define('MJKVI_ISS_QVAR', 'mjk_vi_iss');

        // Default thumbnail paths
        define('MJKVI_DEF_THUMB', JKNAPI::murl() . '/assets/dt_100x150.png');
        define('MJKVI_NYT_THUMB', JKNAPI::murl() . '/assets/dt_grey_100x150.png');
        
        // Set up the cache root
        $this->cache = new JKNCacheRoot();
        
        // Add volume custom post type
        MJKVI_CPT_Volume::add_hooks($spage_order=10);
        
        // Add website custom post type
        MJKVI_CPT_ArchivalWebsite::add_hooks($spage_order=110);
                
        // Add loaders and shortcodes
        MJKVI_Loader::add_hooks();
        MJKVI_Shortcodes::add_shortcodes();

        // Add ACF groups
        MJKVI_ACF_P::add_filters();
        MJKVI_ACF_AW::add_filters();
        MJKVI_ACF_VOL::add_filters();
	    MJKVI_ACF_Options::add_filters();

        // TD
	    if (JKNAPI::theme_dep_met('newspaper')) MJKVI_TD::add_hooks();
    }

	/**
	 * Load the archival websites and volumes and add the rewrite rules.
	 */
    function run_on_init(): void {
        MJKVI_Loader::load_archival_websites();
        MJKVI_Loader::load_volumes();
        MJKVI_Loader::add_rewrite_rules();
    }

	/**
	 * Flush the rewrite rules.
	 */
    function run_on_activate(): void {
	    add_action('wp_loaded', 'flush_rewrite_rules');
    }

	/**
	 * Delete the cache.
	 */
	function run_on_uninstall(): void {
		$root = new JKNCacheRoot();
		$root->purge();
	}
}
