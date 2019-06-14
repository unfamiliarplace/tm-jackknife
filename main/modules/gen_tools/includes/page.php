<?php

/**
 * Represents a generatable page. Hangs on to a renderer, accesses a WP page ID,
 * and schedules generations.
 */
class MJKGenToolsPage {
    use JKNCron_OneHook;
    
    protected $id;
	protected $name;
	protected $source;
	protected $renderer;
	protected $settings_pages = [];


	/*
	 * =========================================================================
	 * Set up
	 * =========================================================================
	 */

	/**
	 * Extract and derive arguments and set up settings.
	 * $args:
	 *      id          string: azAZ09_
	 *      name        string
	 *      source      string: fills {} in "The data is sourced from {}."
	 *      renderer    JKNRenderer
	 *      settings    (optional) array of JKNSettingsPages,
	 *                  each of which will trigger generation when it is saved
	 *
	 * @param array $args
	 */
    function __construct(array $args) {
        
        // Extract arguments
        $this->id       = $args['id'];
        $this->name     = $args['name'];
        $this->source   = $args['source'];
        $this->renderer = $args['renderer'];
        
        // Derive settings
        if (isset($args['settings'])) $this->settings_pages = $args['settings'];
        add_action('admin_menu', [$this, 'hook_settings_generation']);
        
        // Do admin notices
        if (is_admin() && !wp_doing_ajax()) {
            $this->wp_page_admin_notice();
            $this->settings_pages_admin_notices();
        }
        
        // Set up cron
        $this->activate_cron();
        
        // Disable wpautop for this post
        $pid = $this->pid();
        if (!empty($pid)) JKNEditing::disable_wpautop_by_pid($this->pid());
    }
    
    /**
     * Return the ID of this page.
     *
     * @return string
     */
    final function id(): string { return $this->id; }

	/**
	 * Return the name of this page.
	 *
	 * @return string
	 */
	final function name(): string { return $this->name; }

	/**
	 * Return the source of this page.
	 *
	 * @return string
	 */
	final function source(): string { return $this->source; }


	/*
	 * =========================================================================
	 * Settings generation
	 * =========================================================================
	 */
    
    /**
     * Register page generation on the savings of each settings page.
     */
	final function hook_settings_generation(): void {
        
        // Bail if this is an AJAX request (the menu is not loaded)
        if (wp_doing_ajax()) return;
        
        // Bail if there are none
        if (empty($this->settings_pages)) return;
        
        // Otherwise, extract each page and its IDs
        foreach($this->settings_pages as $spage) {
            MJKGTSettingsGeneration::add_gen($spage, $this->id);
        }
    }
    
    /**
     * Return an array of settings pages that will generate this page.
     *
     * @return JKNSettingsPage[]
     */
    final function settings_pages(): array { return $this->settings_pages; }


	/*
	 * =========================================================================
	 * Admin notices
	 * =========================================================================
	 */
    
    /**
     * Create the admin notice on the WP page where this is generated.
     */
	protected final function wp_page_admin_notice(): void {
        global $pagenow;
        
        if (($pagenow == 'post.php') &&
            isset($_GET['post']) && ($_GET['post'] == $this->pid())) {
            add_action('admin_notices', [$this, 'print_wp_page_notice']);
        }
    }
    
    /**
     * Create the admin notice on any settings pages that generate this page.
     */
	protected final function settings_pages_admin_notices(): void {
        
        // Bail early if there are none
        if (empty($this->settings_pages)) return;
        
        // Otherwise, have each one add admin notices on its loading
        foreach($this->settings_pages as $spage) {  
            
            $id = $this->id;
            add_action($spage->load_hook(), function() use ($id) {
                add_action('admin_notices', function() use ($id) {
                    $this->print_settings_page_notice($id);
                });
            });
        }
    }    
    
    /**
     * Print the admin notice for a WP page.
     */
	final function print_wp_page_notice(): void {
        $mod = JKNAPI::module();
        $mod_name = $mod->name();
        $spage_url = $mod->settings_page()->url();
        
        $link = sprintf('<a href="%1$s" title="%2$s">%2$s</a>',
                $spage_url, sprintf('%s Scheduler', $mod_name));

        $msg = sprintf('This page is managed by %s. The page "%s"'
                . ' is set to generate and replace the content.'
                . ' Go to the %s to change this setting.',
                $mod_name, $this->name, $link);

        printf('<div class="notice notice-info"><p>%s</p></div>', $msg);
    }
    
    /**
     * Print the admin notice for a settings page.
     *
     * @param string $page_id
     */
	final function print_settings_page_notice(string $page_id): void {

		// Get the Scheduler's URL and the name of the page that will generate.
        $scheduler_url = JKNAPI::settings_page()->url();
        $gen_page_name = MJKGTAPI::page($page_id)->name;
        
        $link = sprintf('<a href="%1$s" title="%2$s">%2$s</a>',
                $scheduler_url, JKNAPI::module()->name());
        
        $msg = sprintf('This settings page is registered with %s. Saving it'
                . ' will regenerate the "%s" page.', $link,  $gen_page_name);
        
        printf('<div class="notice notice-info"><p>%s</p></div>', $msg);
    }


	/*
	 * =========================================================================
	 * Generation
	 * =========================================================================
	 */
    
    /**
     * Return the callback to run on the scheduled hook.
     *
     * @return callable
     */
	protected final function get_cron_callback(): callable {
		return [$this, 'generate'];
	}

    /**
     * Generate and update the page. Save a record of when it was last generated
     * and the result. If there was an error message, return a non-empty string.
     *
     * @return string|null An error message or null if the generation succeeded.
     */
    final function generate(): ?string {
        
        // If the attempt to generate hasn't already been noted
        $this->update_last_gen_success(false);
        $this->update_last_gen(time());

		// Get the current pid option
	    $pid = $this->pid();

        // If we don't have a page
        if (empty($pid)) {
	        return sprintf(
	        	'MJK Generation Tools: "%s" could not generate,'
                . ' because no WordPress page was specified.',
		        $this->name);

        // If we have a page
        } else {
            $this->update_post($pid);
            $this->update_last_gen_success(true);
            return null;
        }
    }

	/**
	 * Render the content and update the post.
	 *
	 * @param string $pid The ID of the post to update.
	 */
	protected final function update_post(string $pid): void {

		// Allow KSes
		$this->renderer::allow_kses();

		// Render and update page
		$content = $this->get_content();
		$page_array = ['ID' => (int) $pid, 'post_content' => $content];
		wp_update_post($page_array, true);

		// Disallow KSes
		$this->renderer::disallow_kses();
	}

	/**
	 * Return the content to update the page with.
	 * This is a separate function so as to be easily overridden.
	 *
	 * @return string
	 */
	protected function get_content(): string {
		return $this->renderer::render();
	}


	/*
	 * =========================================================================
	 * Options
	 * =========================================================================
	 */
    
    /**
     * Return a name qualified by this page's ID.
     *
     * @param string $name
     * @return string
     */
	protected final function qualify(string $name): string {
        return sprintf('%s_%s', $this->id, $name);        
    }
    
    /**
     * Return the given option name qualified.
     *
     * @param string $opt
     * @return string
     */
    final function qualify_option(string $opt): string {
        return JKNOpts::qualify($this->qualify($opt));
    }
    
    /**
     * Return the value of the given option after qualification.
     *
     * @param string $opt
     * @param mixed|null $default The default value to return if not set.
     * @return mixed
     */
    final function get_option(string $opt, $default=null) {
        return JKNOpts::get($this->qualify($opt), $default);
    }
    
    /**
     * Update the value of an option.
     *
     * @param string $opt The option name.
     * @param mixed $value The value to set.
     * @return bool If it was updated.
     */
    final function update_option(string $opt, $value): bool {
        return JKNOpts::update($this->qualify($opt), $value);
    }

    /**
     * Return the ID of the currently chosen WP page.
     *
     * @return string|null The ID, if one is set; otherwise null.
     */
    final function pid(): ?string {
    	return $this->get_option('pid', null);
    }

    /**
     * Update the pid.
     *
     * @param string $pid The value to set.
     * @return bool Whether it was updated.
     */
    final function update_pid(string $pid): bool {
        return $this->update_option('pid', $pid);        
    }
    
    /**
     * Return the timestamp of the last attempt to generate.
     *
     * @return int The time it was last generated, or 0 if never.
     */
    final function last_gen(): int {
    	return (int) $this->get_option('last_gen');
    }
    
    /**
     * Update the last attempt to generate.
     *
     * @param int $ts A timestamp for thte last generation attempt.
     * @return bool If it was updated.
     */
    final function update_last_gen(int $ts): bool {
        return $this->update_option('last_gen', $ts);
    }
    
    /**
     * Return the success status of the last attempt to generate.
     *
     * @return bool True iff there was a successful last attempt.
     */
    final function last_gen_success(): bool {
        return (bool) $this->get_option('last_gen_success');
    }
    
    /**
     * Update the last attempt to generate.
     *
     * @param bool $success Whether it succeeded or not.
     * @return bool Whether the value was updated.
     */
    final function update_last_gen_success(bool $success): bool {
        return $this->update_option('last_gen_success', $success);
    }


	/*
	 * =========================================================================
	 * Scheduling
	 * =========================================================================
	 */
    
    /**
     * Get a qualified hook name (overrides JKNCron to ensure a unique page ID).
     *
     * @return string
     */
	final function hook(): string {
        return $this->qualify_option('generate_hook');        
    }

	/**
	 * Update the schedule, setting it with the given recurrence, hour of the
	 * day, and day of the week at which to run.
	 *
	 * It will not be scheduled if no page ID is set; therefore this function
	 * looks up the current post ID. If you already know the ID, you can pass
	 * it to save a database query.
	 *
	 * @param string $rec
	 * @param int $hour
	 * @param int $day
	 * @param string $pid
	 */
	final function update_schedule(string $rec, int $hour, int $day,
            string $pid='') {
        
        if (empty($pid)) $pid = $this->pid();
        
        // Clear the schedule (this means 'Never' was chosen)
        if (empty($rec) || empty($pid)) {
            $this->clear_schedule();
       
        // Reschedule if a frequency was chosen and there's a pid
        } else {
            $this->schedule($overwrite=true, $rec, $min=0, $hour, $day);
        }
    }
}
