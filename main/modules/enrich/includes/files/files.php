<?php

/**
 * Adds files to the bottom of articles.
 */
class MJKEnrich_Files {
    
    const cl_div = 'mjk-enrich-files';
    
    /**
     * Register ACF and the content filter.
     */
    static function set_up() {
        
        // ACF
        require_once 'acf_api/registry.php';
        MJKEnrich_ACF_Files::add_filters();
        
        add_filter('the_content', [__CLASS__, 'add_files'], 0);
    }


	/*
	 * =========================================================================
	 * Shortcode
	 * =========================================================================
	 */
    
    /**
     * Filter the content to append any files.
     *
     * @param string $content
     * @return string
     */
    static function add_files(string $content): string {        
        global $post;

        // Short-circuit if this is admin or not a regular post
        if (is_admin() || ($post->post_type != 'post')) return $content;
        
        // See if there are any files
        $files = self::gather_files($post->ID);
        
        // If so, format, add to content, and insert style
        if (!empty($files)) {
            $content .= self::add_catalogue($files);
            self::insert_style();
        }
        
        // This is a filter, so always return content
        return $content;
    }
    
    
    /**
     * Return the files for a given post ID.
     *
     * @param string $pid
     * @return MJKEnrich_File[]
     */
    static function gather_files(string $pid): array {
        $files = [];
        
        // Go through each file
        if (MJKEnrich_ACF_Files::have_rows(MJKEnrich_ACF_Files::files, $pid)) {
            while (MJKEnrich_ACF_Files::have_rows(MJKEnrich_ACF_Files::files, $pid)) {
                the_row();
                
                $desc = MJKEnrich_ACF_Files::sub(MJKEnrich_ACF_Files::file_desc);
                $fid = MJKEnrich_ACF_Files::sub(MJKEnrich_ACF_Files::file_id);
                $file = new MJKEnrich_File($desc, $fid);
                
                $files[] = $file;
            }
        }
        
        return $files;
    }

    /**
     * Return the HTML for the file catalogue.
     *
     * @param MJKEnrich_File[] $files
     * @return string
     */
    static function add_catalogue(array $files): string {
        $html = '';
        
        $heading = '<h6>Files for this story:</h6>';
        $files_html = self::format_files($files);
        
        $html .= sprintf('<hr/><div class="%s">%s%s</div>',
                self::cl_div, $heading, $files_html);
        
        return $html;
    }
    
    /**
     * Return the HTML for the given files.
     *
     * @param MJKEnrich_File[] $files
     * @return string
     */
    static function format_files(array $files): string {
        
        // Add each file as a <li>
        $html_li_items = '';
        foreach($files as $file) {
            $html_li_items .= sprintf('<li>%s</li>', self::format_file($file));
        }        
        
        // Wrap all the <li>s in a <ul>
        $html = sprintf('<ul>%s</ul>', $html_li_items);
        return $html;
    }
    
    /**
     * Return the HTML for an individual file.
     *
     * @param MJKEnrich_File $f
     * @return string
     */	
    static function format_file(MJKEnrich_File $f): string {
    	$url = MJKCommonTools::cdn_url($f->url());
        $html = sprintf('<a href="%s" title="%s">%s</a> (.%s, %s)', $url,
                $f->html_desc(), $f->desc(), $f->ext(), $f->format_size());
        return $html;
    }

	/**
	 * Insert the formatted CSS.
	 */
    static function insert_style(): void {
        echo JKNCSS::tag('
            /* file style */
            
            .'.self::cl_div.' h6 {
                font-size: 17.25px;
            }
            
            .'.self::cl_div.' ul {
               margin-bottom: 5px;
            }

            .'.self::cl_div.' ul li a {
              color: #222;
            }

            .'.self::cl_div.' ul li {
              font-size: 15.25px;
              list-style: none;
            }

            .'.self::cl_div.' ul li::before { 
                content: "→ ";
            }
        ');
    }
}


/*
 * =========================================================================
 * File
 * =========================================================================
 */

/**
 * Represents a file.
 */
class MJKEnrich_File {
    
    var $desc;
    var $id;

    /**
     * Store the ID and description.
     *
     * @param string $desc
     * @param string $id
     */
    function __construct(string $desc, string $id) {
        $this->desc = $desc;
        $this->id = $id;
    }
    
    /**
     * Return the file's description.
     *
     * @return string
     */
    function desc(): string { return $this->desc; }
    
    /**
     * Return the file's description, escaped for HTML.
     *
     * @return string
     */
    function html_desc(): string { return htmlspecialchars($this->desc()); }
    
    /**
     * Return the file's ID (i.e. the file attachment ID in the WP database).
     *
     * @return string
     */
    function id(): string { return $this->id; }
    
    /**
     * Return the URL to this file.
     *
     * @return string
     */
    function url(): string { return wp_get_attachment_url($this->id()); }

	/**
	 * Return the type of this file.
	 *
	 * @return array
	 */
    function type(): array { return wp_check_filetype($this->url()); }
    
    /**
     * Return the extension of this file.
     *
     * @return string
     */
    function ext(): string { return $this->type()['ext']; }
    
    /**
     * Return the size (in bytes) of this file.
     *
     * @return int
     */
    function size(): int { return filesize(get_attached_file($this->id())); }
    
    /**
     * Return a formatted filesize.
     *
     * @return string
     */
    function format_size(): string {
        return JKNFormatting::filesize($this->size(), 1);
    }
}
