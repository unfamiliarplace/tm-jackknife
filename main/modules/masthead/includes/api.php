<?php

/**
 * Provides functions for dealing with the masthead.
 */
final class MJKMHAPI {

	/*
	 * =========================================================================
	 * Divisions
	 * =========================================================================
	 */

	/**
	 * Return the registered divisions.
	 *
	 * @return MJKMH_Division[]
	 */
	static function divisions(): array {
		return MJKMH_Loader::load_divisions();
	}

	/**
	 * Return the division with the given name, if it exists.
	 *
	 * @param string $div_name
	 * @return MJKMH_Division|null
	 */
	static function division(string $div_name): ?MJKMH_Division {
		$divisions = self::divisions();
		return isset($divisions[$div_name]) ? $divisions[$div_name] : null;
	}


	/*
	 * =========================================================================
	 * Roles
	 * =========================================================================
	 */

	/**
	 * Return the roles for the given division name.
	 * If none is supplied, return all the roles in a flat array.
	 *
	 * @param string|null $div_name
	 * @return MJKMH_Role[]
	 */
	static function roles(string $div_name=null): array {

		if (!is_null($div_name)) {
			return self::division($div_name)->roles();

		} else {
			$roles = [];

			foreach(self::divisions() as $div) {
				$roles = array_merge($roles, $div->roles());
			}

			return $roles;
		}
	}

	/**
	 * Return the role with the given title, if it exists.
	 * The ID is of the form 'div_id/role_id' in order to identify the division.
	 *
	 * @param string $role_title
	 * @return MJKMH_Role|null
	 */
	static function role(string $role_title): ?MJKMH_Role {

		foreach(self::divisions() as $division) {
			$role = $division->role($role_title);
			if ($role) return $role;
		}

		return null;
	}


	/*
	 * =========================================================================
	 * User interaction
	 * =========================================================================
	 */

	/**
	 * Return all registered users.
	 *
	 * @return MJKMH_User[]
	 */
	static function users(): array { return JKNAPI::module()->users(); }

	/**
	 * Return the user with the ID (or other field if supplied).
	 * If the user is not yet loaded, load it.
	 *
	 * @param string $value
	 * @param string $field The field to use when fetching the user.
	 * @return MJKMH_User|null
	 */
	static function user(string $value, string $field='id'): ?MJKMH_User {
		$wp_u = get_user_by($field, $value);
		if (!$wp_u) return null;
		return MJKMH_Loader::load_user($wp_u->ID);
	}

	/**
	 * Unload the given user. This allows their roles to be recalculated in the
	 * event that they have changed.
	 *
	 * @param string $value
	 * @param string $field The field to use when fetching the user.
	 */
	static function unload_user(string $value, string $field='id'): void {
		$wp_u = get_user_by($field, $value);
		if (!$wp_u) return;
		MJKMH_Loader::unload_user($wp_u->ID);
	}


	/*
	 * =========================================================================
	 * Held roles
	 * =========================================================================
	 */

	/**
	 * Sort and return the given array of held roles.
	 *
	 * @param MJKMH_HeldRole[] $roles
	 * @return MJKMH_HeldRole[]
	 */
	static function sort_held_roles(array $roles): array {
		usort($roles, function(MJKMH_HeldRole $a, MJKMH_HeldRole $b): int {
			return $a->priority() <=> $b->priority();
		});

		return $roles;
	}


	/*
	 * =========================================================================
	 * Masthead page
	 * =========================================================================
	 */

	/**
	 * Return the URL of the masthead current or archives page, if one is set.
	 *
	 * @param JKNAcademicYear $ay The year to root it to (now by default).
	 * @return string|null The URL.
	 */
	static function url(JKNAcademicYear $ay=null): ?string {
		return JKNAPI::module()->gt_page_url($ay);
	}

	/**
	 * Return the newest academic year for which there is masthead content.
	 *
	 * @return JKNAcademicYear The academic year.
	 */
	static function newest_year(): JKNAcademicYear {
		global $wpdb;

		$mkey = MJKMH_ACF_User::qualify('%_' . MJKMH_ACF_User::year);
		$query = $wpdb->prepare("
            SELECT meta_value
                FROM	wp_usermeta
                WHERE   meta_key LIKE '%s'
        ", $mkey);

		$results = $wpdb->get_results($query);
		$results = array_map(function(stdClass $a): string {
			return $a->meta_value;
		}, $results);

		$results = array_unique($results);
		rsort($results);
		return JKNAcademicYear::make_from_format(reset($results));
	}

	/**
	 * Return an array of users who were had a role in the given academic year.
	 *
	 * @param string $year A standard academic year format, e.g. '2017/18'
	 * @return MJKMH_User[]
	 */
	static function year_to_users(string $year): array {
		global $wpdb;

		$mkey = MJKMH_ACF_User::qualify('%_' . MJKMH_ACF_User::year);
		$query = $wpdb->prepare("
            SELECT user_id
                FROM	wp_usermeta
                WHERE   meta_key LIKE '%s'
                	AND meta_value = '%s'
        ", $mkey, $year);

		// Run the query and get a flat array of string IDs
		$ids = $wpdb->get_results($query);
		$ids = array_map(function(stdClass $a): string {
			return $a->user_id;
		}, $ids);

		// Unique strings and convert to user objects
		$ids = array_unique($ids);
		$users = array_map(function(string $id): MJKMH_User {
			return MJKMHAPI::user($id);
		}, $ids);

		return $users;
	}

	/**
	 * Return an array of users who were had a role in the given academic year.
	 *
	 * @param JKNAcademicYear $ac_year
	 * @return MJKMH_User[]
	 */
	static function ac_year_to_users(JKNAcademicYear $ac_year): array {
		return self::year_to_users($ac_year->format());
	}

	/**
	 * Return an array of [division_name => users] for the given year.
	 * The current masthead year is chosen if a year is not supplied.
	 * N.B. If displaying roles, you will still have to filter users' roles.
	 *
	 * @param JKNAcademicYear|null $year
	 * @return array[]
	 */
	static function masthead(JKNAcademicYear $year=null): array {
		if (is_null($year)) $year = self::newest_year();

		$mh = [];

		// Gather the users
		$users = self::ac_year_to_users($year);

		// Get each user's roles for the year, then split them by division
		$user_to_priority = [];
		foreach($users as $user) {
			$roles_in_year = $user->by_ac_year($year);
 			$divs_to_roles = $user->divisions_to_roles($roles_in_year);

 			// Sort the users into their divisions in the masthead
 			foreach($divs_to_roles as $div => $roles) {
 				if (!isset($mh[$div])) $mh[$div] = [];
 				$mh[$div][] = $user;

 				// Set priority based on division, for sorting below
			    $priority = $user->priority($roles);
			    $id = $user->id();
			    if (!isset($user_to_priority[$id])) $user_to_priority[$id] = [];
			    $user_to_priority[$id][$div] = $priority;
		    }
		}

		// Sort each division's users
		foreach($mh as $div_name => $users) {
			$sorted = $users;
			usort($sorted, function(MJKMH_User $a, MJKMH_User $b)
			use ($user_to_priority, $div_name): int {
				$pri_a = $user_to_priority[$a->id()][$div_name];
				$pri_b = $user_to_priority[$b->id()][$div_name];
				return $pri_a <=> $pri_b;
			});
			$mh[$div_name] = $sorted;
		}

		// Sort the divisions themselves
		uksort($mh, function(string $a, string $b): int {
			$pri_a = MJKMHAPI::division($a)->priority();
			$pri_b = MJKMHAPI::division($b)->priority();
			return $pri_a <=> $pri_b;
		});

		return $mh;
	}
}
