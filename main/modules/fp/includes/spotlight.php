<?php

/**
 * Represent a spotlight entry.
 */
class MJKFP_Spotlight {

    private $header;
	private $body;
	private $link;

	/**
	 * Store the header text, the body text, and the link (as a URL).
	 *
	 * @param string $header
	 * @param string$body
	 * @param string|null $link
	 */
    function __construct(string $header, string $body, string $link=null) {
        $this->header = $header;
        $this->body = $body;
        $this->link = $link;
    }

	/**
	 * Render this Spotlight.
	 *
	 * @return string
	 */
    function render(): string {
        $html = '';

        // Get header together
        $html .= sprintf('<span class="%s">%s</span>',
	        MJKFP_Shortcodes::cl_header, $this->header);

        // Get link together if needed
        $body = $this->body;
        if (!empty($this->link)) {
            $body = sprintf('<a class="%s" href="%s" title="%s">%s</a>',
	            MJKFP_Shortcodes::cl_link, $this->link, $this->body, $body);
        }

        // Get body together
        $html .= sprintf('<span class="%s">%s</span>',
	        MJKFP_Shortcodes::cl_body, $body);

        return $html;
    }
}
