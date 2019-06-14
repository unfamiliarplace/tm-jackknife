<?php

/**
 * An emailer for the 'almost' user update.
 */
final class MJKSC_Almost_Email {

	/*
	 * =========================================================================
	 * Content of the email
	 * =========================================================================
	 */

	const break = '<br>';
	const double = self:: break . self::break;

	const subject = 'Staff Check: almost eligible';
	const intro = 'Hello,' . self::double . 'This is your report on users that'
		. ' are almost eligible for a staff title or to vote. If this week\'s'
	    . ' issue brings them over, print their title or let them vote.';

	const empty = 'Result: No users were found near enough to qualification.';

	const threshold_staff = 'The threshold for staff this semester is %s'
		. ' contributions (article authored or photo taken).';

	const threshold_vote = 'The threshold for voting this semester is %s'
		. ' separate issues contributed to (any kind of contribution).';

	const messages = [
		'writers'   => 'These users are almost eligible for Staff Writer:',
		'photos'    => 'These users are almost eligible for Staff Photographer:',
		'voters'    => 'These users are almost eligible to vote:'
	];

	const outtro = 'That\'s all! Have a good week.' . self::double . 'Yours,' .
	               self::break . 'Staff Check';


	/*
	 * =========================================================================
	 * Notification controller
	 * =========================================================================
	 */

	/**
	 * Respond to a set of users who almost qualify for a position or to vote.
	 * $almosts contains up to 3 keys: 'writers', 'photographers', 'voters',
	 * each pointing to an array of MJKSC_Users.
	 *
	 * Send an email about them if the option is on and there is any content.
	 *
	 * @param array[] $almosts
	 * @param int $threshold
	 */
	static function notify(array $almosts, int $threshold): void {

		// Check the recipients and bail if there are none
		$recipients = MJKSC_API::almost_recipients();
		if (empty($recipients)) return;

		// Calculate the total to check whether this is a pointless email
		$total = array_sum(array_map(function (array $g): int {
				return count($g);
			}, $almosts));

		// If there is something to report
		if ($total && !empty($almosts)) {
			$message = self::format_message($almosts, $threshold);

		// If there is nothing to report
		} else {

			// Whether to send an empty note. Bail if not allowed
			$send_empty = MJKSC_ACF::get(MJKSC_ACF::auto_email_none);
			if (! (bool) $send_empty) return;

			// Otherwise format the empty message
			$message = self::format_empty_message();
		}

		// Send the email
		$email = new MJKSC_Email($recipients, self::subject, $message);
		$email->send();
	}


	/*
	 * =========================================================================
	 * Formatters
	 * =========================================================================
	 */

	/**
	 * Return a formatted message.
	 * $almosts is as described in 'notify'.
	 *
	 * @param array[] $almosts
	 * @param int $threshold The number users would have needed to qualify.
	 * @return string
	 */
	static function format_message(array $almosts, int $threshold): string {

		// Introduction
		$message = self::intro;

		// Array up the main parts
		$parts = [];

		// Any photographers?
		if (!empty($almosts['photographers'])) {
			$u = self::format_users($almosts['photographers'], $threshold);
			$parts[] = self::messages['photos'] . self::break . $u;
		}

		// Any writers?
		if (!empty($almosts['writers'])) {
			$u = self::format_users($almosts['writers'], $threshold);
			$parts[] = self::messages['writers'] . self::break . $u;
		}

		// If either of the above, do the 'staff' threshold message
		if (!empty($parts)) {
			$threshold_note = sprintf(self::threshold_staff, $threshold);
			$message .= sprintf('%s%s%s%s', self::double, $threshold_note,
				self::double, implode(self::double, $parts));
		}

		// Any voters?
		if (!empty($almosts['voters'])) {
			$u = self::format_users($almosts['voters'], $threshold);
			$voters = self::messages['voters'] . self::break . $u;

			// If any, do the 'voters' threshold message
			$threshold_note = sprintf(self::threshold_vote, $threshold);
			$message .= sprintf('%s%s%s%s', self::double, $threshold_note,
				self::double, $voters);
		}

		// Bundle it all up
		return $message . self::double . self::outtro;
	}

	/**
	 * Return a suitable "no changes" message.
	 *
	 * @return string
	 */
	static function format_empty_message(): string {
		return self::intro . self::double . self::empty .
		       self::double . self::outtro;
	}

	/**
	 * Return a formatted list of users.
	 *
	 * @param MJKSC_User[] $users
	 * @param int $threshold The number needed to qualify.
	 * @return string
	 */
	static function format_users(array $users, int $threshold): string {
		$list = '';

		// Each user becomes "Name (X/Y contribs)"
		foreach($users as $id => $contribs) {
			$name = get_user_by('id', $id)->display_name;
			$list .= sprintf('%s (%s/%s contributions so far this semester)%s',
				$name, $contribs, $threshold, self::break);
		}

		// Remove the last extra line break and return
		$list = JKNStrings::replace_last(self::break, '', $list);
		return $list;
	}
}
