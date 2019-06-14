<?php

/**
 * Provides functionality for displaying the latest Disqus comments.
 */
final class MJKDisqusTools {
    use JKNCron_OneHook_Static;

	/*
	 * =========================================================================
	 * General
	 * =========================================================================
	 */
    
    /**
     * Reset the cache and cron.
     */
    static function reset(): void {
        $cache = JKNAPI::module()->get_cache();
        $cache->purge();
        $cache->write();
        
        self::clear_schedule();
        self::update_schedule();
    }


	/*
	 * =========================================================================
	 * Cron
	 * =========================================================================
	 */
    
    /**
     * Return the callback for the cron.
     *
     * @return callable
     */
    static function get_cron_callback(): callable {
        $cache = JKNAPI::module()->get_cache();
        return function() use ($cache) { $cache->write([], $overwrite=true); };
    }
    
    /**
     * Update the cron schedule.
     */
    static function update_schedule(): void {
        $rec = MJKDisqus_ACF::get(MJKDisqus_ACF::cache_sched);
        $base = MJKDisqus_ACF::get(MJKDisqus_ACF::sched_base);
        list($hour, $min) = explode(':', $base);
        self::schedule($overwrite=false, $rec, (int) $min, (int) $hour);
    }


	/*
	 * =========================================================================
	 * Content
	 * =========================================================================
	 */
    
    /**
     * Return the link to Disqus's comment API.
     *
     * @return string
     */
    static function url(): string {        
        $options = self::options();
        $shortname = trim($options['shortname']);
        $options_string = self::format_options($options);
        
        return sprintf('https://%s.disqus.com/recent_comments_widget.js?%s',
                $shortname, $options_string);
    }    
    
    /**
     * Return a <script> tag that fetches the JS sychronously.
     *
     * @return string
     */
    static function script(): string {
        return JKNJavascript::tag_src(self::url());
    }
    
    /**
     * Return the content provided by the Disqus API.
     * That returns some JS calling document.write. Use the argument passed.
     * 
     * N.B. document.write cannot be used asynchronously without an element id.
     * This is why we don't bother to output or enqueue any fetcher JS, but
     * just fetch it in PHP.
     *
     * @return string
     */
    static function fetch(): string {
        
        // Get the contents
        $url = self::url();
        $raw_js = file_get_contents($url);
        
        // Remove some crap
        $raw_js = str_replace('\\', '', $raw_js);
        $raw_js = str_replace('\n', '', $raw_js);
        $raw_js = str_replace('  ', ' ', $raw_js);

        // Identify the argument for document.write
        $re = "#write\(.*?'(.*)'.*?\)#sm";
        $matches = [];
        preg_match($re, $raw_js, $matches);
        $inner = $matches[1];
        
        return $inner;
    }
    
    /**
     * Return a 'please set shortname' message.
     *
     * @return string
     */
    static function unready(): string {
        
        $spage = JKNAPI::settings_page();
        $link = sprintf('<a href="%s" title="%s">here</a>',
                $spage->url(), $spage->page_title());
        
        return sprintf("<strong>Configure your Disqus settings %s.</strong>'"
                . ' You must set your Disqus shortname.', $link);        
    }
    
    /**
     * Return the content of the latest comments for the cache.
     *
     * @return string
     */
    static function cache(): string {
        $shortname = MJKDisqus_ACF::get(MJKDisqus_ACF::shortname);
        if (empty($shortname)) {
            return self::unready();
        } else {
            return self::fetch();
        }
    }
    
    /**
     * Return the content of the latest comments, using the cache or script.
     *
     * @return string
     */
    static function content(): string {
        $use_cache = MJKDisqus_ACF::get(MJKDisqus_ACF::use_cache);
        
        if ($use_cache) {
            $cache = JKNAPI::module()->get_cache();
            return $cache->write();
            
        } else {
            return self::script();
        }
    }
    
    /**
     * Return the array of options.
     *
     * @return array
     */
    static function options(): array {
        $options = [];
        
        $options['shortname'] = MJKDisqus_ACF::get(MJKDisqus_ACF::shortname);
        $options['num_items'] = MJKDisqus_ACF::get(MJKDisqus_ACF::num_items);
        $options['hide_avatars'] = MJKDisqus_ACF::get(MJKDisqus_ACF::hide_avatars);
        $options['hide_mods'] = MJKDisqus_ACF::get(MJKDisqus_ACF::hide_mods);
        $options['avatar_size'] = MJKDisqus_ACF::get(MJKDisqus_ACF::avatar_size);
        $options['excerpt_length'] = MJKDisqus_ACF::get(MJKDisqus_ACF::excerpt_length);
        
        return $options;
    }

	/**
	 * Return the given options formatted as GET parameters.
	 *
	 * @param array $options
	 * @return string
	 */
    static function format_options(array $options): string {
        
        // Remove irrelevant options
        unset($options['shortname']);
        
        // Add the 'random' option
        $options['rand'] = rand();
        
        // Concatenate the string
        $options_string = '';
        foreach($options as $key => $val) {
            $options_string .= sprintf('&%s=%s', $key, $val);
        }
        
        // Remove first ampersand
        return substr($options_string, 1);
    }
}
