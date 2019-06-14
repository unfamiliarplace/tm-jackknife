<?php

/**
 * Represents a volume. Has two flavours: Normal and Erindalian.
 */
class MJKVI_Volume {

	// Properties (TODO Make private with getters -- some may already exist)
	public $pid;
	public $num;
	public $is_erindalian;
	public $notes;
	public $website_pid;
	public $cdir;
	public $thumb;
	public $cache_thumb;
	public $issues;
    
    // Table of volume numbers to width * height (in pixels) of its issues
    // Volumes absent from this table are usually assumed to be [100, 150]
    private $exceptional_sizes = [
        '38' => [95, 150]
    ];

	/**
	 * Extract ACF data from a given WP post ID, and create the issues.
	 *
	 * @param string $pid
	 */
	function __construct(string $pid) {
        $MJKVI = JKNAPI::module();

        // Extract the volume's own data
        $this->pid = $pid;
        $this->num = MJKVI_ACF_VOL::get(MJKVI_ACF_VOL::num, $pid);
        $this->is_erindalian = MJKVI_ACF_VOL::get(MJKVI_ACF_VOL::is_erindalian, $pid);
        $this->notes = MJKVI_ACF_VOL::get(MJKVI_ACF_VOL::notes, $pid);
        $this->website_pid = MJKVI_ACF_VOL::get(MJKVI_ACF_VOL::website, $pid);

        // Cache
        $this->cdir = new MJKVICacheDir_Volume($MJKVI->cache,
            ['volume' => $this]);
        $cdir_thumb = new MJKVICacheDir_VolGeneral($this->cdir);
        $this->cache_thumb = new MJKVICacheObject_VolThumb($cdir_thumb,
            ['volume' => $this]);

        // Extract issue data
        $this->issues = [];
        
        if (MJKVI_ACF_VOL::have_rows(MJKVI_ACF_VOL::issues, $pid)) {
            while (MJKVI_ACF_VOL::have_rows(MJKVI_ACF_VOL::issues, $pid)) {
                the_row();

                // Because the possible needs of various issue types are complex, use an args array
                $args = [];

                // Start off with a reference to the volume and cache
                $args['vol'] = $this;

                // Get and process date as DateTime
                $date = MJKVI_ACF_VOL::sub(MJKVI_ACF_VOL::iss_date);
                $args['dt'] = JKNTime::dt(sprintf('%s 00:00:00', $date));

                // Relocate issue date to closest (backwards) Monday when getting posts
                $args['holiday_delay'] = MJKVI_ACF_VOL::sub(MJKVI_ACF_VOL::iss_holiday_delay);

                // Get and clean Issuu URL
                $issuu_url = trim(MJKVI_ACF_VOL::sub(MJKVI_ACF_VOL::iss_issuu_url));				
                $args['issuu_url'] = $this->sanitize_issuu_url($issuu_url);

                // Get and clean Archive.org URL
                $args['archiveorg_url'] = trim(MJKVI_ACF_VOL::sub(MJKVI_ACF_VOL::iss_archiveorg_url));

                // Get and clean posts URL for issues on website besides the main one
                if ( !$this->is_on_main_website() ) {
                    $args['on_site'] = MJKVI_ACF_VOL::sub(MJKVI_ACF_VOL::iss_on_site);
                    $args['posts_url'] = trim(MJKVI_ACF_VOL::sub(MJKVI_ACF_VOL::iss_posts_url));
                }

                // Get and clean any notes
                $args['notes'] = trim(MJKVI_ACF_VOL::sub(MJKVI_ACF_VOL::iss_notes));
                
                // If this is a special issue, get its name and determine whether numbering will be skipped
                // The process differs here because 
                $is_special = MJKVI_ACF_VOL::sub(MJKVI_ACF_VOL::iss_is_special);			
                if (!empty($is_special)) {
                    $args['special'] = trim(MJKVI_ACF_VOL::sub(MJKVI_ACF_VOL::iss_special));
                    $args['skip'] = MJKVI_ACF_VOL::sub(MJKVI_ACF_VOL::iss_skip);

                    // At this point we will create a special name issue variant if needed
                    $this->issues[] = new MJKVI_SpecialNameIssue($args);

                // Otherwise create a regular old issue
                } else {
                    $this->issues[] = new MJKVI_Issue($args);
                }
            }
        }

        // Sort and enumerate issues
        $this->issues = self::enumerate_issues($this->issues);

        // Add a summer issue at this point to clean up extra posts after the last registered issue
        if (!empty($this->issues)) {
            $args = [
                'vol' => $this,
                'dt' => clone(end($this->issues)->dt) // Otherwise it will be mutable
            ];

            $su_issue = new MJKVI_SummerIssue($args);
            $this->issues[$su_issue->get_num()] = $su_issue;
        }
    }
        
	/**
	 * Return this volume's number.
	 * $strict means without any other characters -- needed for The Erindalian.
	 *
	 * @param bool $strict
	 * @return string
	 */
	function get_num(bool $strict=false): string { return $this->num; }

	/**
	 * Return a displayable name for this volume.
	 *
	 * @param bool $short Whether to use a shorter version of the name.
	 * @return string
	 */
	function get_name(bool $short=false): string {
        return static::get_name_from_n($this->get_num($strict=true), $short);
    }

	/**
	 * Return a displayable name from a given volume number.
	 *
	 * @param int $n
	 * @param bool $short Whether to use a shorter version of the name.
	 * @return string
	 */
	static function get_name_from_n(int $n, bool $short=false): string {
        $word = substr('Volume', 0, ($short) ? 3 : 6);
        return sprintf('%s %s', $word, $n);
    }

	/**
	 * Return the relative URL of this volume.
	 *
	 * @return string
	 */
	function get_url(): string { return sprintf('/v/%s', $this->get_num()); }

	/**
	 * Return this volume's issues.
	 *
	 * @return MJKVI_Issue[]
	 */
	function get_issues(): array { return $this->issues; }

	/**
	 * Return a version of the Issuu URL without a trailing number.
	 * (Sometimes Issuu gives you a URL like '...docname/0').
	 *
	 * @param string $issuu_url
	 * @return string
	 */
	private function sanitize_issuu_url(string $issuu_url): string {

            // A trailing '/\d+' on the end of an Issuu URL breaks the thumbnail
            preg_match("/\/docs\/.*?\/(\d+)/", $issuu_url, $matches);

            // Remove the trailing digit and slash if present
            if (count($matches) > 1) {
                    $end = $matches[1];
                    $issuu_url = substr($issuu_url, 0, -(strlen($end)));
            }

            return $issuu_url;
    }

	/**
	 * Order, number and return the given issues.
	 * Before this is called, the issues do not reliably have a number.
	 *
	 * @param MJKVI_Issue[] $issues
	 * @return MJKVI_Issue[]
	 */
	private static function enumerate_issues(array $issues): array {

        // Sort issues by date
        usort($issues,
            function($issue_a, $issue_b) {
                return $issue_a->get_first_day() <=> $issue_b->get_first_day();
            }
        );

        // Prepare associative array
        $enum_issues = [];

        // Enumerate issues
        $i = 0;
        foreach($issues as $issue) {

            // Only number if not skipping numbering
            if (!$issue->skip) {
                $issue->set_num(++$i);
            }

            // But even if skipped, the "number" will be present (for special issues, as the name)
            $enum_issues[$issue->get_num()] = $issue;
        }

        // Return indexical array
        return $enum_issues;
    }

	/**
	 * Return the issue associated with the given number.
	 *
	 * @param string|int $num
	 * @return MJKVI_Issue|null
	 */
	function get_issue_by_num($num): ?MJKVI_Issue {
        return $this->issues[$num];
    }

	/**
	 * Return the ssue associated with the given DateTime,
	 * i.e. the one during whose week the DateTime falls.
	 *
	 * @param DateTime $dt
	 * @param bool $allow_summer
	 * @return MJKVI_Issue|null
	 */
	function get_issue_by_dt(DateTime $dt,
	        bool $allow_summer=true): ?MJKVI_Issue {

        foreach($this->issues as $U => $issue) {
            if ($issue->contains_dt($dt) &&
                    ($allow_summer || !$issue->is_summer)) {
                return $issue;
            }
        }

        return null;
    }

	/**
	 * Return the first issue.
	 *
	 * @return MJKVI_Issue|null
	 */
	function get_first_issue(): ?MJKVI_Issue {
        return reset($this->issues);
    }

	/**
	 * Return the last issue.
	 *
	 * @return MJKVI_Issue|null
	 */
	function get_last_issue(): ?MJKVI_Issue {
        return end($this->issues);
    }

	/**
	 * Return the number of issues.
	 *
	 * @return int
	 */
	function n_issues(): int { return count($this->issues); }

	/**
	 * Return the fall issues.
	 *
	 * @return MJKVI_Issue[]
	 */
	function fall_issues(): array {
		return array_filter($this->issues, function (MJKVI_Issue $iss): bool {
			return $iss->in_fall();
		});
	}

	/**
	 * Return the winter issues.
	 *
	 * @return MJKVI_Issue[]
	 */
	function winter_issues(): array {
		return array_filter($this->issues, function (MJKVI_Issue $iss): bool {
			return $iss->in_winter();
		});
	}

	/**
	 * Return 3 thumbnails from this volume's issues.
	 * If three are not available, fill in gaps with default issue thumbs.
	 *
	 * @return array
	 */
	private function get_thumb_panoply(): array {
        $thumbs = [];

        // Get 3 issue thumbnails
        foreach ($this->issues as $issue) {
            $thumb = $issue->dynamic_thumbnail('medium');

            // At first, reject default / "not yet" thumbnails
            if (!in_array($thumb['src'], [MJKVI_DEF_THUMB, MJKVI_NYT_THUMB])) {
                $thumbs[] = $thumb;
            }

            // Stop as soon as 3 are present
            if (count($thumbs) > 2) break;
        }

        // Only now, if there weren't enough, can defaults be subbed in
        while (count($thumbs) < 3) {
                $thumbs[] = ['src' => MJKVI_DEF_THUMB, 'width' => 100, 'height' => 150];
        }

        return $thumbs;
    }

    // Create and return a composite thumbnail based on the issues' thumbnails

    // Arguments:
    // $thumbs = 3 issue thubmnails out of which to make the panoply
    // $compression = png compression level, 0-9
    // $base_w = base width of an issue thumbnail
    // $base_h = base height of an issue thumbnail
    // $margin = amount to trim on all sides of each thumbnail
    // $x_offset_pcnt = horizontal % of base width to shift when tiling
    // $y_offset_pcnt = vertical % of base height to shift when tiling

    // N.B. at the moment it is just assumed that default thumbnail w/h (100x150) will be used

	/**
	 * Create and return a composite thumbnail based on the issues' thumbnails
	 *
	 * Arguments:
	 *      $thumbs = 3 issue thubmnails out of which to make the panoply
	 *      $compression = png compression level, 0-9
	 *      $base_w = base width of an issue thumbnail
	 *      $base_h = base height of an issue thumbnail
	 *      $margin = amount to trim on all sides of each thumbnail
	 *      $x_offset_pcnt = horizontal % of base width to shift when tiling
	 *      $y_offset_pcnt = vertical % of base height to shift when tiling
	 *
	 * N.B. At the moment the default thumbnail w/h (100x150) is assumed.
	 *
	 * @param array $args
	 * @return string
	 */
	function make_thumbnail($args=[]): string {

        // Extract arguments
        $thumbs = (isset($args['thumbs'])) ? $args['thumbs'] : $this->get_thumb_panoply();
        $compression = (isset($args['compression'])) ? $args['compression'] : 0;
        $base_w = (isset($args['base_w'])) ? $args['base_w'] : 100;
        $base_h = (isset($args['base_h'])) ? $args['base_h'] : 150;
        $margin = (isset($args['margin'])) ? $args['margin'] : 0;
        $x_offset_pcnt = (isset($args['x_offset_pcnt'])) ? $args['x_offset_pcnt'] : 30;
        $y_offset_pcnt = (isset($args['y_offset_pcnt'])) ? $args['y_offset_pcnt'] : 15;

        // Correct base width and height if necessary
        if (isset($this->exceptional_sizes[$this->get_num()])) {
            $size = $this->exceptional_sizes[$this->get_num()];
            $base_w = $size[0];
            $base_h = $size[1];
        }

        // Derive exact numbers
        $n = count($thumbs);
        $x_offset = ($x_offset_pcnt / 100) * ($base_w - (2 * $margin));
        $y_offset = ($y_offset_pcnt / 100) * $base_h - (2 * $margin);
        $width = ($base_w - (2 * $margin)) + (($n-1) * ($x_offset));
        $height = ($base_h - (2 * $margin)) + (($n-1) * ($y_offset));

        // Prepare the transparent background image
        $thumb = imagecreatetruecolor($width, $height);
        imagesavealpha($thumb, true);
        $trans = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
        imagefill($thumb, 0, 0, $trans);

        // Begin counting up offsets
        $temp_x_offset = -$x_offset;
        $temp_y_offset = -$y_offset;

        // Shift offset each time
        for ($i = 0; $i < $n; $i++) {
            $temp_x_offset += $x_offset;
            $temp_y_offset += $y_offset;

            $src = $thumbs[$i]['src'];

            // Determine the type of the thumbnail image supplied
            $ext = pathinfo($src, PATHINFO_EXTENSION);

            $img = null;

            // The default thumbnail is a png file at present
            if ($ext == 'png') {
                $img = imagecreatefrompng($src);

            // The supplied thumb derived from a provider would be a jpg
            } elseif (($ext == 'jpg') or ($ext == 'jpeg')) {
                $img = imagecreatefromjpeg($src);
            }

            // Copy it onto the appropriate place on the base image
            imagecopy($thumb, $img, $temp_x_offset, $temp_y_offset,
                $margin, $margin, $base_w - $margin, $base_h - $margin);

            // Destroy the thumbnail image to free up memory
            imagedestroy($img);
        }

        // Convert to png
        ob_start();
        imagepng($thumb, NULL, $compression);
        $thumb = ob_get_clean();

        return $thumb;
    }

	/**
	 * Return a composite thumbnail based on this volume's issues.
	 * The format is ['src' => string, 'width' => int, 'height' => int]
	 * Issues' default thumbnail widths and heights are used.
	 *
	 * @return array
	 */
	function thumbnail(): array {
        // Get from cache
        $this->cache_thumb->write();
        $src = $this->cache_thumb->url();

        // Format in HTML-compliant terms
        return ['src' => $src, 'width' => null, 'height' => null];
    }

	/**
	 * Return this volume's associated website.
	 *
	 * @return MJKVI_ArchivalWebsite|null
	 */
	function get_website(): ?MJKVI_ArchivalWebsite {
        if (empty($this->website_pid)) return null;
        return MJKVIAPI::get_website_by_pid($this->website_pid);
    }

	/**
	 * Return the academic year of this volume.
	 *
	 * @return JKNAcademicYear
	 */
	function get_academic_year(): JKNAcademicYear {
        return MJKVIAPI::get_vol_academic_year($this);
    }
    
    /**
     * Return a formatted string of the academic year.
     */
    function format_academic_year(): string {
        return MJKVIAPI::format_vol_academic_year($this);
    }

	/**
	 * Return true iff this volume is on the main website.
	 *
	 * @return bool
	 */
	function is_on_main_website(): bool {
        if (empty($this->website_pid)) return false;
        return $this->get_website()->main;
    }

	/**
	 * Return a formatted <a> tag linking to this volume.
	 *
	 * @param bool $short_name
	 * @return string
	 */
	function format_a(bool $short_name=false): string {
        return sprintf('<a href="%s" title="%s">%s</a>',
            $this->get_url(), $this->get_name(), $this->get_name($short_name));
    }

	/**
	 * Return true iff any of this volume's issues have any posts.
	 *
	 * @return bool
	 */
	function has_posts(): bool {
        
        foreach($this->get_issues() as $issue) {
            if ($issue->has_posts()) {
                return true;
            }
        }
        
        return false;
    }

	/**
	 * Return all the posts from this volume's issues.
	 * If $flatten is false, return subarrays keyed by category.
	 * If $flatten is true, return a flat array of posts agnostic of category.
	 *
	 * @param bool $flatten
	 * @return array
	 */
    function get_posts(bool $flatten=true): array {
        
        // Short-circuit if there are none
        if (! $this->has_posts()) return [];
        
        // Otherwise string all issues' posts together
        $posts = [];        
        foreach($this->get_issues() as $issue) {
        	$issue_posts = $issue->get_posts($flatten);

        	// If flattening, just merge the posts in
        	if ($flatten) {
		        $posts = array_merge($posts, $issue_posts);

	        // Otherwise merge one category at a time
	        } else {
        		foreach($issue_posts as $cat => $cat_posts) {
        			if (!isset($posts[$cat])) $posts[$cat] = [];
        			$posts[$cat] = array_merge($posts[$cat], $cat_posts);
		        }
	        }
        }

        // If flattening, we can unique the whole array
        if ($flatten) {
        	$posts = array_unique($posts, SORT_REGULAR);

        // Otherwise unique one category at a time
        } else {
	        foreach($posts as $cat => $cat_posts) {
		        $posts[$cat] = array_unique($cat_posts, SORT_REGULAR);
	        }
        }
        
        return $posts;
    }

    /**
	 * Return the next volume, if there is one.
	 *
	 * @return MJKVI_Volume|null
	 */
	function next(): ?MJKVI_Volume {
		$ay = $this->get_academic_year();
		return MJKVIAPI::get_volume_by_academic_year($ay->next());
	}

	/**
	 * Return the previous volume, if there is one.
	 *
	 * @return MJKVI_Volume|null
	 */
	function previous(): ?MJKVI_Volume {
		$ay = $this->get_academic_year();
		return MJKVIAPI::get_volume_by_academic_year($ay->previous());
	}
}

/**
 * The Erindalian is a slightly altered version of a volume.
 * The Erindalian ran from 1968-1973. It's a precursor to The Medium and we
 * represent its archives, but we do distinguish between its volumes and ours.
 */
class MJKVI_Volume_Erindalian extends MJKVI_Volume {

	/**
	 * Return the given number, prefixed with 'e' for uniqueness.
	 *
	 * @param int $n
	 * @return string
	 */
	static function prefix_num(int $n): string {
        return sprintf('e%s', $n);
    }

	/**
	 * Return the number. It will be prefixed unless $strict is true.
	 *
	 * @param bool $strict
	 * @return string
	 */
	function get_num(bool $strict=false): string {
        $n = parent::get_num();
        return ($strict) ? $n : $this->prefix_num($n);        
    }

	/**
	 * Prefix name by "The Erindalian:".
	 *
	 * @param int $n
	 * @param bool $short
	 * @return string
	 */
	static function get_name_from_n(int $n, bool $short=false): string {
        $word = ($short) ? 'Erin' : 'The Erindalian';
        return sprintf('%s: %s', $word, parent::get_name_from_n($n, $short));
    }
}
