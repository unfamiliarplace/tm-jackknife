<?php

/**
 * An interface between generation pages and the AJAX JS for the scheduler.
 */
final class MJKGTSchedulerAJAX {

    // Constants and set up
    const js_path = 'js/scheduler_ajax.js';
    const js_handle = 'mjk_gt_scheduler_ajax';
    const js_ver = '0.000044'; // If this doesn't change, cache won't change
    
    /**
     * Return an array of AJAX function suffixes to qualified hook suffixes.
     */
    static function ajax_actions(): array {
        $bases = ['switch_pid', 'switch_sched', 'update_reports', 'gen',
	        'update_preload'];
        
        $actions = [];
        foreach($bases as $id) {
            $actions[$id] = sprintf('%s_%s', MJKGTScheduler::id_base, $id);
        }
        
        return $actions;
    }
    
    /**
     * Hook the AJAX actions.
     */
    static function hook_ajax() {
        foreach(self::ajax_actions() as $id => $action) {
            add_action(JKNAjax::qualify_action($action),
                    [__CLASS__, sprintf('ajax_%s', $id)]);
        }
    }


    /*
     * =========================================================================
     * Localization for JS
     * =========================================================================
     */

	/**
	 * Return a list of qualified names.
	 *
	 * @param array $names
	 * @return array
	 */
    static function qualified_names(array $names): array {
        $q_names = [];
        
        foreach($names as $name) {
            $q_names[] = sprintf('%s_%s', MJKGTScheduler::id_base, $name);
        }
        
        return $q_names;
    }
    
    /**
     * Return a list of the input classes for disabling purposes.
     */
    static function input_classes(): array {
        $bases = ['button_gen', 'button_view', 'button_edit',
            'switch_pid', 'switch_sched'];
        return self::qualified_names($bases);
    }
    
    /**
     * Return a list of the <a> classes for disabling purposes.
     */
    static function link_classes(): array {
        $bases = [MJKGTScheduler::cl_a_spage];
        return self::qualified_names($bases);
    }
    
    /**
     * Return a list of pid-dependent selector ids for disabling purposes.
     */
    static function pid_disable_ids(): array {
        $bases = ['view', 'edit', 'gen', 'rec', 'hour', 'day'];
        return $bases;
    }
    
    /**
     * Return an array of the schedule dropdowns' default values.
     */
    static function default_values(): array {
        return [
            'pid'   => MJKGTScheduler::default_pid,
            'rec'   => MJKGTScheduler::default_rec,
            'hour'  => MJKGTScheduler::default_hour,
            'day'   => MJKGTScheduler::default_day
        ];
    }

	/**
	 * Enqueue the JS. Identify the hook using the page ID.
	 *
	 * @param string $pid
	 */
    static function enqueue_js(string $pid) {
        
        add_action('admin_enqueue_scripts',                
            function(string $hook) use($pid) {
            
                // Short-circuit if not on the right page
                if (!JKNStrings::ends_with($hook, $pid)) return;
            
                // Enqueue the script
                wp_enqueue_script(self::js_handle,
                    plugins_url(self::js_path, __FILE__),
                    $deps=['jquery'],
                    $ver=self::js_ver,
                    $in_footer=true
                );
                
                // Localize it
                wp_localize_script(self::js_handle, 'MJKGTSchedulerAjax',
                    [
                        'home_url' => home_url(),
                        'pages' => array_keys(MJKGTAPI::pages()),
                        'input_classes' => MJKGTSchedulerAJAX::input_classes(),
                        'link_classes' => MJKGTSchedulerAJAX::link_classes(),
                        'pid_disable_ids' => MJKGTSchedulerAJAX::pid_disable_ids(),
                        'ajax_actions' => self::ajax_actions(),
                        'default_values' => self::default_values()
                    ]
                );
            }
        );
    }


	/*
	 * =========================================================================
	 * Formatting
	 * =========================================================================
	 */

	/**
	 * Return a formatted success/error message for the last generation.
	 *
	 * @param bool $success
	 * @return string
	 */
    static function format_success(bool $success): string {
        
        if ($success) {
            $status = 'success';
            $cl = MJKGTScheduler::cl_lg_report_true;
            
        } else {
            $status = 'error';
            $cl = MJKGTScheduler::cl_lg_report_false;
        }
        
        return sprintf('<span class="%s">%s</span>', $cl, $status);
    }

	/**
	 * Return a formatted date for the last generation, as well as a success or
	 * error message. $last is a timestamp for said generation; $success is
	 * whether it succeeded.
	 *
	 * @param int|null $last
	 * @param bool|null $success
	 * @return string
	 */
    static function format_last(?int $last, ?bool $success): string {
        
        // If no generation has ever occurred
        if (empty($last)) return 'Never';
        
        return sprintf('%s ago (%s)',
                human_time_diff($last), self::format_success($success));
    }

	/**
	 * Return a formatted date for the next generation.
	 * $next is a timestamp for said generation.
	 *
	 * @param int|null $next
	 * @param null|string $pid
	 * @return string
	 */
    static function format_next(?int $next, ?string $pid): string {
        
        // If no scheduling has been done
        if (empty($next) || empty($pid)) return 'Not scheduled';
        
        // Otherwise
        return sprintf('%s — %s from now', MJKGTAPI::format_date($next),
                human_time_diff($next));
    }

	/**
	 * Return a formatted date for the latest revision of the selected page.
	 *
	 * @param string $pid
	 * @return string
	 */
    static function format_latest_rev(string $pid): string {
        
        // If no page has been chosen, say so
        if (empty($pid)) {
            return 'No page selected';
            
        // Otherwise
        } else {
        	return MJKGTAPI::format_latest_post_revision($pid);
        }
    }


	/*
	 * =========================================================================
	 * AJAX actions
	 * =========================================================================
	 */
    
    /**
     * Extract a generation page object from the JQuery post.
     */
    static function get_page(): MJKGenToolsPage {
        $page_id = $_POST['page'];
        return MJKGTAPI::page($page_id);
    }

    /**
     * AJAX: Switch the page ID and report on the latest revision.
     * Also update the schedule.
     * Response: revision report and schedule report.
     */
    static function ajax_switch_pid() {
        $page = self::get_page();
        
        $pid = $_POST['pid'];
        $rec = $_POST['rec'];
        $hour = (int) $_POST['hour'];
        $day = (int) $_POST['day'];
        
        $page->update_pid($pid);
        $page->update_schedule($rec, $hour, $day, $pid);

        $response = [];
        $response['revision_report'] = self::format_latest_rev($pid);
        $response['schedule_report'] = self::format_next($page->next(), $pid);
        wp_send_json($response);
    }

    /**
     * AJAX: Switch any scheduling element and report on the next generation.
     * Response: schedule report.
     */
    static function ajax_switch_sched() {
        $page = self::get_page();
        
        $pid = $_POST['pid'];
        $rec = $_POST['rec'];
        $hour = (int) $_POST['hour'];
        $day = (int) $_POST['day'];
        
        $page->update_schedule($rec, $hour, $day, $pid);
        
        $response['report'] = self::format_next($page->next(), $pid);
        wp_send_json($response);
    }
    
    /**
     * AJAX: Update the schedules for every page.
     * Response: array of schedule reports and array of revision reports.
     */
    static function ajax_update_reports() {
        $pages = MJKGTAPI::pages();
        $post_ids = $_POST['page_to_pid'];
        
        $response = [];
        $response['page_to_sched_report'] = [];
        $response['page_to_rev_report'] = [];
        $response['page_to_last_gen_report'] = [];
        
        foreach($pages as $page) {
            $page_id = $page->id();
            $post_id = $post_ids[$page_id];
            
            $sched = self::format_next($page->next(), $post_id);
            $response['page_to_sched_report'][$page_id] = $sched;
            
            $rev = self::format_latest_rev($post_id);
            $response['page_to_rev_report'][$page_id] = $rev;
            
            $last_gen = self::format_last($page->last_gen(),
                    $page->last_gen_success());
            $response['page_to_last_gen_report'][$page_id] = $last_gen;
        }
        
        $response['message'] = 'Updated schedule reports.';
        wp_send_json($response);
    }

    /**
     * AJAX: Generate a page.
     * Response: Revision report and confirmation message.
     */
    static function ajax_gen() {
        $page = self::get_page();
        $pid = $_POST['pid'];
        
        $response = [];
        
        $gen_result = $page->generate();
        $rev_text = self::format_latest_rev($pid);
        $response['rev_report'] = $rev_text;

        $last_gen = $page->last_gen();
        $success = $page->last_gen_success();
        $last_gen_text = self::format_last($last_gen, $success);
        $response['last_gen_report'] = $last_gen_text;
        
        // If no error, announce successful generation
        if (empty($gen_result)) {
	        $gen_result = sprintf('The %s page was successfully generated.',
                    $page->name());
        }
        
        $response['message'] = $gen_result;
        wp_send_json($response);
    }

    /**
     * AJAX: Update the page's preload_all option.
     * Response: '1' on success.
     */
    static function ajax_update_preload(): void {
	    $page = self::get_page();
	    $checked = $_POST['preload'];
	    $preload = (strtolower($checked === 'true'));

	    $page->update_preload_all($preload);
	    wp_send_json('1');
    }
}
