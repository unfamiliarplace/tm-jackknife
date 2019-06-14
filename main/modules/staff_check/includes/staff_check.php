<?php

/**
 * Represents a single Staff Check.
 * Stores a year, volume, issues, thresholds, posts, and users.
 * Provides lists of users who meet various qualifications.
 */
final class MJKSC_StaffCheck {

	/*
	 * =========================================================================
	 * Constants
	 * =========================================================================
	 */

	// We presume that Masthead has been set up to include these divisions
	const no_staff_divs = ['Editorial Board', 'Assistant & Associate Editors'];

	/*
	 * =========================================================================
	 * Properties
	 * =========================================================================
	 */

	private $year;
	private $vol;
	private $current;

	private $almost;

	private $fall_issues;
	private $winter_issues;
	private $fall_threshold;
	private $winter_threshold;

	private $posts;
	private $users;

	private $excluding_comic;

	// Optimization
	private $checked_issues_so_far = false;
	private $issues_so_far;


	/*
	 * =========================================================================
	 * Construction
	 * =========================================================================
	 */

	/**
	 * Store the year and extracts of its volume and issue data.
	 * Gather the posts and users for this year.
	 *
	 * @param JKNAcademicYear $year
	 */
	function __construct(JKNAcademicYear $year) {
		$this->year = $year;
		$this->current = $year->is(MJKMHAPI::newest_year());

		// Determine "almost" value
		$this->almost = (int) MJKSC_ACF::get(MJKSC_ACF::almost_n);

		// Determine whether we are excluding the comic
		$this->determine_comic_exclusion();

		// Extract year data
		$this->vol = MJKVIAPI::get_volume_by_academic_year($year);
		$this->fall_issues = $this->vol->fall_issues();
		$this->winter_issues = $this->vol->winter_issues();
		$this->fall_threshold = floor(count($this->fall_issues) / 2);
		$this->winter_threshold = floor(count($this->winter_issues) / 2);

		// Set up post ID and user tables
		$this->posts = MJKSC_API::gather_posts($this);
		$this->users = MJKSC_API::gather_users($this);
	}

	/**
	 * Determine whether we are excluding comic from staff consideration.
	 */
	function determine_comic_exclusion(): void {
		$e_comic = (bool) MJKSC_ACF::get(MJKSC_ACF::exclude_comic);

		if ($e_comic) {
			$this->excluding_comic = true;

		} else {
			$e_comic_years = MJKSC_ACF::get(MJKSC_ACF::exclude_comic_years);
			if (empty($e_comic_years)) $e_comic_years = [];
			$this->excluding_comic = in_array($this->year()->format(), $e_comic_years);
		}
	}


	/*
	 * =========================================================================
	 * Getters
	 * =========================================================================
	 */

	/**
	 * @return JKNAcademicYear
	 */
	function year(): JKNAcademicYear { return $this->year; }

	/**
	 * @return MJKVI_Volume
	 */
	function vol(): MJKVI_Volume { return $this->vol; }

	/**
	 * @return int
	 */
	function almost(): int { return $this->almost; }

	/**
	 * Return whether this staff check represents the current/newest year.
	 *
	 * @return bool
	 */
	function current(): bool { return $this->current; }

	/**
	 * @return MJKVI_Issue[]
	 */
	function fall_issues(): array { return $this->fall_issues; }

	/**
	 * @return MJKVI_Issue[]
	 */
	function winter_issues(): array { return $this->winter_issues; }

	/**
	 * @return int
	 */
	function fall_threshold(): int { return $this->fall_threshold; }

	/**
	 * @return int
	 */
	 function winter_threshold(): int { return $this->winter_threshold; }

	/**
	 * @return MJKSC_Post[]
	 */
	function posts(): array { return $this->posts; }

	/**
	 * @return MJKSC_User[]
	 */
	function users(): array { return $this->users; }

	/**
	 * @return bool
	 */
	function excluding_comic(): bool { return $this->excluding_comic; }


	/*
	 * =========================================================================
	 * Issues
	 * =========================================================================
	 */

	/**
	 * Return the number of issues that have been published so far this year.
	 *
	 * @return int
	 */
	function issues_so_far(): int {

		if (!$this->checked_issues_so_far) {

			$so_far = 0;
			$issues = array_merge($this->fall_issues, $this->winter_issues);
			foreach($issues as $iss) {
				$so_far += (int) !$iss->in_future();
			}

			$this->issues_so_far = $so_far;
			$this->checked_issues_so_far = true;
		}

		return $this->issues_so_far;
	}


	/*
	 * =========================================================================
	 * Helpers
	 * =========================================================================
	 */

	/**
	 * Return true iff the number of items short qualifies as "almost" enough.
	 *
	 * @param int $short
	 * @return bool
	 */
	function _almost(int $short): bool {
		return (0 < $short) && ($short <= $this->almost);
	}


	/*
	 * =========================================================================
	 * Staff
	 * =========================================================================
	 */

	/**
	 * Return true iff the given user is allowed to hold a staff role.
	 *
	 * @param MJKSC_User $u
	 * @return bool
	 */
	static function _can_be_staff(MJKSC_User $u): bool {
		foreach($u->roles() as $role) {
			if (in_array($role->role()->division()->name(),
				self::no_staff_divs)) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Return the users who are eligible to be staff writers.
	 *
	 * @return MJKSC_User[]
	 */
	function eligible_writers(): array {
		return array_filter($this->users, function(MJKSC_User $u): bool {
			return self::_can_be_staff($u) && $u->earned_writer();
		});
	}

	/**
	 * Return the users who are eligible to be staff photographers.
	 *
	 * @return MJKSC_User[]
	 */
	function eligible_photographers(): array {
		return array_filter($this->users, function(MJKSC_User $u): bool {
			return self::_can_be_staff($u) && $u->earned_photographer();
		});
	}

	/**
	 * Return the users who are almost eligible to be staff writers
	 * based on their fall contributions.
	 *
	 * @return MJKSC_User[]
	 */
	function almost_fall_writers(): array {
		return array_filter($this->users, function(MJKSC_User $u): bool {
			return self::_can_be_staff($u) && !$u->earned_writer() &&
			       self::_almost($u->fall_writer_short());
		});
	}

	/**
	 * Return the users who are almost eligible to be staff writers
	 * based on their winter contributions.
	 *
	 * @return MJKSC_User[]
	 */
	function almost_winter_writers(): array {
		return array_filter($this->users, function(MJKSC_User $u): bool {
			return self::_can_be_staff($u) && !$u->earned_writer() &&
			       self::_almost($u->winter_writer_short());
		});
	}

	/**
	 * Return the users who are almost eligible to be staff photographers
	 * based on their fall contributions.
	 *
	 * @return MJKSC_User[]
	 */
	function almost_fall_photographers(): array {
		return array_filter($this->users, function(MJKSC_User $u): bool {
			return self::_can_be_staff($u) && !$u->earned_photographer() &&
			       self::_almost($u->fall_photographer_short());
		});
	}

	/**
	 * Return the users who are almost eligible to be staff photographers
	 * based on their winter contributions.
	 *
	 * @return MJKSC_User[]
	 */
	function almost_winter_photographers(): array {
		return array_filter($this->users, function(MJKSC_User $u): bool {
			return self::_can_be_staff($u) && !$u->earned_photographer() &&
				self::_almost($u->winter_photographer_short());
		});
	}

	/**
	 * Return the users who have been assigned a staff writer role
	 * in the masthead.
	 *
	 * @return MJKSC_User[]
	 */
	function assigned_writers(): array {
		return array_filter($this->users, function(MJKSC_User $u): bool {
			return $u->has_writer_role();
		});
	}

	/**
	 * Return the users who have been assigned a staff photographer role
	 * in the masthead.
	 *
	 * @return MJKSC_User[]
	 */
	function assigned_photographers(): array {
		return array_filter($this->users, function(MJKSC_User $u): bool {
			return $u->has_photographer_role();
		});
	}


	/*
	 * =========================================================================
	 * Voting
	 * =========================================================================
	 */

	/**
	 * Return the users who have voting rights due to their role.
	 *
	 * @return MJKSC_User[]
	 */
	function inherent_voters(): array {
		return array_filter($this->users, function(MJKSC_User $u): bool {
			return $u->has_voting_role();
		});
	}

	/**
	 * Return the users who have earned voting rights through contributions.
	 *
	 * @return MJKSC_User[]
	 */
	function earned_voters(): array {
		return array_filter($this->users, function(MJKSC_User $u): bool {
			return !$u->has_voting_role() && $u->earned_voter();
		});
	}

	/**
	 * Return the users who have almost earned voting rights through
	 * contributions made in the fall.
	 *
	 * @return MJKSC_User[]
	 */
	function almost_fall_voters(): array {
		return array_filter($this->users, function(MJKSC_User $u): bool {
			return !$u->has_voting_role() && !$u->earned_voter() &&
			       self::_almost($u->fall_voter_short());
		});
	}

	/**
	 * Return the users who have almost earned voting rights through
	 * contributions made in the winter.
	 *
	 * @return MJKSC_User[]
	 */
	function almost_winter_voters(): array {
		return array_filter($this->users, function(MJKSC_User $u): bool {
			return !$u->has_voting_role() && !$u->earned_voter() &&
			       self::_almost($u->winter_voter_short());
		});
	}


	/*
	 * =========================================================================
	 * Sections
	 * =========================================================================
	 */

	/**
	 * Return a section of users and posts for an overview, i.e. all of them.
	 *
	 * @return MJKSC_Section_All
	 */
	function section_all(): MJKSC_Section_All {
		return new MJKSC_Section_All($this, 'Overview');
	}

	/**
	 * Return a section of users and posts for a given category slug.
	 *
	 * @param string $slug
	 * @return MJKSC_Section_Cat
	 */
	function section_cat(string $slug): MJKSC_Section_Cat {
		$cat = get_category_by_slug($slug);
		return new MJKSC_Section_Cat($this, $cat->name, ['cat' => $slug]);
	}

	/**
	 * Return a section of users and posts for the 'photo' contribution type.
	 *
	 * @return MJKSC_Section_Photo
	 */
	function section_photo(): MJKSC_Section_Photo {
		return new MJKSC_Section_Photo($this, 'Photos');
	}
}
