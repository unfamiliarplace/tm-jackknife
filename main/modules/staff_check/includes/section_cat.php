<?php

/**
 * A section that filters posts based on a category slug.
 * Also only retains the 'author' and 'notes' types since there is a separate
 * section for photos.
 */
final class MJKSC_Section_Cat extends MJKSC_Section {

	/**
	 * Return the number of posts are in the requested category.
	 *
	 * @param string $type
	 * @param MJKSC_Post[] $posts
	 * @return int
	 */
	protected function type_contributions(string $type, array $posts): int {

		// Bail if the type is not author or notes
		if (!in_array($type, ['author', 'notes'])) return 0;

		// Filter the posts that are in this category
		$cat = $this->args['cat'];
		$in_cat = array_filter($posts, function(MJKSC_Post $p) use ($cat): bool {
			return $p->cat() == $cat;
		});

		// Return the # of them
		return count($in_cat);
	}
}
