<?php

/**
 * A section that only keeps track of contributions of the 'photo' type.
 */
final class MJKSC_Section_Photo extends MJKSC_Section {

	/**
	 * Return the number of posts iff the type is 'photo', otherwise 0.
	 *
	 * @param string $type
	 * @param MJKSC_Post[] $posts
	 * @return int
	 */
	protected function type_contributions(string $type, array $posts): int {
		return $type == 'photo' ? count($posts) : 0;
	}
}
