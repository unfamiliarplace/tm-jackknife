<?php

/**
 * ACF for adding corrections to articles. Uses the below_wysiwyg group.
 */
class MJKEnrich_ACF_Corrections extends MJKEnrich_ACF_Below_WYSIWYG {

	/**
	 * Define the unique data ID -- important since the group is shared.
	 *
	 * @return string
	 */
    static function data_id(): string { return 'corr'; }
    
    // Fields
    const corrections = 'corrections';
    const corr_date = 'corr_date';
    const corr_nature = 'corr_nature';
    const corr_will_print = 'corr_will_print';
    const corr_print_next_iss = 'corr_print_next_iss';
    const corr_notice_date = 'corr_notice_date';

	/**
	 * Add the filters for the fields.
	 */
    static function add_filters(): void {
        add_action('acf/init', [__CLASS__, 'add_corrections']);
        add_action('acf/init', [__CLASS__, 'add_corr_date']);
        add_action('acf/init', [__CLASS__, 'add_corr_nature']);
        add_action('acf/init', [__CLASS__, 'add_corr_will_print']);
        add_action('acf/init', [__CLASS__, 'add_corr_print_next_iss']);
        add_action('acf/init', [__CLASS__, 'add_corr_notice_date']);
        
        // Add validation functions
        add_filter(sprintf('acf/validate_value/key=%s',
                self::qualify_field(self::corr_date)),
                [__CLASS__, 'validate_corr_date'], 10, 4);

        add_filter(sprintf('acf/validate_value/key=%s',
                self::qualify_field(self::corr_notice_date)),
                [__CLASS__, 'validate_corr_notice_date'], 10, 4);
    }

	/**
	 * Add the 'corrections' field.
	 */
    static function add_corrections(): void {
        self::add_acf_field(self::corrections, [
            'label' => 'Corrections',
            'type' => 'repeater',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'layout' => 'block',
            'button_label' => 'Add correction',
            'collapsed' => self::qualify_field(self::corr_date)
        ]);
    }

	/**
	 * Add the 'corr_date' field.
	 */
    static function add_corr_date(): void {
        
        self::add_acf_inner_field(self::corrections, self::corr_date, [
            'label' => 'Correction date',
            'type' => 'date_time_picker',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => [
                    'width' => 15,
                    'class' => '',
                    'id' => '',
            ],
            'display_format' => 'F j, Y g:i a',
            'return_format' => 'F j, Y g:i a',
            'first_day' => 0,
            'disabled' => 0
        ]);
    }

	/**
	 * Add the 'corr_nature' field.
	 */
    static function add_corr_nature(): void {
        self::add_acf_inner_field(self::corrections, self::corr_nature, [
            'label' => 'What was corrected?',
            'type' => 'text',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => 40,
                'class' => '',
                'id' => '',
            ],
            'default_value' => '',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
            'readonly' => 0,
            'disabled' => 0,
        ]);
    }

	/**
	 * Add the 'corr_will_print' field.
	 */
    static function add_corr_will_print(): void {
        self::add_acf_inner_field(self::corrections, self::corr_will_print, [
            'label' => 'Will you print a notice?',
            'type' => 'true_false',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => 15,
                'class' => '',
                'id' => '',
            ],
            'message' => '',
            'default_value' => 1,
            'ui' => 1
        ]);
    }

	/**
	 * Add the 'corr_print_next_iss' field.
	 */
    static function add_corr_print_next_iss(): void {
        self::add_acf_inner_field(self::corrections, self::corr_print_next_iss, [
            'label' => 'In the next issue?',
            'type' => 'true_false',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => self::qualify_field(self::corr_will_print),
                        'operator' => '==',
                        'value' => '1',
                    ],
                ],
            ],
            'wrapper' => [
                'width' => 15,
                'class' => '',
                'id' => '',
            ],
            'message' => '',
            'default_value' => 1,
            'ui' => 1
        ]);
    }

	/**
	 * Add the 'corr_notice_date' field.
	 */
    static function add_corr_notice_date(): void {
        self::add_acf_inner_field(self::corrections, self::corr_notice_date, [
            'label' => 'Notice date',
            'type' => 'date_picker',
            'instructions' => 'There must be an issue registered to this date.',
            'required' => 1,
            'conditional_logic' => [
                [
                    [
                        'field' => self::qualify_field(self::corr_will_print),
                        'operator' => '==',
                        'value' => '1',
                    ],
                    [
                        'field' => self::qualify_field(self::corr_print_next_iss),
                        'operator' => '!=',
                        'value' => '1',
                    ],
                ],
            ],
            'wrapper' => [
                'width' => 15,
                'class' => '',
                'id' => '',
            ],
            'display_format' => 'F j, Y',
            'return_format' => 'F j, Y',
            'first_day' => 0,
        ]);
    }


	/*
	 * =========================================================================
	 * Field validating
	 * =========================================================================
	 */

	/**
	 * Return true iff correction date is not before the article was published.
	 *
	 * @param bool|string $valid
	 * @param mixed $value
	 * @param array $field
	 * @param string $input
	 * @return bool|string|null
	 */
    static function validate_corr_date($valid, $value, $field, $input) {
    	global $post;
        
        // Short-circuit if value is already invalid
        if (!$valid) return $valid;
        
        // Check that date is not too old
        $dt = JKNTime::dt($value);
        $pid = $post->ID;
        $post_dt = JKNTime::dt_pid($pid);
        
        if ($dt <= $post_dt) {
            $valid = 'The correction must have taken place'
	        . ' after the article was published.';
        }
        
        // Return result
        return $valid;
    }

	/**
	 * Return true iff notice date is at least one issue later than article.
	 *
	 * @param bool|string $valid
	 * @param mixed $value
	 * @param array $field
	 * @param string $input
	 * @return bool|string|null
	 */
    static function validate_corr_notice_date($valid, $value, $field, $input) {
	    global $post;
        
        // Short-circuit if value is already invalid
        if (!$valid) return $valid;
        
        // Check that date lands on an issue
        $notice_dt = JKNTime::dt($value);
        $notice_issue = MJKVIAPI::get_issue_by_dt($notice_dt, $allow_summer=false);
        if (empty($notice_issue) || !$notice_issue->lands_on_dt($notice_dt)) {
            $valid = 'The notice date must have an issue printed on it.';            
        
        // If so, check that the issue it lands on is at least article + 1
        } else {
	        $pid = $post->ID;
            $post_dt = JKNTime::dt_pid($pid);
            $post_issue = MJKVIAPI::get_issue_by_dt($post_dt);
            
            // Ensure it's from a later issue
            if (!empty($post_issue) &&
                    ($notice_issue->get_first_day() <= $post_issue->get_first_day())) {
                $valid = 'The notice issue must be later than the original issue.';
            }
        }
        
        // Return result
        return $valid;
    }
}
