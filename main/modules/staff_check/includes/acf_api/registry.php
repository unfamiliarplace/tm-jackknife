<?php

/**
 * The Advanced Custom Fields Pro registry for Staff Check.
 */
final class MJKSC_ACF extends JKNACF {

	/**
	 * Define the type.
	 *
	 * @return string
	 */
	static function type(): string { return self::LOCATION_OPTIONS; }

	// Fields
	const exclude_comic         = 'exclude_comic';
	const exclude_comic_years   = 'exclude_comic_years';

	const auto_add              = 'auto_add';
	const auto_remove           = 'auto_remove';
	const auto_on_gen           = 'auto_on_gen';
	const auto_email_on_gen     = 'auto_email_on_gen';
	const auto_day              = 'auto_day';
	const auto_time             = 'auto_time';
	const auto_emails           = 'auto_emails';
	const auto_email            = 'auto_email';
	const auto_email_none       = 'auto_email_none';

	const almost_n              = 'almost_n';
	const almost                = 'almost';
	const almost_day            = 'almost_day';
	const almost_time           = 'almost_time';
	const almost_emails         = 'almost_emails';
	const almost_email          = 'almost_email';
	const almost_email_none     = 'almost_email_none';
    
    
    /**
     * Add the filters for the group and fields.
     */
    static function add_filters(): void {

        // Group
        add_action('acf/init', [__CLASS__, 'add_group']);

        // Exclude comic
	    self::add_tab('Comic');
	    add_action('acf/init', [__CLASS__, 'add_exclude_comic']);
	    add_action('acf/init', [__CLASS__, 'add_exclude_comic_years']);

	    // Auto add/remove
	    self::add_tab('Auto add/remove roles');
	    add_action('acf/init', [__CLASS__, 'add_auto_add']);
	    add_action('acf/init', [__CLASS__, 'add_auto_remove']);
	    add_action('acf/init', [__CLASS__, 'add_auto_on_gen']);
	    add_action('acf/init', [__CLASS__, 'add_auto_day']);
	    add_action('acf/init', [__CLASS__, 'add_auto_time']);
	    add_action('acf/init', [__CLASS__, 'add_auto_emails']);
	    add_action('acf/init', [__CLASS__, 'add_auto_email']);
	    add_action('acf/init', [__CLASS__, 'add_auto_email_none']);
	    add_action('acf/init', [__CLASS__, 'add_auto_email_on_gen']);

	    // Auto add/remove
	    self::add_tab('Almost notifications');
	    add_action('acf/init', [__CLASS__, 'add_almost_n']);
	    add_action('acf/init', [__CLASS__, 'add_almost']);
	    add_action('acf/init', [__CLASS__, 'add_almost_day']);
	    add_action('acf/init', [__CLASS__, 'add_almost_time']);
	    add_action('acf/init', [__CLASS__, 'add_almost_emails']);
	    add_action('acf/init', [__CLASS__, 'add_almost_email']);
	    add_action('acf/init', [__CLASS__, 'add_almost_email_none']);


	    // Redo cache and cron on save
	    add_filter('acf/save_post', [__CLASS__, 'reset'], 20);

	    // Fill exclude comic
	    add_filter(sprintf('acf/load_field/key=%s',
		    self::qualify_field(self::exclude_comic_years)),
		    [__CLASS__, 'fill_exclude_comic_years']
	    );
    }
    
    /**
     * Add the group.
     */
    static function add_group(): void {
        self::add_acf_group([
            'title' => sprintf('%s %s',
	            JKNAPI::space()->name(), JKNAPI::module()->name()),
            'location' => [
                [
                    [
                        'param' => 'options_page',
                        'operator' => '==',
                        'value' => JKNAPI::settings_page()->slug()
                    ],
                ],
            ],
            'menu_order' => 0,            
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
            'active' => 1,
            'description' => '',
        ]);
    }


	/*
	 * =========================================================================
	 * Exclude comic
	 * =========================================================================
	 */

	/**
	 * Add the 'exclude_comic' field.
	 */
	static function add_exclude_comic(): void {
		self::add_acf_field(self::exclude_comic, [
			'label'     => 'Exclude comic from staff contributions in all years',
			'type'      => 'true_false',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '50',
				'class' => '',
				'id' => '',
			],
			'ui' => 1,
			'default_value' => 1
		] );
	}

	/**
	 * Add the 'exclude_comic_years' field.
	 */
	static function add_exclude_comic_years(): void {
		self::add_acf_field(self::exclude_comic_years, [
			'label'     => 'Exclude comic only in these years',
			'type'      => 'select',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => [
				[
					[
						'field' => self::qualify_field(self::exclude_comic),
						'operator' => '!=',
						'value' => '1',
					]
				]
			],
			'wrapper' => [
				'width' => '50',
				'class' => '',
				'id' => '',
			],
			'multiple' => 1,
			'ui' => 1,
			'ajax' => 0
		] );
	}


	/*
	 * =========================================================================
	 * Auto add/remove
	 * =========================================================================
	 */

	/**
	 * Add the 'auto_add' field.
	 */
	static function add_auto_add(): void {
		self::add_acf_field(self::auto_add, [
			'label'     => 'Automatically add staff roles to eligible users?',
			'type'      => 'true_false',
			'instructions' => 'N.B. This only applies to the current year.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '33',
				'class' => '',
				'id' => '',
			],
			'ui' => 1,
			'default_value' => 1
		]);
	}

	/**
	 * Add the 'auto_remove' field.
	 */
	static function add_auto_remove(): void {
		self::add_acf_field(self::auto_remove, [
			'label'     => 'Automatically remove staff roles from ineligible users?',
			'type'      => 'true_false',
			'instructions' => 'N.B. This only applies to the current year.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '33',
				'class' => '',
				'id' => '',
			],
			'ui' => 1
		]);
	}

	/**
	 * Add the 'auto_on_gen' field.
	 */
	static function add_auto_on_gen(): void {
		self::add_acf_field(self::auto_on_gen, [
			'label'     => 'Also do these actions whenever the Staff Check' .
			               ' page is regenerated?',
			'type'      => 'true_false',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => [
				[
					[
						'field' => self::qualify_field(self::auto_add),
						'operator' => '==',
						'value' => '1',
					]
				],
				[
					[
						'field' => self::qualify_field(self::auto_remove),
						'operator' => '==',
						'value' => '1',
					]
				]
			],
			'wrapper' => [
				'width' => '33',
				'class' => '',
				'id' => '',
			],
			'ui' => 1
		]);
	}

	/**
	 * Add the 'auto_day' field.
	 */
	static function add_auto_day(): void {
		self::add_acf_field(self::auto_day, [
			'label'     => 'Day to auto add/remove',
			'type'      => 'select',
			'instructions' => '',
			'required' => 1,
			'conditional_logic' => [
				[
					[
						'field' => self::qualify_field(self::auto_add),
						'operator' => '==',
						'value' => '1',
					]
				],
				[
					[
						'field' => self::qualify_field(self::auto_remove),
						'operator' => '==',
						'value' => '1',
					]
				]
			],
			'wrapper' => [
				'width' => '15',
				'class' => '',
				'id' => '',
			],
			'choices' => [
				0   => 'Sunday',
				1   => 'Monday',
				2   => 'Tuesday',
				3   => 'Wednesday',
				4   => 'Thursday',
				5   => 'Friday',
				6   => 'Saturday'
			],
			'multiple' => 0,
			'ui' => 1,
			'ajax' => 0
		]);
	}

	/**
	 * Add the 'auto_time' field.
	 */
	static function add_auto_time(): void {
		self::add_acf_field(self::auto_time, [
			'label'     => 'Time to auto add/remove',
			'type'      => 'time_picker',
			'instructions' => '',
			'required' => 1,
			'conditional_logic' => [
				[
					[
						'field' => self::qualify_field(self::auto_add),
						'operator' => '==',
						'value' => '1',
					]
				],
				[
					[
						'field' => self::qualify_field(self::auto_remove),
						'operator' => '==',
						'value' => '1',
					]
				]
			],
			'wrapper' => [
				'width' => '15',
				'class' => '',
				'id' => '',
			],
			'display_format' => 'g:i a',
			'return_format' => 'H:i',
		]);
	}

	/**
	 * Add the 'auto_emails' field.
	 */
	static function add_auto_emails(): void {
		self::add_acf_field(self::auto_emails, [
			'label'     => 'Send a report to the following emails',
			'type'      => 'repeater',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => [
				[
					[
						'field' => self::qualify_field(self::auto_add),
						'operator' => '==',
						'value' => '1',
					]
				],
				[
					[
						'field' => self::qualify_field(self::auto_remove),
						'operator' => '==',
						'value' => '1',
					]
				]
			],
			'wrapper' => [
				'width' => '30',
				'class' => '',
				'id' => '',
			],
			'collapsed' => '',
			'min' => '',
			'max' => '',
			'layout' => 'row',
			'button_label' => 'Add email'
		]);
	}

	/**
	 * Add the 'auto_email' field.
	 */
	static function add_auto_email(): void {
		self::add_acf_inner_field(self::auto_emails, self::auto_email, [
			'label'     => '',
			'type'      => 'text',
			'instructions' => '',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			],
			'default_value' => '',
			'placeholder' => 'someone@themedium.ca',
			'prepend' => '',
			'append' => '',
			'maxlength' => '',
		]);
	}

	/**
	 * Add the 'auto_email_none' field.
	 */
	static function add_auto_email_none(): void {
		self::add_acf_field(self::auto_email_none, [
			'label'     => 'Email even when no changes are made?',
			'type'      => 'true_false',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => [
				[
					[
						'field' => self::qualify_field(self::auto_add),
						'operator' => '==',
						'value' => '1',
					]
				],
				[
					[
						'field' => self::qualify_field(self::auto_remove),
						'operator' => '==',
						'value' => '1',
					]
				]
			],
			'wrapper' => [
				'width' => '20',
				'class' => '',
				'id' => '',
			],
			'ui' => 1
		]);
	}

	/**
	 * Add the 'auto_email_on_gen' field.
	 */
	static function add_auto_email_on_gen(): void {
		self::add_acf_field(self::auto_email_on_gen, [
			'label'     => 'Send an email when this is done during generation?',
			'type'      => 'true_false',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => [
				[
					[
						'field' => self::qualify_field(self::auto_on_gen),
						'operator' => '==',
						'value' => '1',
					]
				]
			],
			'wrapper' => [
				'width' => '20',
				'class' => '',
				'id' => '',
			],
			'ui' => 1
		]);
	}

	/*
	 * =========================================================================
	 * Almost notifications
	 * =========================================================================
	 */

	/**
	 * Add the 'almost_n' field.
	 */
	static function add_almost_n(): void {
		self::add_acf_field(self::almost_n, [
			'label'     => 'How close to qualifying for voting or for Staff' .
			               ' Writer/Photographer should be called "almost"?',
			'type'      => 'number',
			'instructions' => 'Default number of contributions short: 2',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '30',
				'class' => '',
				'id' => '',
			],
			'default_value' => 2,
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
			'min' => 1,
			'max' => '5',
			'step' => 1,
		] );
	}

	/**
	 * Add the 'almost' field.
	 */
	static function add_almost(): void {
		self::add_acf_field(self::almost, [
			'label'     => 'Send a weekly notification about users who are'
				. ' about to qualify for staff/voting?',
			'type'      => 'true_false',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '70',
				'class' => '',
				'id' => '',
			],
			'ui' => 1
		] );
	}

	/**
	 * Add the 'almost_day' field.
	 */
	static function add_almost_day(): void {
		self::add_acf_field(self::almost_day, [
			'label'     => 'Day to notify',
			'type'      => 'select',
			'instructions' => '',
			'required' => 1,
			'conditional_logic' => [
				[
					[
						'field' => self::qualify_field(self::almost),
						'operator' => '==',
						'value' => '1',
					]
				]
			],
			'wrapper' => [
				'width' => '15',
				'class' => '',
				'id' => '',
			],
			'choices' => [
				0   => 'Sunday',
				1   => 'Monday',
				2   => 'Tuesday',
				3   => 'Wednesday',
				4   => 'Thursday',
				5   => 'Friday',
				6   => 'Saturday'
			],
			'multiple' => 0,
			'ui' => 1,
			'ajax' => 0
		]);
	}

	/**
	 * Add the 'almost_time' field.
	 */
	static function add_almost_time(): void {
		self::add_acf_field(self::almost_time, [
			'label'     => 'Time to notify',
			'type'      => 'time_picker',
			'instructions' => '',
			'required' => 1,
			'conditional_logic' => [
				[
					[
						'field' => self::qualify_field(self::almost),
						'operator' => '==',
						'value' => '1',
					]
				]
			],
			'wrapper' => [
				'width' => '15',
				'class' => '',
				'id' => '',
			],
			'display_format' => 'g:i a',
			'return_format' => 'H:i',
		]);
	}

	/**
	 * Add the 'almost_emails' field.
	 */
	static function add_almost_emails(): void {
		self::add_acf_field(self::almost_emails, [
			'label'     => 'Send a report to the following emails',
			'type'      => 'repeater',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => [
				[
					[
						'field' => self::qualify_field(self::almost),
						'operator' => '==',
						'value' => '1',
					]
				]
			],
			'wrapper' => [
				'width' => '30',
				'class' => '',
				'id' => '',
			],
			'collapsed' => '',
			'min' => 1,
			'max' => 10,
			'layout' => 'row',
			'button_label' => 'Add email'
		]);
	}

	/**
	 * Add the 'almost_email' field.
	 */
	static function add_almost_email(): void {
		self::add_acf_inner_field(self::almost_emails, self::almost_email, [
			'label'     => '',
			'type'      => 'text',
			'instructions' => '',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			],
			'default_value' => '',
			'placeholder' => 'someone@themedium.ca',
			'prepend' => '',
			'append' => '',
			'maxlength' => '',
		]);
	}



	/**
	 * Add the 'almost_email_none' field.
	 */
	static function add_almost_email_none(): void {
		self::add_acf_field(self::almost_email_none, [
			'label'     => 'Email even when no one is near the threshold?',
			'type'      => 'true_false',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => [
				[
					[
						'field' => self::qualify_field(self::almost),
						'operator' => '==',
						'value' => '1',
					]
				]
			],
			'wrapper' => [
				'width' => '20',
				'class' => '',
				'id' => '',
			],
			'ui' => 1
		]);
	}


	/*
	 * =========================================================================
	 * Save post actions
	 * =========================================================================
	 */

    /**
     * Trigger a reset when the settings page is saved.
     *
     * @param string $pid The post ID. Unused since field key establishes post.
     */
    static function reset(string $pid): void {

        // Bail early if this isn't our page
        $field_key = self::qualify_field(self::almost);
        if (!isset($_POST['acf'][$field_key])) return;

        MJKSC_Almost::reset_schedule();
	    MJKSC_Update::reset_schedule();
    }


    /*
     * =========================================================================
     * Field filling
     * =========================================================================
     */

	/**
	 * Fill the exclude comic dropdown with academic years.
	 *
	 * @param array $field
	 * @return array
	 */
	static function fill_exclude_comic_years(array $field): array {
		$field['choices'] = [];

		// Year of earliest post to year of latest masthead
		$start_year = MJKSC_API::earliest_year()->year();
		$end_year = MJKMHAPI::newest_year()->year();
		$ac_years = MJKCommonTools::academic_years($start_year, $end_year);

		foreach ($ac_years as $ac_year) {
			$format = $ac_year->format();
			$field['choices'][$format] = $format;
		}

		return $field;
	}
}
