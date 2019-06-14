<?php

/**
 * Represent a single division.
 */
final class MJKMH_Division {

	private $name;
	private $notes;
	private $priority;
	private $roles = [];

	/**
	 * Save the name, notes, and priority.
	 *
	 * @param array $args
	 */
	function __construct(array $args) {
		$this->name = $args['name'];
		$this->notes = $args['notes'];
		$this->priority = $args['priority'];
	}

	/**
	 * Return the name.
	 *
	 * @return string
	 */
	function name(): string { return $this->name; }

	/**
	 * Return the notes.
	 *
	 * @return string
	 */
	function notes(): string { return $this->notes; }

	/**
	 * Return the priority as an alphabetizable string.
	 *
	 * @return string
	 */
	function priority(): string {
		return str_pad($this->priority, 2, '0', STR_PAD_LEFT);
	}

	/**
	 * Return the roles, indexed by title.
	 *
	 * @return MJKMH_Role[]
	 */
	function roles(): array { return $this->roles; }

	/**
	 * Add the given role.
	 *
	 * @param MJKMH_Role $role
	 */
	function add_role(MJKMH_Role $role): void {
		$this->roles[$role->title()] = $role;
	}

	/**
	 * Get the role with the given title, if it exists.
	 *
	 * @param string $title
	 * @return MJKMH_Role|null
	 */
	function role(string $title): ?MJKMH_Role {
		return isset($this->roles[$title]) ? $this->roles[$title] : null;
	}
}
