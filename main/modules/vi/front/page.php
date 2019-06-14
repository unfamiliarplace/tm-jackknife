<?php

/**
 * Represents a page template.
 */
abstract class MJKVI_Page {
    
    const cl_back = 'mjk_vi_back';

	/*
	 * =========================================================================
	 * Override
	 * =========================================================================
	 */

	/**
	 * Return the HTML title head suitable for this page.
	 *
	 * @return string
	 */
	abstract function get_page_title(): string;


	/*
	 * =========================================================================
	 * Do not override
	 * =========================================================================
	 */

	/**
	 * Return the breadcrumbs (Archive / Volume # / Issue #
	 *
	 * @param MJKVI_Volume $vol
	 * @param MJKVI_Issue|null $issue
	 * @return string
	 */
	protected function format_breadcrumbs(MJKVI_Volume $vol,
			MJKVI_Issue $issue=null): string {

        // Fixed archive page link
        $archive_part = sprintf('<a href="%s" title="Archive">Archive</a>',
                home_url('archive'));

        // No issue: archive is a link, volume is not a link
        if (empty($issue)) {                
            $part_2 = sprintf('%s > %s', $archive_part, $vol->get_name());

        // Yes issue: archive is a link, volume is a link, issue is not a link
        } else {                
            $vol_part = sprintf('<a href="/v/%1$s" title="%2$s">%2$s</a>',
                    $vol->get_num(), $vol->get_name());

            $part_2 = sprintf('%s > %s > %s', $archive_part, $vol_part,
                    $issue->get_name());
        }

        // Wrap in a paragraph tag and return
        return sprintf('<p class="%s">%s</p>', self::cl_back, $part_2);
    }

	/**
	 * Return the HTML wrappers for the page.
	 *
	 * @return string
	 */
	private function format_wrappers_open(): string {
            $html = '';

            $html .= '<div class="td-main-content-wrap td-main-page-wrap">';
            $html .= '<div class="td-container">';

            return $html;
    }

	/**
	 * Return the closing HTML wrappers for the page.
	 *
	 * @return string
	 */
	private function format_wrappers_close(): string {
        return str_repeat('</div>', 2);
    }

	/**
	 * Return the given <title> tag created by get_header with a more suitable
	 * one for this page.
	 *
	 * @param string $html
	 * @return string
	 */
	private function replace_title_tag(string $html): string {

        $title_tag = sprintf('<title>%s | %s</title>',
                $this->get_page_title(), get_bloginfo('name'));

	    preg_match('#<title>.*?</title>#', $html, $matches);

	    if (isset($matches[0])) {
	    	$content = str_replace($matches[0], $title_tag, $html);
	    } else {
	    	$content = str_replace('<head>', '<head>' . $title_tag, $html);
	    }

        return $content;
    }

	/**
	 * Return the formatted header html for the page.
	 *
	 * @return string
	 */
	function format_header(): string {

            // Add body classes
            add_filter('body_class', function($classes) {
                    $classes[] = 'page';
                    $classes[] = 'page-template-default';
                    return $classes;
            });

            // Capture the content from get_header, which is otherwise, echoed
            ob_start();
            get_header();
            $html = ob_get_contents();
            ob_end_clean();

            // This is so that we can replace the title tag
            $html = $this->replace_title_tag($html);
            $html .= $this->format_wrappers_open();

            return $html;
    }

	/**
	 * Return the formatted footer html for the page.
	 *
	 * @return string
	 */
	function format_footer(): string {

            // Capture the content from get_footer -- we don't alter it but for consistency with get_header
            ob_start();
            get_footer();
            $html = ob_get_contents();
            ob_end_clean();

            return $this->format_wrappers_close() . $html;
    }

	/**
	 * Cause the page to die if the volume requested by number does not exist.
	 *
	 * @param int $n_vol
	 * @return string
	 */
	function die_vol_fail(int $n_vol): string {
            $msg = sprintf('The requested volume (%s) does not exist.', $n_vol);
            $msg .= '<br><br>';
            $msg .= sprintf(
            	'Click <a href="%s" title="Front page">here</a> to return to' .
	            'the front page.', home_url());
            return $msg;
    }

	/**
	 * Cause the page to die if the issue requested by number does not exist.
	 *
	 * @param int $n_vol
	 * @param int $n_iss
	 * @return string
	 */
	function die_iss_fail(int $n_vol, int $n_iss): string {
            $msg = sprintf('The requested volume (%s) exists, but the' .
                           ' requested issue (%s) does not.', $n_vol, $n_iss);
            $msg .= '<br><br>';
            $msg .= sprintf(
            	'Click <a href="%1$s" title="%2$s">here</a> to' .
				'return to the %2$s archive.',
                home_url('v/' . $n_vol), MJKVI_Volume::get_name_from_n($n_vol));

            return $msg;
    }
}
