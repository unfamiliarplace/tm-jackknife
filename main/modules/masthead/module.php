<?php

/**
 * Allows you to define the roles of our organization and assign them to users.
 */
final class MJKMasthead extends JKNModule {
    
    const source = 'user info, specifically the "TM Jackknife Roles"'
		. ' section for each user profile';

    private $divisions = [];
    private $users = [];
    private $loaded_divisions = false;
    private $gtpage;
    
    /**
     * Return the ID.
     *
     * @return string
     */
    function id(): string { return 'masthead'; }
    
    /**
     * Return the name.
     *
     * @return string
     */
    function name(): string { return 'Masthead'; }
    
    /**
     * Return the description.
     *
     * @return string
     */
    function description(): string {
        return 'Provides masthead management and page.';        
    }

	/**
	 * Autoload all classes.
	 */
	function run_on_load(): void {
    	JKNClasses::autoload([
		    'MJKMH_Division'        => 'includes/division.php',
		    'MJKMH_Role'            => 'includes/role.php',
		    'MJKMH_HeldRole'        => 'includes/held_role.php',
		    'MJKMH_User'            => 'includes/user.php',
		    'MJKMH_Loader'          => 'includes/load.php',
		    'MJKMHAPI'              => 'includes/api.php',
		    'MJKMH_ACF_Roles'       => 'includes/acf_api/registry_roles.php',
		    'MJKMH_ACF_User'        => 'includes/acf_api/registry_user.php',
		    'MJKMH_Renderer'        => 'includes/gt_api/renderer.php'
	    ]);
	}
    
    /**
     * Perform the main actions (adding the Gen Tools page, adding the ACF
     * filters, enqueuing the Javascript on the user edit page).
     */
    function run_on_startup(): void {

	    // Add the current page to GT
	    $this->gtpage = MJKGTAPI::add_page([
		    'id' => 'masthead',
		    'name' => 'Masthead',
		    'source' => self::source,
		    'renderer' => 'MJKMH_Renderer',
		    'settings' => [JKNAPI::settings_page('roles')]
	    ]);

        // Add the ACF filters
	    MJKMH_ACF_Roles::add_filters();
	    MJKMH_ACF_User::add_filters();

	    // Add the dynamic field loading on the user ACF page
	    global $pagenow;
	    if (($pagenow === 'user-edit.php') || ($pagenow == 'user-new.php')) {
	    	MJKMH_ACF_User::enqueue_js();
	    }

    }

	/**
	 * Load the divisions.
	 */
	function run_on_init(): void {
		MJKMH_Loader::load_divisions();
		$this->loaded_divisions=true;
	}


	/*
	 * =========================================================================
	 * Division registration
	 * =========================================================================
	 */

	/**
     * Return the divisions.
     *
     * @return MJKMH_Division[]
     */
    function divisions(): array { return $this->divisions; }

    /**
     * Add a given division.
     *
     * @param MJKMH_Division $division
     */
    function add_division(MJKMH_Division $division): void {
    	$this->divisions[$division->name()] = $division;
    }

    /**
     * Get the division with the given name.
     *
     * @param string $name
     * @return MJKMH_Division|null
     */
    function division(string $name): ?MJKMH_Division {
    	return isset($this->divisions[$name]) ? $this->divisions[$name] : null;
    }

    /**
     * Return true iff the divisions have been loaded.
     *
     * @return bool
     */
    function loaded_divisions(): bool { return $this->loaded_divisions; }


	/*
	 * =========================================================================
	 * User registration
	 * =========================================================================
	 */

	/**
	 * Return the users.
	 *
	 * @return MJKMH_User[]
	 */
	function users(): array { return $this->users; }

	/**
	 * Add a given user.
	 *
	 * @param MJKMH_User $user
	 */
	function add_user(MJKMH_User $user): void {
		$this->users[$user->id()] = $user;
	}

	/**
	 * Get the user with the given ID.
	 *
	 * @param string $id The user's ID.
	 * @return MJKMH_User|null
	 */
	function user(string $id): ?MJKMH_User {
		return isset($this->users[$id]) ? $this->users[$id] : null;
	}

	/**
	 * Return true iff the user with the given ID has been loaded.
	 *
	 * @param string $id The user's ID.
	 * @return bool
	 */
	function loaded_user(string $id): bool { return isset($this->users[$id]); }

	/**
	 * Unload the given user by ID if it has been loaded.
	 *
	 * @param string $id The user's ID.
	 */
	function unload_user(string $id): void {
		if (isset($this->users[$id])) unset($this->users[$id]);
	}

	/*
	 * =========================================================================
	 * Gen Tools page
	 * =========================================================================
	 */

	/**
	 * Return the URL of the masthead current or archives page, if one is set.
	 *
	 * @param JKNAcademicYear $ay The year to root it to (now by default).
	 * @return string|null The URL.
	 */
	function gt_page_url(JKNAcademicYear $ay=null): ?string {
		if (is_null($ay)) $ay = MJKMHAPI::newest_year();

		$pid = $this->gtpage->pid();
		if (empty($pid)) return null;

		$option_key = MJKMH_Renderer::option_key();
		$link = get_permalink($pid);
		$opt = sprintf('%s=%s', $option_key, $ay->format());
		return sprintf('%s?%s', $link, $opt);
	}
}
