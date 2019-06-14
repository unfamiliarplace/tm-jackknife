<?php

/**
 * Renders the Staff Check page.
 */
final class MJKSC_Renderer extends JKNRendererSwitch {

	/**
	 * Return the options to switch between: year of earliest post till
	 * the newest year for which there's a masthead, in descending order.
	 *
	 * @return string[] An array of [['value' => value, 'display' => display]]
	 */
	static function switch_options(): array {
		$options = [];

		// Year of earliest post to year of latest masthead
		$start_year = MJKSC_API::earliest_year()->year();
		$end_year = MJKMHAPI::newest_year()->year();
		$ac_years = MJKCommonTools::academic_years($start_year, $end_year);

		foreach(array_keys($ac_years) as $ac_year) {
			$ay = JKNAcademicYear::make_from_format($ac_year);
			$vol = MJKVIAPI::get_volume_by_academic_year($ay);

			// Do not proceed if there is no volume for the year yet
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
		$sc = new MJKSC_StaffCheck(JKNAcademicYear::make_from_format($ac_year));

		// Ensure VC shortcodes are on
		if (class_exists('WPBMap') &&
		    method_exists('WPBMap', 'addAllMappedShortcodes')) {
			WPBMap::addAllMappedShortcodes();
		}

		return do_shortcode(self::format_staff_check($sc));
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
	const cl_sc                     = 'mjksc-gt-sc';
	const cl_tabs                   = 'mjksc-gt-tabs';
	const cl_area                   = 'mjksc-gt-area';
	const cl_area_content           = 'mjksc-gt-area-content';
	const cl_area_intro             = 'mjksc-gt-area-intro';
	const cl_infobox                = 'mjksc-gt-infobox';
	const cl_overview_table         = 'mjksc-gt-contributions-table';
	const cl_table_heading          = 'mjksc-gt-table-heading';
	const cl_table_type             = 'mjksc-gt-table-type';
	const cl_table_div              = 'mjksc-gt-table-div';
	const cl_contributions          = 'mjksc-gt-contributions';
	const cl_section                = 'mjksc-gt-section';
	const cl_section_inner          = 'mjksc-gt-section-inner';
	const cl_section_meta           = 'mjksc-gt-section-meta';
	const cl_divisions              = 'mjksc-gt-divisions';
	const cl_division               = 'mjksc-gt-division';
	const cl_division_users         = 'mjksc-gt-division-users';
	const cl_group                  = 'mjksc-gt-group';
	const cl_users                  = 'mjksc-gt-users';
	const cl_user                   = 'mjksc-gt-user';
	const cl_user_link              = 'mjksc-gt-user-link';
	const cl_u_has_role             = 'mjksc-gt-u-has-role';
	const cl_u_needs_role           = 'mjksc-gt-u-needs-role';
	const cl_u_bad_role             = 'mjksc-gt-u-bad-role';


	/*
	 * =========================================================================
	 * Main formatting
	 * =========================================================================
	 */

	/**
	 * Format the staff check object.
	 * Also perform a user roles update if requested & this is the current year.
	 *
	 * @param MJKSC_StaffCheck $sc
	 * @return string
	 */
	static function format_staff_check(MJKSC_StaffCheck $sc): string {

		// Update users if requested and it's the current year
		$update = (bool) MJKSC_ACF::get(MJKSC_ACF::auto_on_gen);
		if ($update && $sc->current()) {

			// Sanity check that we actually are adding/removing
			$add = (bool) MJKSC_ACF::get(MJKSC_ACF::auto_add);
			$remove = (bool) MJKSC_ACF::get(MJKSC_ACF::auto_remove);

			if ($add || $remove) {

				// Get, filter and update the users
				$to_update = MJKSC_API::users_to_update($sc);
				$to_update = MJKSC_API::filter_updates($to_update, $add, $remove);
				MJKSC_API::update_users($to_update, $sc->year());

				// Send an email if requested
				$e = (bool) MJKSC_ACF::get(MJKSC_ACF::auto_email_on_gen);
				if ($e) MJKSC_Update_Email::notify($to_update, true, true);
			}
		}

		// On to the formatting
		return sprintf('<div class="%s">%s</div>', self::cl_sc,
			self::format_areas($sc));
	}

	/**
	 * Format each area of the staff check object.
	 *
	 * @param MJKSC_StaffCheck $sc
	 * @return string
	 */
	static function format_areas(MJKSC_StaffCheck $sc): string {

		// Table of area name to area formatter
		$area_to_formatter = [
			'Overview' => [__CLASS__, 'format_overview'],
			'Staff writers & photographers' => [__CLASS__, 'format_staff'],
			'Voting' => [__CLASS__, 'format_voting'],
			'Contributions' => [__CLASS__, 'format_contributions']
		];

		// Format each area and wrap it in a div
		$areas = [];
		foreach($area_to_formatter as $title => $formatter) {
			$areas[] = sprintf(
				'<div class="%s"><h2>%s</h2><div class="%s">%s</div></div>',
				self::cl_area, $title, self::cl_area_content,
				$formatter($sc));
		}

		return implode('', $areas);
	}

	/**
	 * Return a formatted list of users using the given callback.
	 *
	 * @param MJKSC_User[] $users
	 * @param callable|null $cb
	 * @return string
	 */
	static function format_users(array $users, callable $cb=null): string {
		if (is_null($cb)) $cb = [__CLASS__, 'format_user'];

		return JKNLayouts::list($users, $cb, 'ul',
			self::cl_users, self::cl_user);
	}

	/**
	 * Return a formatted username & link to author page for the given user ID.
	 *
	 * @param string $id
	 * @return string
	 */
	static function format_username(string $id): string {
		$name = get_user_by('id', $id)->display_name;
		$url = get_author_posts_url($id);

		return sprintf('<a href="%1$s" class="%2$s" title="%3$s">%3$s</a>',
			$url, self::cl_user_link, $name);
	}

	/**
	 * Return a formatted a Visual Composer row for the given columns.
	 *
	 * @param string[] $columns Already enclosed in [vc_column][/vc_column].
	 * @return string
	 */
	static function format_row(array $columns): string {
		return sprintf('[vc_row]%s[/vc_row]', implode('', $columns));
	}

	/**
	 * Return a formatted Visual Composer column with the given content,
	 * title, and width.
	 *
	 * @param string $content
	 * @param string $title
	 * @param string $width As 'x/y', e.g. '1/3'
	 * @return string
	 */
	static function format_column(string $content, string $title,
			string $width='1/1'): string {

		$heading = sprintf('<h3>%s</h3>', $title);
		return sprintf('[vc_column width="%s"]%s%s[/vc_column]', $width,
			$heading, $content);
	}


	/*
	 * =========================================================================
	 * Staff
	 * =========================================================================
	 */

	/**
	 * Return the formatted 'Staff' area (writers and photographres).
	 *
	 * @param MJKSC_StaffCheck $sc
	 * @return string
	 */
	static function format_staff(MJKSC_StaffCheck $sc): string {

		// An intro
		$intro = sprintf('A staff writer or photographer is a volunteer who has'
			. ' made enough author or photo contributions in a single semester.'
			. ' (Editors, assistants & associates are not counted.) The roles'
			. ' referred to here are whether the user is correctly credited'
		    . ' on the masthead page.<br><br>The threshold for qualification'
			. ' this year is <strong>%s</strong> contributions in the fall or'
		    . ' <strong>%s</strong> in the winter.',
			$sc->fall_threshold(), $sc->winter_threshold());

		// A legend
		$legend = sprintf(
			'<span class="%s">Earned & assigned role</span> |'
			. ' <span class="%s">Earned role but not assigned</span> |'
			. ' <span class="%s">Assigned role but not earned</span> |'
			. ' Neither earned nor assigned',
			self::cl_u_has_role, self::cl_u_needs_role, self::cl_u_bad_role
		);

		// Bundle 'em up
		$intro = sprintf('<div class="%s">%s<br><br>%s</div>',
			self::cl_area_intro, $intro, $legend);

		// The writers group
		$writers = self::format_writers($sc);
		$writers = sprintf('<div class="%s">%s</div>', self::cl_group, $writers);

		// The photographers group
		$photographers = self::format_photographers($sc);
		$photographers = sprintf('<div class="%s">%s</div>', self::cl_group,
			$photographers);

		// All bundled together
		return sprintf('%s%s%s', $intro, $writers, $photographers);
	}


	/*
	 * =========================================================================
	 * Writers
	 * =========================================================================
	 */

	/**
	 * Return the formatted writers group.
	 *
	 * @param MJKSC_StaffCheck $sc
	 * @return string
	 */
	static function format_writers(MJKSC_StaffCheck $sc): string {

		// Get & sort the eligible writers
		$eligible = $sc->eligible_writers();
		usort($eligible, function (MJKSC_User $a, MJKSC_User $b): int {
			return $b->n_writer() <=> $a->n_writer();
		});

		// Make them a VC column
		$existing_h  = self::format_users($eligible,
			[__CLASS__, 'format_user_writer']);
		$existing_h   = self::format_column($existing_h,
			sprintf('Earned writer (%s)', count($eligible)), '1/4');

		// =====================================================================

		// Get & sort the assigned writers
		$assigned = $sc->assigned_writers();
		usort($assigned, function (MJKSC_User $a, MJKSC_User $b): int {
			return $b->n_writer() <=> $a->n_writer();
		});

		// Make them a VC column
		$roled_h  = self::format_users($assigned,
			[__CLASS__, 'format_user_assigned_writer']);
		$roled_h   = self::format_column($roled_h,
			sprintf('Have the role (%s)', count($assigned)), '1/4');

		// =====================================================================

		// Get & sort almost eligible writers based on fall contributions
		$almost_f = $sc->almost_fall_writers();
		usort($almost_f, function (MJKSC_User $a, MJKSC_User $b): int {
			return $b->n_fall_writer() <=> $a->n_fall_writer();
		});

		// Make them a VC column
		$almost_f_h  = self::format_users($almost_f,
			[__CLASS__, 'format_user_writer_fall']);
		$almost_f_h   = self::format_column($almost_f_h,
			sprintf('Almost/Fall (%s)', count($almost_f)), '1/4');

		// =====================================================================

		// Get & sort almost eligible writers based on winter contributions
		$almost_w = $sc->almost_winter_writers();
		usort($almost_w, function (MJKSC_User $a, MJKSC_User $b): int {
			return $b->n_winter_writer() <=> $a->n_winter_writer();
		});

		// Make them a VC column
		$almost_w_h  = self::format_users($almost_w,
			[__CLASS__, 'format_user_writer_winter']);
		$almost_w_h   = self::format_column($almost_w_h,
			sprintf('Almost/Winter (%s)', count($almost_w)), '1/4');

		// =====================================================================

		// Format the VC row
		return self::format_row([
			$existing_h, $roled_h, $almost_f_h, $almost_w_h
		]);
	}

	/**
	 * Format a user as a staff writer, i.e. with their fall and winter
	 * contribution numbers and an indication of their masthead status.
	 *
	 * @param MJKSC_User $u
	 * @return string
	 */
	static function format_user_writer(MJKSC_User $u): string {
		$name = self::format_username($u->id());

		$cl = $u->has_writer_role() ? self::cl_u_has_role : self::cl_u_needs_role;
		$name = sprintf('<span class="%s">%s</span>', $cl, $name);

		$fall = $u->n_fall_writer();
		$winter = $u->n_winter_writer();

		return sprintf('%s (F %s | W %s)', $name, $fall, $winter);
	}

	/**
	 * Format a user as an assigned staff writer, i.e. with an indication
	 * of their masthead status.
	 *
	 * @param MJKSC_User $u
	 * @return string
	 */
	static function format_user_assigned_writer(MJKSC_User $u): string {
		$name = self::format_username($u->id());

		$cl = $u->earned_writer() ? self::cl_u_has_role : self::cl_u_bad_role;
		$name = sprintf('<span class="%s">%s</span>', $cl, $name);

		return $name;
	}

	/**
	 * Format a user as a staff writer almost eligible in the fall,
	 * i.e. with their fall contributions.
	 *
	 * @param MJKSC_User $u
	 * @return string
	 */
	static function format_user_writer_fall(MJKSC_User $u): string {
		$name = self::format_username($u->id());
		return sprintf('%s (%s)', $name, $u->n_fall_writer());
	}

	/**
	 * Format a user as a staff writer almost eligible in the winter,
	 * i.e. with their winter contributions.
	 *
	 * @param MJKSC_User $u
	 * @return string
	 */
	static function format_user_writer_winter(MJKSC_User $u): string {
		$name = self::format_username($u->id());
		return sprintf('%s (%s)', $name, $u->n_winter_writer());
	}


	/*
	 * =========================================================================
	 * Photographers
	 * =========================================================================
	 */

	/**
	 * Format the photographers group.
	 *
	 * @param MJKSC_StaffCheck $sc
	 * @return string
	 */
	static function format_photographers(MJKSC_StaffCheck $sc): string {

		// Get & sort the eligible photographers
		$eligible = $sc->eligible_photographers();
		usort($eligible, function (MJKSC_User $a, MJKSC_User $b): int {
			return $b->n_photographer() <=> $a->n_photographer();
		});

		// Make them a VC column
		$existing_h  = self::format_users($eligible,
			[__CLASS__, 'format_user_photographer']);
		$existing_h   = self::format_column($existing_h,
			sprintf('Earned photo (%s)', count($eligible)), '1/4');

		// =====================================================================

		// Get & sort the assigned photographers
		$assigned = $sc->assigned_photographers();
		usort($assigned, function (MJKSC_User $a, MJKSC_User $b): int {
			return $b->n_photographer() <=> $a->n_photographer();
		});

		// Make them a VC column
		$roled_h  = self::format_users($assigned,
			[__CLASS__, 'format_user_assigned_photographer']);
		$roled_h   = self::format_column($roled_h,
			sprintf('Have the role (%s)', count($assigned)), '1/4');

		// =====================================================================

		// Get & sort almost eligible photographers based on fall contributions
		$almost_f = $sc->almost_fall_photographers();
		usort($almost_f, function (MJKSC_User $a, MJKSC_User $b): int {
			return $b->n_fall_photographer() <=> $a->n_fall_photographer();
		});

		// Make them a VC column
		$almost_f_h  = self::format_users($almost_f,
			[__CLASS__, 'format_user_photographer_fall']);
		$almost_f_h   = self::format_column($almost_f_h,
			sprintf('Almost/Fall (%s)', count($almost_f)), '1/4');

		// =====================================================================

		// Get & sort almost eligible photographers based on winter contributions
		$almost_w = $sc->almost_winter_photographers();
		usort($almost_w, function (MJKSC_User $a, MJKSC_User $b): int {
			return $b->n_winter_photographer() <=> $a->n_winter_photographer();
		});

		// Make them a VC column
		$almost_w_h  = self::format_users($almost_w,
			[__CLASS__, 'format_user_photographer_winter']);
		$almost_w_h   = self::format_column($almost_w_h,
			sprintf('Almost/Winter (%s)', count($almost_w)), '1/4');

		// =====================================================================

		// Make it all a VC row
		return self::format_row([
			$existing_h, $roled_h, $almost_f_h, $almost_w_h
		]);
	}

	/**
	 * Format a user as a staff photographer, i.e. with their fall and winter
	 * contribution numbers and an indication of their masthead status.
	 *
	 * @param MJKSC_User $u
	 * @return string
	 */
	static function format_user_photographer(MJKSC_User $u): string {
		$name = self::format_username($u->id());

		$cl = $u->has_photographer_role() ? self::cl_u_has_role : self::cl_u_needs_role;
		$name = sprintf('<span class="%s">%s</span>', $cl, $name);

		$fall = $u->n_fall_photographer();
		$winter = $u->n_winter_photographer();
		return sprintf('%s (F %s | W %s)', $name, $fall, $winter);
	}
	/**
	 * Format a user as an assigned staff photographer, i.e. with an indication
	 * of their masthead status.
	 *
	 * @param MJKSC_User $u
	 * @return string
	 */
	static function format_user_assigned_photographer(MJKSC_User $u): string {
		$name = self::format_username($u->id());

		$cl = $u->earned_photographer() ? self::cl_u_has_role : self::cl_u_bad_role;
		$name = sprintf('<span class="%s">%s</span>', $cl, $name);

		return $name;
	}

	/**
	 * Format a user as a staff photographer almost eligible in the fall,
	 * i.e. with their fall contributions.
	 *
	 * @param MJKSC_User $u
	 * @return string
	 */
	static function format_user_photographer_fall(MJKSC_User $u): string {
		$name = self::format_username($u->id());
		return sprintf('%s (%s)', $name, $u->n_fall_photographer());
	}

	/**
	 * Format a user as a staff photographer almost eligible in the winter,
	 * i.e. with their winter contributions.
	 *
	 * @param MJKSC_User $u
	 * @return string
	 */
	static function format_user_photographer_winter(MJKSC_User $u): string {
		$name = self::format_username($u->id());
		return sprintf('%s (%s)', $name, $u->n_winter_photographer());
	}

	/*
	 * =========================================================================
	 * Voting
	 * =========================================================================
	 */

	/**
	 * Format the voting area.
	 *
	 * @param MJKSC_StaffCheck $sc
	 * @return string
	 */
	static function format_voting(MJKSC_StaffCheck $sc): string {

		// An intro
		$intro = sprintf('Voting rights: (1) a person holds one of the voting'
			. ' positions listed in the constitution or (2) they contribute to'
			. ' half the issues published in one semester.<br>(Unlike the staff'
			. ' writer/photographer positions, issues are only counted once for'
			. ' voting.) <br><br>The threshold this year is <strong>%s</strong>'
			. ' issues contributed to in the fall or <strong>%s</strong> in the'
			. ' winter.',
			$sc->fall_threshold(), $sc->winter_threshold());

		// Bundle it up
		$intro = sprintf('<div class="%s">%s</div>', self::cl_area_intro,
			$intro);

		return sprintf('%s<div class="%s">%s</div>', $intro,
			self::cl_group, self::format_voters($sc));
	}

	/**
	 * Format the voters group.
	 *
	 * @param MJKSC_StaffCheck $sc
	 * @return string
	 */
	static function format_voters(MJKSC_StaffCheck $sc): string {

		// Get & sort the voters with voting rights due to their role
		$inherent = $sc->inherent_voters();
		usort($inherent, function (MJKSC_User $a, MJKSC_User $b): int {
			return $a->voting_role()->priority(false) <=>
			       $b->voting_role()->priority(false);
		});

		// Make them a VC column
		$inherent_h = self::format_users($inherent,
			[__CLASS__, 'format_user_voter_inherent']);
		$inherent_h = self::format_column($inherent_h,
			sprintf('By role (%s)', count($inherent)), '1/4');

		// =====================================================================

		// Get & sort the voters with voting rights due to their contributions
		$earned = $sc->earned_voters();
		usort($earned, function (MJKSC_User $a, MJKSC_User $b): int {
			return $b->n_voter() <=> $a->n_voter();
		});

		// Make them a VC column
		$earned_h = self::format_users($earned,
			[__CLASS__, 'format_user_voter_earned']);
		$earned_h = self::format_column($earned_h,
			sprintf('By contributions (%s)', count($earned)), '1/4');

		// =====================================================================

		// Get & sort users who have almost earnred voting rights in the fall
		$almost_f = $sc->almost_fall_voters();
		usort($almost_f, function (MJKSC_User $a, MJKSC_User $b): int {
			return $b->n_fall_voter() <=> $a->n_fall_voter();
		});

		// Make them a VC column
		$almost_f_h = self::format_users($almost_f,
			[__CLASS__, 'format_user_voter_fall']);
		$almost_f_h = self::format_column($almost_f_h,
			sprintf('Almost/Fall (%s)', count($almost_f)), '1/4');

		// =====================================================================

		// Get & sort users who have almost earnred voting rights in the fall
		$almost_w = $sc->almost_winter_voters();
		usort($almost_w, function (MJKSC_User $a, MJKSC_User $b): int {
			return $b->n_winter_voter() <=> $a->n_winter_voter();
		});

		// Make them a VC column
		$almost_w_h = self::format_users($almost_w,
			[__CLASS__, 'format_user_voter_winter']);
		$almost_w_h = self::format_column($almost_w_h,
			sprintf('Almost/Winter (%s)', count($almost_w)), '1/4');

		// =====================================================================

		// Make it all a VC row
		return self::format_row([
			$inherent_h, $earned_h, $almost_f_h, $almost_w_h]);
	}

	/**
	 * Format a user as a voter with automatic voting rights, i.e. with
	 * an indication of the role that gives them that.
	 *
	 * @param MJKSC_User $u
	 * @return string
	 */
	static function format_user_voter_inherent(MJKSC_User $u): string {
		$name = self::format_username($u->id());
		return sprintf('%s (%s)', $name, $u->voting_role()->title());
	}

	/**
	 * Format a user as a voter with earned voting rights, i.e. with
	 * an indication of their fall and winter contributions.
	 *
	 * @param MJKSC_User $u
	 * @return string
	 */
	static function format_user_voter_earned(MJKSC_User $u): string {
		$name = self::format_username($u->id());
		$fall = $u->n_fall_voter();
		$winter = $u->n_winter_voter();

		return sprintf('%s (F %s | W %s)', $name, $fall, $winter);
	}

	/**
	 * Format a user as a voter with voting rights almost earned in the fall,
	 * i.e. with an indication of their fall contributions.
	 *
	 * @param MJKSC_User $u
	 * @return string
	 */
	static function format_user_voter_fall(MJKSC_User $u): string {
		$name = self::format_username($u->id());
		return sprintf('%s (%s)', $name, $u->n_fall_voter());
	}

	/**
	 * Format a user as a voter with voting rights almost earned in the winter,
	 * i.e. with an indication of their winter contributions.
	 *
	 * @param MJKSC_User $u
	 * @return string
	 */
	static function format_user_voter_winter(MJKSC_User $u): string {
		$name = self::format_username($u->id());
		return sprintf('%s (%s)', $name, $u->n_winter_voter());
	}


	/*
	 * =========================================================================
	 * Contributions
	 * =========================================================================
	 */

	/**
	 * Format the contributions area.
	 *
	 * @param MJKSC_StaffCheck $sc
	 * @return string
	 */
	static function format_contributions(MJKSC_StaffCheck $sc): string {

		// An introduction
		$intro = 'The regular sections count author and notes contributions. ' .
			' The photos section counts photo contributions only.';
		$intro = sprintf('<div class="%s">%s</div>', self::cl_area_intro,
			$intro);

		// A section for each main category + photos
		$sections = [
			$sc->section_cat('news'),
			$sc->section_cat('opinion'),
			$sc->section_cat('arts'),
			$sc->section_cat('features'),
			$sc->section_cat('sports'),
			$sc->section_photo()
		];

		$grid = JKNLayouts::grid($sections, 3, [__CLASS__, 'format_section'],
			self::cl_contributions, '', self::cl_section);

		return sprintf('%s<br />%s', $intro, $grid);
	}

	/**
	 * Format an individual section.
	 *
	 * @param MJKSC_Section $sec
	 * @return string
	 */
	static function format_section(MJKSC_Section $sec): string {

		// Format the name
		$name = sprintf('<h3>%s</h3>', $sec->name());

		// Start counting total contributors (not all those listed will make it)
		$total_contributions = $sec->totals()['contributions'];
		$total_contributors = 0;

		// Format each division
		$div_lis = [];
		foreach($sec->matrix() as $div => $users) {

			// Filter out users with no contributions, summing them as we go
			$user_to_conts = [];
			foreach($users as $user => $types) {
				$conts = array_sum($types);
				if ($conts) $user_to_conts[$user] = $conts;
			}

			// Bail if there are none
			if (empty($user_to_conts)) continue;

			// Add to the total contributors
			$total_contributors += count($user_to_conts);

			// Sort from most contributions to fewest
			arsort($user_to_conts);

			// Make the <li>
			$div_lis[] = sprintf('<li>%s</li>',
				self::format_division($div, $user_to_conts));
		}

		// Make the <ul>
		$div_ul = sprintf('<div class="%s"><ul>%s</ul></div>',
			self::cl_divisions, implode('', $div_lis));

		// Format the meta info
		$meta = sprintf(
			'<div class="%s">Contributors: %s<br>Contributions: %s'
			. '<br>Contributions per contributor: %s</div>',
			self::cl_section_meta,
			$total_contributors, $total_contributions,
			round($total_contributions / $total_contributors, 1));

		// Bundle it all up
		return sprintf('<div class="%s %s-%s">%s%s%s</div>',
			self::cl_section_inner, self::cl_section,
			JKNStrings::sanitize($sec->name()), $name, $meta, $div_ul);
	}

	/**
	 * Format an individual division of users.
	 *
	 * @param string $div_name
	 * @param MJKSC_User[] $users
	 * @return string
	 */
	static function format_division(string $div_name, array $users): string {

		// Heading
		$name = sprintf('<h4>%s (%s)</h4>', $div_name, count($users));

		// <li> items
		$user_lis = [];
		foreach($users as $id => $conts) {
			$user_lis[] = sprintf('<li>%s</li>',
				self::format_user_section($id, $conts));
		}

		// Make the <ul> and wrap in a div
		$user_ul = sprintf('<div class="%s"><ul>%s</ul></div>',
			self::cl_division_users, implode('', $user_lis));

		// Bundle it all up
		return sprintf('<div class="%s">%s%s</div>',
			self::cl_division, $name, $user_ul);
	}

	/**
	 * format a user for use in a section, i.e. with their total contributions
	 * to that section.
	 *
	 * @param string $id
	 * @param int $conts
	 * @return string
	 */
	static function format_user_section(string $id, int $conts): string {
		$name = self::format_username($id);
		return sprintf('%s (%s)', $name, $conts);
	}


	/*
	 * =========================================================================
	 * Overview
	 * =========================================================================
	 */

	/**
	 * Format and return an overview of the section, including sundry data.
	 *
	 * 1. The date this SC was last updated.
	 * 2. The number of issues in the year, incl. per fall and winter.
	 * 3. The number of articles published, as well as the average per issue.
	 * 4. A table of contributions, contributors, and ratios, organized by
	 *      type of contribution and division of contributor.
	 * 5. Useful links.
	 *
	 * Yes, currently this is the most bloated function on the planet.
	 *
	 * @param MJKSC_StaffCheck $sc
	 * @return string
	 */
	static function format_overview(MJKSC_StaffCheck $sc): string {
		$view = $sc->section_all();
		$totals = $view->totals();

		/*
		 * ===================================================================
		 * Gather variables
		 * ===================================================================
		 */

		// Issues
		$fall = count($sc->fall_issues());
		$winter = count($sc->winter_issues());
		$issues = $fall + $winter;
		$so_far = $sc->issues_so_far();

		// Articles
		$articles = count($sc->posts());
		$articles_per_issue = round($articles / $so_far);

		// Contributions
		$contributions  = $totals['contributions'];
		$type_to_ctions = $totals['type_to_contributions'];
		$div_to_ctions  = $totals['div_to_contributions'];

		// Contributors
		$contributors   = $totals['contributors'];
		$type_to_ctors  = $totals['type_to_contributors'];
		$div_to_ctors   = $totals['div_to_contributors'];

		// Summarize types and divisions
		$types = array_keys($type_to_ctions);
		$divs = array_keys($div_to_ctions);

		// Remove empty types
		foreach($types as $key => $type) {
			if (empty($type_to_ctions[$type])) unset($types[$key]);
		}

		// Remove empty divisions
		foreach($divs as $key => $div) {
			if (empty($div_to_ctions[$div])) unset($divs[$div]);
		}

		// Rate of contributions per contributor
		$rate = round($contributions / $contributors, 1);

		// Type to rate
		$type_to_rate = [];
		foreach($types as $type) {
			$type_to_rate[$type] =
				round($type_to_ctions[$type] / $type_to_ctors[$type], 1);
		}

		// Division to rate
		$div_to_rate = [];
		foreach($divs as $div) {
			$div_to_rate[$div] =
				round($div_to_ctions[$div] / $div_to_ctors[$div], 1);
		}

		/*
		 * ===================================================================
		 * Format infoboxes
		 * ===================================================================
		 */

		// Format last updated infobox
		$now = JKNTime::dt_now()->getTimestamp();
		$ib_update = sprintf('Last updated: %s', MJKGTAPI::format_date($now));

		// Format issues infobox
		$so_far_h = $issues == $so_far ? '' : sprintf(' (published so far: %s)',
			$so_far);
		$ib_issues = sprintf(
			'Issues: %s%s | Fall: %s | Winter: %s',
			$issues, $so_far_h, $fall, $winter);

		// Format articles infobox
		$ib_articles = sprintf('Articles: %s | Average per issue: %s',
			$articles, $articles_per_issue);

		// Format links infobox
		$link_mh = sprintf('<a href="%1$s" title="%2$s">%2$s</a>',
			MJKMHAPI::url($sc->year()),
			sprintf('Masthead %s', $sc->year()->format()));

		$link_contribs = '<a href="/contributors" title="All contributors">'
		                 . 'All contributors</a>';

		$ib_links = sprintf( 'Links: %s | %s', $link_mh, $link_contribs);

		/*
		 * ===================================================================
		 * Format contributions table infobx
		 * ===================================================================
		 */

		// Heading row
		$top_cols = ['<td></td>',
			sprintf('<td class="%s">Total</td>', self::cl_table_heading)];
		foreach($types as $type) {
			$top_cols[] = sprintf('<td class="%s %s">Type: %s</td>',
				self::cl_table_type, self::cl_table_heading,
				JKNStrings::capitalize($type));
		}
		foreach($divs as $div) {
			$top_cols[] = sprintf('<td class="%s %s">%s</td>',
				self::cl_table_div, self::cl_table_heading, $div);
		}

		// Contributors row
		$ctors_cols = [sprintf('<td class="%s">Contributors</td>',
			self::cl_table_heading), sprintf('<td>%s</td>', $contributors)];
		foreach($types as $type) {
			$ctors_cols[] = sprintf('<td class="%s">%s</td>',
				self::cl_table_type, $type_to_ctors[$type]);
		}
		foreach($divs as $div) {
			$ctors_cols[] = sprintf('<td class="%s">%s</td>',
				self::cl_table_div, $div_to_ctors[$div]);
		}

		// Contributions row
		$ctions_cols = [sprintf('<td class="%s">Contributions</td>',
			self::cl_table_heading), sprintf('<td>%s</td>', $contributions)];
		foreach($types as $type) {
			$ctions_cols[] = sprintf('<td class="%s">%s</td>',
				self::cl_table_type, $type_to_ctions[$type]);
		}
		foreach($divs as $div) {
			$ctions_cols[] = sprintf('<td class="%s">%s</td>',
				self::cl_table_div, $div_to_ctions[$div]);
		}

		// Rates row
		$rates_cols = [sprintf('<td class="%s">Contributions per contributor</td>',
			self::cl_table_heading), sprintf('<td>%s</td>', $rate)];
		foreach($types as $type) {
			$rates_cols[] = sprintf('<td class="%s">%s</td>',
				self::cl_table_type, $type_to_rate[$type]);
		}
		foreach($divs as $div) {
			$rates_cols[] = sprintf('<td class="%s">%s</td>',
				self::cl_table_div, $div_to_rate[$div]);
		}

		// Merge all the <tr>s
		$contribs_rows = [];
		foreach([$top_cols, $ctors_cols, $ctions_cols, $rates_cols] as $cols) {
			$contribs_rows[] = sprintf('<tr>%s</tr>', implode('', $cols));
		}

		// The infobox is a table
		$ib_contribs = sprintf('<table class="%s"><tbody>%s</tbody></table>',
			self::cl_overview_table, implode('', $contribs_rows));

		/*
		 * ===================================================================
		 * Merge infoboxes to form the final area
		 * ===================================================================
		 */

		$area = '';
		$ibs = [$ib_update, $ib_issues, $ib_articles, $ib_contribs, $ib_links];
		foreach($ibs as $ib) {
			$area .= sprintf('<div class="%s">%s</div>', self::cl_infobox, $ib);
		}

		// wrap in a div for good measure
		return sprintf('<div>%s</div>', $area);
	}


	/*
	 * =========================================================================
	 * Style
	 * =========================================================================
	 */

	/**
	 * Return the formatted CSS in a <style> tag.
	 *
	 * @return string
	 */
	static function style(): string {

		$main       = MJKCommonTools::colour('main');

		// Determine section colorations
		$section_colours = '';
		$sections = ['news', 'opinion', 'arts', 'features', 'sports', 'photos'];
		foreach($sections as $section) {

			// Have to hardcode this one...

			// Derive class
			$suffix = $section == 'arts' ? 'a&amp;e' : $section;
			$cl = sprintf('%s-%s', self::cl_section,
				JKNStrings::sanitize($suffix));

			// Derive colours
			$hex = MJKCommonTools::colour($section);
			$rgba = JKNColours::hex_to_rgba($hex, 0.15);

			// Generate CSS
			$section_colours .= sprintf('.%s h3 { color: %s; }', $cl, $hex);
			$section_colours .= sprintf('.%s { background: %s; }', $cl, $rgba);
		}

		return JKNCSS::tag($section_colours . '
		
			.'.self::cl_sc.' a {
			    color: #222;
			}
			
			.'.self::cl_sc.' {
			    color: #000;
			}
			
			.'.self::cl_area.' {
			    padding-bottom: 37px;
			    border-bottom: 1px solid '.$main.';
			}
			
			.'.self::cl_area.':last-child {
			    padding-bottom: 0;
			    border-bottom: none;
			}
			
			.'.self::cl_infobox.',
			.'.self::cl_group.',
			.'.self::cl_section_inner.' {
				border: 1px solid '.$main.';
				border-radius: 5px;
				overflow: hidden;
			}
			
			.'.self::cl_infobox.' {
			    padding: 10px;
			    margin: 10px 0;
			    border-left: 3px solid '.$main.';
			    font-size: 16px;
			    background: '.JKNColours::hex_to_rgba($main, 0.035).';
			}
			
			.'.self::cl_overview_table.' td {
			    border: none;
			    border-right: 1px solid '.JKNColours::hex_to_rgba($main, 0.2).';
			    font-size: 15px;
			    text-align: center;
			}
			
			.'.self::cl_overview_table.' td:last-child {
			    border-right: none;
			}
			
			.'.self::cl_overview_table.' tr {
			    border-bottom: 1px solid '.JKNColours::hex_to_rgba($main, 0.2).';
			}
			
			.'.self::cl_overview_table.' tr:last-child {
			    border-bottom: none;
			}
			
			.'.self::cl_table_heading.' {
			    background: '.JKNColours::hex_to_rgba($main, 0.03).';
			}
			
			.'.self::cl_group.' {
			    padding: 20px 10px;
			    background: '.JKNColours::hex_to_rgba($main, 0.03).';
			    margin-top: 10px;
			    margin-bottom: 10px;
			}
			
			.'.self::cl_group.' h3 {
			    margin-top: 0;
			}		
			
			.'.self::cl_users.' {
			    margin-bottom: 0 !important;
			}
			
			.'.self::cl_users.' li {
			    list-style: none;
			    margin-left: 0;
			    font-size: 14px;
			    letter-spacing: -.3px;
			}
			
			.'.self::cl_users.' li a {  
			    padding: 3px 1px;
			}
			
			.'.self::cl_u_has_role.' {
			    padding: 3px 0;
			    background: '.JKNColours::good().';
			}
			
			.'.self::cl_u_needs_role.' {
			    padding: 3px 0;
			    background: '.JKNColours::bad().';
			}
			
			.'.self::cl_u_bad_role.' {
			    padding: 3px 0;
			    background: '.JKNColours::meh().';
			}
			
			.'.self::cl_contributions.' .vc_row {
			    margin-bottom: 40px;
			}
			
			.'.self::cl_contributions.' .vc_row:last-child {
			    margin-bottom: 0;
			}
			
			.'.self::cl_section.' h3 {
			    margin-top: 0;
			    font-weight: bold;
			}
			
			.'.self::cl_section_inner.' {
			    padding: 15px;
			}
			
			.'.self::cl_divisions.' ul {
			    margin-bottom: 0 !important;
			}
			
			.'.self::cl_divisions.' ul li {
			    list-style: none;
			    margin-left: 0;
			}
			
			.'.self::cl_division_users.' li {
			    margin-left: 10px !important;
			    font-size: 14px;
			}
		');
	}
}
