<?php

/**
 * Represents a Staff Check'd section. A data tabulation engine.
 *
 * Holds on to a list of users, as well as matrix with this structure:
 * [ division_name =>
 *      [ user_name =>
 *          [ type_name => n_contributions ]
 *      ]
 * ]
 *
 * Also reports on totals across various fields.
 */
abstract class MJKSC_Section {

	/*
	 * =========================================================================
	 * Constants
	 * =========================================================================
	 */

	const no_role_div = 'Users with no role';
	const types = ['author' => 0, 'photo' => 0, 'notes' => 0, 'video' => 0];

	/*
	 * =========================================================================
	 * Properties
	 * =========================================================================
	 */

	private $name;
	private $users;
	private $matrix;
	private $totals;
	protected $args;


	/*
	 * =========================================================================
	 * Overrides
	 * =========================================================================
	 */

	/**
	 * Return the number of contributions there are among the given posts
	 * for the given type.
	 *
	 * @param string $type
	 * @param MJKSC_Post[] $posts
	 * @return int
	 */
	protected abstract function type_contributions(string $type,
			array $posts): int;


	/*
	 * =========================================================================
	 * Basics
	 * =========================================================================
	 */

	/**
	 * Store a name and arguments & extract users from the staff check object.
	 * Also calculate the matrix and totals immediately.
	 *
	 * @param MJKSC_StaffCheck $sc
	 * @param string $name
	 * @param array $args
	 */
	final function __construct(MJKSC_StaffCheck $sc, string $name,
			array $args=[]) {

		$this->users = $sc->users();
		$this->name = $name;
		$this->args = $args;
		$this->make_matrix();
		$this->sum_totals();
	}

	/**
	 * @return string
	 */
	final function name(): string { return $this->name; }


	/*
	 * =========================================================================
	 * Matrix
	 * =========================================================================
	 */

	/**
	 * Construct the matrix and sort its division keys.
	 */
	protected final function make_matrix(): void {
		foreach($this->users as $u) {

			// Identify the user's division
			$div = $this->get_user_division($u);

			// Get an array of [type => n_contributions]
			if (!isset($this->matrix[$div])) $this->matrix[$div] = [];
			$this->matrix[$div][$u->id()] = $this->user_contributions($u);
		}

		// Sort the matrix by division importance
		uksort($this->matrix, function(string $a, string $b): int {
			if ($a == MJKSC_Section::no_role_div) return 1;
			if ($b == MJKSC_Section::no_role_div) return -1;

			$pri_a = MJKMHAPI::division($a)->priority();
			$pri_b = MJKMHAPI::division($b)->priority();
			return $pri_a <=> $pri_b;
		});
	}

	/**
	 * @return array[]
	 */
	final function matrix(): array { return $this->matrix; }


	/*
	 * =========================================================================
	 * Users
	 * =========================================================================
	 */

	/**
	 * Return tabulations of the user's contributions, keyed by type.
	 *
	 * @param MJKSC_User $u
	 * @return int[] An array of [type => n_contributions]
	 */
	protected final function user_contributions(MJKSC_User $u): array {
		$types = self::types;

		foreach($u->contribution_types() as $type => $posts) {
			$types[$type] = $this->type_contributions($type, $posts);
		}

		return $types;
	}

	/**
	 * Return the name of the masthead division the user is in.
	 *
	 * @param MJKSC_User $u
	 * @return string
	 */
	protected final function get_user_division(MJKSC_User $u): string {
		$divisions = [];

		foreach($u->roles() as $role) {
			$division = $role->role()->division();
			if (!in_array($division, $divisions)) $divisions[] = $division;
		}

		if (empty($divisions)) return self::no_role_div;

		usort($divisions, function(MJKMH_Division $a, MJKMH_Division $b): int {
			return $a->priority() <=> $b->priority();
		});

		return reset($divisions)->name();
	}

	/**
	 * Return an array of the given user's contributions, keyed by type.
	 *
	 * @param MJKSC_User $u
	 * @return int[]
	 */
	final function by_user(MJKSC_User $u): array {
		$div = $this->get_user_division($u);
		return $this->matrix[$div][$u->id()];
	}


	/*
	 * =========================================================================
	 * Totals
	 * =========================================================================
	 */

	/**
	 * Sum up the totals of various vectors.
	 */
	protected final function sum_totals(): void {

		// Total number of contributions made in this section
		$contributions = 0;

		// Total number of contributors to this section
		$contributors = count($this->users);

		// Table of type to number of contributions
		$type_to_contributions = [];

		// Table of type to number of contributors
		$type_to_contributors = [];

		// Table of divison to number of contributions
		$div_to_contributions = [];

		// Table of division to number of contributors
		$div_to_contributors = [];

		// Go through each division and its users
		foreach($this->matrix as $div => $users) {

			// Record the # of users are in the division
			$div_to_contributors[$div] = count($users);

			// Got through each user and its types of contributions
			foreach($users as $uid => $u_types) {

				// Go through each type and the number of contributions in it
				foreach($u_types as $type => $n) {

					// Add to the total contributions
					$contributions += $n;

					// Add to the # of contributors for this type
					if(!isset($type_to_contributors[$type])) {
						$type_to_contributors[$type] = 0;
					}
					$type_to_contributors[$type] += ($n > 0);

					// Add to the # of contributions for this type
					if(!isset($type_to_contributions[$type])) {
						$type_to_contributions[$type] = 0;
					}
					$type_to_contributions[$type] += $n;

					// Add to the # of contributions for this division
					if(!isset($div_to_contributions[$div])) {
						$div_to_contributions[$div] = 0;
					}
					$div_to_contributions[$div] += $n;
				}
			}
		}

		// Put into a mini database
		$this->totals = [
			'contributions'         => $contributions,
			'contributors'          => $contributors,
			'type_to_contributions' => $type_to_contributions,
			'type_to_contributors'  => $type_to_contributors,
			'div_to_contributions'  => $div_to_contributions,
			'div_to_contributors'   => $div_to_contributors
		];
	}

	/**
	 * @return array
	 */
	final function totals(): array { return $this->totals; }
}
