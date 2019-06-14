<?php

/**
 * Creates a Disqus latest comments shortcode and widget.
 */
final class MJKDisqusComments extends JKNModule {

    private $cache_object;

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
    function id(): string { return 'disqus'; }
    
    /**
     *
     * @return string
     * Return the name of this module.
     */
    function name(): string { return 'Disqus Latest Comments'; }
    
    /**
     * Return the description of this module.
     *
     * @return string
     */
    function description(): string {
        return 'Creates a Disqus latest comments shortcode and widget.';
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
			'MJKDisqus_ACF'         => 'includes/acf_api/registry.php',
			'MJKDisqusTools'        => 'includes/tools.php',
			'MJKDisqus_Shortcode'   => 'front/shortcode.php',
			'MJKDisqus_Widget'      => 'front/widget.php'
		]);
	}
    
    /**
     * Set up the ACF group, cache, cron, shortcode, and widget.
     */
    function run_on_startup(): void {
        MJKDisqus_ACF::add_filters();

        $this->create_cache();

        MJKDisqusTools::activate_cron();
        MJKDisqus_Shortcode::add_shortcode();
        
        // Register widget actions
        add_action('widgets_init', function(): void {
            register_widget('MJKDisqus_Widget');            
        });
    }
    
    /**
     * Set the schedule. (Can't be done earlier because ACF isn't initialized.)
     */
    function run_on_init(): void { MJKDisqusTools::update_schedule(); }

	/**
	 * Deactivate the cron.
	 */
	function run_on_pause(): void { MJKDisqusTools::deactivate_cron(); }

	/**
	 * Deactivate the cron.
	 */
	function run_on_deactivate(): void { MJKDisqusTools::deactivate_cron(); }

	/**
	 * Delete the cache.
	 */
	function run_on_uninstall(): void {
		$root = new JKNCacheRoot();
		$root->purge();
	}

	/**
	 * Create a cache object for the Disqus API data.
	 */
	function create_cache(): void {

		$root = new JKNCacheRoot();

		$dir = new class($root) extends JKNCacheDir {
			final function id(): string { return 'disqus_api'; }
		};

		$this->cache_object = new class($dir) extends JKNCacheObject {
			final function fname(): string { return 'latest_comments.txt'; }
			final function fetcher(array $args=[]): callable {
				return ['MJKDisqusTools', 'cache'];
			}
		};
	}
    
    /**
     * Return the cache object.
     *
     * @return JKNCacheObject
     */
    function get_cache(): JKNCacheObject { return $this->cache_object; }
}
