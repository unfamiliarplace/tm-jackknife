<?php

/**
 * Provides the ability to add an update notice at the top of an article.
 */
class MJKEnrich_Updates {
    
    const cl_div = 'mjk-enrich-updates';
    const cl_date = 'mjk-enrich-updates-date';
    const cl_nature = 'mjk-enrich-updates-nature';
    
    /**
     * Add the ACF group, the content filter, and the Gen Tools page.
     */
    static function set_up(): void {
        
        // ACF
        require_once 'acf_api/registry.php';
        MJKEnrich_ACF_Updates::add_filters();
        
        // Page content
        add_filter('the_content', [__CLASS__, 'add_notice'], 10);
        
        // Gen tools
	    require_once 'gt_api/renderer.php';
	    MJKGTAPI::add_page([
		    'id' => 'updates',
		    'name' => 'Updated articles',
		    'source' => 'posts marked as updated on the post editing screen',
		    'renderer' => 'MJKEnrichUpdatesRenderer'
	    ]);
    }


	/*
	 * =========================================================================
	 * Shortcode
	 * =========================================================================
	 */

    /**
     * Filter the content, prepending the update notice.
     *
     * @param string $content
     * @return string
     */	
    static function add_notice(string $content): string {
        global $post;
        
        // Short-circuit if this is admin or not a regular post
        if (is_admin() || ($post->post_type != 'post')) return $content;
        
        // See if there are any updates
        $updates = self::gather_updates($post->ID);
        
        // If so, format, add to content, and insert style
        if (!empty($updates)) {
            $content = self::format_notice($updates) . $content;
            self::insert_style();
        }
        
        // This is a filter, so always return content
        return $content;
    }

    /**
     * Return the updates for this post ID.
     *
     * @param string $pid
     * @return MJKEnrich_Update[]
     */
    static function gather_updates(string $pid): array {
        $updates = [];
        
        // Go through each update
        if (MJKEnrich_ACF_Updates::have_rows(MJKEnrich_ACF_Updates::updates, $pid)) {
            while (MJKEnrich_ACF_Updates::have_rows(MJKEnrich_ACF_Updates::updates, $pid)) {
                the_row();
                
                // Grab subfields
                $date = MJKEnrich_ACF_Updates::sub(MJKEnrich_ACF_Updates::update_date);
                $nature = MJKEnrich_ACF_Updates::sub(MJKEnrich_ACF_Updates::update_nature);
                
                // Instantiate an update and index it
                $update = new MJKEnrich_Update($date, $nature);
                $updates[] = $update;
            }
        }
        
        // Sort by date made (most recent first) and return
        usort($updates,
	        function (MJKEnrich_Update $a, MJKEnrich_Update $b): int {
                return $b->dt() <=> $a->dt(); }
        );
        
        return $updates;
    }
    
    /**
     * Return the formatted HTML for an update notice.
     *
     * @param MJKEnrich_Update[] $updates
     * @return string
     */
    static function format_notice(array $updates): string {
        $html = '';
        
        $heading = '<h5>This article has been updated.</h5>';
        $updates_html = self::format_updates($updates);
        
        $html .= sprintf('<div class="%s">%s%s</div>',
                self::cl_div, $heading, $updates_html);
        
        return $html;
    }
    
    /**
     * Return formatted HTML for a list of updates.
     *
     * @param MJKEnrich_Update[] $updates
     * @return string
     */
    static function format_updates(array $updates): string {
        
        // Add each update as a <tr>
        $html_li_items = '';
        foreach($updates as $update) {
            $html_li_items .= sprintf('<tr>%s</tr>',
                    self::format_update($update));
        }        
        
        // Wrap all the <li>s in a <table>
        $html = sprintf('<table>%s</table>', $html_li_items);
        return $html;
    }
    
    /**
     * Return formatted HTML for a single update.
     *
     * @param MJKEnrich_Update $update
     * @return string
     */
    static function format_update(MJKEnrich_Update $update): string {
        $html = '';
        
        // Extract features
        $dt = $update->dt();
        $nature = $update->nature();
        
        // Add the date line
        $date_str = $dt->format('F j, Y \@ g a');
        $date_str = substr($date_str, 0, -1) . '.m.'; // a.m. p.m.
        
        // Make a couple of table columns
        $html .= sprintf('<td class="%s">%s</td><td class="%s">%s</td>',
                self::cl_date, $date_str, self::cl_nature, $nature);
        
        return $html;
    }

    /**
     * Insert the formatted CSS.
     */
    static function insert_style(): void {
        echo JKNCSS::tag('
            /* Update notices (post) */
            
            .'.self::cl_div.' h5 {
                font-size: 17.25px;
            }
            
            .'.self::cl_div.' table {
                margin-bottom: 0;
            }
            
            .'.self::cl_div.' {
                margin-bottom: 15px;
                padding-bottom: 15px;
                padding-top: 15px;
                border-top: 1px solid #555;
                border-bottom: 1px solid #555;
            }

            .'.self::cl_div.' td {
                border: none;
                vertical-align: top;
                margin-bottom: 10px;
                padding-bottom: 10px;
                padding-top: 10px;
            }
            
            .'.self::cl_nature.' p {
                margin-bottom: 0;
                margin-top: 20px;
                padding-left: 10px;
            }
            
            .'.self::cl_nature.' p:first-child {
                margin-top: 0;
            }

            .'.self::cl_div.' tr {
                border-bottom: 1px dashed #555;
            }
            
            .'.self::cl_date.' {
                font-size: 17px;
                width: 275px;
                font-style: italic;
                background: rgba(17, 65, 111, .1);
            }
            
            
            @media (max-width: 479px) {
                .'.self::cl_date.' {
                    width: 135px;
                }
            }

            .'.self::cl_div.' tr:last-child td {
                padding-bottom: 0;
                margin-bottom: 0;
            }

            .'.self::cl_div.' tr:last-child {  
                 border-bottom: 0;
            }
        ');
    }
}


/*
 * =========================================================================
 * Update
 * =========================================================================
 */

/**
 * Represents an update.
 */
class MJKEnrich_Update {
    
    var $dt;
    var $nature;
    
    /**
     * Initialize the DateTime and the nature.
     *
     * @param string $date A datestring.
     * @param string $nature A description of the update.
     */
    function __construct(string $date, string $nature) {
        $this->dt = JKNTime::dt($date);
        $this->nature = $nature;
    }
    
    /**
     * Return the DateTime at which this update was made.
     *
     * @return DateTime
     */
    function dt(): DateTime { return clone $this->dt; }
    
    /**
     * Return the nature of this update.
     *
     * @return string
     */
    function nature(): string { return $this->nature; }
}
