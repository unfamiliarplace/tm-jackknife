<?php

/**
 * Provides authorship and subtitle metadata for articles.
 */
final class MJKMeta extends JKNModule {

	/*
	 * =========================================================================
	 * Module registration
	 * =========================================================================
	 */
    
    /**
     * Return the ID of this module.
     *
     * @return string
     */
    function id(): string { return 'meta'; }
    
    /**
     * Return the name of this module.
     *
     * @return string
     */
    function name(): string { return 'Article Meta'; }
    
    /**
     * Return the description of this module.
     *
     * @return string
     */
    function description(): string {
        return 'Provides authorship and subtitle metadata for articles.';
    }

	/**
	 * Autoload the classes.
	 */
	function run_on_load(): void {
    	JKNClasses::autoload([
    		'MJKMetaAPI'            => 'includes/api.php',
		    'MJKMeta_Subtitles'     => 'includes/subtitles/subtitles.php',
		    'MJKMeta_Authorship'    => 'includes/authorship/authorship.php'
	    ]);
	}
    
    /**
     * Add the ACF groups' filters.
     */
    function run_on_startup(): void {
        MJKMeta_Subtitles::set_up();
        MJKMeta_Authorship::set_up();
    }
}
