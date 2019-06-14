<?php

/**
 * ACF for adding updates to articles. Uses the below_wysiwyg group.
 */
class MJKEnrich_ACF_Updates extends MJKEnrich_ACF_Below_WYSIWYG {

	/**
	 * Define the unique data ID -- important since the group is shared.
	 *
	 * @return string
	 */
	static function data_id(): string { return 'updates'; }

	/**
	 * Define the type.
	 *
	 * @return string
	 */
	static function type(): string { return self::LOCATION_POST; }
    
    // Fields
    const updates = 'updates';
    const update_date = 'update_date';
    const update_nature = 'update_nature';

	/**
	 * Add the field filters.
	 */
    static function add_filters(): void {
        add_action('acf/init', [__CLASS__, 'add_updates']);
        add_action('acf/init', [__CLASS__, 'add_update_date']);
        add_action('acf/init', [__CLASS__, 'add_update_nature']);
        
        // Add validation function
        add_filter(sprintf('acf/validate_value/key=%s',
                self::qualify_field(self::update_date)),
                [__CLASS__, 'validate_update_date'], 10, 4);
    }

	/**
	 * Add the 'updates' field.
	 */
    static function add_updates(): void {
        self::add_acf_field(self::updates, [
            'label' => 'Updates',
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
            'button_label' => 'Add update',
            'collapsed' => self::qualify_field(self::update_date)
        ]);
    }

	/**
	 * Add the 'updates' field.
	 */
    static function add_update_date(): void {
        self::add_acf_inner_field(self::updates, self::update_date, [
            'label' => 'Update date',
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
	 * Add the 'updates' field.
	 */
    static function add_update_nature(): void {
        self::add_acf_inner_field(self::updates, self::update_nature, [
            'label' => 'Update',
            'type' => 'wysiwyg',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => 85,
                'class' => '',
                'id' => '',
            ],
            'default_value' => '',
            'tabs' => 'all',
            'toolbar' => 'basic',
            'media_upload' => 0,
            'delay' => 0,
        ]);
    }


	/*
	 * =========================================================================
	 * Field validating
	 * =========================================================================
	 */

	/**
	 * Return true iff update date is not before the article was published.
	 *
	 * @param bool|string $valid
	 * @param mixed $value
	 * @param array $field
	 * @param string $input
	 * @return bool|string|null
	 */
    static function validate_update_date($valid, $value, $field, $input) {
    	global $post;
        
        // Short-circuit if value is already invalid
        if (!$valid) return $valid;
        
        // Check that date is not too old
        $dt = JKNTime::dt($value);
        $pid = $post->ID;
        $post_dt = JKNTime::dt_pid($pid);
        
        if ($dt <= $post_dt) {
            $valid = 'The update must have taken place after the article' .
	            ' was published.';
        }
        
        // Return result
        return $valid;
    }
}
