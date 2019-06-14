<?php

/**
 * Represent an issue.
 * There are three 'flavours': normal, summer, and special name.
 */
class MJKVI_Issue {

	// The exclusion categories for posts
	static $post_issue_excl;

	// The categories to get posts from
	const cats = ['news', 'opinion', 'arts', 'features', 'sports'];
	const cat_videos = 'videos';

	// Properties (TODO Make private with getters. Some may already exist)
	public $num;
	public $vol;
	public $dt;
	public $holiday_delay;
	public $issuu_url;
	public $archiveorg_url;
	public $on_site;
	public $posts_url;
	public $notes;
	public $skip;
	public $cdir;
	public $cache_issuu;
	public $cache_ao;
	public $has_posts;
	public $is_summer;
	public $is_special;

	// Optimization
	private $posts;

	/**
	 * Extract arguments (passed from the volume).
	 *
	 * @param array $args
	 */
	function __construct(array $args) {
        
        $this->num = null; // Will be set by volume later
        $this->vol = $args['vol']; // Volume
        $this->dt = $args['dt']; // DateTime of hard copy publish
        
        // Optional / variant class parameters
        $this->holiday_delay = (isset($args['holiday_delay'])) ? $args['holiday_delay'] : false;
        $this->issuu_url = (isset($args['issuu_url'])) ? $args['issuu_url'] : '';
        $this->archiveorg_url = (isset($args['archiveorg_url'])) ? $args['archiveorg_url'] : '';
        $this->on_site = (isset($args['on_site'])) ? $args['on_site'] : true;
        $this->posts_url = (isset($args['posts_url'])) ? $args['posts_url'] : '';
        $this->notes = (isset($args['notes'])) ? $args['notes'] : '';
        $this->special = (isset($args['special'])) ? $args['special'] : '';
        $this->skip = (isset($args['skip'])) ? $args['skip'] : false;

        // Caches
        $vol_cdir = $this->vol->cdir;
        $this->cdir = new MJKVICacheDir_Issue($vol_cdir,
            ['issue' => $this]);
        $this->cache_issuu = new MJKVICacheObject_IssuuData($this->cdir,
            ['issue' => $this]);
        $this->cache_ao = new MJKVICacheObject_ArchiveorgThumb($this->cdir,
            ['issue' => $this]);

        // Singleton (only request once)
        $this->has_posts = null;

        // Class identifiers
        $this->is_summer = false;
        $this->is_special = false;
    }

	/**
	 * Return the issue's number.
	 *
	 * @return string|int
	 */
	function get_num() {
        return $this->num;
    }

	/**
	 * Set the issue's number.
	 *
	 * @param int|string $num
	 */
	function set_num($num): void {
        $this->num = $num;
    }

	/**
	 * Return a nice name for the issue (capitalized if requested).
	 *
	 * @param bool $cap
	 * @return string
	 */
	function get_name(bool $cap=true): string {
        return sprintf('%sssue %s', ($cap) ? 'I' : 'i', $this->get_num());
    }

	/**
	 * Return the relative URL of this issue.
	 *
	 * @return string
	 */
	function get_url(): string {
        return sprintf('%s/%s', $this->vol->get_url(), $this->get_num());
    }

	/**
	 * Return a DateTime for the first day to include posts on.
	 * A separate function because a holiday delay could mean posts from
	 * earlier need to be queried.
	 *
	 * @return DateTime
	 */
	function get_first_day(): DateTime {
        return ($this->holiday_delay) ? JKNTime::dt_start_of_week($this->dt) : $this->dt;
    }

	/**
	 * Return a DateTime for the last day to include posts on.
	 *
	 * @return DateTime
	 */
	function get_last_day(): DateTime {
        $dt_first = $this->get_first_day();        
        $dt_last = clone $dt_first; // If not cloned, the added intervals will change the original object
        
        // Moving it ahead by 7 days and subtracting one second : 11:59:59 pm. previous day
        $dt_last->add(new DateInterval('P7D'));
        $dt_last->sub(new DateInterval('PT1S'));
        return $dt_last;
    }

	/**
	 * Return true iff this issue is not yet published.
	 *
	 * @return bool
	 */
	function in_future(): bool {
        return JKNTime::dt_now() < $this->get_first_day();
    }

	/**
	 * Return true iff this is a fall issue.
	 *
	 * @return bool
	 */
	function in_fall(): bool {
		$ay = $this->vol->get_academic_year();
		$fd = $this->get_first_day();
		return ($ay->start_of_fall() <= $fd) && ($fd <= $ay->end_of_fall());
	}

	/**
	 * Return true iff this is a winter issue.
	 *
	 * @return bool
	 */
	function in_winter(): bool {
		$ay = $this->vol->get_academic_year();
		$fd = $this->get_first_day();
		return ($ay->start_of_winter() <= $fd) && ($fd <= $ay->end_of_winter());
	}

	/**
	 * Return true iff this issue has an embed or posts to show or link to.
	 *
	 * @return bool
	 */
	function has_content() {
        return (!empty($this->issuu_url)) or ( !empty($this->archiveorg_url)) or ( $this->has_posts());
    }

	/**
	 * Fetch and return the Issuu JSON data.
	 *
	 * @return null|string
	 */
	function create_issuu_data(): ?string {
        // Short-circuit if no Issuu URL
        if (empty($this->issuu_url))
            return null;

        // Prepare to get data using Issuu oembed API
        // Maxheight is harcoded (same height as archiveorg embeds by default)
        $params = 'format=json&iframe=true&maxheight=384&url=%s';
        $loc = sprintf('http://issuu.com/oembed?%s', $params, $this->issuu_url);

        // StdClass: version, type, width, height, title, url, author_url, provider_name,
        // provider_url, html (of embed), thumbnail_url, thumbnail_width, thumbnail_height
        $data = file_get_contents(sprintf($loc, $this->issuu_url));

        // Data is in JSON format
        return $data;
    }

	/**
	 * Get the Issuu JSON data from the cache.
	 *
	 * @return stdClass
	 */
	function issuu_data(): stdClass {
		return json_decode($this->cache_issuu->write());
	}

	/**
	 * Return an array for the issuu embed.
	 * ['html' => string, 'width' => int, 'height' => int]
	 *
	 * @return array|null
	 */
	function issuu_embed(): ?array {

        // Short-circuit if no Issuu URL
        if (empty($this->issuu_url)) return null;

        // Otherwise fetch/read from cache
        $data = $this->issuu_data();

        return [
            'html' => $data->html,
            'width' => $data->width,
            'height' => $data->height
        ];
    }

	/**
	 * Return an array for the issuu thumbnail.
	 * ['html' => string, 'width' => int, 'height' => int]
	 *
	 * @param string $size
	 * @return array|null
	 */
	function issuu_thumbnail(string $size='medium'): ?array {

        // Short-circuit if no Issuu URL
        if (empty($this->issuu_url)) return null;

        $data = $this->issuu_data();
        $src = $data->thumbnail_url;

        // Use small or large size if requested
        if (in_array($size, ['small', 'large'])) {
            $src = str_replace('medium', $size, $src);
        }

        // Determine width and height
        // The ratio is 2/3 width/height by default
		// Issuu's three sizes have heights of 100, 150, 480
        switch ($size) {
            case('small'):
                $height = 100;
                break;
            case('medium'):
                $height = 150;
                break;
            case('large'):
                $height = 480;
                break;
	        default:
	        	$height = 150;
	        	break;
        }
        $width = (int) round($height * 2 / 3);

        // Combine into array
        return ['src' => $src, 'width' => $width, 'height' => $height];
    }

	/**
	 * Extract the ID portion from the Archive.org URL.
	 * Could change if AO changes whole site structure.
	 *
	 * @return null|string
	 */
	function archiveorg_id(): ?string {

        // Short-circuit
        if (empty($this->archiveorg_url)) return null;

        // Begin with the URL
        $ao_id = $this->archiveorg_url;

        // Strip trailing slash
        if (substr($ao_id, -1) == '/')
            $ao_id = substr($ao_id, 0, -1);

        // Extract part from last slash (+1) to end
        return substr($ao_id, strrpos($ao_id, '/', -1) + 1);
    }

	/**
	 * Return an array for the archiveorg embed.
	 * ['html' => string, 'width' => int, 'height' => int]
	 *
	 * @param int $width
	 * @param int $height
	 * @return array|null
	 */
	function archiveorg_embed(int $width=560, int $height=384): ?array {

        // Short-circuit if no Archive.org URL
        if (empty($this->archiveorg_url)) return null;

        // Fixed iframe html from AO
        $ao_id = $this->archiveorg_id();
        $src = sprintf('https://archive.org/stream/%s?ui=embed#mode/2up', $ao_id);

        $html = sprintf('<iframe src="%s" width="%s" height="%s"'
                . 'frameborder="0" webkitallowfullscreen="true"'
                . 'mozallowfullscreen="true" allowfullscreen></iframe>',
            $src, $width, $height);

        return ['html' => $html, 'width' => $width, 'height' => $height];
    }

	/**
	 * Create and return the archive.org thumbnail.
	 * Quality is jpeg quality, 1-100.
	 *
	 * @param array $args
	 * @return string
	 */
	function create_archiveorg_thumbnail(array $args=[]): string {

        // Derive the jpeg quality if set
        $quality = (isset($args['quality'])) ? $args['quality'] : 100;

        // Fetch image as gif (it's an animated run of all PDF pages)
        $ao_id = $this->archiveorg_id();
        $src = 'http://archive.org/download/%1$s/%1$s.gif';
        $thumb = imagecreatefromgif(sprintf($src, $ao_id));

        // Convert image to jpeg to get just 1st page, store in a variable
        ob_start();
        imagejpeg($thumb, NULL, $quality);
        $thumb = ob_get_clean();

        // Return that data
        return $thumb;
    }

	/**
	 * Return an array including the URL of the archive.org thumbnail.
	 * ['src' => string, 'width' => int, 'height' => 152]
	 *
	 * @param bool $internal
	 * @return array|null
	 */
	function archiveorg_thumbnail(bool $internal=false): ?array {

        // Short-circuit if no archive.org URL
        if (empty($this->archiveorg_url)) return null;

        // Otherwise get the URL from the cache
        $this->cache_ao->write();
        $src = $this->cache_ao->url();

        // Format in HTML-compliant terms
        return ['src' => $src, 'width' => 100, 'height' => 152];
    }

	/**
	 * Return an array for a dynamic thumbnail (could be Issuu or AO).
	 * Use the Issuu URL if available, then the Archive.org one if needed.
	 *
	 * ['src' => string, 'width' => int, 'height' => int
	 *
	 * @param string $size 'small', 'medium' or 'large' (Issuu data options)
	 * @return array
	 */
	function dynamic_thumbnail(string $size='medium'): array {

        // N.B. The width and height are the requested ones
        // What is available may differ, hence the returned array
        // First, if no content then the "not yet" thumb
        if (!$this->has_content()) {
            return ['src' => MJKVI_NYT_THUMB, 'width' => 100, 'height' => 150];

            // Otherwise try Issuu first
        } elseif (!empty($this->issuu_url)) {
            return $this->issuu_thumbnail($size);

            // Then archive.org (N.B. the width & height here are fixed)
        } elseif (!empty($this->archiveorg_url)) {
            return $this->archiveorg_thumbnail();

            // If there is content but no pdf, use the "default" thumb
        } else {
            return ['src' => MJKVI_DEF_THUMB, 'width' => 100, 'height' => 150];
        }
    }

	/**
	 * Return a formatted date string (e.g. 'February 1, 2018').
	 *
	 * @return string
	 */
	function format_date(): string {
        return $this->dt->format('F j, Y');
    }

	/**
	 * Return true iff the given DateTime (from a post) is within this issue.
	 *
	 * @param DateTime $dt
	 * @return bool
	 */
	function contains_dt(DateTime $dt): bool {
        return (($this->get_first_day() <= $dt) &&
                ($dt <= $this->get_last_day()));
    }

	/**
	 * Return true iff the given DateTime is the same day as this issue's first.
	 *
	 * @param DateTime $dt
	 * @return bool
	 */
	function lands_on_dt(DateTime $dt): bool {
        $f = 'Y-m-d';
        return $this->get_first_day()->format($f) == $dt->format($f);
    }

	/**
	 * Format and return a DateTime suitable for use in a WP_Query.
	 *
	 * @param DateTime $dt
	 * @return string
	 */
	protected function get_wp_query_date_str(DateTime $dt): string {
        return $dt->format('Ymd H:i:s e');
    }

	/**
	 * Return the WP Query args for the post query.
	 *
	 * @return array
	 */
	protected function get_wp_query_args(): array {
        return [
            'date_query' => [
                'after' => $this->get_wp_query_date_str($this->get_first_day()),
                'before' => $this->get_wp_query_date_str($this->get_last_day()),
                'inclusive' => true,
                'column' => 'post_date_gmt' // Otherwise only posts 4 a.m. and after are included
            ],
            'post_status' => 'publish',
            'post_type' => 'post',
            'orderby' => 'date',
            'order' => 'DESC',
        ];
    }

	/**
	 * Return the post IDs, filtering out those that are not supposed to be
	 * attached to an issue.
	 *
	 * @param string[] $pids
	 * @return string[]
	 */
	function filter_no_iss_pids(array $pids): array {

    	// Get the excluded categories if not yet gotten
	    if (is_null(static::$post_issue_excl)) {
	    	$cats = MJKVI_ACF_Options::get(MJKVI_ACF_Options::no_issue_cats);
		    static::$post_issue_excl = $cats;
	    }

	    // Filter
        return array_filter($pids, function(string $pid): bool {

        	// Disallow if individual post is set to avoid attaching to an issue
	        if (MJKVI_ACF_P::get(MJKVI_ACF_P::no_iss, $pid)) return false;

	        // Disallow if the category is set to avoid attaching to an issue
	        foreach(wp_get_post_categories($pid) as $cat) {
		        if (in_array($cat, static::$post_issue_excl)) return false;
	        }

	        // Otherwise good
	        return true;
        });
    }

	/**
	 * Return the posts, filtering out those that are not supposed to be
	 * attached to an issue.
	 *
	 * @param WP_Post[] $posts
	 * @return WP_Post[]
	 */
	function filter_no_iss_posts(array $posts): array {
	    $pids = JKNPosts::to_pids($posts);
	    $surviving = $this->filter_no_iss_pids($pids);
	    return array_filter($posts, function(WP_Post $p) use ($surviving): bool {
	    	return in_array($p->ID, $surviving);
	    });
    }

	/**
	 * Return true iff any posts (on this site or another) are associated with
	 * this issue.
	 *
	 * @return bool
	 */
	function has_posts(): bool {

        // Only load once to avoid duplicate queries
        if (is_null($this->has_posts)) {

            // Post must have an associated website
            $ws = $this->get_website();
            if (!empty($ws)) {

                // If it's on the main site, run a query
                if ($ws->main) {
                	return !empty($this->get_posts());

                // Otherwise it must have a specific posts URL
                } else {
                    $this->has_posts = (!empty($this->posts_url));
                }

            // No website
            } else {
            	$this->has_posts = false;
            }
        }

        // Return the set value
        return $this->has_posts;
    }

	/**
	 * Return the posts associated with the given category slug.
	 *
	 * @param string $cat_slug
	 * @return WP_Post[]
	 */
	function get_posts_in_cat(string $cat_slug): array {
        $args = $this->get_wp_query_args();
        $args['category_name'] = $cat_slug;
        $query = new WP_Query($args);

	    $posts = $query->posts;
        $posts = $this->filter_no_iss_posts($posts);
        return array_unique($posts, SORT_REGULAR);
    }

	/**
	 * Return all the posts from this issue.
	 * If $flatten is false, return subarrays keyed by category.
	 * If $flatten is true, return a flat array of posts agnostic of category.
	 *
	 * @param bool $flatten
	 * @return array
	 */
    function get_posts(bool $flatten=false): array {

    	// Fetch if not yet fetched
    	if (is_null($this->posts)) {
		    $posts = [];

		    // Only query if the issue is from this website
		    $ws = $this->get_website();
		    if (!empty($ws) && $ws->main) {

			    // Could replace with a constant
			    $cat_slugs = array_merge(self::cats, [self::cat_videos]);

			    foreach ($cat_slugs as $cat_slug) {
				    $cat_posts = $this->get_posts_in_cat($cat_slug);

				    // Filter video posts: should only keep those without other cat
				    if ($cat_slug == self::cat_videos) {
					    $cat_posts = self::filter_video_posts($cat_posts);
				    }

				    // Add if not empty
				    if (!empty($cat_posts)) $posts[$cat_slug] = $cat_posts;
			    }
		    }

		    $this->posts = $posts;
	    }

	    // Get and flatten if necessary
        $posts = $this->posts;

        if ($flatten && !empty($posts)) {
            $posts = JKNArrays::flatten_2D($posts);
            $posts = array_unique($posts, SORT_REGULAR);
        }

        return $posts;
    }

	/**
	 * Filter and return the given video category posts, keeping them only if
	 * they have no other category.
	 *
	 * @param WP_Post[] $cat_posts
	 * @return WP_Post[]
	 */
	function filter_video_posts(array $posts): array {

		$filter = function(WP_Post $p): bool {

			// Get flat array of category slugs
			$slugs = array_map(function (WP_Term $cat): string {
				return $cat->slug;
			}, get_the_category($p->ID));

			// Check if any of them are in our list of categories
			foreach($slugs as $slug) {
				if (in_array($slug, self::cats)) return false;
			}

			return true;
		};

		return array_filter($posts, $filter);
	}

	/**
	 * Return the website for this issue's volume.
	 *
	 * @return MJKVI_ArchivalWebsite
	 */
	function get_website(): ?MJKVI_ArchivalWebsite {
        return $this->vol->get_website();
    }

    /**
     * Return true iff this issue has a print edition.
     */
    function has_print_edition(): bool {
        return (!empty($this->issuu_url) || !empty($this->archiveorg_url));
    }


	/*
	 * =========================================================================
	 * Formatting
	 * =========================================================================
	 */

    /**
     * Return a formatted full name (Volume, Issue) for this issue.
     *
     * @param bool $short_vol Whether to use a short volume name.
     * @param bool $cap Whether to capitalize 'Issue'.
     * @return string
     */
    function format_vol_iss_title(bool $short_vol=false,
            bool $cap=false): string {
        
        return sprintf('%s, %s', $this->vol->get_name($short_vol),
                $this->get_name($cap));
    }

	/**
	 * Return a formatted <a> tag linking to this issue.
	 *
	 * @param bool $cap Whether to capitalize 'Issue'.
	 * @return string
	 */
	function format_a(bool $cap=true): string {
        return sprintf('<a href="%s" title="%s">%s</a>', $this->get_url(),
            $this->format_vol_iss_title(), $this->get_name($cap));
    }

	/**
	 * Return a fully formatted vol, iss pair of <a> tags
	 *
	 * @param bool $short_vol_name Whether to use a short volume name.
	 * @param bool $cap Whether to capitalize 'Issue'.
	 * @return string
	 */
	function format_vol_iss_a(bool $short_vol_name=false,
	        bool $cap=false): string {

        $vol_a = $this->vol->format_a($short_vol_name);
        $iss_a = $this->format_a($cap);
        return sprintf('%s, %s', $vol_a, $iss_a);
    }
}


/*
 * =========================================================================
 * Summer Issue
 * =========================================================================
 */

/**
 * A variant of an issue that includes only posts published after the last
 * issue in the year.
 *
 * It is assumed to have no print edition. (If there is one, it should be a
 * regular issue!)
 */
class MJKVI_SummerIssue extends MJKVI_Issue {

	/**
	 * Call parent, setting summer to true.
	 * TODO Users of this should do get_class instead of the is_summer property.
	 *
	 * Note that DateTime passed is that of the final issue of the year.
	 *
	 * @param array $args
	 */
	function __construct(array $args) {
        parent::__construct($args);
        $this->is_summer = true;
    }

	/**
	 * Format the date.
	 * Summer date is formatted as e.g. 'April 1 to August 31, 2017'.
	 *
	 * @return string
	 */
	function format_date(): string {
        return sprintf('%s to %s',
	        $this->get_first_day()->format('F j'),
	        $this->get_last_day()->format('F j, Y'));
    }

	/**
	 * Return 'su' for a number.
	 *
	 * @return string|int
	 */
	function get_num() { return 'su'; }

	/**
	 * Return a nice name for this issue, capitalized if requested.
	 *
	 * @param bool $cap Whether to capitalize 'Summer'.
	 * @return string
	 */
	function get_name(bool $cap=true): string {
        return sprintf('%summer articles', ($cap) ? 'S' : 's');
    }

	/**
	 * Return the first day. The first day is 1 week after the last issue.
	 *
	 * @return DateTime
	 */
	function get_first_day(): DateTime {
        //  Must clone, otherwise the add will affect the first day
        $final_issue_first_dt = clone parent::get_first_day();

        // Move up 7 days
        $final_issue_first_dt->add(new DateInterval('P7D'));
        return $final_issue_first_dt;
    }

	/**
	 * Return the last day. The last day is just before the next cademic year.
	 *
	 * @return DateTime
	 */
	function get_last_day(): DateTime {
        $ay = new JKNAcademicYear($this->dt);
        return $ay->end();
    }

}


/*
 * =========================================================================
 * Special Name Issue
 * =========================================================================
 */

/**
 * Variant of issue that has been given a special name (e.g. The Tedium).
 */
class MJKVI_SpecialNameIssue extends MJKVI_Issue {

	/**
	 * Call parent, setting special to true.
	 * TODO Users of this should do get_class instead of the is_special property.
	 *
	 * @param array $args
	 */
	function __construct(array $args) {
        parent::__construct($args);
        $this->is_special = true;
    }

	/**
	 * Special name will be used for query var and cache director, so sanitize.
	 *
	 * @return string
	 */
	private function sanitize_special(): string {
        return preg_replace("/[^-a-z0-9]/",
	        '', str_replace(' ', '-', strtolower($this->special)));
    }

	/**
	 * Return the number: the special name, unless not skipping numbering.
	 *
	 * @return string|int
	 */
	function get_num() {
        $special = $this->sanitize_special();
        return ($this->skip) ? $special :
	        sprintf('%s-%s', $this->num, $special);
    }

	/**
	 * Return the name. Prepend number if not skipping numbering, and
	 * capitalize if requested.
	 *
	 * @param bool $cap Whether to capitalize 'Issue'.
	 * @return string
	 */
	function get_name(bool $cap=true): string {
        return ($this->skip) ? $this->special :
	        sprintf('%sssue %s: %s', ($cap) ? 'I' :
		        'i', $this->get_num(), $this->special);
    }

}
