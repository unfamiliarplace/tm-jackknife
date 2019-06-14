<?php

/**
 * A scheduler for the update user action.
 */
final class MJKSC_Update {
	use JKNCron_OneHook_Static;

	/**
	 * Return the 'do' method for a cron callback.
	 *
	 * @return callable
	 */
	static function get_cron_callback(): callable { return [__CLASS__, 'do']; }

	/**
	 * Clear the schedule and, if the option is set, re-add it.
	 */
	static function reset_schedule(): void {
		self::clear_schedule();

		$auto_add = (bool) MJKSC_ACF::get(MJKSC_ACF::auto_add);
		$auto_remove = (bool) MJKSC_ACF::get(MJKSC_ACF::auto_remove);
		if ($auto_add || $auto_remove) self::update_schedule();
	}

	/**
	 * Add/readd the schedule.
	 */
	static function update_schedule(): void {
		$day = (int) MJKSC_ACF::get(MJKSC_ACF::auto_day);
		$time = MJKSC_ACF::get(MJKSC_ACF::auto_time);
		list($hour, $min) = explode(':', $time);
		self::schedule($overwrite=false, 'weekly', (int) $min, (int) $hour, $day);
	}

	/**
	 * Do the main action: gather users who need to have roles added or removed,
	 * then do so and send an email about it.
	 */
	static function do(): void {

		// Bail if there is no SC yet for this year
		$now = JKNTime::dt_now();
		$ay = MJKMHAPI::newest_year();
		if ($now > $ay->end_of_winter()) return;

		// Get the add and remove options and make sure we're going forward
		$add = (bool) MJKSC_ACF::get(MJKSC_ACF::auto_add);
		$remove = (bool) MJKSC_ACF::get(MJKSC_ACF::auto_remove);
		if (!$add && !$remove) return;

		// Get the users to update, filter by our options, and update
		$to_update = MJKSC_API::users_to_update();
		$to_update = MJKSC_API::filter_updates($to_update, $add, $remove);
		MJKSC_API::update_users($to_update, $ay);

		// Regenerate the page if possible
		if (empty($to_update)) {
			$generated = false;
		} else {
			$generated = MJKSC_API::generate_page();
		}

		// Send the update email
		MJKSC_Update_Email::notify($to_update, $generated);
	}
}
