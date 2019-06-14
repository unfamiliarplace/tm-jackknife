<?php

/**
 * Represents an archival website.
 */
class MJKVI_ArchivalWebsite {

	// Properties (TODO Make private with getters)
	public $pid;
	public $name;
	public $link;
	public $current;
	public $main;
	public $dt_from;
	public $dt_till;
	public $thumb_id;
	public $notes;

	/**
	 * Gather data from the post with the given post ID.
	 *
	 * @param string $pid
	 */
    function __construct(string $pid) {

        // Extract the basics
        $this->pid = $pid;
        
        $this->name = trim(MJKVI_ACF_AW::get(MJKVI_ACF_AW::name, $pid));
        
        $this->link = trim(MJKVI_ACF_AW::get(MJKVI_ACF_AW::link, $pid));
        $this->current = MJKVI_ACF_AW::get(MJKVI_ACF_AW::current, $pid);
        $this->main = MJKVI_ACF_AW::get(MJKVI_ACF_AW::main, $pid);

        // Extract dates
        $date_from = MJKVI_ACF_AW::get(MJKVI_ACF_AW::date_from, $pid);
        $date_till = MJKVI_ACF_AW::get(MJKVI_ACF_AW::date_till, $pid);
        
        // Convert to datetimes
        $this->dt_from = JKNTime::dt($date_from);
        $this->dt_till = ($this->current) ? null : JKNTime::dt($date_till);

        // Extract thumbnail ID and notes
        $this->thumb_id = MJKVI_ACF_AW::get(MJKVI_ACF_AW::thumb, $pid);
        $this->notes = MJKVI_ACF_AW::get(MJKVI_ACF_AW::notes, $pid);
    }

	/**
	 * Return a formatted string date (format for websites is Month Year).
	 *
	 * @return string
	 */
    function format_date(): string {
        $from_format = $this->dt_from->format('M Y');
        $to_format = ($this->current) ? 'Present' : $this->dt_till->format('M Y');		
        return sprintf('%s - %s', $from_format, $to_format);
    }

	/**
	 * Return a formatted name, tacking on a main site identifier if needed.
	 *
	 * @return string
	 */
    function format_name(): string {
        return sprintf('%s%s', $this->name, ($this->main) ? ' (Main site)' : '');
    }
}
