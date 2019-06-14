<?php

/**
 * A section that simply tallies up the number of posts (a catch-all type).
 */
final class MJKSC_Section_All extends MJKSC_Section {

	/**
	 * Return the number of posts passed regardless of type.
	 *
	 * @param string $type
	 * @param MJKSC_Post[] $posts
	 * @return int
	 */
	protected function type_contributions(string $type, array $posts): int {
		return count($posts);
	}
}
