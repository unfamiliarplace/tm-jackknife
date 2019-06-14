<?php

/**
 * Interface for common volume, issue, and website tasks.
 */
class MJKVIAPI {

	/*
	 * =========================================================================
	 * Websites
	 * =========================================================================
	 */

	/**
	 * Return the websites sorted from most recent to least.
	 *
	 * @param MJKVI_ArchivalWebsite[] $websites
	 * @param bool $desc
	 * @return MJKVI_ArchivalWebsite[]
	 */
	static function sort_websites(array $websites, bool $desc=true): array {
        $sorted = [];

        // Extract various types
        $main = array_filter($websites, function($ws) { return $ws->main; } );
        $current = array_filter($websites, function($ws) { return $ws->current && !$ws->main; } );
        $past = array_filter($websites, function($ws) { return !$ws->current && !$ws->main; } );

        // Sort current by date used from
        uasort($current, function($a, $b) { return $a->dt_from <=> $b->dt_from; } );

        // Sort past by date used till, then date used from
        uasort($past, function($a, $b) {
            if ($a->dt_till != $b->dt_till) {
                return $a->dt_till <=> $b->dt_till;
            } else {
                return $a->dt_from <=> $b->dt_from;
            }
        } );

        // Add each past website
        foreach($past as $ws) {
            $sorted[$ws->name] = $ws;
        }

        // Then add each current website
        foreach($current as $ws) {
            $sorted[$ws->name] = $ws;
        }

        // Then main as last website
        $main = end($main);
        $sorted[$main->name] = $main;

        // Reverse to descending by default (most recent first)		
        return ($desc) ? array_reverse($sorted) : $sorted;
    }

	/**
	 * Return the website object given its name (id).
	 * If $websites is supplied, use that array; otherwise use all.
	 *
	 * @param string $name
	 * @param array|null $websites
	 * @return MJKVI_ArchivalWebsite|null
	 */
	static function get_website_by_name(string $name,
	        array $websites=null): ?MJKVI_ArchivalWebsite {

        if (is_null($websites)) $websites = self::archival_websites();
        foreach($websites as $website) {
            if ($website->name == $name) return $website;
        }

        return null;
    }

	/**
	 * Return the website object given its post id.
	 * If $websites is supplied, use that array; otherwise use all.
	 *
	 * @param string $pid
	 * @param MJKVI_ArchivalWebsite[]|null $websites
	 * @return MJKVI_ArchivalWebsite
	 */
	static function get_website_by_pid(string $pid,
	        array $websites=null): ?MJKVI_ArchivalWebsite {

        if (is_null($websites)) $websites = self::archival_websites();
        foreach($websites as $website) {
            if ($website->pid == $pid) return $website;
        }

        return null;
    }

	/**
	 * Return the main archival website.
	 * If $websites is supplied, use that array; otherwise use all.
	 *
	 * @param MJKVI_ArchivalWebsite[]|null $websites
	 * @return MJKVI_ArchivalWebsite
	 */
	static function get_main_website(array $websites=null):
            ?MJKVI_ArchivalWebsite {

        if (is_null($websites)) $websites = self::archival_websites();
        foreach($websites as $website) {
            if ($website->main) return $website;
        }

        return null;
    }


	/*
	 * =========================================================================
	 * Academic years
	 * =========================================================================
	 */

	/**
	 * Return the academic year given a volume number.
	 *
	 * @param int|string $vn
	 * @return JKNAcademicYear
	 */
	static function get_vn_academic_year($vn): JKNAcademicYear {
        
        // First volume is 1974/75
        $year = ((int) $vn) + 1973;
        $ay = JKNAcademicYear::make_from_year($year);
        return $ay;
    }

	/**
	 * Return the formatted academic year given a volume number.
	 *
	 * @param int|string $vn
	 * @return string
	 */
	static function format_vn_academic_year($vn): string {
        return self::get_vn_academic_year($vn)->format();
    }

	/**
	 * Return the academic year given a volume.
	 *
	 * @param MJKVI_Volume $vol
	 * @return JKNAcademicYear
	 */
	static function get_vol_academic_year(MJKVI_Volume $vol): JKNAcademicYear {
        
        // Get strict year (to prevent Erindalian prefix)
        $n = $vol->get_num($strict=true);
        
        // There were 6 years of the Erindalian
        if ($vol->is_erindalian) $n -= 6;
        
        // Have math done
        return self::get_vn_academic_year($n);
    }

	/**
	 * Return the formatted academic year given a volume.
	 *
	 * @param MJKVI_Volume $vol
	 * @return string
	 */
	static function format_vol_academic_year(MJKVI_Volume $vol): string {
        return self::get_vol_academic_year($vol)->format();
    }


	/*
	 * =========================================================================
	 * Specialized volume and issue getters
	 * =========================================================================
	 */

	/**
	 * Get a volume given a number.
	 *
	 * @param int|string $num
	 * @return MJKVI_Volume|null
	 */
	static function get_vol_by_num($num): ?MJKVI_Volume {
        $vols = self::volumes();
        foreach($vols as $vol) {
            if ($vol->get_num() == $num) return $vol;
        }

        return null;
    }

	/**
	 * Get a volume given its post ID.
	 *
	 * @param string $pid
	 * @return MJKVI_Volume|null
	 */
	static function get_vol_by_pid(string $pid): ?MJKVI_Volume {
        $vols = self::volumes();
        foreach($vols as $vol) {
            if ($vol->pid == $pid) return $vol;
        }

        return null;
    }

	/**
	 * Get a volume given a DateTime.
	 *
	 * @param DateTime $dt
	 * @return MJKVI_Volume|null
	 */
	static function get_volume_by_dt(DateTime $dt): ?MJKVI_Volume {
        $ay = new JKNAcademicYear($dt);
        return self::get_volume_by_academic_year($ay);
    }

	/**
	 * Get a volume given an academic year.
	 *
	 * @param JKNAcademicYear $ay
	 * @return MJKVI_Volume|null
	 */
	static function get_volume_by_academic_year(JKNAcademicYear $ay):
                ?MJKVI_Volume {
        
        $vols = self::volumes();
        $format = $ay->format();
        if (in_array($format, array_keys($vols))) return $vols[$format];
        return null;
    }

	/**
	 * Return an array [volume, issue] for a given post or post ID.
	 *
	 * @param WP_Post|string $p_or_pid
	 * @return array
	 */
    static function get_post_vi($p_or_pid): array {
    	$pid = JKNPosts::to_pid($p_or_pid);

        $dt = JKNTime::dt_pid($pid);
        $vol = self::get_volume_by_dt($dt);
        $iss = null;

        // Get an issue iff volume is found and the post doesn't skip issue
        if (!empty($vol)) {
            $iss = $vol->get_issue_by_dt($dt);
            $iss = $iss && !empty($iss->filter_no_iss_pids([$pid])) ? $iss : null;
        }

        return [$vol, $iss];
    }

	/**
	 * Return just the volume for a given post or post ID.
	 *
	 * @param WP_Post|string $p_or_pid
	 * @return MJKVI_Volume|null
	 */
	static function get_post_volume($p_or_pid): ?MJKVI_Volume {
		return self::get_post_vi($p_or_pid)[0];
	}

	/**
	 * Return just the issue for a given post ID.
	 *
	 * @param WP_Post|string $p_or_pid
	 * @return MJKVI_Issue
	 */
	static function get_post_issue($p_or_pid): MJKVI_Issue {
		return self::get_post_vi($p_or_pid)[1];
	}

	/**
	 * Return a URL for a given post or post ID's volume and issue.
	 *
	 * @param WP_Post|string $p_or_pid
	 * @return string
	 */
	static function get_post_vi_url($p_or_pid): string {
		list($vol, $iss) = self::get_post_vi($p_or_pid);

		if ($iss) {
			return $iss->get_url();
		} else {
			return $vol->get_url();
		}
	}

	/**
	 * Return a formatted title for a given post or post ID's volume and issue.
	 *
	 * @param WP_Post|string$p_or_pid
	 * @param bool $short_vol_title
	 * @param bool $cap_iss_title
	 * @return string
	 */
    static function get_post_vi_title($p_or_pid, bool $short_vol_title=false,
            bool $cap_iss_title=true): string {
        
        list($vol, $iss) = self::get_post_vi($p_or_pid);
        
        if ($iss) {
            return $iss->format_vol_iss_title($short_vol_title, $cap_iss_title);
        } else {
            return $vol->get_name($short_vol_title);
        }
    }


	/*
	 * =========================================================================
	 * Generel getters.
	 * =========================================================================
	 */

	/**
	 * Return all volumes.
	 *
	 * @return MJKVI_Volume[]
	 */
	static function volumes(): array {
        return MJKVI_Loader::load_volumes();
    }

	/**
	 * Return all archival websites.
	 *
	 * @return MJKVI_ArchivalWebsite[]
	 */
	static function archival_websites(): array {
        return MJKVI_Loader::load_archival_websites();
    }

	/**
	 * Return the current volume.
	 *
	 * @return MJKVI_Volume
	 */
	static function current_volume(): MJKVI_Volume {
    	$ay = new JKNAcademicYear();
    	$vol = self::get_volume_by_academic_year($ay);
    	while (is_null($vol) && $ay->format() != '1968/69') {
    		$ay = $ay->previous();
		    $vol = self::get_volume_by_academic_year($ay);
	    }

        return $vol;
    }

	/**
	 * Return either the last (moving backward) or the first (moving forward)
	 * issue that meets the criteria checked by checker_function.
	 * If none are found, return null.
	 *
	 * @param callable $checker_function
	 * @param MJKVI_Volume $v
	 * @param bool $backward
	 * @return MJKVI_Issue|null
	 */
	static function suitable_issue(callable $checker_function, MJKVI_Volume $v,
	        bool $backward=true): ?MJKVI_Issue {

        $issue = null;
        while (is_null($issue) && !(is_null($v))) {

	        // Get a copy of volume's issues, newest to oldest
	        $issues = $v->issues;
	        $issues = ($backward) ? array_reverse($issues) : $issues;

	        // Check each issue
	        foreach ($issues as $iss) {
		        if (! empty($iss) && ! empty($checker_function($iss))) {
			        return $iss;
		        }
	        }

	        // If we still don't have an issue, back up / go forward a volume
	        $v = $backward ? $v->previous() : $v->next();
        }

        return null;
    }

	/**
	 * Return the current issue.
	 * If $content_required, return the most recent issue that has content.
	 *
	 * @param bool $content_required
	 * @return MJKVI_Issue|null
	 */
	static function current_issue(bool $content_required=true): ?MJKVI_Issue {
        $checker = function (MJKVI_Issue $iss) use ($content_required) {
            return (! $content_required) or $iss->has_content();
        };

        return self::suitable_issue($checker, self::current_volume());
    }

	/**
	 * Return the current print edition.
	 *
	 * @return MJKVI_Issue|null
	 */
	static function current_print_edition(): ?MJKVI_Issue {
        $checker = function(MJKVI_Issue$iss) {
            return (($iss->has_content()) && ($iss->has_print_edition()));
        };

        return self::suitable_issue($checker, self::current_volume());
    }

	/**
	 * Return an array of the past volumes (exclusive of current).
	 * Can supply a current if you want past relative to that.
	 * $include_erindalian: include the 6 volumes of The Erindalian, 1968-73.
	 *
	 * @param MJKVI_Volume|null $current
	 * @param bool $include_erindalian
	 * @return MJKVI_Volume[]
	 */
	static function past_volumes(MJKVI_Volume $current=null,
	        bool $include_erindalian=false): array {

        $vols = self::volumes();
        $current = (!empty($current)) ? $current : self::current_volume();

        $past = [];
        foreach($vols as $vol) {
            if ($vol != $current &&
                    ($include_erindalian  || !$vol->is_erindalian)) {
                $past[$vol->format_academic_year()] = $vol;
            }            
        }

        return $past;
    }

	/**
	 * eturn an array of the volumes of the Erindalian.
	 *
	 * @return MJKVI_Volume_Erindalian[]
	 */
	static function erindalian_volumes(): array {
        $vols = self::volumes();
        
        $past = [];
        foreach($vols as $vol) {
            if ($vol->is_erindalian) $past[$vol->format_academic_year()] = $vol;
        }
        
        return $past;
    }

	/**
	 * Return an array of the past issues (exclusive of current).
	 * This is within the same volume as the current issue.
	 * Can supply a current if you want past relative to that.
	 *
	 * @param MJKVI_Issue|null $current
	 * @return MJKVI_Issue[]
	 */
	static function past_issues(MJKVI_Issue $current=null): array {
        $current = (!empty($current)) ? $current : self::current_issue();

        $vol = $current->vol;

        $past = [];
        foreach($vol->issues as $issue) {
            if ($issue == $current) break;
            $past[] = $issue;
        }

        return $past;
    }

	/**
	 * Return an array of volumes on the main website.
	 *
	 * @return MJKVI_Volume[]
	 */
	static function volumes_on_main_website(): array {
        $volumes = self::volumes();
        
        $on_main = [];
        foreach($volumes as $volume) {
            if ($volume->is_on_main_website()) {
                $on_main[] = $volume;
            }
        }
        
        return $on_main;
    }

	/**
	 * Return the ssue associated with the given DateTime,
	 * i.e. the one during whose week the DateTime falls.
	 *
	 * @param DateTime $dt
	 * @param bool $allow_summer
	 * @return MJKVI_Issue|null
	 */
	static function get_issue_by_dt(DateTime $dt,
	        bool $allow_summer=true): ?MJKVI_Issue {

        $vol = self::get_volume_by_dt($dt);
        if (!empty($vol)) {
            return $vol->get_issue_by_dt($dt, $allow_summer);
        }

        return null;
    }

	/**
	 * Return the next volume (or the one $n after it).
	 *
	 * @param MJKVI_Volume $vol
	 * @param int $n
	 * @return MJKVI_Volume|null
	 */
	static function next_volume(MJKVI_Volume $vol, int $n=1): ?MJKVI_Volume {
        return JKNArrays::following($vol, self::volumes(), $n);
    }

	/**
	 * Return the previous volume (or the one $n before it).
	 *
	 * @param MJKVI_Volume $vol
	 * @param int $n
	 * @return MJKVI_Volume|null
	 */
	static function previous_volume(MJKVI_Volume $vol, int $n=1): ?MJKVI_Volume {
        return self::next_volume($vol, -$n);
    }

	/**
	 * Return the next issue (or the one $n after it).
	 * If $cross_volume is true, skip to the next volume to find one
	 *
	 * @param MJKVI_Issue $iss
	 * @param bool $cross_volume
	 * @param int $n
	 * @return MJKVI_Issue|null
	 */
	static function next_issue(MJKVI_Issue $iss, bool $cross_volume=false,
	        int $n=1): ?MJKVI_Issue {
        
        // Try to get the next issue
        $vol = $iss->vol;
        $issues = array_values($vol->get_issues());
        $next = JKNArrays::following($iss, $issues, $n);
        
        // If it doesn't exist, and we're allowed to cross volumes...
        while (empty($next) && $cross_volume) {
            
            // Get the next volume
            $vol = self::next_volume($vol);
            
            // If there was one, append its issues and try again
            if ($vol) {
                $issues = array_merge($issues, array_values($vol->get_issues()));
                $next = JKNArrays::following($iss, $issues, $n);
            
            // Otherwise we've run out of options
            } else {
                return null;
            }
        }
        
        // Return whatever we ended up with, be it an issue or null
        return $next;
    }

	/**
	 * Return the previous issue (or the one $n before it).
	 *
	 * @param MJKVI_Issue $iss
	 * @param bool $cross_volume
	 * @param int $n
	 * @return MJKVI_Issue|null
	 */
	static function previous_issue(MJKVI_Issue $iss, bool $cross_volume=false,
	        int $n=1): ?MJKVI_Issue {
        
        // Try to get the previous issue
        $vol = $iss->vol;
        $issues = array_values($vol->get_issues());
        $prev = JKNArrays::following($iss, $issues, $n);
        
        // If it doesn't exist, and we're allowed to cross volumes...
        while (empty($prev) && $cross_volume) {
            
            // Get the previous volume
            $vol = self::previous_volume($vol);
            
            // If there wasone, prepend its issues and try again
            if ($vol) {
                $issues = array_merge(array_values($vol->get_issues()), $issues);
                $prev = JKNArrays::following($iss, $issues, $n);
            
            // Otherwise we've run out of options
            } else {
                return null;
            }
        }
        
        // Return whatever we ended up with, be it an issue or null
        return $prev;
    }
}
