<?php

/**
 * Represents a role held by a particular user in a particular year.
 */
class MJKMH_HeldRole {

	private $role_title;
	private $alias;
	private $year;
	private $preferred;
	private $interim;
	private $start;
	private $end;

	/**
	 * Save the title, alias, year, preferred status, interim status, and
	 * start and end dates.
	 *
	 * @param array $args
	 */
	function __construct(array $args) {
		$this->role_title = $args['role_title'];
		$this->alias = $args['alias'];
		$this->year = $args['year'];
		$this->preferred = $args['preferred'];
		$this->interim = $args['interim'];
		$this->start = $args['start'];
		$this->end = $args['end'];
	}

	/*
	 * =========================================================================
	 * Getters
	 * =========================================================================
	 */

	/**
	 * Return the actual role object.
	 *
	 * @return MJKMH_Role
	 */
	function role(): MJKMH_Role { return MJKMHAPI::role($this->role_title); }

	/**
	 * Return the effective title of this role.
	 *
	 * @return string
	 */
	function title(): string {
		$role = $this->alias;
		return $this->interim ? sprintf('Interim %s', $role) : $role;
	}

	/**
	 * Return the formatted academic year this role was held.
	 *
	 * @return string
	 */
	function year(): string { return $this->year; }

	/**
	 * Return the academic year object for when this role was held.
	 *
	 * @return JKNAcademicYear
	 */
	function ac_year(): JKNAcademicYear {
		return JKNAcademicYear::make_from_format($this->year);
	}

	/**
	 * Return true iff this is one of the user's preferred roles.
	 *
	 * @return bool
	 */
	function preferred(): bool { return $this->preferred; }

	/**
	 * Return a sortable priority string. The priority is in several stages:
	 * -- Preferred (if preferred is set to be included via the argument)
	 * -- Priority of the role itself
	 * -- Whether it's an interim role
	 * -- The start date
	 * -- The end date
	 * -- The role title, alphabetically
	 *
	 * @param bool $pref Whether to factor in the roles' preferred status.
	 * @return string
	 */
	function priority(bool $pref=true): string {

		$base = sprintf('%s-%s-%s-%s-%s',
			str_pad($this->role()->priority(), 2, '0', STR_PAD_LEFT),
			(int) $this->interim, $this->start->format('Ymd'),
			$this->end->format('Ymd'), $this->role_title);

		return $pref ? sprintf('%s-%s', (int) !$this->preferred, $base) : $base;
	}

	/**
	 * Return true iff this is an interim role.
	 *
	 * @return bool
	 */
	function interim(): bool { return $this->interim; }

	/**
	 * Return the first day this role was held.
	 *
	 * @return DateTime
	 */
	function start(): DateTime { return $this->start; }

	/**
	 * Return the last day this role was held.
	 *
	 * @return DateTime
	 */
	function end(): DateTime { return $this->end; }

	/**
	 * Return true iff this role was active at the given time (now if no
	 * time is supplied).
	 *
	 * @param DateTime|null $dt
	 * @return bool
	 */
	function active(DateTime $dt=null): bool {
		if (is_null($dt)) $dt = JKNTime::dt_now();
		return ($this->start() <= $dt) && ($dt <= $this->end());
	}
}
