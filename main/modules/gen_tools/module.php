<?php

/**
 * Gen Tools provides tools for rendering dynamic page content and saving it
 * automatically to a WP page, either whenever the user wants or when the
 * data is updated or on a regular schedule.
 */
final class MJKGenTools extends JKNModule {

	private $pages = [];

	/*
	 * =========================================================================
	 * Module registration
	 * =========================================================================
	 */

	/**
	 * @return string
	 */
	function id(): string { return 'gen_tools'; }

	/**
	 * @return string
	 */
	function name(): string { return 'Generation Tools'; }

	/**
	 * @return string
	 */
	function description(): string {
		return 'Tools for automatically generating various pages.';
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
			'MJKGTAPI'                  => 'includes/api.php',
			'MJKGenToolsPage'           => 'includes/page.php',
			'MJKGenToolsPageSwitch'     => 'includes/page_switch.php',
			'MJKGTSchedulerAJAX'        => 'includes/scheduler_ajax.php',
			'MJKGTScheduler'            => 'includes/scheduler.php',
			'MJKGTSettingsGeneration'   => 'includes/settings_generation.php'
		]);
	}

	/**
	 * Actually do nothing on startup (but must implement abstract function).
	 */
	function run_on_startup(): void {}

	/**
	 * Deactivate the cron.
	 */
	function run_on_deactivate(): void { $this->clear_all_cron(); }

	/**
	 * Remove all cron jobs originating from this module.
	 * Since deactivation of this module leads to pages not being registered,
	 * it's not possible to simple use their deactivation cron method.
	 */
	function clear_all_cron(): void {
		$wp_cron = get_option('cron');

		// Unset each page's cron
		foreach ($wp_cron as $ts => $hooks) {
			foreach ($hooks as $hook => $data) {
				if (JKNStrings::starts_with($hook, $this->prefix())) {
					unset($wp_cron[$ts][$hook]);
					if (empty($wp_cron[$ts])) {
						unset($wp_cron[$ts]);
					}
				}
			}
		}

		// Update the WP option
		update_option('cron', $wp_cron);
	}


	/*
	 * =========================================================================
	 * Page registry
	 * =========================================================================
	 */

	/**
	 * Return the pages registered to this module.
	 *
	 * @return MJKGenToolsPage[]
	 */
	function pages(): array { return $this->pages; }

	/**
	 * Return the page with the given ID.
	 *
	 * @param string $id
	 * @return MJKGenToolsPage
	 */
	function page(string $id): MJKGenToolsPage { return $this->pages[$id]; }

	/**
	 * Add the given page to the catalogue.
	 *
	 * @param MJKGenToolsPage $page
	 */
	function add_page(MJKGenToolsPage $page): void {
		$this->pages[$page->id()] = $page;
	}
}
