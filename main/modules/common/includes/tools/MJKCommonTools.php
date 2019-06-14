<?php

/**
 * Provides a collection of general functions.
 */
final class MJKCommonTools {

	/*
	 * =========================================================================
	 * Constants & statics
	 * =========================================================================
	 */

	static $using_cdn;

	const from_email = 'notifications@themedium.ca';

    const to_colour_option = [
    	'main'      => MJKCommon_ACF::colour_main,
	    'news'      => MJKCommon_ACF::colour_news,
	    'opinion'   => MJKCommon_ACF::colour_opinion,
	    'arts'      => MJKCommon_ACF::colour_arts,
	    'features'  => MJKCommon_ACF::colour_features,
	    'sports'    => MJKCommon_ACF::colour_sports,
	    'photos'    => MJKCommon_ACF::colour_photos,
	    'videos'    => MJKCommon_ACF::colour_photos // For now
    ];

	/*
	 * =========================================================================
	 * Colours
	 * =========================================================================
	 */

	/**
	 * Return the hex colour code for the given category slug.
	 *
	 * @param string|null $key A category slug or other key. Defaults to 'main'.
	 * @return string The hex colour code.
	 */
	static function colour(string $key=null): string {
		if (is_null($key)) $key = 'main';
		if (!isset(self::to_colour_option[$key])) return '';

		$opt = self::to_colour_option[$key];
		return MJKCommon_ACF::get($opt);
	}


	/*
	 * =========================================================================
	 * CDN (wrappers for JKNCDN)
	 * =========================================================================
	 */

	/**
	 * Load the CDN static if not already done.
	 */
	private static function load_using_cdn(): void {
		if (is_null(self::$using_cdn)) {
			self::$using_cdn = MJKCommon_ACF::get(MJKCommon_ACF::cdn_use);
		}
	}

	/**
	 * Wrapper for JKNCDN::images that checks for the MJKCommon CDN setting.
	 * Return the given HTML with image URLs replaced by CDN URLs.
	 *
	 * @param string $html
	 * @return string
	 */
	static function cdn_images(string $html): string {
		self::load_using_cdn();
		return self::$using_cdn ? JKNCDN::images($html) : $html;
	}

	/**
	 * Wrapper for JKNCDN::images that checks for the MJKCommon CDN setting.
	 * Rewrite a given URL to a CDN URL.
	 *
	 * @param string $url
	 * @return string
	 */
	static function cdn_url(string $url): string {
		self::load_using_cdn();
		return self::$using_cdn ? JKNCDN::url($url) : $url;
	}


	/*
	 * =========================================================================
	 * Time
	 * =========================================================================
	 */

    /**
     * Return an array of academic years since we have been in operation.
     *
     * @param int $from The earliest year, as a JKNAcademicYear format.
     * @param int|null $to The latest year, as a JKNAcademicYear format.
     * @return JKNAcademicYear[] The years between $from and $to.
     */
    static function academic_years(?int $from=null, int $to=null): array {

    	// If no from was supplied, use 1968
	    if (is_null($from)) {
		    $year_1968 = JKNAcademicYear::make_from_datestring('1968');
		    $from      = (int) $year_1968->date( 'Y' );
	    }

    	// If no end was supplied, use the current year
    	if (is_null($to)) {
		    $this_year = new JKNAcademicYear();
		    $to        = (int) $this_year->date( 'Y' );
	    }

	    // Make a JKNAcademicYear for each one and index by format
	    $ac_years = [];
	    foreach(range($from, $to) as $fall_year) {
    		$ac_year = JKNAcademicYear::make_from_year($fall_year);
    		$ac_years[$ac_year->format()] = $ac_year;
	    }

	    return $ac_years;
    }
}
