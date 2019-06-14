<?php

/**
 * The Gen Tools Scheduler is a form of WP settings page that presents all the
 * registered pages along with the ability to select which WP page they replace,
 * and scheduling info to automatically generate them.
 */
final class MJKGTScheduler extends JKNSettingsPageWP {

    // For IDing elements
    const id_base = 'mjk_gt_ajax';
    
    // Dropdown defaults
    const default_pid = '';
    const default_rec = '';
    const default_hour = 0;
    const default_day = 1;
    
    // CSS
    const id_main = 'mjk-gt-scheduler';
    const cl_intro = 'mjk-gt-scheduler-intro';
    const cl_dropdown = 'mjk-gt-scheduler-dropdown';
    const cl_p_report_table = 'mjk-gt-p-report-table';
    const cl_p_report = 'mjk-gt-p-report';
    const cl_a_spage = 'mjk-gt-spage';    
    const cl_lg_report_true = 'mjk-gt-lg-report-true';
    const cl_lg_report_false = 'mjk-gt-lg-report-false';
    const cl_preload_note = 'mjk-gt-preload-note';


	/*
	 * =========================================================================
	 * Set up
	 * =========================================================================
	 */
    
    /**
     * Return an array of the option names to be registered/unregistered.
     *
     * @return string[]
     */
    function option_names(): array {
        $option_names = [];

        foreach (MJKGTAPI::pages() as $page) {
            $option_names[] = $page->qualify_option('pid');
            $option_names[] = $page->qualify_option('last_gen');
            $option_names[] = $page->qualify_option('last_gen_success');
        }
        
        return $option_names;
    }

    /**
     * Set up the settings page as the parent does, but also hook AJAX and
     * enqueue the JS for the scheduling functions.+
     *
     * @param JKNModule $module The module to attach this settings page to.
     */
    function __construct(JKNModule $module) {
        parent::__construct($module);
        add_action('admin_init', [$this, 'prepare_ajax']);
    }
    
    /**
     * Return null to avoid setting a separate name than the page title.
     *
     * @return string|null
     */
    function name(): ?string { return null; }

	/**
	 * Return the page title.
     *
     * @return string
	 */
	function page_title(): string {
		return sprintf('%s — %s: Scheduler', $this->module->space()->name(),
			$this->module->name());
	}
    
    /**
     * Prepare the AJAX portion if this is an admin page
     */
    function prepare_ajax(): void {
        if (is_admin()) {
            JKNTime::reset_timezone();

            // Register AJAX and enqueue JS
            MJKGTSchedulerAJAX::hook_ajax();
            MJKGTSchedulerAJAX::enqueue_js($this->slug());
        }
    }
    
    /**
     * (Dummy) We show all our settings without WP's interface (most of them
     * are never stored in the database, only their cron consequences are).
     */
    function add_sections_and_fields(): void {}


	/*
	 * =========================================================================
	 * Rendering
	 * =========================================================================
	 */
    
    /**
     * Output the content of the page, as well as its CSS.
     * N.B. This does mean we avoid the normal parent render entirely.
     */
    public function render(): void {
        $pages = MJKGTAPI::pages();
        $wp_cron = get_option('cron');

        $this->render_css();

        printf('<div class="wrap">');
        $this->output_intro();
        printf('<div id="%s">', self::id_main);
        $this->output_sections($pages, $wp_cron);
        printf('</div></div>');
    }

    /**
     * Output the HTML for the introduction at the top of the page.
     */
    protected function output_intro(): void {
        ?>
        <h2><?php echo $this->page_title(); ?></h2>
        <div class="<?php echo self::cl_intro; ?>"><p><ol>
            <li>For each generated page, pick an existing WordPress page to be
            overwritten with the generated content, and a schedule for automatic generation.</li>
            <li>You can also generate a page immediately. You'll see a confirmation message when it finishes.</li>
            <li>WordPress scheduled actions are not always exactly on time, especially during low-traffic periods.</li>
            <li>If the content doesn't change after generation, a new revision will not be created.</li>
        </ol></p></div>
        <?php
    }

    /**
     * Output each "section" (a page to generate).
     * $wp_cron is an array originating from the WP options table.
     *
     * @param MJKGenToolsPage $pages
     * @param array $wp_cron The WordPress cron setting.
     */
    private function output_sections(array $pages, array $wp_cron): void {
        foreach ($pages as $page) {
            $this->output_section($page, $wp_cron);
        }
    }

    /**
     * Output an individual page section.
     * $wp_cron is an array originating from the WP options table.
     *
     * @param MJKGenToolsPage $page
     * @param array $wp_cron The WordPress cron setting.
     */
    private function output_section(MJKGenToolsPage $page,
            array $wp_cron):void {

        ?>
        <div class="<?php echo self::cl_p_report_table; ?>">
            <table class=" <?php echo self::cl_p_report; ?> ">
            <tr><td>
                    
                    <?php printf('<h2>%s</h2>', $page->name()); ?>
                    
                </td></tr><tr><td>
                    
                    <?php
                        printf('This page is created based on data from %s.',
                            $page->source());
                        
                        $this->render_settings_page_report($page);                        
                    ?>
                    
                    
                </td></tr><tr><td>
                    
                    <?php
                        $this->render_pid_area($page);
                    ?>
                    
                </td></tr><tr><td>
                    
                    <?php
                        $this->render_sched_area($page, $wp_cron);
                    ?>
                    
                </td></tr>
            </table>
        </div>
        <?php
    }
    
    /**
     * Output a report on settings page that regenerate the given page on save.
     *
     * @param MJKGenToolsPage $page
     */
    private function render_settings_page_report(MJKGenToolsPage $page): void {
        
        // Get the settings pages (instances of JKNSettingsPage)
        $settings_pages = $page->settings_pages();                    
        if (!empty($settings_pages)) {
            
            printf('<br>When any of the following settings areas are updated,'
                    . ' this page is automatically generated: ');

            // Turn each one into a link
            $spage_links = '';
            foreach($settings_pages as $settings_page) {
                $link = sprintf('<a class="%s_%s" href="%s" title="%s">%s</a>',
                        self::id_base, self::cl_a_spage,
                        $settings_page->url(),
                        $settings_page->page_title(),
                        $settings_page->menu_title());

                $spage_links .= sprintf('%s, ', $link);
            }

            // Output
            printf('%s', substr($spage_links, 0, -2));
        }
    }
    
    /**
     * Output a report on the last time the page was generated.
     *
     * @param MJKGenToolsPage $page
     */
    private function render_last_gen_report(MJKGenToolsPage $page): void {
        
        $last_ts = $page->last_gen();
        $last_success = $page->last_gen_success();
        $last_text = MJKGTSchedulerAJAX::format_last($last_ts, $last_success);
        printf('<b>Last generated</b> <span id="last_gen_%s">%s</span><br>',
                $page->id(), $last_text);
    }


	/*
	 * =========================================================================
	 * Page ID area
	 * =========================================================================
	 */

    /**
     * Output the page ID area:
     * Page ID selector; view; edit; generate; latest revision.
     *
     * @param MJKGenToolsPage $page
     */
    private function render_pid_area(MJKGenToolsPage $page): void {
        $pid = $page->pid();
        if (empty($pid)) $pid = self::default_pid;

        echo '<b>WP page</b>';
        $this->render_pid_dropdown($page, $pid);
        $this->render_view_button($page);
        $this->render_edit_button($page);
        $this->render_gen_button($page);

        if (get_class($page) == 'MJKGenToolsPageSwitch') {
            echo '<br><br>';
	        $this->render_preload_option($page);
        }
        
        echo '<br><br>';
        $this->render_last_gen_report($page);
        $this->render_rev_report($page, $pid);
    }
    
    /**
     * Output the page ID selector dropdown.
     *
     * @param MJKGenToolsPage $page
     * @param string|null $previous The previously selected option, if any.
     */
    private function render_pid_dropdown(MJKGenToolsPage $page,
            string $previous=null): void {
        
        $onchange = 'mjk_gt_switch_pid';
        
        // Get WP pages
        $select = wp_dropdown_pages([
            'echo' => 0,
            'show_option_none' => '-- None --',
            'selected' => $previous,
            'class' => sprintf('%s_switch_pid', self::id_base),
            'id' => sprintf('pid_%s', $page->id())
        ]);
        
        // We have to add an onchange to the select returned by WP
        $select = str_replace('<select',
                sprintf('<select onchange="%s(this.id)"',
                        $onchange), $select);
        
        printf($select);
    }
    
    /**
     * Output the 'Latest revision' report.
     *
     * @param MJKGenToolsPage $page
     * @param string $pid The currently selected page ID to check revisions of.
     */
    private function render_rev_report(MJKGenToolsPage $page,
            string $pid): void {
        
        $last_text = MJKGTSchedulerAJAX::format_latest_rev($pid);
        printf('<b>Latest new revision |</b> <span id="rev_%s">%s</span><br>',
                $page->id(), $last_text);
    }

	/**
	 * Output the 'Preload all?' option chooser.
     *
     * @param MJKGenToolsPageSwitch $page The page to use to ID the box.
	 */
	private function render_preload_option(MJKGenToolsPageSwitch $page): void {

	    $preload_all = $page->get_preload_all();
	    $checkbox = sprintf('<input type="checkbox" class="preload" id="%s"'
            . ' value="1" %s>', sprintf('preload_%s', $page->id()),
            checked($preload_all, true, false));

	    $msg = 'This is a Switch page. Do you want to preload all'
            . ' the options and not just the defaults? <br>(<small>This takes'
            . ' longer to generate, but the user will be able to see all the'
            . ' options without having to load them individually.</small>)';


	    printf('<div class="%1$s">%2$s</div><div class="%1$s">%3$s</div>',
            self::cl_preload_note, $msg, $checkbox);
    }


	/*
	 * =========================================================================
	 * Scheduling area
	 * =========================================================================
	 */

    /**
     * Output the schedule area:
     * Recurrence; time of day; day of week; next generation scheduled.
     *
     * @param MJKGenToolsPage $page
     * @param array $wp_cron The WordPress cron setting.
     */
    private function render_sched_area(MJKGenToolsPage $page,
            array $wp_cron): void {

        // Get the recurrence, hour and day from the existing scheduled event
        $next = $page->next();
        $sched = $this->get_current_schedule($next, $page->hook(), $wp_cron);
        list($rec, $hour, $day) = $sched;

        echo '<div style="display: inline-block;"><b>Frequency</b>';
        $this->render_rec_dropdown($page, $rec);
        
        echo '</div><div style="display: inline-block;"><b>Time of day</b>';
        $this->render_hour_dropdown($page, $hour);
        
        echo '</div><div style="display: inline-block;"><b>Day of the week</b>';
        $this->render_day_dropdown($page, $day);
        
        echo '</div><br><br>';
        $this->render_sched_report($page, $next);
    }

	/**
	 * Get the recurrence, hour and day from the existing scheduled event.
	 * This is done by analyzing the WP cron option entry.
	 * Defaults are no recurrence, midnight, and Monday.
	 *
	 * @param int $next The next occurrence to look up in WP's cron.
	 * @param string $hook The hook to look up in WP's cron.
	 * @param array $wp_cron The WordPress cron setting.
	 * @return array
	 */
    private function get_current_schedule(int $next, string $hook,
            array $wp_cron): array {

        // Defaults
        JKNTime::reset_timezone();
        $rec = self::default_rec;
        $hour = self::default_hour;
        $day = self::default_day;

        // If an existing event is cheduled, isolate the values from it
        if (isset($wp_cron[$next][$hook])) {
            $hooked = $wp_cron[$next][$hook];
            $rec = reset($hooked)['schedule'];
            $day = strtolower(date('w', $next));
            $hour = date('H', $next);
            
            // However
            if ($rec == 'hourly') {
                $hour = self::default_hour;
            } elseif ($rec == 'daily') {
                $day = self::default_day;
            }
        }

        return [$rec, $hour, $day];
    }

    /**
     * Render a schedule dropdown: recurrence, time of day, or day of week.
     *
     * @param MJKGenToolsPage $page
     * @param array $vals An array of [value => display] for <option> tags.
     * @param string $type Which scheduling setting this dropdown controls.
     * @param string|null $previous The previously selected option, if any.
     */
    private function render_schedule_dropdown(MJKGenToolsPage $page,
            array $vals, string $type, string $previous=null): void {
        
        $onchange = 'mjk_gt_switch_sched';
        
        // Create the initial select with all its identifiers
        $id = sprintf('%s_%s', $type, $page->id());
        printf('<select name="%1$s" id="%1$s" class="%2$s" disabled="disabled"'
                .' onchange="%3$s(this.id)">',
                $id, sprintf('%s_switch_sched', self::id_base), $onchange);

        // Create the option tags
        foreach ($vals as $val => $display) {
            $selected = ($previous == $val) ? 'selected' : '';
            printf('<option value="%s" %s>%s</option>',
                    $val, $selected, $display);
        }

        // Cloes the select
        printf('</select>');
    }

    /**
     * Render the recurrence selector.
     *
     * @param MJKGenToolsPage $page
     * @param string|null $previous The previously selected option, if any.
     */
    private function render_rec_dropdown(MJKGenToolsPage $page,
            string $previous=null): void {
        
        $vals = [
            '' => 'Never automatically',
            'hourly' => 'Hourly',
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'every_four_weeks' => 'Every four weeks'
        ];

        $this->render_schedule_dropdown($page, $vals, 'rec', $previous);
    }

    /**
     * Render the hour selector.
     *
     * @param MJKGenToolsPage $page
     * @param string|null $previous The previously selected option, if any.
     */
    private function render_hour_dropdown(MJKGenToolsPage $page,
            string $previous=null): void {
        
        $vals = [
            0   => 'Midnight',
            1   => '1 a.m.',
            2   => '2 a.m.',
            3   => '3 a.m.',
            4   => '4 a.m.',
            5   => '5 a.m.',
            6   => '6 a.m.',
            7   => '7 a.m.',
            8   => '8 a.m.',
            9   => '9 a.m.',
            10  => '10 a.m.',
            11  => '11 a.m.',
            12  => 'Noon',
            13  => '1 p.m.',
            14  => '2 p.m.',
            15  => '3 p.m.',
            16  => '4 p.m.',
            17  => '5 p.m.',
            18  => '6 p.m.',
            19  => '7 p.m.',
            20  => '8 p.m.',
            21  => '9 p.m.',
            22  => '10 p.m.',
            23  => '11 p.m.'
        ];

        $this->render_schedule_dropdown($page, $vals, 'hour', $previous);
    }

    /**
     * Render the day selector.
     *
     * @param MJKGenToolsPage $page
     * @param string|null $previous The previously selected option, if any.
     */
    private function render_day_dropdown(MJKGenToolsPage $page,
            string $previous=null): void {
        
        $vals = [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            0 => 'Sunday'
        ];

        $this->render_schedule_dropdown($page, $vals, 'day', $previous);
    }

    /**
     * Render the 'Next generation' report.
     *
     * @param MJKGenToolsPage $page
     * @param int $next A timestamp for the next scheduled generation.
     */
    private function render_sched_report(MJKGenToolsPage $page,
                int $next): void {

        $next_text = MJKGTSchedulerAJAX::format_next($next, $page->pid());
        printf('<b>Next generation |</b> <span id="sched_%s">%s</span>',
            $page->id(), $next_text);
    }


	/*
	 * =========================================================================
	 * Buttons
	 * =========================================================================
	 */
    
    /**
     * Render an AJAX button.
     *
     * @param MJKGenToolsPage $page
     * @param string $title The title for the button.
     * @param string $type The identifier for AJAX purposes.
     */
    private function render_ajax_button(MJKGenToolsPage $page,
            string $title, string $type): void {

        $onclick = sprintf("mjk_gt_%s_page('%s');", $type, $page->id());

        printf('<input type="button" class="button %5$s_button_%2$s"'
                . ' title="%1$s" name="%1$s" value="%1$s" id="%2$s_%3$s"'
                . ' onclick="%4$s" disabled="disabled"/>',
                $title, $type, $page->id(), $onclick, self::id_base);
    }

    /**
     * Render a 'View' button.
     *
     * @param MJKGenToolsPage $page
     */
    private function render_view_button(MJKGenToolsPage $page): void {
        $this->render_ajax_button($page, 'View', 'view');
    }

    /**
     * Render an 'Edit' button.
     *
     * @param MJKGenToolsPage $page
     */
    private function render_edit_button(MJKGenToolsPage $page): void {
        $this->render_ajax_button($page, 'Edit', 'edit');
    }

    /**
     * Render a 'Generate now' button.
     *
     * @param MJKGenToolsPage $page
     */
    private function render_gen_button(MJKGenToolsPage $page): void {
        $this->render_ajax_button($page, 'Generate now', 'gen');
    }


	/*
	 * =========================================================================
	 * CSS
	 * =========================================================================
	 */
    
    /**
     * Output the CSS style for this page.
     */
    private function render_css(): void {
        echo JKNCSS::tag('
            tr .'.self::cl_dropdown.' {
                display: inline-table;
                height: 80px;
            }

            tr .'.self::cl_dropdown.' th {
                width: auto;
                max-width: 180px;
                height: 40px;
                vertical-align: middle;
                padding-left: 10px;
            }

            .form-table tr {
                border-bottom: 1px dashed;
                margin-top: 0;
            }
            
            .'.self::cl_intro.' {
                background: #fdfdfd;
                border: 1px dashed #ddd;
                margin-bottom: 5px;
            }

            .'.self::cl_p_report.' {
                padding: 10px;
            }

            .'.self::cl_p_report.' .button {
                margin: 0 3px 0 3px;
                vertical-align: middle;
            }

            .'.self::cl_p_report.' .button:first-child {
                margin-left: 0;
            }

            .'.self::cl_p_report.' tr {
                height: 50px;
                vertical-align: middle;
            }

            .'.self::cl_p_report.' select {
                margin-left: 10px;
                margin-right: 25px;
                vertical-align: middle;
            }
            
            a.disabled, a.disabled:hover {
                color: #222;
                cursor: wait;
            }
            
            .'.self::cl_lg_report_true.' {
                color: #21a847;
            }
            
            .'.self::cl_lg_report_false.' {
                color: #ad0f0f;
            }
            
            #'.self::id_main.' {
                background: #dedede;
            }

            .'.self::cl_p_report_table.' {
                background: #fefefe;
                width: 100%;
                padding-bottom: 10px;
                padding-top: 10px;
                border-bottom: 5px solid #dedede;
            }

            #'.self::id_main.' {
                padding: 5px;
            }

            .'.self::cl_p_report.' tr:nth-child(3) td,
            .'.self::cl_p_report.' tr:nth-child(4) td {
                border-top: 1px dashed #555;
                padding-top: 10px;
            }

            .'.self::cl_p_report.' tr:nth-child(3) td {
                padding-bottom: 10px;
            }


            #'.self::id_main.' table:first-child {
                padding-top: 0;
            }

            .'.self::cl_p_report_table.':last-child {
                border-bottom: none;
            }
            .'.self::cl_preload_note.' {
                display: inline-block;
                padding: 10px;
                vertical-align: middle;
                border-left: 1px solid #ddd;
            }

            #'.self::id_main.' tr:first-child {
                height: 30px;
            }

            #'.self::id_main.' tr:first-child td {
                padding-top: 0;
                padding-bottom: 0;
                height: 15px;
            }

            #'.self::id_main.' h2 {
                margin-top: 0;
                margin-bottom: 0;
            }
        ');
    }
}
