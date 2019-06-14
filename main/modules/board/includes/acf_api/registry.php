<?php
/**
 * Creates the ACF field groups for the board of directors page.
 */
final class MJKBod_ACF extends JKNACF {

	/**
	 * Define the type.
	 *
	 * @return string
	 */
	static function type(): string { return self::LOCATION_OPTIONS; }

    // Fields
	const constitution      = 'constitution';

	const audits            = 'audits';
	const audit             = 'audit';
	const audit_ye          = 'audit_ye';

	const meetings          = 'meetings';
	const meeting_date      = 'meeting_date';
	const meeting_agenda    = 'meeting_agenda';
	const meeting_minutes   = 'meeting_minutes';
	const meeting_quorum    = 'meeting_quorum';

	const agms              = 'agms';
	const agm_date          = 'agm_date';
	const agm_agenda        = 'agm_agenda';
	const agm_minutes       = 'agm_minutes';
	const agm_quorum        = 'agm_quorum';

	const sgms              = 'sgms';
	const sgm_date          = 'sgm_date';
	const sgm_agenda        = 'sgm_agenda';
	const sgm_minutes       = 'sgm_minutes';
	const sgm_quorum        = 'sgm_quorum';

	/**
	 * Register the group and fields.
	 */
    static function add_filters(): void {

        // Group
        add_action('acf/init', [__CLASS__, 'add_group']);

        // Constitution
	    self::add_tab('Constitution');
	    add_action('acf/init', [__CLASS__, 'add_constitution']);

	    // Audits
	    self::add_tab('Audits');
	    add_action('acf/init', [__CLASS__, 'add_audits']);
	    add_action('acf/init', [__CLASS__, 'add_audit']);
	    add_action('acf/init', [__CLASS__, 'add_audit_ye']);

	    // Board Meetings
	    self::add_tab('Board Meetings');
	    add_action('acf/init', [__CLASS__, 'add_meetings']);
	    add_action('acf/init', [__CLASS__, 'add_meeting_date']);
	    add_action('acf/init', [__CLASS__, 'add_meeting_agenda']);
	    add_action('acf/init', [__CLASS__, 'add_meeting_minutes']);
	    add_action('acf/init', [__CLASS__, 'add_meeting_quorum']);

	    // AGMs
	    self::add_tab('AGMs');
	    add_action('acf/init', [__CLASS__, 'add_agms']);
	    add_action('acf/init', [__CLASS__, 'add_agm_date']);
	    add_action('acf/init', [__CLASS__, 'add_agm_agenda']);
	    add_action('acf/init', [__CLASS__, 'add_agm_minutes']);
	    add_action('acf/init', [__CLASS__, 'add_agm_quorum']);

	    // SGMs
	    self::add_tab('SGMs');
	    add_action('acf/init', [__CLASS__, 'add_sgms']);
	    add_action('acf/init', [__CLASS__, 'add_sgm_date']);
	    add_action('acf/init', [__CLASS__, 'add_sgm_agenda']);
	    add_action('acf/init', [__CLASS__, 'add_sgm_minutes']);
	    add_action('acf/init', [__CLASS__, 'add_sgm_quorum']);
    }

	/**
	 * Add the group.
	 */
    static function add_group(): void {
        self::add_acf_group([
            'title' => sprintf('%s: %s',
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
            'style' => 'seamless',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
            'active' => 1,
            'description' => '',
        ]);
    }

	/**
	 * Add the 'constitution' field.
	 */
	static function add_constitution(): void {
		self::add_acf_field(self::constitution, [
			'label' => 'Constitution & By-Laws',
			'type' => 'file',
			'instructions' => 'Choose or upload the constitution & by-laws pdf.',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			],
			'return_format' => 'id',
			'library' => 'all',
			'mime_types' => 'pdf',
		]);
	}

	/**
	 * Add the 'audits' field.
	 */
	static function add_audits(): void {
		self::add_acf_field(self::audits, [
			'label' => 'Audited Financial Statements',
			'type' => 'repeater',
			'instructions' => 'Add audited financial statements with year-end dates.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			],
			'min' => 0,
			'max' => 0,
			'layout' => 'table',
			'button_label' => 'Add year',
		]);
	}

	/**
	 * Add the 'audit' field.
	 */
	static function add_audit(): void {
		self::add_acf_inner_field(self::audits, self::audit, [
			'label' => 'Statement',
			'type' => 'file',
			'instructions' => 'Choose / upload the audit statement pdf.',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			],
			'return_format' => 'id',
			'library' => 'all',
			'min_size' => '',
			'max_size' => '',
			'mime_types' => 'pdf',
		]);
	}

	/**
	 * Add the 'audit_ye' field.
	 */
	static function add_audit_ye(): void {
		self::add_acf_inner_field(self::audits, self::audit_ye, [
			'label' => 'Year-end',
			'type' => 'date_picker',
			'instructions' => 'Pick the year-end. E.g. April 30, 2018',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			],
			'display_format' => 'F j, Y',
			'return_format' => 'F j, Y',
			'first_day' => 1,
		]);
	}

	/**
	 * Add the 'meetings' field.
	 */
	static function add_meetings(): void {
		self::add_acf_field(self::meetings, [
			'label' => 'Board Meetings',
			'type' => 'repeater',
			'instructions' => 'Add board of directors meetings from most' .
			                  ' recent to oldest. For quorum, if the meeting' .
			                  ' hasn\'t happened yet, just update it later' .
			                  ' if quorum wasn\'t reached. It won\'t display' .
			                  ' either way until the day after the meeting.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			],
			'min' => 0,
			'max' => 0,
			'layout' => 'table',
			'button_label' => 'Add meeting',
		]);
	}

	/**
	 * Add the 'meeting_date' field.
	 */
	static function add_meeting_date(): void {
		self::add_acf_inner_field(self::meetings, self::meeting_date, [
			'label' => 'Meeting date',
			'type' => 'date_picker',
			'instructions' => 'Pick the date of the meeting.',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			],
			'display_format' => 'F j, Y',
			'return_format' => 'F j, Y',
			'first_day' => 1,
		]);
	}

	/**
	 * Add the 'meeting_agenda' field.
	 */
	static function add_meeting_agenda(): void {
		self::add_acf_inner_field(self::meetings, self::meeting_agenda, [
			'label' => 'Agenda',
			'type' => 'file',
			'instructions' => 'Choose / upload the agenda pdf.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			],
			'return_format' => 'id',
			'library' => 'all',
			'min_size' => '',
			'max_size' => '',
			'mime_types' => 'pdf',
		]);
	}

	/**
	 * Add the 'meeting_minutes' field.
	 */
	static function add_meeting_minutes(): void {
		self::add_acf_inner_field(self::meetings, self::meeting_minutes, [
			'label' => 'Minutes',
			'type' => 'file',
			'instructions' => 'Choose / upload the meeting minutes as a pdf.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			],
			'return_format' => 'id',
			'library' => 'all',
			'min_size' => '',
			'max_size' => '',
			'mime_types' => 'pdf',
		]);
	}

	/**
	 * Add the 'meeting_quorum' field.
	 */
	static function add_meeting_quorum(): void {
		self::add_acf_inner_field(self::meetings, self::meeting_quorum, [
			'label' => 'Quorum',
			'name' => 'quorum',
			'type' => 'true_false',
			'instructions' => 'Was quorum reached?',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			],
			'message' => '',
			'default_value' => 1,
			'ui' => 1,
		]);
	}

	/**
	 * Add the 'agms' field.
	 */
	static function add_agms(): void {
		self::add_acf_field(self::agms, [
			'label' => 'Annual General Meetings',
			'type'  => 'repeater',
			'instructions' => 'Add annual general meetings from most' .
			                  ' recent to oldest. For quorum, if the meeting' .
			                  ' hasn\'t happened yet, just update it later' .
			                  ' if quorum wasn\'t reached. It won\'t display' .
			                  ' either way until the day after the meeting.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			],
			'min' => 0,
			'max' => 0,
			'layout' => 'table',
			'button_label' => 'Add meeting',
		]);
	}

	/**
	 * Add the 'agm_date' field.
	 */
	static function add_agm_date(): void {
		self::add_acf_inner_field(self::agms, self::agm_date, [
			'label' => 'Meeting date',
			'type' => 'date_picker',
			'instructions' => 'Pick the date of the meeting.',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			],
			'display_format' => 'F j, Y',
			'return_format' => 'F j, Y',
			'first_day' => 1,
		]);
	}

	/**
	 * Add the 'agm_agenda' field.
	 */
	static function add_agm_agenda(): void {
		self::add_acf_inner_field(self::agms, self::agm_agenda, [
			'label' => 'Agenda',
			'type' => 'file',
			'instructions' => 'Choose / upload the agenda pdf.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			],
			'return_format' => 'id',
			'library' => 'all',
			'min_size' => '',
			'max_size' => '',
			'mime_types' => 'pdf',
		]);
	}

	/**
	 * Add the 'agm_minutes' field.
	 */
	static function add_agm_minutes(): void {
		self::add_acf_inner_field(self::agms, self::agm_minutes, [
			'label' => 'Minutes',
			'type' => 'file',
			'instructions' => 'Choose / upload the meeting minutes as a pdf.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			],
			'return_format' => 'id',
			'library' => 'all',
			'min_size' => '',
			'max_size' => '',
			'mime_types' => 'pdf',
		]);
	}

	/**
	 * Add the 'agm_quorum' field.
	 */
	static function add_agm_quorum(): void {
		self::add_acf_inner_field(self::agms, self::agm_quorum, [
			'label' => 'Quorum',
			'name' => 'quorum',
			'type' => 'true_false',
			'instructions' => 'Was quorum reached?',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			],
			'message' => '',
			'default_value' => 1,
			'ui' => 1,
		]);
	}

	/**
	 * Add the 'sgms' field.
	 */
	static function add_sgms(): void {
		self::add_acf_field(self::sgms, [
			'label' => 'Special General Meetings',
			'type'  => 'repeater',
			'instructions' => 'Add special general meetings from most' .
			                  ' recent to oldest. For quorum, if the meeting' .
			                  ' hasn\'t happened yet, just update it later' .
			                  ' if quorum wasn\'t reached. It won\'t display' .
			                  ' either way until the day after the meeting.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			],
			'min' => 0,
			'max' => 0,
			'layout' => 'table',
			'button_label' => 'Add meeting',
		]);
	}

	/**
	 * Add the 'sgm_date' field.
	 */
	static function add_sgm_date(): void {
		self::add_acf_inner_field(self::sgms, self::sgm_date, [
			'label' => 'Meeting date',
			'type' => 'date_picker',
			'instructions' => 'Pick the date of the meeting.',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			],
			'display_format' => 'F j, Y',
			'return_format' => 'F j, Y',
			'first_day' => 1,
		]);
	}

	/**
	 * Add the 'sgm_agenda' field.
	 */
	static function add_sgm_agenda(): void {
		self::add_acf_inner_field(self::sgms, self::sgm_agenda, [
			'label' => 'Agenda',
			'type' => 'file',
			'instructions' => 'Choose / upload the agenda pdf.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			],
			'return_format' => 'id',
			'library' => 'all',
			'min_size' => '',
			'max_size' => '',
			'mime_types' => 'pdf',
		]);
	}

	/**
	 * Add the 'sgm_minutes' field.
	 */
	static function add_sgm_minutes(): void {
		self::add_acf_inner_field(self::sgms, self::sgm_minutes, [
			'label' => 'Minutes',
			'type' => 'file',
			'instructions' => 'Choose / upload the meeting minutes as a pdf.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			],
			'return_format' => 'id',
			'library' => 'all',
			'min_size' => '',
			'max_size' => '',
			'mime_types' => 'pdf',
		]);
	}

	/**
	 * Add the 'sgm_quorum' field.
	 */
	static function add_sgm_quorum(): void {
		self::add_acf_inner_field(self::sgms, self::sgm_quorum, [
			'label' => 'Quorum',
			'name' => 'quorum',
			'type' => 'true_false',
			'instructions' => 'Was quorum reached?',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			],
			'message' => '',
			'default_value' => 1,
			'ui' => 1,
		]);
	}
}
