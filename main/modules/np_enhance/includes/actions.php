<?php

/**
 * Adds actions to modify how Newspaper works.
 */
final class MJKNPE_Actions {

	/**
	 * Add the hooks.
	 */
	static function add_hooks(): void {
		add_action('init', [__CLASS__, 'replace_author_template']);
		add_action('init', [__CLASS__, 'remove_user_contactmethods']);
		add_action('init', [__CLASS__, 'remove_update_view_ajax']);
	}

	/**
	 * Remove the many unnecessary contact methods on the user edit screen,
	 * if the option is set.
	 */
	static function remove_user_contactmethods(): void {
		if (MJKNPE_ACF_Options::get(MJKNPE_ACF_Options::disable_contact)) {
			remove_filter(
				'user_contactmethods',
				'td_extra_contact_info_for_author'
			);
		}
	}

	/**
	 * Remove the Ajax action that counts views, if the option is set.
	 */
	static function remove_update_view_ajax(): void {
		if (MJKNPE_ACF_Options::get(MJKNPE_ACF_Options::disable_viewcount)) {
			remove_action(
				'wp_ajax_nopriv_td_ajax_update_views',
				'td_ajax_update_views');

			remove_action(
				'wp_ajax_td_ajax_update_views',
				'td_ajax_update_views');
		}
	}

	/**
	 * Redirect the author.php template to our own.
	 *
	 * Credit: https://wordpress.stackexchange.com/questions/155871
	 */
	static function replace_author_template(): void {
		if (MJKNPE_ACF_Options::get(MJKNPE_ACF_Options::replace_author_page)) {
			add_filter('template_include', function (string $template): string {
				if (is_author()) return JKNAPI::mpath() . 'templates/author.php';
				return $template;
			});
		}
	}
}
