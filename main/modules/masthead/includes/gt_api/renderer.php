<?php

/**
 * Renderer for the masthead page.
 */
final class MJKMH_Renderer extends JKNRendererSwitch {

	// Useful in some of the functions lower down, despite being evil
	static $show_emails_on;
	static $current_year;
	static $current_division;
	static $showing_emails;

	/*
	 * =========================================================================
	 * Page
	 * =========================================================================
	 */

	/**
	 * Return the intro.
	 *
	 * @return string
	 */
	static function intro(): string {
		return sprintf(
			'These are the editors, staff, and board of <em>The Medium</em>.'
			. ' For a list of all contributors, see'
			. ' <a title="%1$s" href="%2$s">%1$s</a>.'
			. ' Author photos may be credited to the year\'s photo editor.',
			'Contributors', home_url('contributors'));
	}

	/**
	 * Return the options to switch between: all academic years till
	 * the newest year for which there's a masthead, in descending order.
	 *
	 * @return string[] An array of [['value' => value, 'display' => display]]
	 */
	static function switch_options(): array {
		$newest = MJKMHAPI::newest_year();
		$options = [];

		$ac_years = MJKCommonTools::academic_years(null, $newest->year());
		foreach(array_keys($ac_years) as $ac_year) {
			$ay = JKNAcademicYear::make_from_format($ac_year);
			$vol = MJKVIAPI::get_volume_by_academic_year($ay);

			if (is_null($vol)) continue;

			$options[] = [
				'value'     => $ac_year,
				'display'   => sprintf('%s (%s)', $vol->get_name(), $ac_year)
			];
		}

		return array_reverse($options);
	}

	/**
	 * Return the rendered content for one academic year.
	 *
	 * @param string $ac_year An academic year in standard format: '2017/18'
	 * @return string
	 */
	static function content_option(string $ac_year): string {
		$mh = MJKMHAPI::masthead(JKNAcademicYear::make_from_format($ac_year));

		self::$current_year = $ac_year;
		self::$showing_emails = self::$show_emails_on &&
		                     ($ac_year == MJKMHAPI::newest_year()->format());

		return self::format_masthead($mh);
	}

	/**
	 * Return a more user-friendly option key.
	 *
	 * @return string
	 */
	static function option_key(): string { return 'acyear'; }

	/*
	 * =========================================================================
	 * Specific formatting
	 * =========================================================================
	 */

	// CSS classes
	const cl_mh         = 'mjkmh-gt-mh';
	const cl_division   = 'mjkmh-gt-division';
	const cl_notes      = 'mjkmh-gt-notes';
	const cl_grid       = 'mjkmh-gt-grid';
	const cl_user       = 'mjkmh-gt-user';
	const cl_panel      = 'mjkmh-gt-panel';
	const cl_avatar     = 'mjkmh-gt-avatar';
	const cl_meta       = 'mjkmh-gt-meta';
	const cl_name       = 'mjkmh-gt-name';
	const cl_role_box   = 'mjkmh-gt-role-box';
	const cl_contact    = 'mjkmh-gt-contact';
	const cl_roles      = 'mjkmh-gt-roles';
	const cl_role       = 'mjkmh-gt-role';

	/**
	 * Format a complete masthead.
	 * A masthead is of the form [division_name => users].
	 *
	 * @param array $mh
	 * @return string
	 */
	static function format_masthead(array $mh): string {
		if (empty($mh)) return 'No record exists for this year yet.';

		$div_items = [];
		foreach($mh as $div_name => $users) {
			$div_items[] = sprintf('<li>%s</li>',
				self::format_division($div_name, $users));
		}
		$div_list = sprintf('<ul>%s</ul>', implode($div_items));

		return sprintf('<div class="%s">%s</div>', self::cl_mh, $div_list);
	}

	/**
	 * Format a given division and its users.
	 *
	 * @param string $div_name
	 * @param MJKMH_User[] $users
	 * @return string
	 */
	static function format_division(string $div_name, array $users): string {
		self::$current_division = $div_name;

		$notes = sprintf('<div class="%s">%s</div>', self::cl_notes,
			MJKMHAPI::division($div_name)->notes());

		$grid = sprintf('<div class="%s">%s</div>', self::cl_grid,
			JKNLayouts::grid($users, 4, [__CLASS__, 'format_user']));

		return sprintf('<div class="%s"><h2>%s</h2>%s%s</div>',
			self::cl_division, $div_name, $notes, $grid);
	}

	/**
	 * Format a given user.
	 *
	 * @param MJKMH_User $user
	 * @return string
	 */
	static function format_user(MJKMH_User $user): string {
		$wpu = $user->wp();
		$url = get_author_posts_url($wpu->ID);

		// Avatar
		$avatar = sprintf('<a href="%s" title="%s">%s</a>', $url,
			$wpu->display_name, get_avatar($wpu->ID, 190));
		$avatar = sprintf('<div class="%s">%s</div>', self::cl_avatar,
			$avatar);

		// Meta
		$name = sprintf('<a href="%1$s" title="%2$s">%2$s</a>', $url,
			$wpu->display_name);
		$name = sprintf('<div class="%s"><h4>%s</h4></div>', self::cl_name,
			$name);

		$role_box = sprintf('<div class="%s">%s</div>', self::cl_role_box,
			self::format_role_box($user));

		$meta = sprintf('<div class="%s">%s%s</div>', self::cl_meta,
			$name, $role_box);

		// Inner join ;)
		$panel = sprintf('<div class="%s">%s%s</div>',
			self::cl_panel, $avatar, $meta);

		return sprintf('<div class="%s">%s</div>', self::cl_user, $panel);
	}

	/**
	 * Format a given user's contact info and roles.
	 *
	 * @param MJKMH_User $user
	 * @return string
	 */
	static function format_role_box(MJKMH_User $user): string {
		$roles_in_year = $user->by_year(self::$current_year);
		$divs_to_roles = $user->divisions_to_roles($roles_in_year);
		$roles = $divs_to_roles[self::$current_division];

		$roles_list = self::format_roles($roles);
		$contact = self::format_contact($user->choose($roles));

		return sprintf('%s%s', $roles_list, $contact);
	}

	/**
	 * Format a 'contact' link if possible.
	 *
	 * @param MJKMH_HeldRole $role The best role to choose the contact form.
	 * @return string
	 */
	static function format_contact(MJKMH_HeldRole $role): string {
		$email = $role->role()->email();

		if ($email && self::$showing_emails) {
			$link = sprintf(
				'<a href="mailto:%1$s" title="Email %1$s">%1$s</a>', $email);
			return sprintf('<div class="%s">%s</div>', self::cl_contact, $link);
		}

		return '';
	}

	/**
	 * Format the given roles.
	 *
	 * @param MJKMH_Role[] $roles
	 * @return string
	 */
	static function format_roles(array $roles): string {
		return JKNLayouts::list($roles, [__CLASS__, 'format_role'], 'ul',
			self::cl_roles, self::cl_role);
	}

	/**
	 * Format a given role.
	 *
	 * @param MJKMH_HeldRole $role
	 * @return string
	 */
	static function format_role(MJKMH_HeldRole $role): string {
		return $role->title();
	}

	/**
	 * Format the CSS for the page.
	 *
	 * @return string
	 */
	static function style(): string {
		return JKNCSS::tag('
		
			.'.self::cl_mh.' li {
				list-style: none;
				margin-left: 0;
			}
			
			.'.self::cl_notes.' {
				margin-top: -5px;
				margin-bottom: 15px;
			}
			
			.'.self::cl_grid.' .vc_row {
				margin-bottom: 25px;
			}
			
			.'.self::cl_user.' {
				text-align: center;
			}
			
			.'.self::cl_user.' {
                overflow: hidden;
				border: 1px solid #e0e0e0;
				border-radius: 10px;
			}
			
			.'.self::cl_user.':hover {
				background: rgba(17,65,111,.15);
			}
			
			.'.self::cl_panel.' {
				padding-top: 15px;
				padding-bottom: 15px;
				min-height: 315px;
			}
			
			.'.self::cl_name.' h4 {
				margin-top: 2px;
				margin-bottom: 2px;
			}
			
			.'.self::cl_contact.' a {
				font-size: 14px;
				color: #555;
			}
			
			.'.self::cl_roles.' {
				margin-bottom: 0 !important;
			}
			
			.'.self::cl_roles.' li {
				font-size: 15px;
				font-weight: bold;
				margin-left: 0;
				list-style: none;
			}
			
			.'.self::cl_role.' {
				font-family: "Roboto", Arial, sans-serif !important;
			}
		');
	}
}
