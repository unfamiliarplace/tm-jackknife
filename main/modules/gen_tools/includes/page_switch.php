<?php

/**
 * A variant of a page that uses a select dropdown for a switch between
 * different content, loading only the content corresponding to the default
 * value of the select field.
 *
 * You can also load a particular option on page ready by using a get parameter:
 * https://themedium.ca/my-generated-page?option=some-value
 */
final class MJKGenToolsPageSwitch extends MJKGenToolsPage {
    
    /**
     * Construct as parent, but also hook ajax and (if on front) enqueue the JS.
     *
     * @param array $args The same arguments as for a regular page.
     */
    function __construct(array $args) {
        parent::__construct($args);

        // Ajax must be hooked regardless of whether it's admin
	    $this->renderer::hook_ajax();

        // Enqueue JS if we're not on an admin page
        if (!is_admin()) {
        	$this->renderer::enqueue_js($this->pid());
        }
    }

	/**
	 * Return the content of the page.
	 * This is overridden in order to allow preloading all options if desired.
	 *
	 * @return string
	 */
	protected final function get_content(): string {
		$preload_all = $this->get_preload_all();
		return $this->renderer::render(['preload_all' => $preload_all]);
	}

    /**
     * Return the value of the 'preload_all' option.
     *
     * @return bool True if all the options are to be preloaded.
     */
    function get_preload_all(): bool {
    	return (bool) $this->get_option('preload_all', false);
    }

    /**
     * Set the value of the 'preload_all' option.
     *
     * @param bool $value Whether to preload all or not.
     * @return bool Whether the option was successfully updated.
     */
    function update_preload_all(bool $value): bool {
    	return $this->update_option('preload_all', $value);
    }
}
