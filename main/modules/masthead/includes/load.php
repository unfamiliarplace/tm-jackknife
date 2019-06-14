<?php

/**
 * Loads the divisions, roles, and users as required.
 */
final class MJKMH_Loader {

	/**
	 * Load all divisions (if they are not yet loaded) and return them.
	 *
	 * @return MJKMH_Division[]
	 */
	static function load_divisions(): array {
		$mod = JKNAPI::module();

		if (!$mod->loaded_divisions()) {

			// Extract whether we're showing emails
			$show_emails = MJKMH_ACF_Roles::get(MJKMH_ACF_Roles::show_emails);
			MJKMH_Renderer::$show_emails_on = (bool) $show_emails;

			// Go through each division
			if (MJKMH_ACF_Roles::have_rows(MJKMH_ACF_Roles::divisions)) {
				while (MJKMH_ACF_Roles::have_rows(MJKMH_ACF_Roles::divisions)) {
					the_row();

					$d_args = [];
					$d_args['name'] = trim(MJKMH_ACF_Roles::sub(MJKMH_ACF_Roles::name));
					$d_args['notes'] = trim(MJKMH_ACF_Roles::sub(MJKMH_ACF_Roles::notes));
					$d_args['priority'] = MJKMH_ACF_Roles::sub(MJKMH_ACF_Roles::d_priority);
					$division = new MJKMH_Division($d_args);

					// Go through each role
					if (MJKMH_ACF_Roles::have_rows(MJKMH_ACF_Roles::roles)) {
						while (MJKMH_ACF_Roles::have_rows(MJKMH_ACF_Roles::roles)) {
							the_row();

							$r_args = [];
							$r_args['division_name'] = $division->name();
							$r_args['title'] = trim(MJKMH_ACF_Roles::sub(MJKMH_ACF_Roles::title));
							$r_args['priority'] = MJKMH_ACF_Roles::sub(MJKMH_ACF_Roles::r_priority);

							$r_args['voting'] = (bool) MJKMH_ACF_Roles::sub(MJKMH_ACF_Roles::voting);
							$r_args['email'] = trim(MJKMH_ACF_Roles::sub(MJKMH_ACF_Roles::email));
							$r_args['archival'] = (bool) MJKMH_ACF_Roles::sub(MJKMH_ACF_Roles::archival);

							// Go through each alias
							$aliases = [$r_args['title']];
							if (MJKMH_ACF_Roles::have_rows(MJKMH_ACF_Roles::aliases)) {
								while (MJKMH_ACF_Roles::have_rows(MJKMH_ACF_Roles::aliases)) {
									the_row();
									$aliases[] = trim(MJKMH_ACF_Roles::sub(MJKMH_ACF_Roles::alias));
								}
							}
							$r_args['aliases'] = $aliases;

							$role = new MJKMH_Role($r_args);
							$division->add_role($role);
						}
					}

					$mod->add_division($division);
				}
			}
		}

		return $mod->divisions();
	}

	/**
	 * Load the user with the given ID (if it is not yet loaded) and return it.
	 *
	 * @param string $id
	 * @return MJKMH_User
	 */
	static function load_user(string $id): MJKMH_User {
		$mod = JKNAPI::module();

		if (!$mod->loaded_user($id)) {

			$args = ['id' => $id];
			$args['status'] = trim(MJKMH_ACF_User::get(MJKMH_ACF_User::status, $id));
			$user = new MJKMH_User($args);

			// Go through each role
			if (MJKMH_ACF_User::have_rows(MJKMH_ACF_User::roles, $id)) {
				while (MJKMH_ACF_User::have_rows(MJKMH_ACF_User::roles, $id)) {
					the_row();
					$args = [];

					$args['year'] = MJKMH_ACF_User::sub(MJKMH_ACF_User::year);

					$args['role_title'] = MJKMH_ACF_User::sub(MJKMH_ACF_User::role);

					$alias = MJKMH_ACF_User::sub(MJKMH_ACF_User::alias);
					$args['alias'] = empty($alias) ? $args['role_title'] : $alias;
					$args['preferred'] = (bool) MJKMH_ACF_User::sub(MJKMH_ACF_User::preferred);
					$args['interim'] = (bool) MJKMH_ACF_User::sub(MJKMH_ACF_User::interim);

					// Derive start and end
					if ($args['interim']) {
						$start = MJKMH_ACF_User::sub(MJKMH_ACF_User::start);
						$start = JKNAcademicYear::make_from_datestring($start);
						$end = MJKMH_ACF_User::sub(MJKMH_ACF_User::end);
						$end = JKNAcademicYear::make_from_datestring($end);
					} else {
						$ay = JKNAcademicYear::make_from_format($args['year']);
						$start = $ay->start();
						$end = $ay->end();
					}

					$args['start'] = $start;
					$args['end'] = $end;

					// Construct and add role
					$held_role = new MJKMH_HeldRole($args);
					$user->add_role($held_role);
				}
			}

			$mod->add_user($user);
		}

		return $mod->user($id);
	}

	/**
	 * Unload the user with the given ID (if it is loaded).
	 *
	 * @param string $id
	 */
	static function unload_user(string $id): void {
		$mod = JKNAPI::module();
		$mod->unload_user($id);
	}
}
