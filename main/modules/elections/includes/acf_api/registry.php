<?php
/**
 * Creates the ACF field groups for the elections page.
 */
final class MJKElections_ACF extends JKNACF {

	/**
	 * Define the type.
	 *
	 * @return string
	 */
	static function type(): string { return self::LOCATION_OPTIONS; }

    // Fields
	const bods                  = 'bods';
	const bod_date              = 'bod_date';
	const bod_finished          = 'bod_finished';
	const bod_ws                = 'bod_ws';
	const bod_w_name            = 'bod_w_name';
	const bod_vacant            = 'bod_vacant';
	const bod_form              = 'bod_form';
	const bod_nom_start         = 'bod_nom_start';
	const bod_nom_end           = 'bod_nom_end';
	const bod_statements_day    = 'bod_statements_day';
	const bod_voting_start      = 'bod_voting_start';
	const bod_voting_end        = 'bod_voting_end';
	const bod_results_day       = 'bod_results_day';

	const ebs                   = 'ebs';
	const eb_date               = 'eb_date';
	const eb_finished           = 'eb_finished';
	const eb_ws                 = 'eb_ws';
	const eb_w_name             = 'eb_w_name';
	const eb_w_role             = 'eb_w_role';
	const eb_form               = 'eb_form';
	const eb_nom_start          = 'eb_nom_start';
	const eb_nom_end            = 'eb_nom_end';
	const eb_forum_day          = 'eb_forum_day';
	const eb_voting_day         = 'eb_voting_day';
	const eb_results_day        = 'eb_results_day';


	/**
	 * Register the group and fields.
	 */
    static function add_filters(): void {

        // Group
        add_action('acf/init', [__CLASS__, 'add_group']);

        // Board of Directors
	    self::add_tab('Board of Directors Elections');
	    add_action('acf/init', [__CLASS__, 'add_bods']);
	    add_action('acf/init', [__CLASS__, 'add_bod_date']);
	    add_action('acf/init', [__CLASS__, 'add_bod_finished']);
	    add_action('acf/init', [__CLASS__, 'add_bod_ws']);
	    add_action('acf/init', [__CLASS__, 'add_bod_w_name']);
	    add_action('acf/init', [__CLASS__, 'add_bod_vacant']);
	    add_action('acf/init', [__CLASS__, 'add_bod_form']);
	    add_action('acf/init', [__CLASS__, 'add_bod_nom_start']);
	    add_action('acf/init', [__CLASS__, 'add_bod_nom_end']);
	    add_action('acf/init', [__CLASS__, 'add_bod_statements_day']);
	    add_action('acf/init', [__CLASS__, 'add_bod_voting_start']);
	    add_action('acf/init', [__CLASS__, 'add_bod_voting_end']);
	    add_action('acf/init', [__CLASS__, 'add_bod_results_day']);

	    // Editorial Board
	    self::add_tab('Editorial Board Elections');
	    add_action('acf/init', [__CLASS__, 'add_ebs']);
	    add_action('acf/init', [__CLASS__, 'add_eb_date']);
	    add_action('acf/init', [__CLASS__, 'add_eb_finished']);
	    add_action('acf/init', [__CLASS__, 'add_eb_ws']);
	    add_action('acf/init', [__CLASS__, 'add_eb_w_name']);
	    add_action('acf/init', [__CLASS__, 'add_eb_w_role']);
	    add_action('acf/init', [__CLASS__, 'add_eb_form']);
	    add_action('acf/init', [__CLASS__, 'add_eb_nom_start']);
	    add_action('acf/init', [__CLASS__, 'add_eb_nom_end']);
	    add_action('acf/init', [__CLASS__, 'add_eb_forum_day']);
	    add_action('acf/init', [__CLASS__, 'add_eb_voting_day']);
	    add_action('acf/init', [__CLASS__, 'add_eb_results_day']);
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
            'role' => 'normal',
            'style' => 'seamless',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
            'active' => 1,
            'description' => '',
        ]);
    }

	/*
	 * =========================================================================
	 * Board of Directors
	 * =========================================================================
	 */

	/**
	 * Add the 'bods' field.
	 */
	static function add_bods(): void {
		self::add_acf_field(self::bods, [
			'label' => 'Board of Directors Elections',
			'type' => 'repeater',
			'instructions' => 'Enter all board of directors elections from' .
			                  ' most recent to oldest.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			],
			'collapsed' => '',
			'min' => 0,
			'max' => 0,
			'layout' => 'block',
			'button_label' => 'Add election',
		]);
	}

	/**
	 * Add the 'bod_date' field.
	 */
	static function add_bod_date(): void {
		self::add_acf_inner_field(self::bods, self::bod_date, [
			'label' => 'Date',
			'type' => 'text',
			'instructions' => 'Enter the month of the election, e.g. March 2018.',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			],
		]);
	}

	/**
	 * Add the 'bod_finished' field.
	 */
	static function add_bod_finished(): void {
		self::add_acf_inner_field(self::bods, self::bod_finished, [
			'label' => 'Finished',
			'type' => 'true_false',
			'instructions' => 'Is the election finished?',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			],
			'default_value' => 0,
			'ui' => 1,
		]);
	}

	/**
	 * Add the 'bod_ws' field.
	 */
	static function add_bod_ws(): void {
		self::add_acf_inner_field(self::bods, self::bod_ws, [
			'label' => 'ws',
			'type' => 'repeater',
			'instructions' => 'Enter the names of the ws.',
			'required' => 0,
			'conditional_logic' => [
				[
					[
						'field' => self::qualify_field(self::bod_finished),
						'operator' => '==',
						'value' => '1',
					],
				],
			],
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			],
			'min' => 0,
			'max' => 0,
			'layout' => 'table',
			'button_label' => 'Add w',
		]);
	}

	/**
	 * Add the 'bod_w_name' field.
	 */
	static function add_bod_w_name(): void {
		self::add_acf_inner_field(self::bod_ws, self::bod_w_name, [
			'label' => 'Winning candidate',
			'type' => 'text',
			'instructions' => 'Enter a name.',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			],
		]);
	}

	/**
	 * Add the 'bod_vacant' field.
	 */
	static function add_bod_vacant(): void {
		self::add_acf_inner_field(self::bods, self::bod_vacant, [
			'label' => 'Vacant seats',
			'type' => 'number',
			'instructions' => '',
			'required' => 1,
			'conditional_logic' => [
				[
					[
						'field' => self::qualify_field(self::bod_finished),
						'operator' => '!=',
						'value' => '1',
					],
				],
			],
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			],
			'min' => 1,
			'max' => 5,
			'step' => 1,
		]);
	}

	/**
	 * Add the 'bod_form' field.
	 */
	static function add_bod_form(): void {
		self::add_acf_inner_field(self::bods, self::bod_form, [
			'label' => 'Nomination form',
			'type' => 'file',
			'instructions' => 'Upload the nomination form as a pdf.',
			'required' => 1,
			'conditional_logic' => [
				[
					[
						'field' => self::qualify_field(self::bod_finished),
						'operator' => '!=',
						'value' => '1',
					],
				],
			],
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			],
			'return_format' => 'id',
			'library' => 'all',
			'min_size' => '',
			'max_size' => '',
			'mime_types' => '.pdf',
		]);
	}

	/**
	 * Add the 'bod_nom_start' field.
	 */
	static function add_bod_nom_start(): void {
		self::add_acf_inner_field(self::bods, self::bod_nom_start, [
			'label' => 'Nomination period start',
			'type' => 'text',
			'instructions' => 'e.g. March 2 @ 9 a.m.',
			'required' => 1,
			'conditional_logic' => [
				[
					[
						'field' => self::qualify_field(self::bod_finished),
						'operator' => '!=',
						'value' => '1',
					],
				],
			],
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			]
		]);
	}

	/**
	 * Add the 'bod_nom_end' field.
	 */
	static function add_bod_nom_end(): void {
		self::add_acf_inner_field(self::bods, self::bod_nom_end, [
			'label' => 'Nomination period end',
			'type' => 'text',
			'instructions' => 'e.g. March 22 @ 11 p.m.',
			'required' => 1,
			'conditional_logic' => [
				[
					[
						'field' => self::qualify_field(self::bod_finished),
						'operator' => '!=',
						'value' => '1',
					],
				],
			],
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			],
		]);
	}

	/**
	 * Add the 'bod_statements_day' field.
	 */
	static function add_bod_statements_day(): void {
		self::add_acf_inner_field(self::bods, self::bod_statements_day, [
			'label' => 'Candidate statements published',
			'type' => 'text',
			'instructions' => 'e.g. March 23',
			'required' => 1,
			'conditional_logic' => [
				[
					[
						'field' => self::qualify_field(self::bod_finished),
						'operator' => '!=',
						'value' => '1',
					],
				],
			],
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			],
		]);
	}

	/**
	 * Add the 'bod_voting_start' field.
	 */
	static function add_bod_voting_start(): void {
		self::add_acf_inner_field(self::bods, self::bod_voting_start, [
			'label' => 'Voting period start',
			'type' => 'text',
			'instructions' => 'March 23 @ 9 a.m.',
			'required' => 1,
			'conditional_logic' => [
				[
					[
						'field' => self::qualify_field(self::bod_finished),
						'operator' => '!=',
						'value' => '1',
					],
				],
			],
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			],
		]);
	}

	/**
	 * Add the 'bod_voting_end' field.
	 */
	static function add_bod_voting_end(): void {
		self::add_acf_inner_field(self::bods, self::bod_voting_end, [
			'label' => 'Voting period end',
			'type' => 'text',
			'instructions' => 'e.g. March 29 @ 6 p.m.',
			'required' => 1,
			'conditional_logic' => [
				[
					[
						'field' => self::qualify_field(self::bod_finished),
						'operator' => '!=',
						'value' => '1',
					],
				],
			],
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			],
		]);
	}

	/**
	 * Add the 'bod_results_day' field.
	 */
	static function add_bod_results_day(): void {
		self::add_acf_inner_field(self::bods, self::bod_results_day, [
			'label' => 'Results announced date',
			'type' => 'text',
			'instructions' => 'e.g. March 30',
			'required' => 1,
			'conditional_logic' => [
				[
					[
						'field' => self::qualify_field(self::bod_finished),
						'operator' => '!=',
						'value' => '1',
					],
				],
			],
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			],
		]);
	}


	/*
	 * =========================================================================
	 * Editorial Board
	 * =========================================================================
	 */

	/**
	 * Add the 'ebs' field.
	 */
	static function add_ebs(): void {
		self::add_acf_field(self::ebs, [
			'label' => 'Editorial Board Elections',
			'type' => 'repeater',
			'instructions' => 'Enter all editorial board elections' .
			                  ' from most recent to oldest.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			],
			'collapsed' => '',
			'min' => 0,
			'max' => 0,
			'layout' => 'block',
			'button_label' => 'Add election',
		]);
	}

	/**
	 * Add the 'eb_date' field.
	 */
	static function add_eb_date(): void {
		self::add_acf_inner_field(self::ebs, self::eb_date, [
			'label' => 'Date',
			'type' => 'text',
			'instructions' => 'Enter the month of the election, e.g. March 2018.',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			],
		]);
	}

	/**
	 * Add the 'eb_finished' field.
	 */
	static function add_eb_finished(): void {
		self::add_acf_inner_field(self::ebs, self::eb_finished, [
			'label' => 'Finished',
			'type' => 'true_false',
			'instructions' => 'Is the election finished?',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			],
			'message' => '',
			'default_value' => 0,
			'ui' => 1,
		]);
	}

	/**
	 * Add the 'eb_ws' field.
	 */
	static function add_eb_ws(): void {
		self::add_acf_inner_field(self::ebs, self::eb_ws, [
			'label' => 'ws',
			'type' => 'repeater',
			'instructions' => 'Enter the names of the ws.',
			'required' => 0,
			'conditional_logic' => [
				[
					[
						'field' => self::qualify_field(self::eb_finished),
						'operator' => '==',
						'value' => '1',
					],
				],
			],
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			],
			'min' => 0,
			'max' => 0,
			'layout' => 'table',
			'button_label' => 'Add w',
		]);
	}

	/**
	 * Add the 'eb_w_name' field.
	 */
	static function add_eb_w_name(): void {
		self::add_acf_inner_field(self::eb_ws, self::eb_w_name, [
			'label' => 'Winning candidate',
			'type' => 'text',
			'instructions' => 'Enter a name.',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			],
		]);
	}

	/**
	 * Add the 'eb_w_role' field.
	 */
	static function add_eb_w_role(): void {
		self::add_acf_inner_field(self::eb_ws, self::eb_w_role, [
			'label' => 'role',
			'type' => 'select',
			'instructions' => '',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			],
			'choices' => [
				'Editor-in-Chief' => 'Editor-in-Chief',
				'News Editor' => 'News Editor',
				'Arts Editor' => 'Arts Editor',
				'Features Editor' => 'Features Editor',
				'Sports Editor' => 'Sports Editor',
				'Photo Editor' => 'Photo Editor',
			],
			'default_value' => [],
			'allow_null' => 0,
			'multiple' => 0,
			'ui' => 1,
			'ajax' => 0,
			'placeholder' => '',
			'disabled' => 0,
			'readonly' => 0,
			'return_format' => 'value',
		]);
	}

	/**
	 * Add the 'eb_form' field.
	 */
	static function add_eb_form(): void {
		self::add_acf_inner_field(self::ebs, self::eb_form, [
			'label' => 'Nomination form',
			'type' => 'file',
			'instructions' => 'Upload the nomination form as a pdf.',
			'required' => 1,
			'conditional_logic' => [
				[
					[
						'field' => self::qualify_field(self::eb_finished),
						'operator' => '!=',
						'value' => '1',
					],
				],
			],
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			],
			'return_format' => 'id',
			'library' => 'all',
			'min_size' => '',
			'max_size' => '',
			'mime_types' => '.pdf',
		]);
	}

	/**
	 * Add the 'eb_nom_start' field.
	 */
	static function add_eb_nom_start(): void {
		self::add_acf_inner_field(self::ebs, self::eb_nom_start, [
			'label' => 'Nomination period start',
			'type' => 'text',
			'instructions' => 'e.g. March 5 @ 9 a.m.',
			'required' => 1,
			'conditional_logic' => [
				[
					[
						'field' => self::qualify_field(self::eb_finished),
						'operator' => '!=',
						'value' => '1',
					],
				],
			],
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			],
		]);
	}

	/**
	 * Add the 'eb_nom_end' field.
	 */
	static function add_eb_nom_end(): void {
		self::add_acf_inner_field(self::ebs, self::eb_nom_end, [
			'label' => 'Nomination period end',
			'type' => 'text',
			'instructions' => 'e.g. March 26 @ 11 p.m.',
			'required' => 1,
			'conditional_logic' => [
				[
					[
						'field' => self::qualify_field(self::eb_finished),
						'operator' => '!=',
						'value' => '1',
					],
				],
			],
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			],
		]);
	}

	/**
	 * Add the 'eb_forum_day' field.
	 */
	static function add_eb_forum_day(): void {
		self::add_acf_inner_field(self::ebs, self::eb_forum_day, [
			'label' => 'Candidates\' forum date',
			'type' => 'text',
			'instructions' => 'e.g. March 27 @ 5 p.m.',
			'required' => 1,
			'conditional_logic' => [
				[
					[
						'field' => self::qualify_field(self::eb_finished),
						'operator' => '!=',
						'value' => '1',
					],
				],
			],
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			],
		]);
	}

	/**
	 * Add the 'eb_voting_day' field.
	 */
	static function add_eb_voting_day(): void {
		self::add_acf_inner_field(self::ebs, self::eb_voting_day, [
			'label' => 'Voting day',
			'type' => 'text',
			'instructions' => 'e.g. March 29 @ 10 a.m. — 6 p.m.',
			'required' => 1,
			'conditional_logic' => [
				[
					[
						'field' => self::qualify_field(self::eb_finished),
						'operator' => '!=',
						'value' => '1',
					],
				],
			],
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			],
		]);
	}

	/**
	 * Add the 'eb_results_day' field.
	 */
	static function add_eb_results_day(): void {
		self::add_acf_inner_field(self::ebs, self::eb_results_day, [
			'label' => 'Results announced date',
			'type' => 'text',
			'instructions' => 'e.g. March 30',
			'required' => 1,
			'conditional_logic' => [
				[
					[
						'field' => self::qualify_field(self::eb_finished),
						'operator' => '!=',
						'value' => '1',
					],
				],
			],
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			],
		]);
	}
}
