<?php

/**
 * Represent a WordPress post with key data stored for access & optimization.
 */
class MJKSC_Post {

	/*
	 * =========================================================================
	 * Proprerties
	 * =========================================================================
	 */

	private $id;
	private $cat; // Just a slug for the main category
	private $fall;
	private $issue; // Stored as string


	/*
	 * =========================================================================
	 * Set up
	 * =========================================================================
	 */

	/**
	 * Store an ID, main category slug, whether this post is in the fall, and
	 * the issue (as a unique string).
	 *
	 * @param string $id
	 * @param string $cat
	 * @param bool $fall
	 * @param string $issue
	 */
	function __construct(string $id, string $cat, bool $fall, string $issue) {
		$this->id = $id;
		$this->cat = $cat;
		$this->fall = $fall;
		$this->issue = $issue;
	}


	/*
	 * =========================================================================
	 * Getters
	 * =========================================================================
	 */

	/**
	 * @return string
	 */
	function id(): string { return $this->id; }

	/**
	 * @return string
	 */
	function cat(): string { return $this->cat; }

	/**
	 * Return true iff this post is from the fall.
	 *
	 * @return bool
	 */
	function fall(): bool { return $this->fall; }

	/**
	 * Return true iff this post is not from the fall.
	 *
	 * @return bool
	 */
	function winter(): bool { return !$this->fall; }

	/**
	 * @return string
	 */
	function issue(): string { return $this->issue; }

	/**
	 * Return the string value for comparison in array_unique, etc.
	 *
	 * @return string
	 */
	function __toString(): string { return $this->id; }
}
