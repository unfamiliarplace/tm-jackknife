<?php

/**
 * Represents a slide of 4 posts.
 */
class MJKFP_Slide {

	// Properties (TODO Make private with getters)
	public $big;
	public $smalls;
	public $shuffle;
	public $shuffle_big;

	/**
	 * Store the big post, the three small posts, and shuffle parameters.
	 *
	 * @param WP_Post $big
	 * @param WP_Post[] $smalls
	 * @param bool $shuffle
	 * @param bool $shuffle_big
	 */
    function __construct(WP_Post $big, array $smalls, bool $shuffle=false,
	        bool $shuffle_big=false) {
        
        $this->big = $big;
        $this->smalls = $smalls;
        $this->shuffle = $shuffle;
        $this->shuffle_big = $shuffle_big;
    }
    
	/**
	 * Return a flat array of posts, shuffling them as required.
	 * Our TD block will turn index 0 into a big module and 1-3 into small ones.
	 *
	 * @return array|WP_Post[]
	 */
    function get_posts(): array {
        // Add the small posts and shuffle if necessary
        $posts = $this->smalls;        
        if ($this->shuffle) shuffle($posts);
        
        // Add the big post at the beginning and shuffle if necessary
        array_unshift($posts, $this->big);
        if ($this->shuffle_big) shuffle($posts);
        
        return $posts;
    }
}
