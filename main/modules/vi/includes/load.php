<?php

/**
 * Load the volumes and archival websites and add the query vars to allow the
 * custom templates to be used.
 */
class MJKVI_Loader {
	
    /**
     * Add the various WP hooks for the various behaviours.
     */
    static function add_hooks(): void {

        // Query vars and rewrite rules
        add_filter('query_vars', [__CLASS__, 'register_query_vars']);

        // Redirecting the template when appropriate query var is given
        add_action('template_redirect', [__CLASS__, 'add_template_filters'] );

        // Register widget actions
        add_action('widgets_init', function() {
            register_widget('MJKVI_ArchiveWidget');
        });
            
        add_action('widgets_init', function() {
            register_widget('MJKVI_CurrentIssueThumbnailWidget');
        });
    }


	/*
	 * =========================================================================
	 * Templates, rewrites, query
	 * =========================================================================
	 */

    /**
     * Add rewrite tags and rules so Wordpress can interpret the URL.
     */
    static function add_rewrite_rules(): void {

        // Tags are for query vars
        add_rewrite_tag('%' . MJKVI_VOL_QVAR . '%', '([^&]+)');
        add_rewrite_tag('%' . MJKVI_ISS_QVAR . '%', '([^&]+)');

        add_rewrite_rule('^v/print/?$',
            sprintf('index.php?%s=print', MJKVI_VOL_QVAR),
            'top');

        add_rewrite_rule('^v/current/?$',
            sprintf('index.php?%s=current', MJKVI_VOL_QVAR),
            'top');

        // Rules are for general interpretation
        add_rewrite_rule(
            '^v/(e?\d+)/([-a-z0-9]+?)/?$',
            sprintf('index.php?%s=$matches[1]&%s=$matches[2]',
                MJKVI_VOL_QVAR, MJKVI_ISS_QVAR),
            'top'
        );

        add_rewrite_rule(
            '^v/(e?\d+)/?$',
            sprintf('index.php?%s=$matches[1]', MJKVI_VOL_QVAR),
            'top'
        );
    }

	/**
	 * Return the query vars with ours added.
	 *
	 * @param array $vars
	 * @return array
	 */
    static function register_query_vars(array $vars): array {
        $vars[] = MJKVI_VOL_QVAR;
        $vars[] = MJKVI_ISS_QVAR;
        return $vars;
    }

    /**
     * Add filters when WP is deciding which template to load.
     */
    static function add_template_filters(): void {
        global $wp_query;

        // If there is both volume and issue, direct to issue template
        if (isset($wp_query->query_vars[MJKVI_VOL_QVAR]) &&
            isset($wp_query->query_vars[MJKVI_ISS_QVAR])) {
            add_filter('template_include', function() {
                return MJKVI_TEMPLATES . '/issue.php';
            });

        // If there is only volume, redirect to volume
        } elseif (isset($wp_query->query_vars[MJKVI_VOL_QVAR])) {

            // Redirect to current if the volume query var is current
            if ($wp_query->query_vars[MJKVI_VOL_QVAR] == 'current') {
                wp_safe_redirect(sprintf('%s',
                    MJKVIAPI::current_issue()->get_url()));

            // Redirect to latest print if the volume query var is print
            } elseif ($wp_query->query_vars[MJKVI_VOL_QVAR] == 'print') {
                wp_safe_redirect(sprintf('%s',
                    MJKVIAPI::current_print_edition()->get_url()));

            // If not current, load the volume given
            } else {			
                add_filter('template_include', function() {
                    return MJKVI_TEMPLATES . '/volume.php';
                });
            }
        }
    }


	/*
	 * =========================================================================
	 * Volume and website loading
	 * =========================================================================
	 */

	/**
	 * Load the volumes into the module and return them.
	 *
	 * @return MJKVI_Volume[]
	 */
	static function load_volumes(): array {
		$MJKVI = JKNAPI::module();

		// Get volume custom post types
		if (empty($MJKVI->vols_loaded)) {
			$posts = MJKVI_CPT_Volume::posts();

			// Turn them into volumes and key them by academic year
			foreach($posts as $p) {
				$pid = $p->ID;

				// Determine whether this is a volume of The Erindalian or not
				$is_erindalian = MJKVI_ACF_VOL::get(MJKVI_ACF_VOL::is_erindalian, $pid);
				$vol = ($is_erindalian) ? new MJKVI_Volume_Erindalian($pid) : new MJKVI_Volume($pid);

				// Determine the year and add it to the main vol table indexed by that
				$MJKVI->vols[$vol->format_academic_year()] = $vol;
			}

			// Sort them by academic year, set loaded
			ksort($MJKVI->vols);
			$MJKVI->vols_loaded = true;
		}

		return $MJKVI->vols;
	}

	/**
	 * Load the archival websites into the module and return them.
	 *
	 * @return MJKVI_ArchivalWebsite[]
	 */
	static function load_archival_websites(): array {
		$MJKVI = JKNAPI::module();

		if (empty($MJKVI->websites_loaded)) {

			// Get website custom post types
			$posts = MJKVI_CPT_ArchivalWebsite::posts();

			// Turn them into websites, key them by name
			$websites = [];
			foreach($posts as $p) {
				$ws = new MJKVI_ArchivalWebsite($p->ID);
				if (!empty($ws->name)) $websites[$ws->name] = $ws;
			}

			// Sorting websites is a pain so do it via API
			$websites = MJKVIAPI::sort_websites($websites);
			$MJKVI->websites = $websites;

			// Lock and load
			if (!empty($websites)) $MJKVI->websites_loaded = true;
		}

		return $MJKVI->websites;
	}
}
