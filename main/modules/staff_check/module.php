<?php

/**
 * Staff Check provides a summary of post and user data on a yearly basis.
 */
final class MJKStaffCheck extends JKNModule {

	private $gtpage;

	/*
	 * =========================================================================
	 * Module registration
	 * =========================================================================
	 */
    
    /**
     * Return the ID.
     *
     * @return string
     */
    function id(): string { return 'staff_check'; }
    
    /**
     * Return the name.
     *
     * @return string
     */
    function name(): string { return 'Staff Check'; }
    
    /**
     * Return the description.
     *
     * @return string
     */
    function description(): string {
        return 'Provides a page with staff management information.<br>' .
			'An STMP plugin is highly recommended to ensure email functioning.';
    }


	/*
	 * =========================================================================
	 * Actions
	 * =========================================================================
	 */

	/**
	 * Autoload necessay files.
	 */
	function run_on_load(): void {
		JKNClasses::autoload([
			'MJKSC_API'             => 'includes/api.php',
			'MJKSC_ACF'             => 'includes/acf_api/registry.php',
			'MJKSC_StaffCheck'      => 'includes/staff_check.php',
			'MJKSC_Post'            => 'includes/post.php',
			'MJKSC_User'            => 'includes/user.php',
			'MJKSC_Section'         => 'includes/section.php',
			'MJKSC_Section_All'     => 'includes/section_all.php',
			'MJKSC_Section_Cat'     => 'includes/section_cat.php',
			'MJKSC_Section_Photo'   => 'includes/section_photo.php',
			'MJKSC_Renderer'        => 'includes/gt_api/renderer.php',
			'MJKSC_Email'           => 'includes/email.php',
			'MJKSC_Almost'          => 'includes/schedule_almost.php',
			'MJKSC_Update'          => 'includes/schedule_update.php',
			'MJKSC_Almost_Email'    => 'includes/email_almost.php',
			'MJKSC_Update_Email'    => 'includes/email_update.php',
		]);
	}
    
    /**
     * Add the Gen Tools page and the ACF filters.
     */
    function run_on_startup(): void {

    	MJKSC_ACF::add_filters();
	    $this->gtpage = MJKGTAPI::add_page([
		    'id' => 'staff_check',
		    'name' => 'Staff Check',
		    'source' => 'all users created and posts published on the website',
		    'renderer' => 'MJKSC_Renderer'
	    ]);
    }

	/**
	 * Start the crons.
	 */
    function run_on_init(): void {

    	$almost = (bool) MJKSC_ACF::get(MJKSC_ACF::almost);
    	$auto_add = (bool) MJKSC_ACF::get(MJKSC_ACF::auto_add);
	    $auto_remove = (bool) MJKSC_ACF::get(MJKSC_ACF::auto_remove);

	    if ($almost) {
	    	MJKSC_Almost::activate_cron();
	    	MJKSC_Almost::update_schedule();
        }

        if ($auto_add || $auto_remove) {
			MJKSC_Update::activate_cron();
			MJKSC_Update::update_schedule();
        }
    }

	/**
	 * Deactivate the cron jobs.
	 */
	function run_on_pause(): void {
		MJKSC_Update::clear_schedule();
		MJKSC_Update::clear_schedule();
	}

	/**
	 * Deactivate the cron jobs.
	 */
	function run_on_deactivate(): void {
		MJKSC_Update::clear_schedule();
		MJKSC_Update::clear_schedule();
	}

	/**
     * Return the Gen Tools page.
     *
     * @return MJKGenToolsPageSwitch
     */
    function gtpage(): ?MJKGenToolsPageSwitch { return $this->gtpage; }
}
