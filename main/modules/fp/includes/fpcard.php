<?php

/**
 * Represents a Front Page Card.
 */
class MJKFP_FPCard {

	// Properties (TODO Make private with getters)
	public $pid;
	public $dt;
	public $recent_only;
	public $shuffle_all_posts;
	public $shuffle_slides;
	public $shuffle_posts;
	public $shuffle_big_post;
	public $shuffle_spotlights;
	public $spotlights;
	public $slides;


	/**
	 * Derive all parameters from the given post ID, and gather the
	 * spotlights and slides.
	 *
	 * @param $pid
	 */
    function __construct(string $pid) {
        
        // Store the ID
        $this->pid = $pid;
        
        // The datetime of a card represents when it comes into effect
        $this->dt = JKNTime::dt_pid($pid);
        
        // Determine whether we are allowing older posts or not
        $this->recent_only = MJKFP_ACF::get(MJKFP_ACF::recent_only, $pid);
        
        // Determine shuffles        
        $this->shuffle_all_posts = MJKFP_ACF::get(MJKFP_ACF::shuffle_all_posts, $pid);
        $this->shuffle_slides = MJKFP_ACF::get(MJKFP_ACF::shuffle_slides, $pid);
        $this->shuffle_posts = MJKFP_ACF::get(MJKFP_ACF::shuffle_posts, $pid);
        $this->shuffle_big_post = MJKFP_ACF::get(MJKFP_ACF::shuffle_big_post, $pid);
        $this->shuffle_spotlights = MJKFP_ACF::get(MJKFP_ACF::shuffle_spotlights, $pid);
        
        // Get spotlights and slides
        $this->spotlights = $this->gather_spotlights();
        $this->slides = $this->gather_slides();
    }

	/**
	 * Gather and return the registered spotlights.
	 *
	 * @return MJKFP_Spotlight[]
	 */
    private function gather_spotlights(): array {
        $spotlights = [];
        
        // Technically both a post and an "older post" can be saved in db
        if ($this->recent_only) {
            $subf_post = MJKFP_ACF::spotlight_post;
        } else {
            $subf_post = MJKFP_ACF::spotlight_post_older;
        }

        // Loop through each spotlight
        if (MJKFP_ACF::have_rows(MJKFP_ACF::spotlight, $this->pid)) {
            while (MJKFP_ACF::have_rows(MJKFP_ACF::spotlight, $this->pid)) {
                the_row();

                // Determine the type
                $type = MJKFP_ACF::sub(MJKFP_ACF::spotlight_type);
                
                // Get post info if it's an article                
                if ($type == 'article') {
                    $sub_post = MJKFP_ACF::sub($subf_post);
                    
                    $spotlights[] = new MJKFP_SpotlightArticle($sub_post);
                }

                // Get manually entered text info if it's a notice
                elseif ($type == 'notice') {
                    $header = MJKFP_ACF::sub(MJKFP_ACF::spotlight_notice_header);
                    $body = MJKFP_ACF::sub(MJKFP_ACF::spotlight_notice_body);
                    $link = MJKFP_ACF::sub(MJKFP_ACF::spotlight_notice_link);
                    $spotlights[] = new MJKFP_Spotlight($header, $body, $link);
                }
            }
        }

        // Shuffle if set, and return
        if ($this->shuffle_spotlights) shuffle($spotlights);
        return $spotlights;
    }

	/**
	 * Gather and return the registered slides.
	 *
	 * @return MJKFP_Slide[]
	 */
    private function gather_slides(): array {
        $slides = [];
        
        // Technically both a post and an "older post" can be saved in db
        if ($this->recent_only) {
            $subf_big_post = MJKFP_ACF::slides_big_post;
            $subf_small_post = MJKFP_ACF::slides_small_post;
        } else {
            $subf_big_post = MJKFP_ACF::slides_big_post_older;
            $subf_small_post = MJKFP_ACF::slides_small_post_older;
        }
        
        // Loop through each slide
        if (MJKFP_ACF::have_rows(MJKFP_ACF::slides, $this->pid)) {
            while (MJKFP_ACF::have_rows(MJKFP_ACF::slides, $this->pid)) {
                the_row();
                
                // Get the big post        
                $big_post = MJKFP_ACF::sub($subf_big_post);
                
                // Get the small posts
                $small_posts = [];
                if (MJKFP_ACF::have_rows(MJKFP_ACF::slides_small)) {
                    while (MJKFP_ACF::have_rows(MJKFP_ACF::slides_small)) {
                        the_row();
                        $small_posts[] = MJKFP_ACF::sub($subf_small_post);
                    }
                }
                
                // Make a slide
                $slides[] = new MJKFP_Slide($big_post, $small_posts,
                        $this->shuffle_posts, $this->shuffle_big_post);
            }
        }
        
        return $slides;        
    }

	/**
	 * Return a copy of this card's datetime.
	 *
	 * @return DateTime
	 */
	function get_start(): DateTime {
        return clone $this->dt;
    }
    
	/**
	 * Return true iff this card starts in the future.
	 *
	 * @return bool
	 */
	function is_future(): bool {
		return $this->dt > JKNTime::dt_now();
    }
    
	/**
	 * Return the array of spotlights, shuffled if the setting is on.
	 *
	 * @return MJKFP_Spotlight[]
	 */
	function get_spotlights(): array {
        $spotlights = $this->spotlights;
        if ($this->shuffle_spotlights) shuffle($spotlights);
        return $spotlights;
    }
    
	/**
	 *  Return a flat array of posts across all slides, shuffled if necessary.
	 * The way this is used by our TD block: every 1st index is a big post,
	 * and the next 3 are small posts.
	 *
	 * @return WP_Post[]
	 */
	function get_posts(): array {
        
        // Shuffle slides if requested
        $slides = $this->slides;
        if ($this->shuffle_slides) shuffle($slides);
        
        // Flatten slides
        $posts = [];
        foreach ($slides as $slide) {
            $posts = array_merge($posts, $slide->get_posts());
        }
        
        // Shuffle all posts if requested
        if ($this->shuffle_all_posts) shuffle($posts);
        
        return $posts;
    }    
}
