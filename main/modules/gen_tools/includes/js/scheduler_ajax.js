/*
 * =============================================================================
 * Functions for the $ Ajax for the scheduler page.
 * =============================================================================
 */

$ = jQuery;

/**
 * Perform onload actions.
 */
function mjk_gt_scheduler_onload() {
    window.mjk_gt_busy = false;
    mjk_gt_bind_actions();
    mjk_gt_pid_disable_enable_all();
    mjk_gt_sched_disable_enable_all();
    mjk_gt_start_updates();
}

/**
 * PHP localization: MJKGTSchedulerAjax
 *      MJKGTSchedulerAjax.home_url
 *      MJKGTSchedulerAjax.pages
 *      MJKGTSchedulerAjax.input_classes
 *      MJKGTSchedulerAjax.link_classes
 *      MJKGTSchedulerAjax.pid_disable_ids
 *      MJKGTSchedulerAjax.ajax_actions
 *      MJKGTSchedulerAjax.default_values
 */


/*
 * =============================================================================
 * Binding
 * =============================================================================
 */

/**
 * Bind the preload checkbox changes.
 */
function mjk_gt_bind_actions() {
    $pages = MJKGTSchedulerAjax.pages;
    for (var $i = 0, $len = $pages.length ; $i < $len ; $i++) {

        // Bind the preload checkbox
        $preload_id = 'preload_' + $pages[$i];
        $('#' + $preload_id).change(function () {
            mjk_gt_update_preload($(this).attr('id'), $(this).prop('checked'));
        })
    }
}


/*
 * =============================================================================
 * ID manipulation
 * =============================================================================
 */

/**
 * Extract the pid from the unique (i.e. overly qualified) id of an element.
 */
function mjk_gt_unqualify_el_id($pid) {
    return $pid.substring($pid.indexOf('_') + 1);
}

/**
 * Qualify a given dropdown based on its page and option name.
 * e.g. rec_pageid
 */
function mjk_gt_qualify_dropdown($page, $opt) {
    return '#' + $opt + '_' + $page;
}


/*
 * =============================================================================
 * Element reading
 * =============================================================================
 */

/**
 * Return the value of a dropdown corresponding to the given page and option.
 */
function mjk_gt_read_dropdown($page, $opt) {
    $id = mjk_gt_qualify_dropdown($page, $opt);
    return $($id).val();
}

/**
 * Gather and return all the dropdown values for the given page.
 */
function mjk_gt_get_values($page) {
    $vals = {};
    $vals['page'] = $page;
    $vals['pid'] = mjk_gt_read_dropdown($page, 'pid');
    $vals['rec'] = mjk_gt_read_dropdown($page, 'rec');
    $vals['hour'] = mjk_gt_read_dropdown($page, 'hour');
    $vals['day'] = mjk_gt_read_dropdown($page, 'day');
    return $vals;
}

/**
 * Gather and return all the pid values for the given pages.
 */
function mjk_gt_page_to_pid($pages) {
    $page_to_pid = {};
    
    for (var $i = 0, $len = $pages.length ; $i < $len ; $i++) {
        $page = $pages[$i];
        $pid = mjk_gt_read_dropdown($page, 'pid');
        $page_to_pid[$page] = $pid;
    }
    
    return $page_to_pid;
}


/*
 * =============================================================================
 * Element manipulation
 * =============================================================================
 */

/**
 * Change the text on a $ scope (button or buttons).
 */
function mjk_gt_change_button_state($b, $state) {
    $b.attr('value', $state);
    $b.attr('name',  $state);
    $b.attr('title', $state);
}

/**
 * Change the text for the given page's schedule report.
 */
function mjk_gt_update_sched_report($page, $response) {
    $('#sched_' +  $page).html($response);
}

/**
 * Change the text for the given page's revision report.
 */
function mjk_gt_update_rev_report($page, $response) {
    $('#rev_' +  $page).html($response);
}

/**
 * Change the text for the given page's last generation report.
 */
function mjk_gt_update_last_gen_report($page, $response) {
    $('#last_gen_' +  $page).html($response);
}

/**
 * Reset the given schedule dropdown for the given page.
 */
function mjk_gt_reset_schedule_dropdown($page, $dd) {
    $defaults = MJKGTSchedulerAjax.default_values;
    
    $sel = '#'.concat($dd, '_', $page);
    $opt = $sel.concat(' option[value="', $defaults[$dd], '"]');
    $($sel).val($defaults[$dd]);
    $($opt).attr('selected', true);
}

/**
 * Reset the schedule dropdowns (because the pid has been cleared).
 */
function mjk_gt_reset_schedule($page) {
    mjk_gt_reset_schedule_dropdown($page, 'rec');
    mjk_gt_reset_schedule_dropdown($page, 'hour');
    mjk_gt_reset_schedule_dropdown($page, 'day');
}

/**
 * Reset the hour and day schedule dropdowns (because the rec has changed).
 */
function mjk_gt_reset_hour_day($page) {
    var $defaults = MJKGTSchedulerAjax.default_values;
    var $rec = mjk_gt_read_dropdown($page, 'rec');
    
    if (!$rec || ($rec === $defaults['rec']) || ($rec === 'hourly')) {
        mjk_gt_reset_schedule_dropdown($page, 'hour');
        mjk_gt_reset_schedule_dropdown($page, 'day');
        
    } else if ($rec === 'daily') {
        mjk_gt_reset_schedule_dropdown($page, 'day');
    }
}

/**
 * Enable/disable pid-dependent buttons for a given page.
 * val is true iff input should be disabled.
 */
function mjk_gt_pid_disable_enable($page) {
    $vals = mjk_gt_get_values($page);
    $disable = !($vals['pid']);

    $ids = MJKGTSchedulerAjax.pid_disable_ids;

    for (var $i = 0, $len = $ids.length ; $i < $len ; $i++) {
        $('#'.concat($ids[$i], '_', $page)).attr('disabled', $disable);
    }
}

/**
 * Enable/disable pid-dependent buttons for all pages.
 * val is true iff input should be disabled.
 */
function mjk_gt_pid_disable_enable_all() {
    $pages = MJKGTSchedulerAjax.pages;
    for (var $i = 0, $len = $pages.length ; $i < $len ; $i++) {
        mjk_gt_pid_disable_enable($pages[$i]);
    }
}

/**
 * Enable/disable sched-dependent dropdowns for a given page.
 * val is true iff input should be disabled.
 */
function mjk_gt_sched_disable_enable($page) {
    $vals = mjk_gt_get_values($page);
    $defaults = MJKGTSchedulerAjax.default_values;
    
    $disable_hour =
            (!($vals['rec']) ||
            !($vals['pid']) ||
            ($vals['pid'] === $defaults['pid']) ||
            ($vals['rec'] === 'hourly'));
    
    $disable_day =
            (!($vals['rec']) ||
            !($vals['pid']) ||
            ($vals['pid'] === $defaults['pid']) ||
            ($vals['rec'] === 'hourly') ||
            ($vals['rec'] === 'daily'));
    
    $('#hour_'.concat($page)).attr('disabled', $disable_hour);
    $('#day_'.concat($page)).attr('disabled', $disable_day);
}

/**
 * Enable/disable sched-dependent dropdowns for all pages.
 */
function mjk_gt_sched_disable_enable_all() {
    $pages = MJKGTSchedulerAjax.pages;
    for (var $i = 0, $len = $pages.length ; $i < $len ; $i++) {
        mjk_gt_sched_disable_enable($pages[$i]);
    }
}

/**
 * Enable/disable all input.
 * val is true iff input should be disabled.
 */
function mjk_gt_disable_enable_all_input($val) {
    $input_classes = MJKGTSchedulerAjax.input_classes;
    $link_classes = MJKGTSchedulerAjax.link_classes;
    
    // Merge classes and links for attr.disabled
    $all_classes = $input_classes.concat($link_classes);

    for (var $i = 0, $len = $all_classes.length ; $i < $len ; $i++) {
        $els = $('.'.concat($all_classes[$i]));
        $els.attr('disabled', $val);
        if ($val) {
            $els.addClass('disabled');
        } else {
            $els.removeClass('disabled');
        }
    }
    
    // For links, also unbind the click
    for (var $j = 0, $len = $link_classes.length ; $j < $len ; $j++ ) {
        $links = $('.'.concat($link_classes[$j]));
        if ($val) {
            $links.bind('click', function($e) { $e.preventDefault(); } );
        } else {
            $links.unbind('click');
        }
    }
}

/**
 * Change the cursor to the given value for various key elements.
 */
function mjk_gt_change_cursor($value) {
    $('html, body, select, input').each(function() {
            this.style.setProperty('cursor', $value, 'important');
        }
    );
}


/*
 * =============================================================================
 * General AJAX behaviour
 * =============================================================================
 */

/**
 * Carry out an AJAX post.
 * You can replace the success, begin, done, and fail functions.
 */
function mjk_gt_ajax_post($action, $data, $fn_success, $fn_begin, $fn_done,
                          $fn_fail) {
    
    // Prepare all parts of the deferrable
    $data['action'] = $action;
    if (!$fn_success)   { fn_success    = function() {}; }
    if (!$fn_begin)     { $fn_begin     = mjk_gt_ajax_begin; }
    if (!$fn_done)      { $fn_done      = mjk_gt_ajax_done; }
    if (!$fn_fail)      { $fn_fail      = mjk_gt_ajax_fail; }
    
    // Post
    $fn_begin();
    $.ajax({
        url: ajaxurl,
        type: 'post',
        data: $data,
        success: $fn_success
    }).done($fn_done).fail($fn_fail);
}

/**
 * Carry out actions at the beginning of an AJAX post.
 */
function mjk_gt_ajax_begin() {
    mjk_gt_change_cursor('wait');
    mjk_gt_disable_enable_all_input(true);
    window.mjk_gt_busy = true;
}

/**
 * Carry out actions at the end of an AJAX post.
 */
function mjk_gt_ajax_done() {
    
    // Reset cursor
    mjk_gt_change_cursor('');

    // Change generate buttons
    $buttons = $('.mjk_gt_ajax_button_gen');
    mjk_gt_change_button_state($buttons, 'Generate now');
    $buttons.each(function() {
        this.style.setProperty('background-color', '#f7f7f7', 'important');
    });

    // Un-disable all inputs
    mjk_gt_disable_enable_all_input(false);

    // Re-disable pid- and sched-dependent inputs if necessary
    mjk_gt_pid_disable_enable_all();
    mjk_gt_sched_disable_enable_all();

    window.mjk_gt_busy = false;
}

/**
 * Carry out actions at the failure of an AJAX post.
 */
function mjk_gt_ajax_fail($jqXHR) {
    
    // Alert about the error                    
    alert(''.concat('The action generated an error and could not complete.',
        ' The admin-ajax page returned the following headers:\n----\n',
        $jqXHR.getAllResponseHeaders()));

    // Update reports in order to get error messages in, etc.
    mjk_gt_update_reports();

    // Reset the rest
    mjk_gt_ajax_done();
}


/*
 * =============================================================================
 * Specific AJAX actions
 * =============================================================================
 */

/**
 * On clicking the view button for a page, open that WP post.
 */
function mjk_gt_view_page($page) {
    $pid = mjk_gt_read_dropdown($page, 'pid');
    window.open(MJKGTSchedulerAjax.home_url.concat('/?p=',  $pid), '_blank');
}

/**
 * On clicking the edit button for a page, open that WP post's edit screen.
 */
function mjk_gt_edit_page($page) {
    $pid = mjk_gt_read_dropdown($page,  'pid');
    window.open(MJKGTSchedulerAjax.home_url.concat('/wp-admin/post.php?post=',
    $pid, '&action=edit'),  '_blank');
}

/**
 * When the pid dropdown changes, update the page's PID and its schedule.
 */
function mjk_gt_switch_pid($el_id) {
    $page = mjk_gt_unqualify_el_id($el_id);
    
    // If PID has been cleared, clear the schedule
    $pid = mjk_gt_read_dropdown($page, 'pid');
    if (!$pid || ($pid == MJKGTSchedulerAjax.default_values['pid'])) {
        mjk_gt_reset_schedule($page);
    }
    
    // PID action
    $vals = mjk_gt_get_values($page);
    $action = MJKGTSchedulerAjax.ajax_actions['switch_pid'];
    mjk_gt_ajax_post($action, $vals,
        function ($response) {
            mjk_gt_update_rev_report($page, $response.revision_report);
            mjk_gt_update_sched_report($page, $response.schedule_report);
        }
    );
}

/**
 * When any sched dropdown changes, update the page's schedule.
 */
function mjk_gt_switch_sched($el_id) {
    $page = mjk_gt_unqualify_el_id($el_id);
    mjk_gt_reset_hour_day($page);

    // Schedule action
    $action = MJKGTSchedulerAjax.ajax_actions['switch_sched'];
    $vals = mjk_gt_get_values($page);
    mjk_gt_ajax_post($action, $vals,
        function ($response) {
            mjk_gt_update_sched_report($page, $response.report);
        }
    );
}

/**
 * On clicking the gen button for a page, regenerate that page.
 */
function mjk_gt_gen_page($page) {

    // Change this button specifically
    $b = $('#gen_' + $page);
    mjk_gt_change_button_state($b, 'Working...');
    $b[0].style.setProperty('background-color', '#c2f4c1', 'important');

    // Generate action
    $action = MJKGTSchedulerAjax.ajax_actions['gen'];
    $vals = mjk_gt_get_values($page);
    mjk_gt_ajax_post($action, $vals,
        function ($response) {
            mjk_gt_change_cursor('');
            $rev_report = $response.rev_report;
            $last_gen_report = $response.last_gen_report;
            $msg = $response.message;
            mjk_gt_update_rev_report($page, $rev_report);
            mjk_gt_update_last_gen_report($page, $last_gen_report);
            alert($msg);
        }
    );
}

/**
 * On clicking the preload checkbox, update its value.
 */
function mjk_gt_update_preload($el_id, $checked) {
    $page = mjk_gt_unqualify_el_id($el_id);

    $action = MJKGTSchedulerAjax.ajax_actions['update_preload'];
    $vals = {'preload': $checked, 'page': $page};

    mjk_gt_ajax_post($action, $vals,
        function ($response) {
            mjk_gt_change_cursor('');

            if ($response !== '1') {
                alert('Changing the preload value did not succeed.');
            }
        }
    );
}

/**
 * Periodically update the schedule and revision reports.
 */
function mjk_gt_update_reports() {
    $pages = MJKGTSchedulerAjax.pages;
    $action = MJKGTSchedulerAjax.ajax_actions['update_reports'];
    
    $page_to_pid = mjk_gt_page_to_pid($pages);
    $data = {'page_to_pid': $page_to_pid};
    
    // Replace the usual Ajax begin, done, fail -- don't need to alert the user
    $null_begin_fn = function() {};
    $null_end_fn = function($jqXHR) {};
    
    mjk_gt_ajax_post($action, $data,
        function ($response) {

            // Only update if another Ajax post was not started while this ran
            if (!window.mjk_gt_busy) {

                // Extract the schedule report for each page
                $page_to_sched_report = $response.page_to_sched_report;
                for (var $page in $page_to_sched_report) {
                    $report = $page_to_sched_report[$page];
                    mjk_gt_update_sched_report($page, $report);
                }

                // Extract the revision report for each page
                $page_to_rev_report = $response.page_to_rev_report;
                for (var $page in $page_to_rev_report) {
                    $report = $page_to_rev_report[$page];
                    mjk_gt_update_rev_report($page, $report);
                }

                // Extract the revision report for each page
                $page_to_last_gen_report = $response.page_to_last_gen_report;
                for (var $page in $page_to_last_gen_report) {
                    $report = $page_to_last_gen_report[$page];
                    mjk_gt_update_last_gen_report($page, $report);
                }
            }
        }
    , $null_begin_fn, $null_end_fn, $null_end_fn);
}

/**
 * Trigger the report update every minute on the minute.
 */
function mjk_gt_start_updates() {
    
    // Check current time and calculate the delay until next interval
    $interval = 60 * 1000;
    $now = new Date();
    $delay = $interval - $now % $interval;

    function start() {

        // Only run if another ajax post is not running
        if (!window.mjk_gt_busy) {
            mjk_gt_update_reports();
        }

        window.setInterval(mjk_gt_update_reports, $interval);
    }

    // Delay execution until it's the next minute (plus one)
    setTimeout(start, $delay + 1000);
}


/*
 * =============================================================================
 * Onload trigger
 * =============================================================================
 */

mjk_gt_scheduler_onload();
