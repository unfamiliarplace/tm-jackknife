<?php

/**
 * An emailer for the update user notification.
 */
final class MJKSC_Update_Email {

	/*
	 * =========================================================================
	 * Content of the email
	 * =========================================================================
	 */

	const break = '<br />';
	const double = self:: break . self::break;

	const subject = 'Staff Check: updated users';
	const intro = 'Hello,' . self::double . 'This is your report on users that'
		. ' have had Staff Writer & Photographer roles added or deleted.';

	const messages = [
		'add_writers'           => 'Staff Writer role added:',
		'add_photographers'     => 'Staff Photographer role added:',
		'remove_writers'        => 'Staff Writer role removed:',
		'remove_photographers'  => 'Staff Photographer role removed:',
		'generated_true'        => 'The Staff Check page was also regenerated.',
		'generated_false'       => 'The Staff Check page could not be regenerated.'
	];

	const empty = 'Result: No users were found with mis-assigned roles, so no' .
	              ' changes have been made.';

	const outtro = 'That\'s all! Have a good week.' . self::double . 'Yours,' .
	               self::break . 'Staff Check';


	/*
	 * =========================================================================
	 * Notification handler
	 * =========================================================================
	 */

	/**
	 * Respond to a set of users who have gained or lost a staff role.
	 * $to_update contains up to 4 keys: 'add_writers', 'add_photographers',
	 * 'remove_writers, and 'remove_photographers', each pointing to an array
	 * of MJKSC_Users.
	 *
	 * Send an email about them if the option is on and there is any content.
	 *
	 * @param array[] $to_update
	 * @param bool $generated Whether the page was also generated on update.
	 * @param bool $force_suppress_empty Whether to override the empty setting.
	 */
	static function notify(array $to_update, bool $generated,
			bool $force_suppress_empty=false): void {

		// Check the recipients and bail if there are none
		$recipients = MJKSC_API::update_recipients();
		if (empty($recipients)) return;

		// Determine total users affected to see if this is a pointless email
		$total = array_sum(array_map(function (array $g): int {
			return count($g);
		}, $to_update));

		// No changes to report
		if (!$total || empty($to_update)) {
			if ($force_suppress_empty) return;

			// Whether we're allowed to send a "no changes" email. Bail if not.
			$if_empty = MJKSC_ACF::get(MJKSC_ACF::auto_email_none);
			if (! (bool) $if_empty) return;

			// Format the "no changes" message
			$message = self::format_empty_message();

		// Non-empty changes
		} else {
			$message = self::format_message($to_update, $generated);
		}

		// Send the email
		$email = new MJKSC_Email($recipients, self::subject, $message);
		$email->send();
	}


	/*
	 * =========================================================================
	 * Formatting
	 * =========================================================================
	 */

	/**
	 * Format the email. $to_update is as in 'notify'.
	 *
	 * @param array[] $to_update
	 * @param bool $generated
	 * @return string
	 */
	static function format_message(array $to_update, bool $generated): string {

		// Get all the like parts together
		$parts = [];

		// Any writers added?
		if (isset($to_update['add_writers']) &&
		    !empty($to_update['add_writers'])) {

			$u = self::format_users($to_update['add_writers']);
			$parts[] = self::messages['add_writers'] . self::break . $u;
		}

		// Any photographers added?
		if (isset($to_update['add_photographers']) &&
		    !empty($to_update['add_photographers'])) {

			$u = self::format_users($to_update['add_photographers']);
			$parts[] = self::messages['add_photographers'] . self::break . $u;
		}

		// Any writers removed?
		if (isset($to_update['remove_writers']) &&
		    !empty($to_update['remove_writers'])) {

			$u = self::format_users($to_update['remove_writers']);
			$parts[] = self::messages['remove_writers'] . self::break . $u;
		}

		// Any photographers removed?
		if (isset($to_update['remove_photographers']) &&
		    !empty($to_update['remove_photographers'])) {

			$u = self::format_users($to_update['remove_photographers']);
			$parts[] = self::messages['remove_photographers'] . self::break . $u;
		}

		// Add the message about generation
		$gen_note = $generated ? self::messages['generated_true'] :
			self::messages['generated_false'];

		// Bundle it all up
		return self::intro . self::double . implode(self::double, $parts) .
		       self::double . $gen_note . self::double . self::outtro;
	}

	/**
	 * Return a formatted "no changes" message.
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
	 * @return string
	 */
	static function format_users(array $users): string {
		$list = '';

		// Each one will just be a name
		foreach($users as $id => $u) {
			$name = get_user_by('id', $id)->display_name;
			$list .= sprintf('%s%s', $name, self::break);
		}

		// Remove the last extra line break
		$list = JKNStrings::replace_last(self::break, '', $list);
		return $list;
	}
}
