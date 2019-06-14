<?php

/**
 * Determines a template to load based on a post.
 */
class MJKNPE_TemplateSwitcher {

	/*
	 * =========================================================================
	 * Templates
	 * =========================================================================
	 */

	const template_default      = 'single_template_82.php';
	const template_imageless    = 'single_template_83.php';
	const template_video        = 'single_template_84.php';

	/*
	 * =========================================================================
	 * Switching
	 * =========================================================================
	 */

	/**
	 * Return an absolute path to a template to load based on a given post.
	 *
	 * @param WP_Post $p
	 * @return string
	 */
	static function get_template(WP_Post $p): string {

		if (self::use_video($p)) {
			$template = self::template_video;

		} elseif(self::use_imageless($p)) {
			$template = self::template_imageless;

		} else {
			$template = self::template_default;
		}

		return self::locate($template);
	}

	/**
	 * Take a template filename and return the absolute path.
	 *
	 * @param string $template
	 * @return string
	 */
	static function locate(string $template): string {
		$base = JKNAPI::mpath();
		return sprintf('%s/includes/td_api/Newspaper/%s', $base, $template);
	}


	/*
	 * =========================================================================
	 * Switch logic
	 * =========================================================================
	 */

	/**
	 * Return true iff the given post requires a video template.
	 *
	 * @param WP_Post $p
	 * @return bool
	 */
	static function use_video(WP_Post $p): bool {

		// Based solely on post format
		return get_post_format($p->ID) == 'video';
	}


	/**
	 * Return true iff the given post requires an imageless template.
	 *
	 * @param WP_Post $p
	 * @return bool
	 */
	static function use_imageless(WP_Post $p): bool {

		// If this post doesn't even have a featured image
		if (!has_post_thumbnail($p->ID)) return true;

		// If its personal 'hide' option is checked
		if (MJKNPE_ACF::get(MJKNPE_ACF::hide_feat, $p->ID)) return false;

		// If its category's 'hide' option is checked
		$excl = MJKNPE_ACF_Options::get(MJKNPE_ACF_Options::hide_feat_image_cats);
		foreach(wp_get_post_categories($p->ID, ['fields' => 'ids']) as $cat) {
			if (in_array($cat, $excl)) return true;
		}

		// Otherwise we're good to use an image
		return false;
	}
}
