<?php

/**
 * Add extra cron schedules.
 */
class MJKCommon_cron_schedules {

	/**
	 * Add the cron schedules.
	 */
    static function run() {

        $cb = function (array $schedules): array {

            $schedules['weekly'] = [
                'interval' => 604800,
                'display' => __('Weekly')
            ];

            $schedules['every_four_weeks'] = [
                'interval' => 604800 * 4,
                'display' => __('Every four weeks')
            ];

            $schedules['every_five_minutes'] = [
                'interval' => 300,
                'display' => __('Every five minutes'),
            ];

            $schedules['every_fifteen_minutes'] = [
                'interval' => 900,
                'display' => __('Every fifteen minutes'),
            ];

            $schedules['every_half_hour'] = [
                'interval' => 1500,
                'display' => __('Every half-hour'),
            ];

            return $schedules;
        };

        add_filter('cron_schedules', $cb);
    }
}
