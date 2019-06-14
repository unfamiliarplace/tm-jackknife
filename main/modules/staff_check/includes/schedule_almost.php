<?php

/**
 * A scheduler for the 'almost' action.
 */
final class MJKSC_Almost {
	use JKNCron_OneHook_Static;

	/**
	 * Return the 'do' function for a cron callback.
	 *
	 * @return callable
	 */
	static function get_cron_callback(): callable { return [__CLASS__, 'do']; }

	/**
	 * Clear the schedule, and if it's still set to go, re-add it.
	 */
	static function reset_schedule(): void {
		self::clear_schedule();

		$almost = (bool) MJKSC_ACF::get(MJKSC_ACF::almost);
		if ($almost) self::update_schedule();
	}

	/**
	 * Add/readd the schedule.
	 */
	static function update_schedule(): void {
		$day = (int) MJKSC_ACF::get(MJKSC_ACF::almost_day);
		$time = MJKSC_ACF::get(MJKSC_ACF::almost_time);
		list($hour, $min) = explode(':', $time);
		self::schedule($overwrite=false, 'weekly', (int) $min, (int) $hour, $day);
	}

	/**
	 * Do the callback: tally up users who almost qualify to vote or for a
	 * position, and send an email abot it.
	 */
	static function do(): void {

		// Bail if there is no SC yet for the year
		$now = JKNTime::dt_now();
		$ay = MJKMHAPI::newest_year();
		if ($now > $ay->end_of_winter()) return;

		// Get the almost-users, unpack the threshold and unset it
		$almosts = MJKSC_API::almost();
		$threshold = $almosts['threshold'];
		unset($almosts['threshold']);

		// Send the email
		MJKSC_Almost_Email::notify($almosts, $threshold);
	}
}
