<?php

/**
 * Represent a single role.
 */
final class MJKMH_Role {

	private $division_name;
	private $title;
	private $priority;
	private $aliases;
	private $email;
	private $voting;
	private $archival;

	/**
	 * Save the title, priority, aliases, email, whether it has automatic voting
	 * rights, whether it's an archival role, and the name of the division.
	 *
	 * @param array $args
	 */
	function __construct(array $args) {
		$this->title            = $args['title'];
		$this->priority         = $args['priority'];
		$this->aliases          = $args['aliases'];
		$this->email            = $args['email'];
		$this->voting           = $args['voting'];
		$this->archival         = $args['archival'];
		$this->division_name    = $args['division_name'];
	}

	/**
	 * Return the name of the division.
	 *
	 * @return string
	 */
	function division_name(): string { return $this->division_name; }

	/**
	 * Return the title.
	 *
	 * @return string
	 */
	function title(): string { return $this->title; }

	/**
	 * Return the priority as an alphabetizable string.
	 *
	 * @param bool $incl_div Whether to prepend the division's priority.
	 * @return string
	 */
	function priority(bool $incl_div=true): string {

		$base = str_pad($this->priority, 2, '0', STR_PAD_LEFT);

		if ($incl_div) {
			return sprintf('%s-%s', $this->division()->priority(), $base);
		} else {
			return $base;
		}
	}

	/**
	 * Return the aliases.
	 *
	 * @return string[]
	 */
	function aliases(): array { return $this->aliases; }

	/**
	 * Return the email.
	 *
	 * @return string
	 */
	function email(): string { return $this->email; }

	/**
	 * Return whether the role has voting rights.
	 *
	 * @return bool
	 */
	function voting(): bool { return $this->voting; }

	/**
	 * Return whether the role is archival.
	 *
	 * @return bool
	 */
	function archival(): bool { return $this->archival; }

	/**
	 * Return the division object this role belongs to.
	 *
	 * @return MJKMH_Division
	 */
	function division(): MJKMH_Division {
		return MJKMHAPI::division($this->division_name);
	}
}
