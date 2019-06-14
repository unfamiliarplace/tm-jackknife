<?php

/**
 * The volume custom post type.
 */
class MJKVI_CPT_Volume extends JKNCPT {

	/*
	 * =========================================================================
	 * Registration
	 * =========================================================================
	 */
    
    /**
     * Return the name of this post type.
     *
     * @return string
     */
    static function name(): string { return 'Volume'; }
    
    /**
     * Return the description of this post type.
     *
     * @return string
     */
    static function description(): string {
        return 'One volume (academic year) of The Medium';
    }
    
    /**
     * Return true: this post type uses a settings page (edit screen).
     *
     * @return bool
     */
    static function has_edit_screen(): bool { return true; }
    
    /**
     * Return true: this post type uses a metabox on the post edit screen.
     *
     * @return bool
     */
    static function has_metabox(): bool { return true; }
    
    /**
     * Return the registration args with feed rewriting turned on.
     *
     * @return array
     */
    static function register_args(): array {
        return ['rewrite' => ['feeds' => true]];
    }
    
    /**
     * Derive the sort number for this volume.
     * TODO A better way to do this would be to create the volume on the fly.
     *
     * @param WP_Post $p
     * @return int|null
     */
    static function derive_sort_num(WP_Post $p): ?int {

	    // Hack one together from posted data :(

	    $n = (int) MJKVI_ACF_VOL::get_posted(MJKVI_ACF_VOL::num);
	    $is_e = (bool) MJKVI_ACF_VOL::get_posted(MJKVI_ACF_VOL::is_erindalian);

	    if ($is_e) $n -= 6;
	    return $n;
    }
    
    /**
     * Derive a title for this volume.
     * TODO A better way to do this would be to create the volume on the fly.
     *
     * @param WP_Post $p
     * @return string|null
     */
    static function derive_title(WP_Post $p): ?string {

	    // Hack one together from posted data :(
	    $n = MJKVI_ACF_VOL::get_posted(MJKVI_ACF_VOL::num);
	    $is_e = (bool) MJKVI_ACF_VOL::get_posted(MJKVI_ACF_VOL::is_erindalian);

	    $title = sprintf('Volume %s', $n);
	    if ($is_e) $title = sprintf('The Erindalian: %s', $title);
	    return $title;
    }
    
    /**
     * Add the AJAX hook in addition to the usual hooks.
     *
     * @param int|null $spage_order The order of the settings page.
     */
    static function add_hooks(int $spage_order=null): void {
        parent::add_hooks($spage_order);
        
        add_action(sprintf('wp_ajax_%s', self::metabox_id()),
                [__CLASS__, 'ajax_clear_cache']);
    }


	/*
	 * =========================================================================
	 * Edit screen
	 * =========================================================================
	 */
    
    /**
     * Get the columns on the editing screen (checkbox is pre-handled).
     *
     * @return string[]
     */
    static function get_columns(): array {
        return [
            'title' => 'Title',
            self::qcol('year') => 'Year',
            self::qcol('link') => 'Link',
            self::qcol('n_issues') => '# of issues'
        ];
    }
    
    /**
     * Fill the columns on the editing screen.
     *
     * @param string $col The column name.
     * @param string $pid The post ID.
     */
    static function fill_columns(string $col, string $pid): void {
        
        if (get_post_type($pid) == self::qid()) {
            $v = MJKVIAPI::get_vol_by_pid($pid);
        } else {
        	return;
        }
        
        switch ($col) {

            case self::qcol('year'):
                $year = $v->format_academic_year();
                echo $year;
                break;

            case self::qcol('link'):
                $link = sprintf('<a href="%1$s%2$s">%2$s</a>',
                        home_url(), $v->get_url());
                echo $link;
                break;

            case self::qcol('issues'):
                $count = count($v->issues);
                echo $count;
                break;
        }
    }
    
    /**
     * Return the column used for the default sort.
     *
     * @retrrn string
     */
    static function default_sort_key(): string { return self::qcol('year'); }
    
    
    /**
     * Return the sortable columns and corresponding key.
     *
     * @return string[]
     */
    static function get_sortable_columns(): array {
        return [self::qcol('year') => self::qcol('year')];        
    }


	/*
	 * =========================================================================
	 * Trash
	 * =========================================================================
	 */
    
    /**
     * Clear the volume's cache when post is trashed.
     *
     * @param WP_Post $p
     */
    static function do_trash_actions(WP_Post $p): void {
    	self::clear_vol_cache($p);
    }
    
    /**
     * Clear the volume's cache when post is trashed
     *
     * @param WP_Post $p
     */
    static function clear_vol_cache(WP_Post $p) {
        $v = new MJKVI_Volume($p->ID);
        $v->cdir->purge();
    }


	/*
	 * =========================================================================
	 * Mtabox
	 * =========================================================================
	 */
    
    /**
     * Render the metabox.
     */
    static function render_metabox(): void {
        $html = self::render_metabox_html();
        $jq = self::render_metabox_jq();
        echo $html . $jq;
    }

    /**
     * Return the metabox HTML.
     *
     * @return string
     */
    private static function render_metabox_html(): string {
        global $post;

        // Bail if this post is not published.
		if ($post->post_status !== "published") {
			return 'This box will contain View and Clear Cache buttons once'
				. ' the volume is published.';
		}

        $vnum = MJKVI_ACF_VOL::get(MJKVI_ACF_VOL::num, $post->ID);
        
        // Prefix if Erindalian
        $is_erindalian = MJKVI_ACF_VOL::get(MJKVI_ACF_VOL::is_erindalian, $post->ID);
        if ($is_erindalian) $vnum = MJKVI_Volume_Erindalian::prefix_num($vnum);        

        $dis_notice =  '(These buttons are disabled till the volume is published.)';
        return sprintf('%s<br>%s<br>%s',
            self::render_metabox_view_button($vnum, $post),
            self::render_metabox_cache_button($vnum, $post),
            $dis_notice);
    }

    /**
     * Return metabox HTML for the view button.
     *
     * @param int $vnum The volume number.
     * @param WP_Post $post
     * @return string
     */
    static function render_metabox_view_button(int $vnum,
            WP_Post $post): string {
        
        $id = sprintf('%s_view', self::qid());
        $v = MJKVIAPI::get_vol_by_num($vnum);
        $onclick = sprintf("window.open('%s%s', '_blank')",
                home_url(), $v->get_url());

        // Disable if the volume has not yet been published
        $dis_text = (empty($post) or $post->post_status != 'publish') ? 'disabled="disabled"' : '';

        $button_html = sprintf('<input type="button" class="button %1$s" title="%2$s" '
            . 'name="%2$s" value="%2$s" id="%1$s" onclick="%3$s" %4$s />',
            $id, 'View volume', $onclick, $dis_text);

        $explanation = '<br><p>Click to see the volume page in a new tab.</p>';

        return $button_html . $explanation;
    }
    
    /**
     * Return metabox HTML for the cache button.
     *
     * @param int $vnum The volume number.
     * @param WP_Post $post
     * @return string
     */
    static function render_metabox_cache_button(int $vnum,
            WP_Post $post): string {
        
        $id = sprintf('%s_clear_cache', self::qid());
        $onclick = sprintf("mjk_vi_clear_cache('%s');", $vnum); // JQuery / AJAX

        // Disable if the volume has not yet been published
        $dis_text = (empty($post) or $post->post_status != 'publish') ? 'disabled="disabled"' : '';

        $button_html = sprintf('<input type="button" class="button %1$s" title="%2$s" '
            . 'name="%2$s" value="%2$s" id="%1$s" onclick="%3$s" %4$s/>',
            $id, 'Clear thumbnail & embed cache', $onclick, $dis_text);

        $explanation = '<br><p>Each volume stores the thumbnails and embed code of its issues. ';
        $explanation .= 'You can clear that cache and have it reload by using this tool. ';
        $explanation .= 'Note that Cloudflare and/or a CDN may also have to be purged to see the effect.</p>';

        return $button_html . $explanation;
    }


    /**
     * Render the JQuery for the clear cache button.
     * Disable and change the state of the buttons while working.
     * TODO Clean up and make separate .js file.
     *
     * @return string
     */
    static function render_metabox_jq(): string {
        $jq = "
            function mjk_vi_clear_cache(vnum) {

                jQuery('.button, .button-secondary').attr('disabled', true);                    
                jQuery('.button, .button-secondary').each(function() { this.style.setProperty('cursor', 'wait', 'important'); });
                
                jQuery('#mjk_vi_volume_clear_cache').attr('value', 'Working...');
                jQuery('#mjk_vi_volume_clear_cache').attr('name', 'Working...');
                jQuery('#mjk_vi_volume_clear_cache').attr('title', 'Working...');
                jQuery('#mjk_vi_volume_clear_cache').each(function() { this.style.setProperty('background-color', '#c2f4c1', 'important'); });    

                jQuery.post(
                    ajaxurl,
                    {
                        'action': 'mjk_vi_volume_metabox',
                        'vnum': vnum
                    },
                    function (response) {
                        alert(response);
                        jQuery('.button, .button-secondary').attr('disabled', false);                                              
                        jQuery('.button, .button-secondary').each(function() { this.style.setProperty('cursor', '', 'important'); });   

                        jQuery('#mjk_vi_volume_clear_cache').attr('value', 'Clear thumbnail & embed cache');
                        jQuery('#mjk_vi_volume_clear_cache').attr('name', 'Clear thumbnail & embed cache');
                        jQuery('#mjk_vi_volume_clear_cache').attr('title', 'Clear thumbnail & embed cache');
                        jQuery('#mjk_vi_volume_clear_cache').each(function() { this.style.setProperty('background-color', '#f7f7f7', 'important'); });
                    }
                );
            }
        ";

        // Wrap in JS tags
        return JKNJavascript::tag($jq);
    }
    
    /**
     * Clear the volume's cache on an AJAX request.
     */
    static function ajax_clear_cache() {

        // Extract volume number, get volume
        $vnum = $_POST['vnum'];
        $vol = MJKVIAPI::get_vol_by_num($vnum);

        // Clear the cache and die with appropriate message
        $vol->cdir->purge();
        wp_send_json("The cache for volume $vnum has been cleared.");
    }
}
