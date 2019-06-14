<?php

/**
 * Represent a single contributor (a user with roles).
 */
final class MJKMH_User {

	private $id;
	private $status;
	private $roles = [];

	/**
	 * Save the id and status; roles are added separately.
	 *
	 * @param array $args
	 */
	function __construct(array $args) {
		$this->id = $args['id'];
		$this->status = $args['status'];
	}

	/**
	 * Add a role to this user.
	 *
	 * @param MJKMH_HeldRole $role
	 */
	function add_role(MJKMH_HeldRole $role): void { $this->roles[] = $role; }


	/*
	 * =========================================================================
	 * Getters
	 * =========================================================================
	 */

	/**
	 * Return the ID.
	 *
	 * @return string
	 */
	function id(): string { return $this->id; }

	/**
	 * Return the student status.
	 *
	 * @return string
	 */
	function status(): string { return $this->status; }

	/**
	 * Return the roles.
	 *
	 * @return MJKMH_HeldRole[]
	 */
	function roles(): array { return $this->roles; }

	/**
	 * Return the base role corresponding to the given held role title.
	 *
	 * @param string $title
	 * @return MJKMH_Role|null
	 */
	function role_by_alias(string $title): ?MJKMH_Role {
		if (empty($this->roles)) return null;

		foreach($this->roles as $role) {
			if ($role->title() == $title) {
				return $role->role();
			}
		}

		return null;
	}


	/*
	 * =========================================================================
	 * Dynamic selection
	 * =========================================================================
	 */

	/**
	 * Return the WordPress user this user represents.
	 *
	 * @return WP_User
	 */
	function wp(): WP_User { return get_user_by('id', $this->id); }

	/**
	 * Return the roles this user currently holds.
	 *
	 * @return MJKMH_HeldRole[]
	 */
	function current(): array {
		return $this->by_ac_year(new JKNAcademicYear());
	}

	/**
	 * Return the given roles sorted (all roles if none supplied).
	 *
	 * @param MJKMH_HeldRole[] $roles
	 * @return MJKMH_HeldRole[]
	 */
	function sort(array $roles=[]): array {
		if (empty($roles)) $roles = $this->roles;
		return MJKMHAPI::sort_held_roles($roles);
	}

	/**
	 * Return the first among the given roles after sorting
	 * (all roles if none supplied).
	 *
	 * @param MJKMH_HeldRole[] $roles
	 * @return MJKMH_HeldRole
	 */
	function choose(array $roles=[]): MJKMH_HeldRole {
		$sorted = $this->sort($roles);
		return reset($sorted);
	}

	/**
	 * Return an array of the given roles (all if none supplied) keyed by year.
	 * The years and roles inside them are sorted.
	 *
	 * @param MJKMH_HeldRole[] $roles
	 * @return array[]
	 */
	function years_to_roles(array $roles=[]): array {
		if (empty($roles)) $roles = $this->roles;

		$ytr = [];
		foreach($roles as $role) {
			$year = $role->year();
			if (!isset($ytr[$year])) $ytr[$year] = [];
			$ytr[$year][] = $role;
		}

		foreach($ytr as $year => $roles) {
			$ytr[$year] = $this->sort($roles);
		}

		krsort($ytr);
		return $ytr;
	}

	/**
	 * Return the roles corresponding to the given academic year format.
	 *
	 * @param string $year
	 * @return MJKMH_HeldRole[]
	 */
	function by_year(string $year): array {
		$ytr = $this->years_to_roles();
		return isset($ytr[$year]) ? $ytr[$year] : [];
	}

	/**
	 * Return the roles corresponding to the given academic year object.
	 *
	 * @param JKNAcademicYear $ay
	 * @return MJKMH_HeldRole[]
	 */
	function by_ac_year(JKNAcademicYear $ay): array {
		return $this->by_year($ay->format());
	}

	/**
	 * Return the roles corresponding to the 'best' year in terms of the roles
	 * held in it.
	 *
	 * @return MJKMH_HeldRole[]
	 */
	function choose_year(): array {
		$ytr = $this->years_to_roles();
		usort($ytr, function(array $a, array $b): int {
			$best_a = $this->choose($a);
			$best_b = $this->choose($b);
			return $best_a <=> $best_b;
		});

		return reset($ytr);
	}

	/**
	 * Consolidate and return an array of the given roles keyed by role title;
	 * each role title points to an array of years it was held in. The years
	 * are sorted within each role and the roles are sorted by priority.
	 * Use all roles if none are supplied.
	 *
	 * @param MJKMH_HeldRole[] $roles
	 * @return array[]
	 */
	function by_role(array $roles=[]): array {
		if (empty($roles)) $roles = $this->roles();

		// Key the roles by title
		$by_role = [];
		foreach($roles as $role) {
			$title = $role->title();
			$year = $role->ac_year();
			if (!isset($by_role[$title])) $by_role[$title] = [];
			$by_role[$title][] = $year;
		}

		// Sort the years within each role
		foreach($by_role as $title => $years) {
			sort($years);
			$by_role[$title] = $years;
		}

		// Sort the roles by priority
		uksort($by_role, function(string $a, string $b): int {
			$pri_a = $this->role_by_alias($a)->priority();
			$pri_b = $this->role_by_alias($b)->priority();
			return $pri_a <=> $pri_b;
		});

		return $by_role;
	}

	/**
	 * Return the first among the given roles that is marked 'preferred'.
	 * Use all roles if none are supplied.
	 * If none are preferred, return null.
	 *
	 * @param MJKMH_HeldRole[] $roles
	 * @return MJKMH_HeldRole|null
	 */
	function preferred(array $roles=[]): ?MJKMH_HeldRole {
		if (empty($roles)) $roles = $this->roles();

		$preferred = array_filter($roles, function(MJKMH_HeldRole $r): bool {
			return $r->preferred();
		});

		if (empty($preferred)) return null;
		return reset($preferred);
	}

	/**
	 * Return the first year, if any, in which there is a preferred role.
	 *
	 * @return null|string
	 */
	function preferred_year(): ?string {
		$ytr = $this->years_to_roles();
		foreach($ytr as $year => $roles) {
			if (!is_null($this->preferred($roles))) return $year;
		}
		return null;
	}

	/**
	 * Return the given roles keyed by division, such that each division name
	 * corresponds to an array of roles. The divisions and the roles within them
	 * are both sorted. Use all roles if none are supplied.
	 *
	 * @param MJKMH_HeldRole[] $roles
	 * @return array[]
	 */
	function divisions_to_roles(array $roles=[]): array {
		if (empty($roles)) $roles = $this->roles;

		$by_div = [];
		foreach($roles as $role) {
			$div = $role->role()->division_name();
			if (!isset($by_div[$div])) $by_div[$div] = [];
			$by_div[$div][] = $role;
		}

		foreach($by_div as $div => $roles) {
			$by_div[$div] = $this->sort($roles);
		}

		uksort($by_div, function(string $a, string $b): int {
			$pri_a = MJKMHAPI::division($a)->priority();
			$pri_b = MJKMHAPI::division($b)->priority();
			return $pri_a <=> $pri_b;
		});

		return $by_div;
	}

	/**
	 * Return a sortable priority string. The priority is that of the held role,
	 * not taking into account whether it is preferred (user priority does not
	 * depend on their own preferences). Break ties using the number of roles.
	 *
	 * @param array $roles
	 * @return string
	 */
	function priority(array $roles=[]): string {
		if (empty($roles)) $roles = $this->roles;
		$base = $this->choose($roles)->priority($preferred = false);
		return sprintf('%s-%s', $base,
			str_pad(99 - count($roles), 2, '0', STR_PAD_LEFT));
	}
}
