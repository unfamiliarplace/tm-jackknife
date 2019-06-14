<?php

/**
 * The archival website custom post type.
 */
class MJKVI_CPT_ArchivalWebsite extends JKNCPT {

	/*
	 * =========================================================================
	 * Identification
	 * =========================================================================
	 */
    
    /**
     * Return the name of this post type.
     *
     * @return string
     */
    static function name(): string { return 'Archival website'; }
    
    /**
     * Return an ID for this post type.
     *
     * @return string
     */
    static function id(): string { return 'website'; }
    
    /**
     * Return the description of this post type.
     *
     * @return string
     */
    static function description(): string {
        return 'A website owned or used by The Medium';        
    }
    
    /**
     * Return true: this post type uses a settings page (edit screen).
     *
     * @return bool
     */
    static function has_edit_screen(): bool { return true; }


	/*
	 * =========================================================================
	 * Setup
	 * =========================================================================
	 */
    
    /**
     * Return registration args with feed rewriting turned off.
     *
     * @return array
     */
    static function register_args(): array { return ['rewrite' => false]; }


	/*
	 * =========================================================================
	 * Edit screen
	 * =========================================================================
	 */
    
    /**
     * Return the column used for the default sort.
     *
     * @return string
     */
    static function default_sort_key(): string { return 'date_till'; }
    
    /**
     * Get the columns on the editing screen (checkbox is pre-handled).
     *
     * @return string[]
     */
    static function get_columns(): array {
        return [
            'title' => 'Title',
            self::qcol('link') => 'Link',
            self::qcol('date_from') => 'In use from',
            self::qcol('date_till') => 'In use till',
            self::qcol('main') => 'Main website'
        ];
    }
    
    /**
     * Fill the columns on the editing screen.
     *
     * @param string $col The column.
     * @param string $pid The post ID.
     */
    static function fill_columns(string $col, string $pid): void {
        
        if (get_post_type($pid) == self::qid()) {
            $aw = new MJKVI_ArchivalWebsite($pid);
        } else {
	        return;
        }
        
        
        switch ($col) {

            case self::qcol('link'):
                $link = $aw->link;
                echo sprintf('<a href="%1$s">%1$s</a>', $link);
                break;

            case self::qcol('date_from'):
                $dt_from = $aw->dt_from;
                echo $dt_from->format('M Y');
                break;

            // Write 'Present' if volume is still current
            case self::qcol('date_till'):
                $current = $aw->current;

                if (!$current) {
                    $dt_till = $aw->dt_till;
                    $format = $dt_till->format('M Y');

                } else {
                    $format = 'Present';
                }

                echo $format;
                break;

            case self::qcol('main'):
                $main = $aw->main;
                echo ($main) ? 'Yes' : '';
                break;
        }
    }
    
    /**
     * Return the sortable columns and corresponding key.
     *
     * @return string[]
     */
    static function get_sortable_columns(): array {
        return [
            'title' => 'Title',
            self::qcol('date_till') => self::qcol('date_till')];
    }


	/*
	 * =========================================================================
	 * Save
	 * =========================================================================
	 */
    
    /**
     * Switch save_sort_num for save_all_sort_nums.
     *
     * @param WP_Post $p
     */
    static function do_save_actions(WP_Post $p): void {
        self::save_title($p);
        self::save_all_sort_nums();
    }
    
    /**
     * Derive a title for this website.
     * TODO Obviously a better way is to make a whole website.
     *
     * @param WP_Post $p
     * @return string
     */
    static function derive_title(WP_Post $p): string {

	    // Hack one together from posted data
	    return trim(MJKVI_ACF_AW::get_posted(MJKVI_ACF_AW::name));
    }
    
    /**
     * Do not derive a sorting number for this website.
     *
     * @param WP_Post $p
     * @return int|null
     */
    static function derive_sort_num(WP_Post $p): ?int { return null; }
    
    /**
     * Derive sorting numbers for all websites.
     * They all have to be done at once because their order is relative.
     * TODO ONLY way to do this well for first-time saves is create the website.
     */
    static function save_all_sort_nums() {

        // Get all websites
        $wses = MJKVIAPI::archival_websites();
        $wses = MJKVIAPI::sort_websites($wses, $desc=false);
        
        // Number them
        $i = 0;
        foreach($wses as $ws) {
            $ws_pid = $ws->pid;
            
            // Add/update the meta key
            if (!add_post_meta($ws_pid, self::sort_key(), $i, $unique=true)) { 
                update_post_meta($ws_pid, self::sort_key(), $i);
            }
            
            $i++;
        }
    }
}
