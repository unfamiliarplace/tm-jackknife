<?php

/**
 * Functions for organizing Staff Check and making its info easier to reuse.
 */
final class MJKSC_API {

	/*
	 * =========================================================================
	 * Static
	 * =========================================================================
	 */

	static $almost_recipients;
	static $update_recipients;

	/*
	 * =========================================================================
	 * Timing
	 * =========================================================================
	 */

	/**
	 * Return true iff the given post ID represents a post made in fall.
	 * Can optimize performance by passing its year if already known;
	 * otherwise a year will be derived from the post.
	 *
	 * @param string $pid
	 * @param JKNAcademicYear|null $year
	 * @return bool
	 */
	static function is_fall_post(string $pid, JKNAcademicYear $year=null): bool {
		$dt = JKNTime::dt_pid($pid);
		if (is_null($year)) $year = JKNAcademicYear::make_from_dt($dt);

		return ($year->start_of_fall() <= $dt) &&
		       ($dt <= $year->end_of_fall());
	}

	/**
	 * Return true iff the given post ID represents a post made in winter.
	 * Can optimize performance by passing its year if already known;
	 * otherwise a year will be derived from the post.
	 *
	 * @param string $pid
	 * @param JKNAcademicYear|null $year
	 * @return bool
	 */
	static function is_winter_post(string $pid, JKNAcademicYear $year=null): bool {
		$dt = JKNTime::dt_pid($pid);
		if (is_null($year)) $year = JKNAcademicYear::make_from_dt($dt);

		return ($year->start_of_winter() <= $dt) &&
		       ($dt <= $year->end_of_winter());
	}

	/**
	 * @return JKNAcademicYear
	 */
	static function earliest_year(): JKNAcademicYear {
		$first_post = get_posts([
			'numberposts' => 1,
			'post_status' => 'publish',
			'order' => 'ASC'
		]);

		return new JKNAcademicYear(JKNTime::dt_post($first_post[0]));
	}


	/*
	 * =========================================================================
	 * Posts
	 * =========================================================================
	 */

	/**
	 * Return all the posts made in the given staff check object's year.
	 * These are derived from the volume of its year and are hence limited
	 * to the categories used in MJKVI_Issue. Those categories are flattened
	 * in the resulting array but saved to each post object.
	 *
	 * @param MJKSC_StaffCheck $sc
	 * @return MJKSC_Post[]
	 */
	static function gather_posts(MJKSC_StaffCheck $sc): array {
		$year = $sc->year();
		$vol = MJKVIAPI::get_volume_by_academic_year($year);

		$cat_to_posts = $vol->get_posts($flatten=false);

		$posts = [];
		foreach($cat_to_posts as $cat => $cat_posts) {
			$sc_posts = array_map(
				function (WP_Post $p) use ($cat, $year): MJKSC_Post {
					return self::make_post($p->ID, $cat, $year);
				}, $cat_posts);

			$posts = array_merge($posts, $sc_posts);
		}

		return array_unique($posts);
	}

	/**
	 * Construct a Staff Check post based on the given data, storing its
	 * ID, main category slug, whether it was made in fall, and its issue.
	 *
	 * @param string $pid
	 * @param string $cat
	 * @param JKNAcademicYear $year
	 * @return MJKSC_Post
	 */
	static function make_post(string $pid, string $cat,
			JKNAcademicYear $year): MJKSC_Post {

		$fall = self::is_fall_post($pid, $year);
		$issue = MJKVIAPI::get_post_issue($pid)->get_url();
		return new MJKSC_Post($pid, $cat, $fall, $issue);
	}


	/*
	 * =========================================================================
	 * Users
	 * =========================================================================
	 */

	/**
	 * Return the users active in the given staff check object's year,
	 * deriving them from its list of posts. Store their contributions.
	 * An active user is defined as one that is either on the masthead
	 * or has made a contribution to at least one post.
	 *
	 * @param MJKSC_StaffCheck $sc
	 * @return MJKSC_User[]
	 */
	static function gather_users(MJKSC_StaffCheck $sc): array {
		$users = [];
		$year = $sc->year();
		$excluding_comic = $sc->excluding_comic();

		// Go through each post
		foreach($sc->posts() as $p) {
			$pid = $p->id();

			// Exclude comics if required
			if ($excluding_comic) {

				$slugs = array_map(function (WP_Term $cat): string {
					return $cat->slug;
				}, get_the_category($pid));

				if (in_array('comic', $slugs)) continue;
			}

			// A table of type of contribution to contributors of that kind
			$types = [
				'author'    => MJKMetaAPI::authors($pid),
				'notes'     => MJKMetaAPI::notes_contributors($pid),
				'photo'     => MJKMetaAPI::photographers($pid),
				'video'     => MJKMetaAPI::videographers($pid)
			];

			// Go through each type and its contributors
			foreach($types as $type => $contributors) {

				// Go through each contributor
				foreach($contributors as $user_array) {

					// Set up the user
					$uid = $user_array['ID'];
					if (!isset($users[$uid])) {
						$users[$uid] = new MJKSC_User($uid, $sc);
					}

					// Add the post to the user's contributions
					$users[$uid]->add_contribution($type, $p);
				}
			}
		}

		// Ensure that masthead users are there in case they made no contribs
		$already_found = array_keys($users);
		$mh_users = MJKMHAPI::ac_year_to_users($year);

		// For each user, if it wasn't already found, make an empty user for it
		foreach($mh_users as $mhu) {
			$id = $mhu->id();
			if (!in_array($id, $already_found)) {
				$users[$id] = new MJKSC_User($id, $sc);

				// We must also unload the user because their roles may need
				// to be freshly checked during Staff Check calculation.
				MJKMHAPI::unload_user($id);
			}
		}

		return $users;
	}


	/*
	 * =========================================================================
	 * Almosts
	 * =========================================================================
	 */

	/**
	 * Return the users who are almost staff writers, staff photographers, and
	 * voters, in three subarrays keyed by those terms. Return an empty array if
	 * it is not fall or winter right now. The SC year is the current one.
	 *
	 * @return array
	 */
	static function almost(): array {
		$now = JKNTime::dt_now();
		$ay = MJKMHAPI::newest_year();
		if ($now > $ay->end_of_winter()) return [];

		// If it's fall or winter, proceed
		if (($ay->start_of_fall() <= $now) && ($now <= $ay->end_of_winter())) {
			$sc = new MJKSC_StaffCheck($ay);

			// If it's fall
			if ($now <= $ay->end_of_fall()) {
				$threshold = $sc->fall_threshold();

				$writers = $sc->almost_fall_writers();
				$photographers = $sc->almost_fall_photographers();
				$voters = $sc->almost_fall_voters();

				$writer_cb = 'n_fall_writer';
				$photo_cb = 'n_fall_photographer';
				$voter_cb = 'n_fall_voter';

			// If it's winter
			} else {
				$threshold = $sc->winter_threshold();

				$writers = $sc->almost_winter_writers();
				$photographers = $sc->almost_winter_photographers();
				$voters = $sc->almost_winter_voters();

				$writer_cb = 'n_winter_writer';
				$photo_cb = 'n_winter_photographer';
				$voter_cb = 'n_winter_voter';
			}

			// Make the anonymous callbacks
			$writer_cb = function(MJKSC_User $u) use ($writer_cb): int {
				return $u->$writer_cb();
			};

			$photo_cb = function(MJKSC_User $u) use ($photo_cb): int {
				return $u->$photo_cb();
			};

			$voter_cb = function(MJKSC_User $u) use ($voter_cb): int {
				return $u->$voter_cb();
			};

			// Map the results of the callbacks to the users
			return [
				'threshold'     => $threshold,
				'writers'       => array_map($writer_cb, $writers),
				'photographers' => array_map($photo_cb, $photographers),
				'voters'        => array_map($voter_cb, $voters)
			];

		} else {
			return [];
		}
	}


	/*
	 * =========================================================================
	 * Role updates
	 * =========================================================================
	 */

	/**
	 * Return an array of users who are eligible to be writers but do not have
	 * the role ('add_writers'); who are eligible to be photographers but do not
	 * have the role ('add_photographers'); who have the writer role but are not
	 * eligible ('remove_writers'); and who have the photographer role but are
	 * not eligible ('remove_photographers'). The SC year is the current one.
	 *
	 * @return array
	 */
	static function users_to_update(MJKSC_StaffCheck $sc=null): array {

		if (is_null($sc)) {
			$now = JKNTime::dt_now();
			$ay = MJKMHAPI::newest_year();
			if ($now > $ay->end_of_winter()) return [];
			$sc = new MJKSC_StaffCheck($ay);
		}

		// Eligible
		$eligible_writers = $sc->eligible_writers();
		$eligible_photos = $sc->eligible_photographers();

		// Earned
		$assigned_writers = $sc->assigned_writers();
		$assigned_photos = $sc->assigned_photographers();

		// To add
		$writers_to_add = array_diff($eligible_writers, $assigned_writers);
		$photos_to_add = array_diff($eligible_photos, $assigned_photos);

		// To remove
		$writers_to_remove = array_diff($assigned_writers, $eligible_writers);
		$photos_to_remove = array_diff($assigned_photos, $eligible_photos);

		return [
			'add_writers' => $writers_to_add,
			'add_photographers' => $photos_to_add,
			'remove_writers' => $writers_to_remove,
			'remove_photographers' => $photos_to_remove
		];
	}

	/**
	 * Filter and return the requested updates using the ACF settings.
	 *
	 * $args consists of 'add_writers', 'remove_writers', 'add_photographers' &
	 * 'remove_photographers' (or any permutation thereof).
	 * Each key points to an array of MJKSC_Users.
	 * It can therefore be the output of users_to_update.
	 *
	 * @param array[] $updates
	 * @param bool|null $add Whether to add roles. Get ACF option if null.
	 * @param bool|null $remove Whether to remove roles. Get ACF option if null.
	 * @return array[]
	 */
	static function filter_updates(array $updates,
			bool $add=null, bool $remove=null): array {

		// Check the options
		if (is_null($add))
			$add = (bool) MJKSC_ACF::get(MJKSC_ACF::auto_add);

		if (is_null($remove))
			$remove = (bool) MJKSC_ACF::get(MJKSC_ACF::auto_remove);

		// Filter out unrequested additions
		if (!$add) {
			if (isset($updates['add_writers']))
				unset($updates['add_writers']);

			if (isset($updates['add_photographers']))
				unset($updates['add_photographers']);
		}

		// Filter out unrequested deletions
		if (!$remove) {
			if (isset($updates['remove_writers']))
				unset($updates['remove_writers']);

			if (isset($updates['remove_photographers']))
				unset($updates['remove_photographers']);
		}

		return $updates;
	}

	/**
	 * Perform the requested changes on each of the subarrays in $args.
	 *
	 * $args consists of 'add_writers', 'remove_writers', 'add_photographers' &
	 * 'remove_photographers' (or any permutation thereof).
	 * Each key points to an array of MJKSC_Users.
	 * It can therefore be the output of users_to_update.
	 *
	 * @param array[] $args The operations to perform.
	 * @param JKNAcademicYear $ay The academic year to perform it on.
	 */
	static function update_users(array $args, JKNAcademicYear $ay): void {

		// Add writer roles
		if (isset($args['add_writers']) && !empty($args['add_writers'])) {
			MJKSC_API::add_writer_role($args['add_writers'], $ay);
		}

		// Delete writer roles
		if (isset($args['remove_writers']) && !empty($args['remove_writers'])) {
			MJKSC_API::delete_writer_role($args['remove_writers'], $ay);
		}

		// Add photographer roles
		if (isset($args['add_photographers']) &&
		    !empty($args['add_photographers'])) {

			MJKSC_API::add_photographer_role($args['add_photographers'],
				$ay);
		}

		// Delete photographer roles
		if (isset($args['remove_photographers']) &&
		    !empty($args['remove_photographers'])) {

			MJKSC_API::delete_photographer_role($args['remove_photographers'],
				$ay);
		}
	}


	/*
	 * =========================================================================
	 * Role adding
	 * =========================================================================
	 */

	/**
	 * Add the given role to each of the given users for the given
	 * academic year.
	 *
	 * @param string $role
	 * @param MJKSC_User[] $users
	 * @param JKNAcademicYear $ay
	 */
	static function add_role(string $role, array $users,
			JKNAcademicYear $ay): void {

		$year = $ay->format();
		$division = MJKMHAPI::role($role)->division_name();

		foreach($users as $user) {

			$id = $user->id();
			$repeater = MJKMH_ACF_User::get(MJKMH_ACF_User::roles, $id);
			if (empty($repeater)) $repeater = [];

			$row = [
				MJKMH_ACF_User::year => $year,
				MJKMH_ACF_User::division => $division,
				MJKMH_ACF_User::role => $role,
				MJKMH_ACF_User::alias => $role
			];

			// Prepend row
			array_unshift($repeater, $row);
			MJKMH_ACF_User::update(MJKMH_ACF_User::roles, $repeater, $id);

			// Delete the user's role cache and reset rows
			$user->uncache();
			reset_rows();
		}
	}

	/**
	 * Add the Staff Writer role to each of the given users for the given
	 * academic year.
	 *
	 * @param MJKSC_User[] $users
	 * @param JKNAcademicYear $ay
	 */
	static function add_writer_role(array $users,
			JKNAcademicYear $ay): void {

		self::add_role(MJKSC_User::writer_role, $users, $ay);
	}

	/**
	 * Add the Staff Photographer role to each of the given users for the given
	 * academic year.
	 *
	 * @param MJKSC_User[] $users
	 * @param JKNAcademicYear $ay
	 */
	static function add_photographer_role(array $users,
			JKNAcademicYear $ay): void {

		self::add_role(MJKSC_User::photographer_role, $users, $ay);
	}


	/*
	 * =========================================================================
	 * Role deletion
	 * =========================================================================
	 */

	/**
	 * Delete the given role for the given users in the given year.
	 *
	 * @param string $role
	 * @param MJKSC_User[] $users
	 * @param JKNAcademicYear $ay
	 */
	static function delete_roles(string $role, array $users,
			JKNAcademicYear $ay): void {

		$year = $ay->format();

		foreach($users as $user) {

			$id = $user->id();

			// Identify the row to delete. Yes, ACF row index starts at 1
			$i = 1;
			if (MJKMH_ACF_User::have_rows(MJKMH_ACF_User::roles, $id)) {
				while (MJKMH_ACF_User::have_rows(MJKMH_ACF_User::roles, $id)) {
					the_row();
					$row_role = MJKMH_ACF_User::sub(MJKMH_ACF_User::role);
					$row_year = MJKMH_ACF_User::sub(MJKMH_ACF_User::year);

					if (($role == $row_role) && ($year == $row_year)) break;
					$i += 1;
				}
			}

			// Delete the row
			MJKMH_ACF_User::delete_row(MJKMH_ACF_User::roles, $i, $id);

			// Delete the user's role cache and reset rows
			$user->uncache();
			reset_rows();
		}
	}

	/**
	 * Delete the Staff Writer role for the given users in the given year.
	 *
	 * @param MJKSC_User[] $users
	 * @param JKNAcademicYear $ay
	 */
	static function delete_writer_role(array $users,
			JKNAcademicYear $ay): void {

		self::delete_roles(MJKSC_User::writer_role, $users, $ay);
	}

	/**
	 * Delete the Staff Photographer role for the given users in the given year.
	 *
	 * @param MJKSC_User[] $users
	 * @param JKNAcademicYear $ay
	 */
	static function delete_photographer_role(array $users,
		JKNAcademicYear $ay): void {

		self::delete_roles(MJKSC_User::photographer_role, $users, $ay);
	}


	/*
	 * =========================================================================
	 * Email recipients
	 * =========================================================================
	 */

	/**
	 * Return the email addresses to receive the 'almost' notification.
	 *
	 * @return string[]
	 */
	static function almost_recipients(): array {

		if (is_null(self::$almost_recipients)) {

			$recipients = [];
			if (MJKSC_ACF::have_rows(MJKSC_ACF::almost_emails)) {
				while (MJKSC_ACF::have_rows(MJKSC_ACF::almost_emails)) {
					the_row();

					$email = MJKSC_ACF::sub(MJKSC_ACF::almost_email);
					$recipients[] = trim($email);
				}
			}

			self::$almost_recipients = $recipients;
		}

		return self::$almost_recipients;
	}


	/**
	 * Return the email addresses to receive the 'update' notification.
	 *
	 * @return string[]
	 */
	static function update_recipients(): array {

		if (is_null(self::$update_recipients)) {

			$recipients = [];
			if (MJKSC_ACF::have_rows(MJKSC_ACF::auto_emails)) {
				while (MJKSC_ACF::have_rows(MJKSC_ACF::auto_emails)) {
					the_row();

					$email = MJKSC_ACF::sub(MJKSC_ACF::auto_email);
					$recipients[] = trim($email);
				}
			}

			self::$update_recipients = $recipients;
		}

		return self::$update_recipients;
	}


	/*
	 * =========================================================================
	 * Generation Tools page
	 * =========================================================================
	 */

	/**
	 * Return the Generation Tools page.
	 *
	 * @return MJKGenToolsPageSwitch|null
	 */
	static function gen_tools_page(): ?MJKGenToolsPageSwitch {
		return JKNAPI::module()->gtpage();
	}

	/**
	 * Regenerate the Generation Tools page. Return true iff it was successful.
	 *
	 * @return bool True iff the generation was successful, otherwise false.
	 */
	static function generate_page(): bool {
		try {
			self::gen_tools_page()->generate();
			return true;
		} catch (\Exception $e) {
			return false;
		}
	}
}
