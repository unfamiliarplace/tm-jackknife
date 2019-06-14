<?php

/**
 * Works with the Newspaper theme to create a front page to show off image-
 * and text-based content changing on a weekly basis.
 */
final class MJKFP extends JKNModule {
    
    var $fpcards = [];
    var $fpcards_loaded = false;

	/*
	 * =========================================================================
	 * Module registration
	 * =========================================================================
	 */

	/**
	 * @return string
	 */
    function id(): string { return 'fp'; }

	/**
	 * @return string
	 */
    function name(): string { return 'Front Page'; }

	/**
	 * @return string
	 */
    function description(): string {
        return 'Adds weekly "cards" of stories to highlight on the front page.';
    }

	/**
	 * Autoload the classes.
	 */
	function run_on_load(): void {
		JKNClasses::autoload([
			'MJKFPLoader'               => 'includes/load.php',
			'MJKFP_API'                 => 'includes/api.php',
			'MJKFP_FPCard'              => 'includes/fpcard.php',
			'MJKFP_CPT_FPCard'          => 'includes/cpt_fpcard.php',
			'MJKFP_Slide'               => 'includes/slide.php',
			'MJKFP_Spotlight'           => 'includes/spotlight.php',
			'MJKFP_SpotlightArticle'   => 'includes/spotlight_article.php',
			'MJKFP_Shortcodes'          => 'front/shortcodes.php',
			'MJKFP_ACF'                 => 'includes/acf_api/registry.php',
			'MJKFP_TD'                  => 'includes/td_api/registry.php'
		]);
	}

	/**
	 * Add the TD elements, CPT, ACF filters, cron, and shortcodes,
	 * and load the Front Page Card posts.
	 */
    function run_on_startup(): void {

    	MJKFP_TD::add_hooks();
        
        // Add front page card custom post type
        MJKFP_CPT_FPCard::add_hooks($spage_order=20);
        
        MJKFP_ACF::add_filters();
	    MJKFPLoader::activate_cron();
        MJKFPLoader::add_hooks();
        MJKFP_Shortcodes::add_shortcodes();
    }

	/**
	 * Deactivate the cron.
	 */
	function run_on_pause(): void { MJKFPLoader::deactivate_cron(); }

	/**
	 * Deactivate the cron.
	 */
	function run_on_deactivate(): void { MJKFPLoader::deactivate_cron(); }


	/*
	 * =========================================================================
	 * Front Page Cards
	 * =========================================================================
	 */

	/**
	 * @return MJKFP_FPCard[]
	 */
	function fpcards(): array { return $this->fpcards; }

	/**
	 * @return bool
	 */
	function fpcards_loaded(): bool { return $this->fpcards_loaded; }

	/**
	 * Set the loaded state to true.
	 */
	function set_fpcards_loaded(): void { $this->fpcards_loaded = true; }

	/**
	 * Add the given Front Page Card, keying it by its start date.
	 *
	 * @param MJKFP_FPCard $card
	 */
	function add_fpcard(MJKFP_FPCard $card): void {
		$utime = $card->get_start()->format('U');
		$this->fpcards[$utime] = $card;
	}
}
